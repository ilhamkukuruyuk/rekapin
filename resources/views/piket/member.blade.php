@extends($layout)

@section('title', 'Jadwal Piket Saya')
@section('page-title', 'Jadwal Piket Saya')

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Jadwal Piket Saya</h2>
    <p class="mt-1 text-sm text-gray-500">Lihat giliran piket dan lakukan presensi dengan foto selama jam shift.</p>
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

@php
    $hadir = $schedules->where('attendance_status', \App\Models\PiketSchedule::STATUS_HADIR)->count();
    $alpha = $schedules->filter(fn ($schedule) => $schedule->effectiveAttendanceStatus() === \App\Models\PiketSchedule::STATUS_ALPHA)->count();
    $attendanceRate = $hadir + $alpha > 0 ? (int) round($hadir / ($hadir + $alpha) * 100) : 0;
@endphp

<div class="mb-5 grid gap-3 sm:grid-cols-3">
    <div class="rounded-2xl border border-gray-100 bg-white p-4">
        <p class="text-xs font-semibold text-gray-500">Hadir</p>
        <p class="mt-1 text-2xl font-extrabold text-emerald-600">{{ $hadir }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-4">
        <p class="text-xs font-semibold text-gray-500">Alpha</p>
        <p class="mt-1 text-2xl font-extrabold text-red-600">{{ $alpha }}</p>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-4">
        <p class="text-xs font-semibold text-gray-500">Kehadiran</p>
        <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ $attendanceRate }}%</p>
    </div>
</div>

<div class="space-y-3">
    @forelse($schedules as $schedule)
        @php
            $status = $schedule->effectiveAttendanceStatus();
            [$label, $badgeClass] = match ($status) {
                \App\Models\PiketSchedule::STATUS_HADIR => ['Hadir', 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                \App\Models\PiketSchedule::STATUS_IZIN => ['Izin', 'border-amber-200 bg-amber-50 text-amber-700'],
                \App\Models\PiketSchedule::STATUS_ALPHA => ['Alpha', 'border-red-200 bg-red-50 text-red-700'],
                default => ['Terjadwal', 'border-blue-200 bg-blue-50 text-blue-700'],
            };
            $isOnShift = now('Asia/Jakarta')->betweenIncluded($schedule->startsAt(), $schedule->endsAt());
        @endphp
        <article class="rounded-2xl border border-gray-100 bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="font-bold text-gray-900">{{ $schedule->date->locale('id')->translatedFormat('l, d F Y') }}</h3>
                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badgeClass }}">{{ $label }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} · {{ $schedule->location }}</p>
                    @if($schedule->attended_at)
                        <p class="mt-1 text-xs text-gray-500">Presensi {{ $schedule->attended_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                    @endif
                    @if($schedule->attendance_note)
                        <p class="mt-1 text-xs text-gray-500">{{ $schedule->attendance_note }}</p>
                    @endif
                </div>
                @if($schedule->proof_path)
                    <a href="{{ route($routePrefix.'.jadwal-piket.bukti', $schedule) }}" target="_blank" rel="noopener" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Lihat Bukti Foto</a>
                @endif
            </div>

            @if($status === null && $isOnShift)
                <form action="{{ route($routePrefix.'.jadwal-piket.presensi', $schedule) }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-3 border-t border-gray-100 pt-4 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="proof-{{ $schedule->id }}" class="mb-1 block text-xs font-semibold text-gray-700">Foto bukti piket *</label>
                        <input id="proof-{{ $schedule->id }}" name="proof" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required class="block w-full rounded-lg border border-gray-200 px-2 py-2 text-xs text-gray-700">
                    </div>
                    <div>
                        <label for="note-{{ $schedule->id }}" class="mb-1 block text-xs font-semibold text-gray-700">Catatan (opsional)</label>
                        <input id="note-{{ $schedule->id }}" name="attendance_note" maxlength="1000" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="rounded-lg bg-[#1A6B6B] px-4 py-2 text-xs font-bold text-white hover:bg-[#155757]">Presensi Sekarang</button>
                    </div>
                </form>
            @elseif($status === null)
                <p class="mt-3 border-t border-gray-100 pt-3 text-xs text-gray-500">Tombol presensi akan tersedia selama jam shift.</p>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-10 text-center">
            <p class="text-sm font-semibold text-gray-800">Belum ada jadwal piket untuk akun ini.</p>
            <p class="mt-1 text-xs text-gray-500">Jadwal akan muncul di sini setelah dibuat oleh pengurus.</p>
        </div>
    @endforelse
</div>
@endsection
