<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CashTransactionController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $transactions = CashTransaction::with('member')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $transactions]);
        }

        $income = $transactions->where('type', 'masuk')->sum('amount');
        $expense = $transactions->where('type', 'keluar')->sum('amount');
        $runningBalance = 0;

        $transactions = $transactions->map(function (CashTransaction $transaction) use (&$runningBalance) {
            $runningBalance += $transaction->type === 'masuk'
                ? $transaction->amount
                : -$transaction->amount;
            $transaction->setAttribute('running_balance', $runningBalance);

            return $transaction;
        });

        $filter = $request->query('type');
        if (in_array($filter, ['masuk', 'keluar'], true)) {
            $transactions = $transactions->where('type', $filter);
        }

        return view('bendahara.kas', [
            'transactions' => $transactions,
            'members' => User::query()
                ->whereIn('role', ['anggota', 'presbem'])
                ->orderBy('name')
                ->get(['id', 'name', 'nim']),
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'filter' => $filter,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;

        $transaction = CashTransaction::create($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Transaksi berhasil disimpan.', 'data' => $transaction->load('member')], 201);
        }

        return redirect()->route('bendahara.kas')->with('success', 'Transaksi berhasil disimpan.');
    }

    public function update(Request $request, CashTransaction $transaction): RedirectResponse|JsonResponse
    {
        $transaction->update($this->validatedData($request, $transaction));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Transaksi berhasil diperbarui.', 'data' => $transaction->fresh()->load('member')]);
        }

        return redirect()->route('bendahara.kas')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Request $request, CashTransaction $transaction): RedirectResponse|JsonResponse
    {
        $transaction->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Transaksi berhasil dihapus.']);
        }

        return redirect()->route('bendahara.kas')->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Read-only public financial transparency for Anggota and PresBEM
     */
    public function transparansiKas(Request $request): View
    {
        $allTransactions = CashTransaction::with('member:id,name,nim,departemen')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $income = (int) $allTransactions->where('type', 'masuk')->sum('amount');
        $expense = (int) $allTransactions->where('type', 'keluar')->sum('amount');
        $balance = $income - $expense;

        $runningBalance = 0;
        $allTransactions = $allTransactions->map(function (CashTransaction $transaction) use (&$runningBalance) {
            $runningBalance += $transaction->type === 'masuk'
                ? $transaction->amount
                : -$transaction->amount;
            $transaction->setAttribute('running_balance', $runningBalance);

            return $transaction;
        });

        $filter = $request->query('type');
        $filtered = $allTransactions;
        if (in_array($filter, ['masuk', 'keluar'], true)) {
            $filtered = $filtered->where('type', $filter);
        }

        $user = $request->user();
        $layout = $user->role === 'presbem' ? 'layouts.presbem' : 'layouts.anggota';

        return view('anggota.transparansi-kas', [
            'transactions' => $filtered->reverse()->values(),
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'filter' => $filter,
            'layout' => $layout,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?CashTransaction $transaction = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['masuk', 'keluar'])],
            'transaction_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereIn('role', ['anggota', 'presbem']),
            ],
        ]);

        if ($data['type'] === 'keluar') {
            $balanceQuery = CashTransaction::query();
            if ($transaction !== null) {
                $balanceQuery->where('id', '!=', $transaction->id);
            }

            $balance = (int) $balanceQuery
                ->selectRaw(
                    'COALESCE(SUM(CASE WHEN type = ? THEN amount WHEN type = ? THEN -amount ELSE 0 END), 0) AS balance',
                    ['masuk', 'keluar'],
                )
                ->value('balance');

            if ($data['amount'] > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo kas tidak mencukupi untuk transaksi kas keluar.',
                ]);
            }
        }

        return $data;
    }
}
