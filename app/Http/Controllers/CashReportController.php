<?php

namespace App\Http\Controllers;

use App\Models\CashPayment;
use App\Models\CashTransaction;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashReportController extends Controller
{
    public function dashboard(Request $request): View
    {
        $transactions = CashTransaction::query()
            ->with('member:id,name,nim')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
        $pemasukan = (int) CashTransaction::query()->where('type', 'masuk')->sum('amount');
        $pengeluaran = (int) CashTransaction::query()->where('type', 'keluar')->sum('amount');

        return view($request->user()->role === 'presbem' ? 'presbem.dashboard' : 'bendahara.dashboard', [
            'saldo' => $pemasukan - $pengeluaran,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'anggotaCount' => User::query()
                ->whereIn('role', ['anggota', 'bendahara', 'adkeu', 'psdi', 'presbem'])
                ->count(),
            'pendingPaymentsCount' => CashPayment::query()
                ->where('status', CashPayment::STATUS_PENDING)
                ->count(),
            'transaksiTerbaru' => $transactions,
            'kegiatanAktif' => Kegiatan::query()
                ->whereIn('status', [Kegiatan::STATUS_AKAN_DATANG, Kegiatan::STATUS_BERLANGSUNG])
                ->whereDate('tanggal', '>=', now('Asia/Jakarta')->toDateString())
                ->orderBy('tanggal')
                ->limit(5)
                ->get(),
        ]);
    }

    public function index(Request $request): View
    {
        return view('presbem.rekap-kas', $this->reportData() + [
            'layout' => $request->routeIs('presbem.*') ? 'layouts.presbem' : 'layouts.bendahara',
            'exportRoute' => $request->routeIs('presbem.*')
                ? 'presbem.rekap-kas.export'
                : 'bendahara.rekap-kas.export',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $report = $this->reportData();

        $filename = 'rekap-kas-bem-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.csv';

        return Response::streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Gagal membuka aliran berkas rekap kas.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Ringkasan Kas Organisasi']);
            fputcsv($output, ['Total Pemasukan', (string) $report['pemasukan']]);
            fputcsv($output, ['Total Pengeluaran', (string) $report['pengeluaran']]);
            fputcsv($output, ['Saldo Akhir', (string) $report['saldo']]);
            fputcsv($output, []);
            fputcsv($output, ['Daftar Transaksi Kas']);
            fputcsv($output, ['Tanggal', 'Jenis', 'Deskripsi', 'Jumlah', 'Fungsionaris']);
            foreach ($report['transaksi'] as $tx) {
                fputcsv($output, array_map($this->csvSafeValue(...), [
                    $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '',
                    $tx->type,
                    $tx->description,
                    (string) $tx->amount,
                    $tx->member?->name ?? '-',
                ]));
            }
            fputcsv($output, []);
            fputcsv($output, ['Rekapitulasi per Kementerian / Kebiroan']);
            fputcsv($output, ['Kementerian / Kebiroan', 'Jumlah Fungsionaris', 'Total Kas Masuk', 'Total Denda Masuk', 'Total Tunggakan']);
            foreach ($report['rekapDepartemen'] as $dept) {
                fputcsv($output, [
                    $dept['nama'],
                    (string) $dept['anggota_count'],
                    (string) $dept['kas_lunas'],
                    (string) $dept['denda_lunas'],
                    (string) $dept['total_tunggakan'],
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, ['Status Pembayaran Kas per Fungsionaris']);
            fputcsv($output, array_merge(
                ['Nama', 'NIM', 'Kementerian / Kebiroan'],
                array_column($report['periode'], 'label'),
                ['Total Lunas', 'Total Denda Dibayar', 'Total Tunggakan']
            ));

            foreach ($report['anggota'] as $member) {
                $paidCount = 0;
                $statuses = [];
                $dendaPaid = 0;
                $tunggakan = 0;

                foreach ($report['periode'] as $period) {
                    $key = $member->id.'|'.$period['key'];
                    $payment = $report['pembayaranMap']->get($key);

                    if ($payment && $payment->status === CashPayment::STATUS_APPROVED) {
                        $statuses[] = 'Lunas';
                        $paidCount++;
                        $dendaPaid += $payment->late_fee_amount;
                    } elseif ($payment && $payment->status === CashPayment::STATUS_PENDING) {
                        $statuses[] = 'Menunggu Validasi';
                    } elseif ($payment && $payment->status === CashPayment::STATUS_REJECTED) {
                        $statuses[] = 'Ditolak';
                        $tunggakan += CashPayment::AMOUNT + 10000;
                    } else {
                        $statuses[] = 'Belum Dibayar';
                        $tunggakan += CashPayment::AMOUNT + 10000;
                    }
                }

                fputcsv($output, array_map($this->csvSafeValue(...), array_merge(
                    [$member->name, $member->nim ?? '', $member->departemen ?? '-'],
                    $statuses,
                    [
                        $paidCount.'/'.count($report['periode']),
                        (string) $dendaPaid,
                        (string) $tunggakan,
                    ]
                )));
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, mixed>
     */
    private function reportData(): array
    {
        $transactions = CashTransaction::query()
            ->with('member:id,name,nim,departemen')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get(['id', 'user_id', 'type', 'transaction_date', 'description', 'amount']);
        $pemasukan = (int) $transactions->where('type', 'masuk')->sum('amount');
        $pengeluaran = (int) $transactions->where('type', 'keluar')->sum('amount');

        $periode = collect(range(6, 0))
            ->map(fn (int $offset): array => [
                'key' => now()->startOfMonth()->subMonths($offset)->format('Y-m'),
                'label' => now()->startOfMonth()->subMonths($offset)->locale('id')->isoFormat('MMM Y'),
                'carbon' => now()->startOfMonth()->subMonths($offset),
            ])
            ->values()
            ->all();

        $byMonth = $transactions->groupBy(
            fn (CashTransaction $transaction): string => $transaction->transaction_date->format('Y-m')
        );
        $bulan = array_map(fn (array $period): array => [
            'label' => $period['label'],
            'masuk' => (int) $byMonth->get($period['key'], collect())->where('type', 'masuk')->sum('amount'),
            'keluar' => (int) $byMonth->get($period['key'], collect())->where('type', 'keluar')->sum('amount'),
        ], $periode);

        $firstPeriod = $periode[0]['key'].'-01';
        $lastPeriod = $periode[count($periode) - 1]['key'].'-'.now()->endOfMonth()->format('d');

        $allMembers = User::query()
            ->whereIn('role', ['anggota', 'presbem'])
            ->orderBy('departemen')
            ->orderBy('name')
            ->get(['id', 'name', 'nim', 'departemen', 'jabatan', 'role']);

        $allPayments = CashPayment::query()
            ->whereBetween('period', [$firstPeriod, $lastPeriod])
            ->orderBy('id')
            ->get();

        // Sort so that approved > pending > rejected, then key by user_id|period
        $pembayaranMap = $allPayments->sortBy(function (CashPayment $p): string {
            $statusRank = match ($p->status) {
                CashPayment::STATUS_APPROVED => 3,
                CashPayment::STATUS_PENDING => 2,
                default => 1,
            };

            return sprintf('%d_%010d', $statusRank, $p->id);
        })->keyBy(fn (CashPayment $p): string => $p->user_id.'|'.$p->period->format('Y-m'));

        // Pengelompokan & Rekapitulasi per Kementerian / Kebiroan
        $departemenList = $allMembers->pluck('departemen')->filter()->unique()->values();
        $rekapDepartemen = [];

        foreach ($departemenList as $deptName) {
            $deptMembers = $allMembers->where('departemen', $deptName);
            $deptKasLunas = 0;
            $deptDendaLunas = 0;
            $deptTunggakanKas = 0;
            $deptTunggakanDenda = 0;

            foreach ($deptMembers as $member) {
                foreach ($periode as $period) {
                    $key = $member->id.'|'.$period['key'];
                    $payment = $pembayaranMap->get($key);

                    if ($payment && $payment->status === CashPayment::STATUS_APPROVED) {
                        $deptKasLunas += $payment->amount;
                        $deptDendaLunas += $payment->late_fee_amount;
                    } elseif ($payment && $payment->status === CashPayment::STATUS_PENDING) {
                        // Menunggu validasi
                    } else {
                        // Belum bayar / ditolak
                        $deptTunggakanKas += CashPayment::AMOUNT;
                        if (now()->greaterThan(CashPayment::deadlineFor($period['carbon']))) {
                            $deptTunggakanDenda += 10000;
                        }
                    }
                }
            }

            $rekapDepartemen[] = [
                'nama' => $deptName,
                'anggota_count' => $deptMembers->count(),
                'kas_lunas' => $deptKasLunas,
                'denda_lunas' => $deptDendaLunas,
                'total_diterima' => $deptKasLunas + $deptDendaLunas,
                'tunggakan_kas' => $deptTunggakanKas,
                'tunggakan_denda' => $deptTunggakanDenda,
                'total_tunggakan' => $deptTunggakanKas + $deptTunggakanDenda,
            ];
        }

        $selectedDept = request()->query('departemen');
        $filteredMembers = $selectedDept && $selectedDept !== 'all'
            ? $allMembers->where('departemen', $selectedDept)->values()
            : $allMembers;

        return [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $pemasukan - $pengeluaran,
            'transaksi' => $transactions,
            'bulan' => $bulan,
            'anggota' => $filteredMembers,
            'allMembers' => $allMembers,
            'periode' => $periode,
            'pembayaran' => $pembayaranMap,
            'pembayaranMap' => $pembayaranMap,
            'departemenList' => $departemenList,
            'rekapDepartemen' => $rekapDepartemen,
            'selectedDept' => $selectedDept ?: 'all',
        ];
    }

    private function csvSafeValue(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }
}
