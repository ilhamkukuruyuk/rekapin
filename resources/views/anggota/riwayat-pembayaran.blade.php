@extends(auth()->user()->role === 'presbem' ? 'layouts.presbem' : 'layouts.anggota')

@section('title', 'Riwayat Pembayaran')
@section('page-title', 'Riwayat Pembayaran')

@section('content')
@php
    $paidMonths = $monthlyStatuses->where('isPaid', true)->count();
    $unpaidMonths = $monthlyStatuses->count() - $paidMonths;
@endphp

<div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Riwayat Pembayaran</h2>
    <p class="mt-0.5 text-sm text-gray-500">Status pembayaran kas bulanan dan seluruh riwayat pengajuan Anda.</p>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="text-2xl font-extrabold text-emerald-600">{{ $paidMonths }}</div>
        <div class="mt-1 text-xs font-medium text-gray-500">Bulan lunas pada {{ $year }}</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="text-2xl font-extrabold text-red-600">{{ $unpaidMonths }}</div>
        <div class="mt-1 text-xs font-medium text-gray-500">Bulan belum lunas pada {{ $year }}</div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5">
        <div class="text-2xl font-extrabold text-[#1A6B6B]">Rp{{ number_format($totalPaid, 0, ',', '.') }}</div>
        <div class="mt-1 text-xs font-medium text-gray-500">Total pembayaran yang disetujui</div>
    </div>
</div>

<section class="mb-6 overflow-hidden rounded-2xl border border-gray-100 bg-white">
    <div class="border-b border-gray-100 px-5 py-4">
        <h3 class="text-sm font-bold text-gray-900">Status Pembayaran Bulanan · {{ $year }}</h3>
        <p class="mt-1 text-xs text-gray-500">Pembayaran dihitung lunas setelah disetujui oleh ADKEU.</p>
    </div>
    <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($monthlyStatuses as $monthlyStatus)
            @php
                $payment = $monthlyStatus['payment'];
                $isPaid = $monthlyStatus['isPaid'];
                $statusLabel = $isPaid
                    ? ($monthlyStatus['isLate'] ? 'Lunas · Terlambat' : 'Lunas')
                    : ($monthlyStatus['isOverdue']
                        ? 'Terlambat'.($payment?->status === 'pending' ? ' · Menunggu verifikasi' : ($payment?->status === 'rejected' ? ' · Ditolak' : ''))
                        : ($payment?->status === 'pending' ? 'Menunggu verifikasi' : ($payment?->status === 'rejected' ? 'Ditolak' : 'Belum lunas')));
                $statusClass = $isPaid && !$monthlyStatus['isLate']
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : ($monthlyStatus['isOverdue']
                        ? 'border-red-200 bg-red-50 text-red-700'
                        : ($payment?->status === 'pending'
                            ? 'border-amber-200 bg-amber-50 text-amber-700'
                            : 'border-gray-200 bg-gray-50 text-gray-600'));
            @endphp
            <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 p-4">
                <div>
                    <div class="text-sm font-semibold text-gray-800">{{ $monthlyStatus['period']->locale('id')->translatedFormat('F Y') }}</div>
                    @if($payment)
                        <div class="mt-1 text-xs text-gray-500">Rp{{ number_format($payment->amount + $payment->late_fee_amount, 0, ',', '.') }}</div>
                        @if($payment->late_fee_amount > 0)
                            <div class="mt-0.5 text-xs text-red-600">Denda Rp{{ number_format($payment->late_fee_amount, 0, ',', '.') }}</div>
                        @endif
                    @endif
                </div>
                <span class="shrink-0 rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
        @endforeach
    </div>
</section>

<section class="overflow-hidden rounded-2xl border border-gray-100 bg-white">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4">
        <div>
            <h3 class="text-sm font-bold text-gray-900">Daftar Riwayat Transaksi Pembayaran Kas</h3>
            <p class="mt-1 text-xs text-gray-500">Termasuk pengajuan menunggu verifikasi, ditolak, dan disetujui.</p>
        </div>
        <span class="text-xs font-semibold text-gray-500">Total {{ $payments->count() }} transaksi</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Periode</th>
                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal pengajuan</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Jumlah</th>
                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Status pembayaran</th>
                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal verifikasi</th>
                    <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Diperiksa oleh / catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($payments as $payment)
                    @php
                        [$label, $class] = match ($payment->status) {
                            'approved' => $payment->isLate()
                                ? ['Lunas · Terlambat', 'border-amber-200 bg-amber-50 text-amber-700']
                                : ['Lunas', 'border-emerald-200 bg-emerald-50 text-emerald-700'],
                            'pending' => $payment->isLate()
                                ? ['Terlambat · Menunggu verifikasi', 'border-red-200 bg-red-50 text-red-700']
                                : ['Menunggu verifikasi', 'border-amber-200 bg-amber-50 text-amber-700'],
                            'rejected' => $payment->isOverdue()
                                ? ['Terlambat · Ditolak', 'border-red-200 bg-red-50 text-red-700']
                                : ['Ditolak', 'border-red-200 bg-red-50 text-red-700'],
                            default => ['Status tidak diketahui', 'border-gray-200 bg-gray-50 text-gray-600'],
                        };
                    @endphp
                    <tr class="align-top hover:bg-gray-50/70">
                        <td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-800">{{ $payment->period->locale('id')->translatedFormat('F Y') }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-gray-800">
                            Rp{{ number_format($payment->amount + $payment->late_fee_amount, 0, ',', '.') }}
                            @if($payment->late_fee_amount > 0)
                                <div class="mt-0.5 text-xs font-normal text-red-600">Termasuk denda Rp{{ number_format($payment->late_fee_amount, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold {{ $class }}">{{ $label }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                            {{ $payment->reviewed_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="max-w-xs px-5 py-4 text-gray-600">
                            @if($payment->reviewer)
                                <div>{{ $payment->reviewer->name }}</div>
                            @endif
                            @if($payment->rejection_reason)
                                <div class="mt-1 text-xs text-red-600">Alasan penolakan: {{ $payment->rejection_reason }}</div>
                            @elseif(!$payment->reviewer)
                                <span class="text-gray-400">Belum diverifikasi</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">Belum ada riwayat pengajuan pembayaran kas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
