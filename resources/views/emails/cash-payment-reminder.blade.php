<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengingat Pembayaran Kas</title>
</head>
<body>
    <h1>Pengingat Pembayaran Kas</h1>
    <p>Halo {{ $memberName }},</p>
    <p>Catatan kami menunjukkan pembayaran kas untuk periode berikut belum lunas dan telah melewati tenggat tanggal {{ config('cash.deadline_day', 1) }}:</p>
    <ul>
        @foreach($overduePeriods as $period)
            <li>{{ $period->locale('id')->translatedFormat('F Y') }} — Rp{{ number_format(\App\Models\CashPayment::AMOUNT, 0, ',', '.') }}</li>
        @endforeach
    </ul>
    <p>Silakan masuk ke Rekapin dan kirim bukti pembayaran. Jika Anda sudah membayar atau mengirim bukti yang masih menunggu verifikasi, Anda dapat mengabaikan pengingat ini.</p>
    <p>Terima kasih,<br>Tim Rekapin</p>
</body>
</html>
