@extends('layouts.bendahara')

@section('title', 'Validasi Pembayaran')
@section('page-title', 'Validasi Pembayaran')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Validasi Pembayaran</h2>
        <p class="mt-1 text-sm text-gray-500">Periksa bukti transfer kas anggota, lalu setujui atau tolak.</p>
    </div>
    <div class="rounded-full border border-amber-200 bg-amber-50 px-3.5 py-1.5 text-xs font-bold text-amber-700">
        {{ $counts['pending'] ?? 0 }} menunggu validasi
    </div>
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

<div class="mb-6 inline-flex flex-wrap items-center gap-2 rounded-2xl bg-gray-100/80 p-1.5 text-xs font-semibold">
    @foreach([
        'all' => 'Semua',
        'pending' => 'Menunggu ('.($counts['pending'] ?? 0).')',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ] as $key => $label)
        <a href="{{ route('bendahara.validasi-pembayaran', ['status' => $key]) }}"
           class="rounded-xl px-4 py-2 transition {{ $status === $key ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if($payments->contains(fn ($payment) => $payment->status === 'pending'))
    <form id="bulk-approve-form" action="{{ route('bendahara.validasi-pembayaran.approve-multiple') }}" method="POST" class="mb-4 flex flex-col gap-3 rounded-2xl border border-teal-100 bg-teal-50 p-4 sm:flex-row sm:items-center sm:justify-between">
        @csrf
        <p class="text-sm text-teal-900">Pilih satu atau beberapa pembayaran yang masih menunggu validasi.</p>
        <button type="submit" onclick="return confirm('Setujui semua pembayaran terpilih dan catat sebagai kas masuk?')" class="rounded-xl bg-[#1A6B6B] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#155757]">
            Setujui Pembayaran Terpilih
        </button>
    </form>
@endif

<div class="space-y-4">
    @forelse($payments as $payment)
        <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                <div class="flex items-start gap-4">
                    @if($payment->status === 'pending')
                        <input type="checkbox" form="bulk-approve-form" name="payments[]" value="{{ $payment->id }}" aria-label="Pilih pembayaran {{ $payment->member->name }} untuk {{ $payment->period->locale('id')->translatedFormat('F Y') }}" class="mt-4 h-4 w-4 rounded border-gray-300 text-[#1A6B6B] focus:ring-[#1A6B6B]">
                    @endif
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#1A6B6B] text-sm font-bold text-white">
                        {{ $payment->member->initials }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $payment->member->name }}</h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            {{ $payment->member->nim ?: $payment->member->email }} · Kas Bulanan - {{ $payment->period->locale('id')->translatedFormat('F Y') }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-gray-400">Dikirim {{ $payment->created_at->format('d/m/Y H:i') }}</p>
                        @if($payment->rejection_reason)
                            <p class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">
                                Alasan penolakan: {{ $payment->rejection_reason }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 lg:justify-end">
                    <div class="text-left lg:text-right">
                        <div class="text-base font-bold text-gray-900">Rp{{ number_format($payment->amount + $payment->late_fee_amount, 0, ',', '.') }}</div>
                        <div class="mt-0.5 text-xs text-gray-500">
                            Kas Rp{{ number_format($payment->amount, 0, ',', '.') }}
                            @if($payment->late_fee_amount > 0)
                                + denda Rp{{ number_format($payment->late_fee_amount, 0, ',', '.') }}
                            @else
                                · tanpa denda
                            @endif
                        </div>
                        <span class="mt-0.5 inline-block rounded-full border px-2.5 py-0.5 text-[11px] font-bold
                            {{ $payment->status === 'pending' ? 'border-amber-200 bg-amber-50 text-amber-700' : '' }}
                            {{ $payment->status === 'approved' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : '' }}
                            {{ $payment->status === 'rejected' ? 'border-red-200 bg-red-50 text-red-700' : '' }}">
                            {{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$payment->status] }}
                        </span>
                    </div>
                    <a href="{{ route('bendahara.validasi-pembayaran.evidence', $payment) }}" target="_blank" rel="noopener"
                       class="rounded-xl border border-gray-200 px-4 py-2 text-xs font-bold text-[#1A6B6B] transition hover:bg-teal-50">
                        Lihat Bukti
                    </a>
                </div>
            </div>

            @if($payment->status === 'pending')
                <div class="mt-5 grid gap-4 border-t border-gray-100 pt-4 lg:grid-cols-[1fr_auto]">
                    <form action="{{ route('bendahara.validasi-pembayaran.reject', $payment) }}" method="POST" class="flex flex-col gap-2 sm:flex-row">
                        @csrf
                        <input type="text" name="rejection_reason" maxlength="1000" required placeholder="Alasan penolakan (wajib)" class="min-w-0 flex-1 rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs text-gray-800 placeholder-gray-400 focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-200">
                        <button type="submit" onclick="return confirm('Tolak pembayaran ini?')" class="rounded-xl border border-red-200 px-4 py-2.5 text-xs font-bold text-red-600 transition hover:bg-red-50">
                            Tolak
                        </button>
                    </form>
                    <form action="{{ route('bendahara.validasi-pembayaran.approve', $payment) }}" method="POST" onsubmit="return confirm('Setujui pembayaran dan catat ke kas masuk?')">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-[#1A6B6B] px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#155757]">
                            Setujui Pembayaran
                        </button>
                    </form>
                </div>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-gray-100 bg-white px-5 py-12 text-center text-sm text-gray-500">
            Tidak ada pengajuan pembayaran pada kategori ini.
        </div>
    @endforelse
</div>
@endsection
