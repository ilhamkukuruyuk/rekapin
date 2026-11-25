@extends($layout ?? 'layouts.psdi')

@section('title', 'Rekap Presensi')
@section('page-title', 'Rekap Presensi')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Rekap Presensi Kegiatan</h2>
        <p class="mt-1 text-sm text-gray-500">Ringkasan kedisiplinan fungsionaris pada kegiatan yang telah selesai.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route($exportRoute ?? 'psdi.rekap-presensi.export') }}" class="rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#155757]">
            Unduh Rekap CSV
        </a>
        <button type="button" onclick="window.print()" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-50">
            Cetak / Simpan PDF
        </button>
    </div>
</div>

<section aria-label="Ringkasan presensi" class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center shadow-sm">
        <div class="text-3xl font-black text-emerald-600">{{ $ringkasanUmum['rataRata'] }}%</div>
        <div class="mt-1 text-xs font-semibold text-gray-500">Rata-rata Kehadiran</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center shadow-sm">
        <div class="text-3xl font-black text-red-600">{{ $ringkasanUmum['totalAlpha'] }}</div>
        <div class="mt-1 text-xs font-semibold text-gray-500">Total Alpha</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center shadow-sm">
        <div class="text-3xl font-black text-amber-500">{{ $ringkasanUmum['totalIzin'] }}</div>
        <div class="mt-1 text-xs font-semibold text-gray-500">Total Izin</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 text-center shadow-sm">
        <div class="text-3xl font-black text-[#1A6B6B]">{{ $ringkasanUmum['diBawah75'] }}</div>
        <div class="mt-1 text-xs font-semibold text-gray-500">Anggota di Bawah 75%</div>
    </div>
</section>

<p class="mb-6 text-xs text-gray-500">
    Persentase kehadiran dihitung dari jumlah hadir dibanding seluruh kegiatan selesai. Tidak adanya catatan presensi/izin pada kegiatan selesai dihitung sebagai Alpha.
    {{ $ringkasanUmum['totalStatus'] }} status dievaluasi dari {{ $ringkasanUmum['totalKegiatanSelesai'] }} agenda selesai.
</p>

<section class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="mb-5">
        <h3 class="text-base font-bold text-gray-900">Kehadiran per Kegiatan</h3>
        <p class="mt-1 text-xs text-gray-500">Maksimal 12 kegiatan selesai terbaru; Alpha termasuk status yang belum dicatat.</p>
    </div>
    <div class="relative h-72">
        <canvas id="attendanceReportChart" aria-label="Grafik status presensi per kegiatan"></canvas>
    </div>
</section>

<section class="mb-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="border-b border-gray-100 p-5">
        <h3 class="text-base font-bold text-gray-900">Kehadiran per Fungsionaris</h3>
        <p class="mt-1 text-xs text-gray-500">Persentase = jumlah hadir dibagi seluruh kegiatan selesai.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-gray-50/70 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3">Fungsionaris</th>
                    <th class="px-5 py-3 text-center">Hadir</th>
                    <th class="px-5 py-3 text-center">Izin</th>
                    <th class="px-5 py-3 text-center">Alpha</th>
                    <th class="px-5 py-3 text-right">Kehadiran</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($anggota as $user)
                    @php
                        $stat = $ringkasan[$user->id];
                    @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->nim ?? $user->email }} · {{ $user->departemen ?? $user->role }}</div>
                        </td>
                        <td class="px-5 py-4 text-center font-bold text-emerald-600">{{ $stat['hadir'] }}</td>
                        <td class="px-5 py-4 text-center font-bold text-amber-600">{{ $stat['izin'] }}</td>
                        <td class="px-5 py-4 text-center font-bold text-red-600">{{ $stat['alpha'] }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full {{ $stat['persentase'] >= 75 ? 'bg-[#1A6B6B]' : 'bg-red-500' }}" style="width: {{ $stat['persentase'] }}%"></div>
                                </div>
                                <span class="w-10 text-right font-bold">{{ $stat['persentase'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Belum ada data fungsionaris.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="border-b border-gray-100 p-5">
        <h3 class="text-base font-bold text-gray-900">Rekap per Kegiatan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-gray-50/70 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3">Kegiatan</th>
                    <th class="px-5 py-3 text-center">Hadir</th>
                    <th class="px-5 py-3 text-center">Izin</th>
                    <th class="px-5 py-3 text-center">Alpha</th>
                    <th class="px-5 py-3 text-right">Kelengkapan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($kegiatan as $event)
                    @php
                        $eventHadir = $event->presensis->where('status', 'hadir')->count();
                        $eventIzin = $event->presensis->where('status', 'izin')->count();
                        $eventAlpha = $event->status === \App\Models\Kegiatan::STATUS_SELESAI
                            ? max(0, $anggota->count() - $eventHadir - $eventIzin)
                            : $event->presensis->where('status', 'tidak-hadir')->count();
                    @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-gray-900">{{ $event->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $event->tanggal->locale('id')->isoFormat('D MMMM Y') }} · {{ $event->status }}</div>
                        </td>
                        <td class="px-5 py-4 text-center font-bold text-emerald-600">{{ $eventHadir }}</td>
                        <td class="px-5 py-4 text-center font-bold text-amber-600">{{ $eventIzin }}</td>
                        <td class="px-5 py-4 text-center font-bold text-red-600">{{ $eventAlpha }}</td>
                        <td class="px-5 py-4 text-right">{{ $event->status === \App\Models\Kegiatan::STATUS_SELESAI ? '100%' : 'Belum selesai' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Belum ada data kegiatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script>
    new Chart(document.getElementById('attendanceReportChart'), {
        type: 'bar',
        data: {
            labels: @json($grafik['labels']),
            datasets: [
                { label: 'Hadir', data: @json($grafik['hadir']), backgroundColor: '#1A6B6B', borderRadius: 4 },
                { label: 'Izin', data: @json($grafik['izin']), backgroundColor: '#E5A00D', borderRadius: 4 },
                { label: 'Alpha', data: @json($grafik['alpha']), backgroundColor: '#DC2626', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
</script>
<style>
    @media print {
        body { background: white !important; }
        main { margin: 0 !important; padding: 0 !important; }
        #sidebar, header { display: none !important; }
        #mainContent { margin-left: 0 !important; width: 100% !important; }
        .shadow-sm { box-shadow: none !important; }
    }
</style>
@endpush
