@extends($layout)

@section('title', 'Rekap Kas Organisasi')
@section('page-title', 'Rekap Kas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Rekap Kas Organisasi</h2>
            <p class="mt-1 text-sm text-gray-500">Laporan komprehensif kas masuk, kas keluar, dan kepatuhan kas fungsionaris per kementerian/kebiroan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route($exportRoute) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#155757]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Unduh CSV
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Ringkasan Kas Global -->
    <div class="grid gap-5 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pemasukan</div>
            <div class="text-2xl font-black tracking-tight text-emerald-600">Rp{{ number_format($pemasukan, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-gray-400">Kas masuk dari iuran dan penerimaan lain</div>
        </div>
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pengeluaran</div>
            <div class="text-2xl font-black tracking-tight text-rose-600">Rp{{ number_format($pengeluaran, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-gray-400">Total belanja program kerja & operasional</div>
        </div>
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Saldo Akhir Kas</div>
            <div class="text-2xl font-black tracking-tight text-[#1A6B6B]">Rp{{ number_format($saldo, 0, ',', '.') }}</div>
            <div class="mt-1 text-xs text-gray-400">Sisa kas bersih BEM FILKOM UB</div>
        </div>
    </div>

    <!-- Ringkasan Rekapitulasi per Kementerian / Kebiroan -->
    @if(!empty($rekapDepartemen))
    <div class="rounded-2xl border border-gray-100/80 bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Rekapitulasi per Kementerian & Kebiroan</h3>
                <p class="text-xs text-gray-500">Ringkasan penerimaan kas, denda keterlambatan, dan tunggakan berdasarkan unit organisasi.</p>
            </div>
            <span class="rounded-lg bg-teal-50 px-3 py-1 text-xs font-bold text-[#1A6B6B]">
                {{ count($rekapDepartemen) }} Divisi / Departemen
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs">
                <thead class="border-b border-gray-100 bg-gray-50/70 font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5">Kementerian / Kebiroan</th>
                        <th class="px-4 py-3.5 text-center">Fungsionaris</th>
                        <th class="px-4 py-3.5 text-right">Kas Terkumpul</th>
                        <th class="px-4 py-3.5 text-right">Denda Terkumpul</th>
                        <th class="px-4 py-3.5 text-right">Total Diterima</th>
                        <th class="px-4 py-3.5 text-right text-rose-600">Sisa Tunggakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/70 text-gray-700">
                    @foreach($rekapDepartemen as $dept)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-3.5 font-bold text-gray-900">
                                {{ $dept['nama'] }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-medium text-gray-600">
                                {{ $dept['anggota_count'] }} orang
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-emerald-600">
                                Rp{{ number_format($dept['kas_lunas'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-medium text-amber-600">
                                Rp{{ number_format($dept['denda_lunas'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-gray-900">
                                Rp{{ number_format($dept['total_diterima'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold {{ $dept['total_tunggakan'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $dept['total_tunggakan'] > 0 ? 'Rp' . number_format($dept['total_tunggakan'], 0, ',', '.') : 'Lunas Semua' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Grafik Arus Kas -->
    <div class="rounded-2xl border border-gray-100/80 bg-white p-6 shadow-sm print:hidden">
        <div class="mb-5">
            <h3 class="text-base font-bold text-gray-900">Grafik Arus Kas Bulanan</h3>
            <p class="mt-1 text-xs text-gray-500">Perbandingan arus masuk dan arus keluar selama 7 bulan terakhir (dalam jutaan rupiah).</p>
        </div>
        <div class="relative h-64">
            <canvas id="chartKasBulananPres" aria-label="Grafik pemasukan dan pengeluaran bulanan"></canvas>
        </div>
    </div>

    <!-- Tabel Status Pembayaran Anggota -->
    <div class="overflow-hidden rounded-2xl border border-gray-100/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-100 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Status Pembayaran Kas per Fungsionaris</h3>
                <p class="mt-1 text-xs text-gray-500">Rincian status kas bulanan (Kas: Rp20.000 / Denda keterlambatan: Rp10.000).</p>
            </div>

            <!-- Filter Dropdown Kementerian / Kebiroan -->
            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2 print:hidden">
                <label for="filter-departemen" class="text-xs font-semibold text-gray-600 whitespace-nowrap">Kementerian/Biro:</label>
                <select id="filter-departemen" name="departemen" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-1.5 text-xs font-medium text-gray-800 focus:border-[#1A6B6B] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#1A6B6B]">
                    <option value="all" {{ empty($selectedDept) || $selectedDept === 'all' ? 'selected' : '' }}>Semua Kementerian / Biro</option>
                    @foreach($departemenList as $deptOpt)
                        <option value="{{ $deptOpt }}" {{ $selectedDept === $deptOpt ? 'selected' : '' }}>
                            {{ $deptOpt }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-center text-xs">
                <thead class="border-b border-gray-100 bg-gray-50/70 font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Fungsionaris</th>
                        <th class="px-4 py-3.5 text-left">Kementerian / Biro</th>
                        @foreach ($periode as $period)
                            <th class="px-3 py-3.5">{{ $period['label'] }}</th>
                        @endforeach
                        <th class="px-4 py-3.5">Status Lunas</th>
                        <th class="px-4 py-3.5 text-right">Denda Terbayar</th>
                        <th class="px-5 py-3.5 text-right text-rose-600">Sisa Tunggakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/70 text-gray-700">
                    @forelse($anggota as $member)
                        @php
                            $paidMonths = 0;
                            $memberDendaPaid = 0;
                            $memberTunggakan = 0;

                            foreach ($periode as $period) {
                                $key = $member->id.'|'.$period['key'];
                                $pay = $pembayaran->get($key);
                                if ($pay instanceof \Illuminate\Support\Collection) {
                                    $pay = $pay->first();
                                }
                                if ($pay && $pay->status === \App\Models\CashPayment::STATUS_APPROVED) {
                                    $paidMonths++;
                                    $memberDendaPaid += (int) $pay->late_fee_amount;
                                } elseif ($pay && $pay->status === \App\Models\CashPayment::STATUS_PENDING) {
                                    // Menunggu validasi
                                } else {
                                    $memberTunggakan += \App\Models\CashPayment::AMOUNT;
                                    if (now()->greaterThan(\App\Models\CashPayment::deadlineFor($period['carbon']))) {
                                        $memberTunggakan += 10000;
                                    }
                                }
                            }
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-4 text-left">
                                <div class="font-bold text-sm text-gray-900">{{ $member->name }}</div>
                                <div class="text-[11px] text-gray-400">{{ $member->nim ?? 'NIM belum diisi' }}</div>
                            </td>
                            <td class="px-4 py-4 text-left">
                                <span class="inline-flex rounded-lg bg-gray-100 px-2 py-1 text-[11px] font-semibold text-gray-700">
                                    {{ $member->departemen ?? 'BEM' }}
                                </span>
                            </td>
                            @foreach ($periode as $period)
                                @php
                                    $payRecord = $pembayaran->get($member->id.'|'.$period['key']);
                                    if ($payRecord instanceof \Illuminate\Support\Collection) {
                                        $payRecord = $payRecord->first();
                                    }
                                @endphp
                                <td class="px-3 py-4">
                                    @if($payRecord && $payRecord->status === \App\Models\CashPayment::STATUS_APPROVED)
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-100">
                                            Lunas
                                        </span>
                                    @elseif($payRecord && $payRecord->status === \App\Models\CashPayment::STATUS_PENDING)
                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 border border-amber-100">
                                            Menunggu
                                        </span>
                                    @elseif($payRecord && $payRecord->status === \App\Models\CashPayment::STATUS_REJECTED)
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-[11px] font-bold text-rose-700 border border-rose-100">
                                            Ditolak
                                        </span>
                                    @else
                                        @php
                                            $isOverdue = now()->greaterThan(\App\Models\CashPayment::deadlineFor($period['carbon']));
                                        @endphp
                                        <span class="inline-flex items-center rounded-md {{ $isOverdue ? 'bg-rose-50 text-rose-600' : 'bg-gray-50 text-gray-400' }} px-2 py-0.5 text-[11px] font-medium">
                                            {{ $isOverdue ? 'Denda' : 'Belum' }}
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-4">
                                <span class="rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $paidMonths === count($periode) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                    {{ $paidMonths }}/{{ count($periode) }} Bln
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-amber-600">
                                {{ $memberDendaPaid > 0 ? 'Rp' . number_format($memberDendaPaid, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-5 py-4 text-right font-bold {{ $memberTunggakan > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $memberTunggakan > 0 ? 'Rp' . number_format($memberTunggakan, 0, ',', '.') : 'Rp0 (Lunas)' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($periode) + 5 }}" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data fungsionaris pada filter kementerian/kebiroan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const cashChart = document.getElementById('chartKasBulananPres');
    if (cashChart) {
        new Chart(cashChart, {
            type: 'bar',
            data: {
                labels: @json(array_column($bulan, 'label')),
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: @json(array_map(fn ($month) => $month['masuk'] / 1000000, $bulan)),
                        backgroundColor: '#1A6B6B',
                        borderRadius: 6
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json(array_map(fn ($month) => $month['keluar'] / 1000000, $bulan)),
                        backgroundColor: '#E5A00D',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => `Rp${value} jt` }
                    }
                }
            }
        });
    }
</script>
<style>
    @media print {
        body { background: white !important; }
        main { margin: 0 !important; padding: 0 !important; }
        #sidebar, header, .print\:hidden { display: none !important; }
        #mainContent { margin-left: 0 !important; width: 100% !important; }
        .shadow-sm { box-shadow: none !important; }
    }
</style>
@endpush
