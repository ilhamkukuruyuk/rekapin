@extends('layouts.bendahara')

@section('title', 'Jadwal Piket')
@section('page-title', 'Jadwal Piket')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Jadwal Piket</h2>
    <p class="mt-1 text-sm text-gray-500">Buat shift dan tugaskan anggota piket sekretariat.</p>
</div>

@if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <ul class="list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="mb-4 text-base font-bold text-gray-900">Buat Jadwal Baru</h3>
    @if($members->isEmpty())
        <p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500">Belum ada akun anggota untuk ditugaskan.</p>
    @else
        <form action="{{ route('bendahara.jadwal-piket.store') }}" method="POST" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="user_id" class="mb-1.5 block text-xs font-semibold text-gray-700">Anggota *</label>
                <select id="user_id" name="user_id" required class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800">
                    <option value="">Pilih anggota</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>{{ $member->name }}{{ $member->nim ? ' · '.$member->nim : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date" class="mb-1.5 block text-xs font-semibold text-gray-700">Tanggal *</label>
                <input id="date" name="date" type="date" min="{{ now()->toDateString() }}" value="{{ old('date', now()->toDateString()) }}" required class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800">
            </div>
            <div>
                <label for="location" class="mb-1.5 block text-xs font-semibold text-gray-700">Lokasi *</label>
                <input id="location" name="location" maxlength="255" value="{{ old('location', 'Sekretariat BEM') }}" required class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800">
            </div>
            <div>
                <label for="start_time" class="mb-1.5 block text-xs font-semibold text-gray-700">Jam mulai *</label>
                <input id="start_time" name="start_time" type="time" value="{{ old('start_time', '08:00') }}" required class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800">
            </div>
            <div>
                <label for="end_time" class="mb-1.5 block text-xs font-semibold text-gray-700">Jam selesai *</label>
                <input id="end_time" name="end_time" type="time" value="{{ old('end_time', '10:00') }}" required class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#155757]">Buat Jadwal</button>
            </div>
        </form>
    @endif
</section>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h3 class="text-base font-bold text-gray-900">Semua Jadwal</h3>
    <a href="{{ route('bendahara.presensi-piket') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">Lihat Presensi</a>
</div>

<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-3">Tanggal / Shift</th>
                    <th class="px-5 py-3">Anggota</th>
                    <th class="px-5 py-3">Lokasi</th>
                    <th class="px-5 py-3">Status</th>
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
                            <div class="mt-0.5 text-xs text-gray-500">{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-800">{{ $schedule->member?->name ?? 'Akun dihapus' }}</div>
                            <div class="mt-0.5 text-xs text-gray-500">{{ $schedule->member?->nim }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $schedule->location }}</td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if($schedule->attended_at)
                                <div class="mt-1 text-xs text-gray-500">{{ $schedule->attended_at->timezone('Asia/Jakarta')->format('H:i') }} WIB</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada jadwal piket. Buat jadwal melalui formulir di atas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
