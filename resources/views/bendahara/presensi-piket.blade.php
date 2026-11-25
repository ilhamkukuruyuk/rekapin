@extends('layouts.bendahara')

@section('title', 'Presensi Piket')
@section('page-title', 'Presensi Piket')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Presensi Piket</h2>
        <p class="mt-1 text-sm text-gray-500">Pantau kehadiran dan bukti foto anggota pada setiap shift.</p>
    </div>
    <a href="{{ route('bendahara.jadwal-piket') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50">Kelola Jadwal</a>
</div>

<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Shift</th>
                    <th class="px-5 py-3">Anggota</th>
                    <th class="px-5 py-3">Status / Waktu Presensi</th>
                    <th class="px-5 py-3">Bukti</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($schedules as $schedule)
                    @php
                        [$statusLabel, $statusClass] = match ($schedule->effectiveAttendanceStatus()) {
                            \App\Models\PiketSchedule::STATUS_HADIR => ['Hadir', 'bg-emerald-50 text-emerald-700'],
                            \App\Models\PiketSchedule::STATUS_IZIN => ['Izin', 'bg-amber-50 text-amber-700'],
                            \App\Models\PiketSchedule::STATUS_ALPHA => ['Alpha', 'bg-red-50 text-red-700'],
                            default => ['Terjadwal', 'bg-blue-50 text-blue-700'],
                        };
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="font-semibold text-gray-800">{{ $schedule->date->format('d/m/Y') }}</div>
                            <div class="mt-0.5 text-xs text-gray-500">{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} · {{ $schedule->location }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800">{{ $schedule->member?->name ?? 'Akun dihapus' }}</div>
                            <div class="mt-0.5 text-xs text-gray-500">{{ $schedule->member?->nim }}</div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($schedule->attended_at)
                                <div class="mt-1 text-xs text-gray-500">{{ $schedule->attended_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($schedule->proof_path)
                                <a href="{{ route('bendahara.jadwal-piket.bukti', $schedule) }}" target="_blank" rel="noopener" class="font-semibold text-[#1A6B6B] hover:underline">Lihat foto</a>
                            @else
                                <span class="text-xs text-gray-400">Belum ada</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada jadwal atau catatan presensi piket.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
