@extends('layouts.bendahara')

@section('title', 'Kas')
@section('page-title', 'Kas')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Kas</h2>
        <p class="mt-1 text-sm text-gray-500">Pencatatan kas masuk dan keluar BEM FILKOM UB</p>
    </div>
    <button type="button" onclick="openModalTambah()" class="inline-flex items-center gap-2 rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#155757]">
        <span aria-hidden="true">+</span> Tambah Transaksi
    </button>
</div>

@if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3">
    <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
        <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Saldo Saat Ini</div>
        <div class="text-3xl font-black tracking-tight text-[#1A6B6B]">Rp{{ number_format($balance, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
        <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pemasukan</div>
        <div class="text-3xl font-black tracking-tight text-emerald-600">Rp{{ number_format($income, 0, ',', '.') }}</div>
    </div>
    <div class="rounded-2xl border border-gray-100/80 bg-white p-5 shadow-sm">
        <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pengeluaran</div>
        <div class="text-3xl font-black tracking-tight text-red-600">Rp{{ number_format($expense, 0, ',', '.') }}</div>
    </div>
</div>

<div class="overflow-hidden rounded-2xl border border-gray-100/80 bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-gray-100 p-6 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-base font-bold text-gray-900">Riwayat Transaksi</h3>
        <div class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100/80 p-1 text-xs font-semibold">
            @foreach(['all' => 'Semua', 'masuk' => 'Masuk', 'keluar' => 'Keluar'] as $type => $label)
                <a href="{{ route('bendahara.kas', $type === 'all' ? [] : ['type' => $type]) }}"
                   class="rounded-lg px-4 py-1.5 transition {{ ($type === 'all' && !in_array($filter, ['masuk', 'keluar'], true)) || $filter === $type ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-xs">
            <thead class="border-b border-gray-100 bg-gray-50/70 font-semibold uppercase tracking-wider text-gray-400">
                <tr>
                    <th class="px-6 py-3.5">Tanggal</th>
                    <th class="px-6 py-3.5">Keterangan</th>
                    <th class="px-6 py-3.5 text-center">Jenis</th>
                    <th class="px-6 py-3.5 text-right">Nominal</th>
                    <th class="px-6 py-3.5 text-right">Saldo</th>
                    <th class="px-6 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100/70 text-gray-700">
                @forelse($transactions as $transaction)
                    <tr class="kas-row" data-type="{{ $transaction->type }}">
                        <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-800">{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $transaction->description }}</div>
                            @if($transaction->member)
                                <div class="mt-0.5 text-[11px] text-gray-500">Pembayar: {{ $transaction->member->name }}</div>
                            @endif
                            @if($transaction->notes)
                                <div class="mt-0.5 text-[11px] text-gray-400">{{ $transaction->notes }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-[11px] font-bold {{ $transaction->type === 'masuk' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-600' }}">
                                {{ $transaction->type === 'masuk' ? '↑ Masuk' : '↓ Keluar' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold {{ $transaction->type === 'masuk' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'masuk' ? '+' : '-' }}Rp{{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-gray-900">Rp{{ number_format($transaction->running_balance, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-right">
                            <button type="button"
                                    class="mr-2 font-semibold text-[#1A6B6B] hover:underline"
                                    onclick="openModalEdit(this)"
                                    data-id="{{ $transaction->id }}"
                                    data-url="{{ route('bendahara.kas.update', $transaction) }}"
                                    data-type="{{ $transaction->type }}"
                                    data-date="{{ $transaction->transaction_date->format('Y-m-d') }}"
                                    data-description="{{ $transaction->description }}"
                                    data-amount="{{ $transaction->amount }}"
                                    data-notes="{{ $transaction->notes }}"
                                    data-user-id="{{ $transaction->user_id }}">
                                Edit
                            </button>
                            <form action="{{ route('bendahara.kas.destroy', $transaction) }}" method="POST" class="inline" onsubmit="return confirm('Hapus transaksi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada transaksi kas yang dicatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('modals')
<div id="modalTambahKas" class="fixed inset-0 z-50 {{ $errors->any() ? 'flex' : 'hidden' }} items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
            <h3 id="modalTitle" class="text-base font-bold text-gray-900">Tambah Transaksi Kas</h3>
            <button type="button" onclick="closeModalTambah()" class="text-gray-400 transition hover:text-gray-600" aria-label="Tutup">✕</button>
        </div>

        <form id="formKas" action="{{ route('bendahara.kas.store') }}" method="POST" class="space-y-4 p-6">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="transaction_id" id="transactionId" value="{{ old('transaction_id') }}">

            <div>
                <label for="type" class="mb-1.5 block text-xs font-semibold text-gray-700">Jenis Transaksi</label>
                <select id="type" name="type" required class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs text-gray-800 focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/30">
                    <option value="masuk" @selected(old('type', 'masuk') === 'masuk')>Kas Masuk</option>
                    <option value="keluar" @selected(old('type') === 'keluar')>Kas Keluar</option>
                </select>
                @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="transaction_date" class="mb-1.5 block text-xs font-semibold text-gray-700">Tanggal</label>
                <input id="transaction_date" name="transaction_date" type="date" required value="{{ old('transaction_date', now()->toDateString()) }}" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs text-gray-800 focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/30">
                @error('transaction_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-xs font-semibold text-gray-700">Keterangan</label>
                <input id="description" name="description" type="text" maxlength="150" required value="{{ old('description') }}" placeholder="Contoh: Pembayaran kas bulanan" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs text-gray-800 placeholder-gray-400 focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/30">
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="amount" class="mb-1.5 block text-xs font-semibold text-gray-700">Nominal (Rp)</label>
                <input id="amount" name="amount" type="number" min="1" max="999999999999" step="1" required value="{{ old('amount') }}" placeholder="0" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs text-gray-800 focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/30">
                @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="user_id" class="mb-1.5 block text-xs font-semibold text-gray-700">Anggota (opsional)</label>
                <select id="user_id" name="user_id" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs text-gray-800 focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/30">
                    <option value="">Tidak terkait anggota</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('user_id') === (string) $member->id)>{{ $member->name }}{{ $member->nim ? ' · '.$member->nim : '' }}</option>
                    @endforeach
                </select>
                @error('user_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="notes" class="mb-1.5 block text-xs font-semibold text-gray-700">Catatan (opsional)</label>
                <textarea id="notes" name="notes" rows="2" maxlength="2000" placeholder="Detail tambahan..." class="w-full resize-none rounded-xl border border-gray-200 px-3.5 py-2.5 text-xs text-gray-800 placeholder-gray-400 focus:border-[#1A6B6B] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B]/30">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" onclick="closeModalTambah()" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50">Batal</button>
                <button type="submit" class="w-full rounded-xl bg-[#1A6B6B] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#155757]">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const kasForm = document.getElementById('formKas');

    function openModalTambah() {
        kasForm.reset();
        kasForm.action = @json(route('bendahara.kas.store'));
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('transactionId').value = '';
        document.getElementById('modalTitle').textContent = 'Tambah Transaksi Kas';
        document.getElementById('transaction_date').value = @json(now()->toDateString());
        document.getElementById('modalTambahKas').classList.remove('hidden');
        document.getElementById('modalTambahKas').classList.add('flex');
    }

    function openModalEdit(button) {
        kasForm.action = button.dataset.url;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('transactionId').value = button.dataset.id;
        document.getElementById('modalTitle').textContent = 'Edit Transaksi Kas';
        document.getElementById('type').value = button.dataset.type;
        document.getElementById('transaction_date').value = button.dataset.date;
        document.getElementById('description').value = button.dataset.description;
        document.getElementById('amount').value = button.dataset.amount;
        document.getElementById('notes').value = button.dataset.notes || '';
        document.getElementById('user_id').value = button.dataset.userId || '';
        document.getElementById('modalTambahKas').classList.remove('hidden');
        document.getElementById('modalTambahKas').classList.add('flex');
    }

    function closeModalTambah() {
        document.getElementById('modalTambahKas').classList.add('hidden');
        document.getElementById('modalTambahKas').classList.remove('flex');
    }

    const oldTransactionId = document.getElementById('transactionId').value;
    if (oldTransactionId) {
        kasForm.action = @json(route('bendahara.kas.update', ['transaction' => '__TRANSACTION__'])).replace('__TRANSACTION__', oldTransactionId);
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('modalTitle').textContent = 'Edit Transaksi Kas';
        document.getElementById('modalTambahKas').classList.add('flex');
    }
</script>
@endpush
