<?php

namespace App\Http\Controllers;

use App\Models\CashPayment;
use App\Models\CashTransaction;
use App\Models\Kegiatan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CashPaymentController extends Controller
{
    public function memberDashboard(Request $request): View
    {
        $penalties = $this->memberLateFees($request);
        $currentPayment = CashPayment::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('period', now()->startOfMonth()->toDateString())
            ->first();

        return view('anggota.dashboard', [
            'outstandingLateFees' => $penalties->where('status', '!=', 'paid')->sum('amount'),
            'activeLateFeeCount' => $penalties->where('status', '!=', 'paid')->count(),
            'currentPayment' => $currentPayment,
            'upcomingActivities' => Kegiatan::query()
                ->whereIn('status', [
                    Kegiatan::STATUS_AKAN_DATANG,
                    Kegiatan::STATUS_BERLANGSUNG,
                ])
                ->whereDate('tanggal', '>=', now('Asia/Jakarta')->toDateString())
                ->orderBy('tanggal')
                ->orderBy('jam_mulai')
                ->limit(5)
                ->get(),
        ]);
    }

    public function memberProfile(Request $request): View
    {
        $penalties = $this->memberLateFees($request);

        return view('anggota.profile', [
            'outstandingLateFees' => $penalties->where('status', '!=', 'paid')->sum('amount'),
            'activeLateFeeCount' => $penalties->where('status', '!=', 'paid')->count(),
            'currentPayment' => CashPayment::query()
                ->where('user_id', $request->user()->id)
                ->whereDate('period', now()->startOfMonth()->toDateString())
                ->first(),
            'totalCashPaid' => CashPayment::query()
                ->where('user_id', $request->user()->id)
                ->where('status', CashPayment::STATUS_APPROVED)
                ->sum('amount'),
        ]);
    }

    public function memberFines(Request $request): View
    {
        $penalties = $this->memberLateFees($request);
        $viewName = $request->user()->role === 'presbem'
            ? 'presbem.denda'
            : 'anggota.denda';

        return view($viewName, [
            'penalties' => $penalties,
            'outstandingLateFees' => $penalties->where('status', '!=', 'paid')->sum('amount'),
            'paidLateFees' => $penalties->where('status', 'paid')->sum('amount'),
            'lateFeePerMonth' => (int) config('cash.late_fee_per_month', 10000),
        ]);
    }

    public function memberIndex(Request $request): View
    {
        $user = $request->user();
        $now = now('Asia/Jakarta');
        $startOfYear = $now->copy()->startOfYear();
        $currentMonth = $now->copy()->startOfMonth();

        // Ambil semua pembayaran user di tahun ini
        $payments = CashPayment::query()
            ->where('user_id', $user->id)
            ->whereBetween('period', [$startOfYear->toDateString(), $currentMonth->copy()->endOfMonth()->toDateString()])
            ->orderBy('period')
            ->get();

        $paymentsByPeriod = $payments->groupBy(fn (CashPayment $p): string => $p->period->format('Y-m'));

        // Susun status setiap bulan dari Januari hingga bulan saat ini
        $monthlyObligations = collect();
        $periodIterator = $startOfYear->copy();

        while ($periodIterator->lessThanOrEqualTo($currentMonth)) {
            $periodKey = $periodIterator->format('Y-m');
            $periodPayments = $paymentsByPeriod->get($periodKey, collect());
            $deadline = CashPayment::deadlineFor($periodIterator);
            $isOverdue = $now->greaterThan($deadline);

            $kasPayment = $periodPayments->first(fn (CashPayment $p): bool => $p->amount > 0);
            $dendaPayment = $periodPayments->first(fn (CashPayment $p): bool => $p->late_fee_amount > 0);

            $isKasPaid = $kasPayment?->status === CashPayment::STATUS_APPROVED;
            $isKasPending = $kasPayment?->status === CashPayment::STATUS_PENDING;

            $isDendaPaid = $dendaPayment?->status === CashPayment::STATUS_APPROVED;
            $isDendaPending = $dendaPayment?->status === CashPayment::STATUS_PENDING;

            $canPayKas = ! $isKasPaid && ! $isKasPending;
            $canPayDenda = $isOverdue && ! $isDendaPaid && ! $isDendaPending;
            $canPay = $canPayKas || $canPayDenda;

            $status = 'belum_bayar'; // belum_bayar | pending | approved | rejected
            $statusLabel = 'Belum Dibayar';

            if ($isKasPaid && ($isDendaPaid || ! $isOverdue)) {
                $status = 'approved';
                $statusLabel = 'Lunas';
            } elseif ($isKasPending || $isDendaPending) {
                $status = 'pending';
                $statusLabel = 'Menunggu Validasi';
            } elseif ($kasPayment?->status === CashPayment::STATUS_REJECTED || $dendaPayment?->status === CashPayment::STATUS_REJECTED) {
                $status = 'rejected';
                $statusLabel = 'Ditolak';
            } elseif ($isKasPaid) {
                $status = 'approved';
                $statusLabel = 'Kas Lunas';
            }

            $rejectionReason = $kasPayment?->rejection_reason ?? $dendaPayment?->rejection_reason;

            $kasAmount = $canPayKas ? CashPayment::AMOUNT : 0;
            $lateFee = $canPayDenda ? CashPayment::LATE_FEE_AMOUNT : 0;
            $totalObligation = $kasAmount + $lateFee;

            $monthlyObligations->push([
                'period' => $periodIterator->copy(),
                'period_key' => $periodKey,
                'period_name' => $periodIterator->locale('id')->translatedFormat('F Y'),
                'deadline' => $deadline,
                'is_overdue' => $isOverdue,
                'status' => $status,
                'status_label' => $statusLabel,
                'can_pay' => $canPay,
                'can_pay_kas' => $canPayKas,
                'can_pay_denda' => $canPayDenda,
                'kas_amount' => $kasAmount > 0 ? $kasAmount : CashPayment::AMOUNT,
                'late_fee' => $lateFee > 0 ? $lateFee : ($isOverdue ? CashPayment::LATE_FEE_AMOUNT : 0),
                'total' => $totalObligation > 0 ? $totalObligation : (CashPayment::AMOUNT + ($isOverdue ? CashPayment::LATE_FEE_AMOUNT : 0)),
                'payment' => $kasPayment ?? $dendaPayment,
                'rejection_reason' => $rejectionReason,
            ]);

            $periodIterator->addMonthNoOverflow();
        }

        // Filter bulan yang masih menunggak (bisa dibayar)
        $unpaidMonths = $monthlyObligations->filter(fn ($item) => $item['can_pay'])->values();
        $totalTunggakanKas = $unpaidMonths->sum('kas_amount');
        $totalTunggakanDenda = $unpaidMonths->sum('late_fee');
        $totalTunggakan = $unpaidMonths->sum('total');

        $allPayments = CashPayment::query()
            ->where('user_id', $user->id)
            ->orderByDesc('period')
            ->get();

        $accountLateFee = CashPayment::accountLateFeeSummaryFor($user->id, $now);

        return view('anggota.bayar-kas', [
            'monthlyObligations' => $monthlyObligations,
            'unpaidMonths' => $unpaidMonths,
            'totalTunggakanKas' => $totalTunggakanKas,
            'totalTunggakanDenda' => $totalTunggakanDenda,
            'totalTunggakan' => $totalTunggakan,
            'accountLateFee' => $accountLateFee,
            'payments' => $allPayments,
            'currentPeriod' => $currentMonth,
            'currentDeadline' => CashPayment::deadlineFor($currentMonth),
        ]);
    }

    public function memberHistory(Request $request): View
    {
        $yearStart = now()->startOfYear();
        $currentPeriod = now()->startOfMonth();
        $payments = CashPayment::query()
            ->with('reviewer')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('period')
            ->orderByDesc('created_at')
            ->get();

        $paymentsByPeriod = $payments->keyBy(fn (CashPayment $payment): string => $payment->period->format('Y-m'));
        $monthlyStatuses = collect(CarbonPeriod::create($yearStart, '1 month', $currentPeriod))
            ->map(function (Carbon $period) use ($paymentsByPeriod): array {
                $payment = $paymentsByPeriod->get($period->format('Y-m'));

                return [
                    'period' => $period->copy(),
                    'payment' => $payment,
                    'isPaid' => $payment?->status === CashPayment::STATUS_APPROVED,
                    'isLate' => $payment?->isLate() ?? false,
                    'isOverdue' => $payment?->isOverdue()
                        ?? now()->greaterThan(CashPayment::deadlineFor($period)),
                ];
            })
            ->reverse()
            ->values();

        return view('anggota.riwayat-pembayaran', [
            'payments' => $payments,
            'monthlyStatuses' => $monthlyStatuses,
            'year' => $yearStart->year,
            'totalPaid' => $payments
                ->where('status', CashPayment::STATUS_APPROVED)
                ->sum(fn (CashPayment $payment): int => $payment->amount + $payment->late_fee_amount),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        // Support both single period dropdown (name="period") and array (name="periods")
        if ($request->filled('period') && ! $request->filled('periods')) {
            $request->merge(['periods' => [(string) $request->input('period')]]);
        }

        // Clean formatted manual nominal input
        if ($request->filled('amount')) {
            $cleaned = preg_replace('/[^\d]/', '', (string) $request->input('amount'));
            if ($cleaned !== '') {
                $request->merge(['amount' => (int) $cleaned]);
            }
        }

        $data = $request->validate([
            'payment_type' => ['nullable', 'string', 'in:kas_only,denda_only,kas_dan_denda'],
            'periods' => ['required', 'array', 'min:1'],
            'periods.*' => ['required', 'date_format:Y-m', 'distinct'],
            'amount' => ['nullable', 'numeric', 'min:1000'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'periods.required' => 'Pilih bulan tagihan pembayaran.',
            'periods.min' => 'Pilih bulan tagihan pembayaran.',
            'periods.*.distinct' => 'Setiap periode hanya boleh dipilih sekali.',
            'amount.min' => 'Nominal pembayaran minimal Rp1.000.',
            'proof.required' => 'Pilih bukti transfer untuk diunggah.',
            'proof.mimes' => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'proof.max' => 'Ukuran bukti maksimal 2 MB.',
        ]);

        $now = now('Asia/Jakarta');
        $currentMonth = $now->copy()->startOfMonth();

        $periods = collect($data['periods'])
            ->map(fn (string $period): Carbon => Carbon::createFromFormat('!Y-m', $period, 'Asia/Jakarta')->startOfMonth())
            ->sortBy(fn (Carbon $period): string => $period->toDateString())
            ->values();

        if ($periods->contains(fn (Carbon $period): bool => $period->greaterThan($currentMonth))) {
            throw ValidationException::withMessages([
                'periods' => 'Periode pembayaran tidak boleh di masa depan.',
            ]);
        }

        $proofDisk = (string) config('cash.proof_disk', 'local');
        $disk = Storage::disk($proofDisk);
        $proofPath = $request->file('proof')->store('cash-payment-proofs', $proofDisk);

        if (! $proofPath) {
            throw new RuntimeException('Bukti pembayaran gagal disimpan.');
        }

        $submissionGroupId = (string) \Illuminate\Support\Str::uuid();
        $submittedAt = $now;
        $oldProofPaths = [];

        try {
            DB::transaction(function () use ($periods, $request, $proofPath, $submissionGroupId, $submittedAt, &$oldProofPaths, $data): void {
                $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);

                $payments = CashPayment::query()
                    ->where('user_id', $request->user()->id)
                    ->where(function ($query) use ($periods): void {
                        foreach ($periods as $period) {
                            $query->orWhereDate('period', $period->toDateString());
                        }
                    })
                    ->orderBy('period')
                    ->lockForUpdate()
                    ->get()
                    ->groupBy(fn (CashPayment $payment): string => $payment->period->format('Y-m'));

                $paymentType = $data['payment_type'] ?? 'kas_dan_denda';

                foreach ($periods as $period) {
                    $periodKey = $period->format('Y-m');
                    $periodPayments = $payments->get($periodKey, collect());
                    $periodName = $period->locale('id')->translatedFormat('F Y');

                    $kasPayment = $periodPayments->first(fn (CashPayment $p): bool => $p->amount > 0 && in_array($p->status, [CashPayment::STATUS_APPROVED, CashPayment::STATUS_PENDING], true));
                    $dendaPayment = $periodPayments->first(fn (CashPayment $p): bool => $p->late_fee_amount > 0 && in_array($p->status, [CashPayment::STATUS_APPROVED, CashPayment::STATUS_PENDING], true));

                    if ($paymentType === 'kas_only') {
                        if ($kasPayment?->status === CashPayment::STATUS_APPROVED) {
                            throw ValidationException::withMessages([
                                'periods' => "Periode {$periodName} kas pokok sudah berstatus lunas.",
                            ]);
                        }
                        if ($kasPayment?->status === CashPayment::STATUS_PENDING) {
                            throw ValidationException::withMessages([
                                'periods' => "Periode {$periodName} kas pokok sedang menunggu verifikasi ADKEU.",
                            ]);
                        }
                    } elseif ($paymentType === 'denda_only') {
                        if ($dendaPayment?->status === CashPayment::STATUS_APPROVED) {
                            throw ValidationException::withMessages([
                                'periods' => "Denda periode {$periodName} sudah lunas.",
                            ]);
                        }
                        if ($dendaPayment?->status === CashPayment::STATUS_PENDING) {
                            throw ValidationException::withMessages([
                                'periods' => "Denda periode {$periodName} sedang menunggu verifikasi ADKEU.",
                            ]);
                        }
                    } else { // kas_dan_denda
                        if ($kasPayment?->status === CashPayment::STATUS_APPROVED) {
                            throw ValidationException::withMessages([
                                'periods' => "Periode {$periodName} sudah berstatus lunas.",
                            ]);
                        }
                        if ($kasPayment?->status === CashPayment::STATUS_PENDING) {
                            throw ValidationException::withMessages([
                                'periods' => "Periode {$periodName} sedang menunggu verifikasi ADKEU.",
                            ]);
                        }
                        if ($dendaPayment?->status === CashPayment::STATUS_PENDING) {
                            throw ValidationException::withMessages([
                                'periods' => "Periode {$periodName} sedang menunggu verifikasi ADKEU.",
                            ]);
                        }
                    }
                }

                $manualAmount = isset($data['amount']) && (int) $data['amount'] > 0 ? (int) $data['amount'] : null;

                foreach ($periods as $period) {
                    $periodKey = $period->format('Y-m');
                    $periodPayments = $payments->get($periodKey, collect());
                    $isOverdue = CashPayment::isPeriodOverdue($period, $submittedAt);

                    if ($paymentType === 'kas_only') {
                        $itemAmount = ($manualAmount !== null && $periods->count() === 1)
                            ? $manualAmount
                            : CashPayment::AMOUNT;
                        $lateFee = 0;
                        $targetPayment = $periodPayments->first(fn (CashPayment $p): bool => $p->amount > 0 && $p->status === CashPayment::STATUS_REJECTED);
                    } elseif ($paymentType === 'denda_only') {
                        $itemAmount = 0;
                        $lateFee = ($manualAmount !== null && $periods->count() === 1)
                            ? $manualAmount
                            : (int) config('cash.late_fee_per_month', CashPayment::LATE_FEE_AMOUNT);
                        $targetPayment = $periodPayments->first(fn (CashPayment $p): bool => $p->late_fee_amount > 0 && $p->status === CashPayment::STATUS_REJECTED);
                    } else { // kas_dan_denda
                        if ($manualAmount !== null && $periods->count() === 1) {
                            if ($manualAmount >= CashPayment::AMOUNT) {
                                $itemAmount = CashPayment::AMOUNT;
                                $lateFee = $manualAmount - CashPayment::AMOUNT;
                            } else {
                                $itemAmount = $manualAmount;
                                $lateFee = 0;
                            }
                        } else {
                            $itemAmount = CashPayment::AMOUNT;
                            $lateFee = $isOverdue ? (int) config('cash.late_fee_per_month', CashPayment::LATE_FEE_AMOUNT) : 0;
                        }
                        $targetPayment = $periodPayments->first(fn (CashPayment $p): bool => $p->status === CashPayment::STATUS_REJECTED);
                    }

                    if ($targetPayment?->proof_path) {
                        $oldProofPaths[] = $targetPayment->proof_path;
                    }

                    $attributes = [
                        'submission_group_id' => $submissionGroupId,
                        'amount' => $itemAmount,
                        'late_fee_amount' => $lateFee,
                        'proof_path' => $proofPath,
                        'submitted_at' => $submittedAt,
                        'status' => CashPayment::STATUS_PENDING,
                        'reviewed_by' => null,
                        'reviewed_at' => null,
                        'rejection_reason' => null,
                    ];

                    if ($targetPayment) {
                        $targetPayment->update($attributes);
                    } else {
                        CashPayment::create($attributes + [
                            'user_id' => $request->user()->id,
                            'period' => $period->toDateString(),
                        ]);
                    }
                }
            });
        } catch (Throwable $exception) {
            $disk->delete($proofPath);
            throw $exception;
        }

        foreach (array_unique(array_filter($oldProofPaths)) as $oldProofPath) {
            $stillReferenced = CashPayment::query()
                ->where('proof_path', $oldProofPath)
                ->exists();

            if (! $stillReferenced) {
                $disk->delete($oldProofPath);
            }
        }

        return redirect()->route($this->paymentPageRoute($request))
            ->with('success', 'Bukti pembayaran untuk '.$periods->count().' periode berhasil dikirim untuk diverifikasi ADKEU.');
    }

    public function reviewIndex(Request $request): View
    {
        $status = $request->query('status', CashPayment::STATUS_PENDING);
        if (! in_array($status, ['all', 'pending', 'approved', 'rejected'], true)) {
            $status = CashPayment::STATUS_PENDING;
        }

        $query = CashPayment::with('member')->orderByDesc('created_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('bendahara.validasi-pembayaran', [
            'payments' => $query->get(),
            'status' => $status,
            'counts' => CashPayment::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function evidence(CashPayment $payment)
    {
        $disk = Storage::disk((string) config('cash.proof_disk', 'local'));
        abort_unless($disk->exists($payment->proof_path), 404);

        return $disk->response($payment->proof_path);
    }

    public function approve(Request $request, CashPayment $payment): RedirectResponse
    {
        DB::transaction(function () use ($request, $payment): void {
            $lockedPayment = CashPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status !== CashPayment::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran ini sudah diproses.',
                ]);
            }

            $this->recordApproval($lockedPayment, $request->user()->id, now());
        });

        return redirect()->route('bendahara.validasi-pembayaran')
            ->with('success', 'Pembayaran berhasil disetujui dan dicatat sebagai kas masuk.');
    }

    public function approveMultiple(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payments' => ['required', 'array', 'min:1'],
            'payments.*' => ['required', 'integer', 'distinct', 'exists:cash_payments,id'],
        ], [
            'payments.required' => 'Pilih minimal satu pembayaran untuk disetujui.',
            'payments.min' => 'Pilih minimal satu pembayaran untuk disetujui.',
            'payments.*.distinct' => 'Pembayaran yang sama tidak boleh dipilih lebih dari sekali.',
        ]);

        $approvedCount = count($data['payments']);

        DB::transaction(function () use ($data, $request): void {
            $payments = CashPayment::query()
                ->whereIn('id', $data['payments'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($payments->count() !== count($data['payments']) ||
                $payments->contains(fn (CashPayment $payment): bool => $payment->status !== CashPayment::STATUS_PENDING)) {
                throw ValidationException::withMessages([
                    'payments' => 'Semua pembayaran yang dipilih harus masih menunggu validasi.',
                ]);
            }

            $reviewedAt = now();
            foreach ($payments as $payment) {
                $this->recordApproval($payment, $request->user()->id, $reviewedAt);
            }
        });

        return redirect()->route('bendahara.validasi-pembayaran')
            ->with('success', $approvedCount.' pembayaran berhasil disetujui dan dicatat sebagai kas masuk.');
    }

    private function recordApproval(CashPayment $payment, int $reviewerId, Carbon $reviewedAt): void
    {
        CashTransaction::create([
            'user_id' => $payment->user_id,
            'created_by' => $reviewerId,
            'type' => 'masuk',
            'transaction_date' => $reviewedAt->toDateString(),
            'description' => 'Kas Bulanan - '.$payment->period->locale('id')->translatedFormat('F Y'),
            'amount' => $payment->amount + $payment->late_fee_amount,
            'notes' => $payment->late_fee_amount > 0
                ? 'Kas bulanan Rp'.number_format($payment->amount, 0, ',', '.')
                    .' dan denda keterlambatan Rp'.number_format($payment->late_fee_amount, 0, ',', '.').'.'
                : 'Pembayaran disetujui dari bukti transfer.',
        ]);

        $payment->update([
            'status' => CashPayment::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => $reviewedAt,
            'rejection_reason' => null,
        ]);
    }

    public function reject(Request $request, CashPayment $payment): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $updated = CashPayment::query()
            ->whereKey($payment->id)
            ->where('status', CashPayment::STATUS_PENDING)
            ->update([
                'status' => CashPayment::STATUS_REJECTED,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran ini sudah diproses.',
            ]);
        }

        return redirect()->route('bendahara.validasi-pembayaran')
            ->with('success', 'Pembayaran ditolak. Anggota dapat mengirim bukti pengganti.');
    }

    private function paymentPageRoute(Request $request): string
    {
        return $request->user()->role === 'presbem'
            ? 'presbem.bayar-kas'
            : 'anggota.bayar-kas';
    }

    private function memberLateFees(Request $request): Collection
    {
        $currentPeriod = now()->startOfMonth();
        $payments = CashPayment::query()
            ->where('user_id', $request->user()->id)
            ->whereBetween('period', [now()->startOfYear()->toDateString(), $currentPeriod->copy()->endOfMonth()->toDateString()])
            ->get()
            ->keyBy(fn (CashPayment $payment): string => $payment->period->format('Y-m'));

        $penalties = $payments
            ->filter(fn (CashPayment $payment): bool => $payment->late_fee_amount > 0
                && in_array($payment->status, [CashPayment::STATUS_APPROVED, CashPayment::STATUS_PENDING], true))
            ->map(function (CashPayment $payment): array {
                $feePerMonth = max(1, (int) config('cash.late_fee_per_month', 10000));

                return [
                    'period' => $payment->period->copy(),
                    'amount' => $payment->late_fee_amount,
                    'late_days' => 0,
                    'late_months' => (int) ceil($payment->late_fee_amount / $feePerMonth),
                    'status' => $payment->status === CashPayment::STATUS_APPROVED ? 'paid' : 'pending',
                ];
            })
            ->values();

        $accountLateFee = CashPayment::accountLateFeeSummaryFor($request->user()->id, now());

        if ($accountLateFee['fee_amount'] > 0 && $accountLateFee['period']) {
            $penalties->push([
                'period' => $accountLateFee['period'],
                'amount' => $accountLateFee['fee_amount'],
                'late_days' => $accountLateFee['late_days'],
                'late_months' => $accountLateFee['late_months'],
                'status' => 'unpaid',
            ]);
        }

        return $penalties;
    }
}
