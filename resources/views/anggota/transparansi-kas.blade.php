@extends($layout)

@section('title', 'Transparansi Kas BEM')
@section('page-title', 'Transparansi Kas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Transparansi Kas Kabinet</h2>
            <p class="mt-1 text-sm text-gray-500">Laporan keterbukaan arus kas masuk dan kas keluar BEM FILKOM UB</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700 border border-emerald-100">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Data Terbuka & Tervalidasi</span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Saldo Akhir</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-[#1A6B6B]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M3 12h18m-7 6h7" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black tracking-tight text-[#1A6B6B]">
                Rp{{ number_format($balance, 0, ',', '.') }}
            </div>
            <p class="mt-1 text-xs text-gray-400">Kas bersih saat ini</p>
        </div>

        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pemasukan</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black tracking-tight text-emerald-600">
                Rp{{ number_format($income, 0, ',', '.') }}
            </div>
            <p class="mt-1 text-xs text-gray-400">Total akumulasi dana masuk</p>
        </div>

        <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pengeluaran</span>
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black tracking-tight text-rose-600">
                Rp{{ number_format($expense, 0, ',', '.') }}
            </div>
            <p class="mt-1 text-xs text-gray-400">Total operasional & kegiatan</p>
        </div>
    </div>

    <!-- Table Card -->
    <div class="overflow-hidden rounded-2xl border border-gray-100/80 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900">Buku Kas Kabinet</h3>
                <p class="text-xs text-gray-500">Daftar transaksi kas organisasi yang telah dibukukan oleh Biro ADKEU</p>
            </div>
            <div class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100/80 p-1 text-xs font-semibold">
                @php
                    $currentRoute = auth()->user()->role === 'presbem' ? 'presbem.transparansi-kas' : 'anggota.transparansi-kas';
                @endphp
                @foreach(['all' => 'Semua', 'masuk' => 'Pemasukan', 'keluar' => 'Pengeluaran'] as $type => $label)
                    <a href="{{ route($currentRoute, $type === 'all' ? [] : ['type' => $type]) }}"
                       class="rounded-lg px-3.5 py-1.5 transition {{ ($type === 'all' && !in_array($filter, ['masuk', 'keluar'], true)) || $filter === $type ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[750px] text-left text-xs">
                <thead class="border-b border-gray-100 bg-gray-50/70 font-semibold uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Keterangan</th>
                        <th class="px-6 py-3.5 text-center">Jenis</th>
                        <th class="px-6 py-3.5 text-right">Nominal</th>
                        <th class="px-6 py-3.5 text-right">Saldo Berjalan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/70 text-gray-700">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-800">
                                {{ $transaction->transaction_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $transaction->description }}</div>
                                @if($transaction->member)
                                    <div class="mt-0.5 text-[11px] text-gray-500">
                                        Fungsionaris: {{ $transaction->member->name }} ({{ $transaction->member->departemen ?? 'BEM' }})
                                    </div>
                                @endif
                                @if($transaction->notes)
                                    <div class="mt-0.5 text-[11px] text-gray-400">{{ $transaction->notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($transaction->type === 'masuk')
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                                        Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 border border-rose-100">
                                        Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-bold {{ $transaction->type === 'masuk' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $transaction->type === 'masuk' ? '+' : '-' }}Rp{{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right font-mono font-medium text-gray-900">
                                Rp{{ number_format($transaction->running_balance, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <svg class="mx-auto h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm font-medium">Belum ada transaksi kas yang dicatat</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
