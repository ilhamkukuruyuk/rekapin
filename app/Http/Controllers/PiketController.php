<?php

namespace App\Http\Controllers;

use App\Models\PiketSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PiketController extends Controller
{
    public function memberIndex(Request $request): View
    {
        PiketSchedule::syncPastSchedules();
        $user = $request->user();
        $routePrefix = $user->role === 'presbem' ? 'presbem' : 'anggota';

        return view('piket.member', [
            'layout' => $routePrefix === 'presbem' ? 'layouts.presbem' : 'layouts.anggota',
            'routePrefix' => $routePrefix,
            'schedules' => PiketSchedule::query()
                ->where('user_id', $user->id)
                ->orderByDesc('date')
                ->orderByDesc('start_time')
                ->get(),
        ]);
    }

    public function managementIndex(): View
    {
        PiketSchedule::syncPastSchedules();

        return view('bendahara.jadwal-piket', [
            'members' => User::query()
                ->where('role', 'anggota')
                ->orderBy('name')
                ->get(['id', 'name', 'nim', 'departemen']),
            'schedules' => PiketSchedule::query()
                ->with('member:id,name,nim,departemen')
                ->orderByDesc('date')
                ->orderByDesc('start_time')
                ->get(),
        ]);
    }

    public function attendanceIndex(): View
    {
        PiketSchedule::syncPastSchedules();

        return view('bendahara.presensi-piket', [
            'schedules' => PiketSchedule::query()
                ->with('member:id,name,nim,departemen')
                ->orderByDesc('date')
                ->orderByDesc('start_time')
                ->get(),
        ]);
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'location' => ['required', 'string', 'max:255'],
        ], [
            'date.after_or_equal' => 'Tanggal piket tidak boleh di masa lalu.',
        ]);

        if ($data['end_time'] <= $data['start_time']) {
            throw ValidationException::withMessages([
                'end_time' => 'Jam selesai harus setelah jam mulai pada hari yang sama.',
            ]);
        }

        $member = User::query()->findOrFail($data['user_id']);
        if ($member->role !== 'anggota') {
            throw ValidationException::withMessages([
                'user_id' => 'Jadwal hanya dapat diberikan kepada anggota.',
            ]);
        }

        DB::transaction(function () use ($data, $request, $member): void {
            User::query()->whereKey($member->id)->lockForUpdate()->firstOrFail();

            $hasConflict = PiketSchedule::query()
                ->where('user_id', $member->id)
                ->whereDate('date', $data['date'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'user_id' => 'Jadwal anggota ini bentrok dengan shift yang sudah ada.',
                ]);
            }

            PiketSchedule::create($data + ['created_by' => $request->user()->id]);
        });

        return redirect()->route('bendahara.jadwal-piket')
            ->with('success', 'Jadwal piket berhasil dibuat.');
    }

    public function attend(Request $request, PiketSchedule $schedule): RedirectResponse
    {
        $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'attendance_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'proof.required' => 'Unggah foto bukti piket untuk melakukan presensi.',
            'proof.mimes' => 'Bukti harus berupa JPG, PNG, atau WebP.',
            'proof.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        if ($schedule->user_id !== $request->user()->id) {
            abort(404);
        }

        $proofPath = null;
        try {
            DB::transaction(function () use ($request, $schedule, &$proofPath): void {
                $lockedSchedule = PiketSchedule::query()->lockForUpdate()->findOrFail($schedule->id);

                if ($lockedSchedule->attendance_status !== null) {
                    throw ValidationException::withMessages([
                        'proof' => 'Presensi untuk jadwal ini sudah tercatat.',
                    ]);
                }

                $now = now('Asia/Jakarta');
                if ($now->lt($lockedSchedule->startsAt()) || $now->gt($lockedSchedule->endsAt())) {
                    throw ValidationException::withMessages([
                        'proof' => 'Presensi hanya dapat dilakukan selama jam shift yang tertera.',
                    ]);
                }

                $proofPath = $request->file('proof')->store('piket-proofs', 'local');
                if (! $proofPath) {
                    throw new RuntimeException('Foto bukti piket gagal disimpan.');
                }

                $lockedSchedule->update([
                    'attendance_status' => PiketSchedule::STATUS_HADIR,
                    'attended_at' => $now,
                    'proof_path' => $proofPath,
                    'attendance_note' => $request->input('attendance_note'),
                ]);
            });
        } catch (Throwable $exception) {
            if ($proofPath) {
                Storage::disk('local')->delete($proofPath);
            }

            throw $exception;
        }

        $routePrefix = $request->user()->role === 'presbem' ? 'presbem' : 'anggota';

        return redirect()->route($routePrefix.'.jadwal-piket')
            ->with('success', 'Presensi piket berhasil dicatat.');
    }

    public function evidence(Request $request, PiketSchedule $schedule)
    {
        abort_unless(
            $schedule->user_id === $request->user()->id || $request->user()->isBendahara(),
            404
        );

        abort_unless($schedule->proof_path && Storage::disk('local')->exists($schedule->proof_path), 404);

        return Storage::disk('local')->response($schedule->proof_path);
    }
}
