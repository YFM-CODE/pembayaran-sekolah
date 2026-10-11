<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi SPP - {{ $startDate }} s.d {{ $endDate }}</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; line-height: 1.3; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 14px; }
        .kop h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .kop p { margin: 2px 0 0; font-size: 11px; }
        .judul { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 12px; text-transform: uppercase; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 5px 6px; }
        th { background: #f3f4f6; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { margin-top: 15px; width: 45%; float: right; border-collapse: collapse; }
        .ttd-area { margin-top: 80px; width: 100%; display: flex; justify-content: space-between; }
        .ttd { width: 220px; text-align: center; }
        .space { height: 60px; }
        .btn-print { position: fixed; top: 15px; right: 15px; padding: 6px 14px; background: #4f46e5; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        @media print { .btn-print { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen</button>

    <div class="kop">
        <h2>{{ env('NAMA_SEKOLAH', config('app.name', 'SEKOLAH')) }}</h2>
        <p>LAPORAN EKSEKUTIF REKAPITULASI PENERIMAAN SPP</p>
    </div>

    <div class="judul">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s.d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Kuitansi</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Periode Tagihan</th>
                <th>Metode</th>
                <th>Waktu Transaksi</th>
                <th class="text-right">Jumlah Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi as $idx => $t)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $t->nomor_kuitansi }}</td>
                <td class="text-center">{{ $t->tagihan?->siswa?->nis ?? '-' }}</td>
                <td>{{ $t->tagihan?->siswa?->nama_lengkap ?? '-' }}</td>
                <td class="text-center">{{ $t->tagihan?->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                <td class="text-center">Bln {{ $t->tagihan?->bulan }}/{{ $t->tagihan?->tahun }}</td>
                <td class="text-center">{{ strtoupper(is_object($t->metode_pembayaran) ? $t->metode_pembayaran->value : $t->metode_pembayaran) }}</td>
                <td class="text-center">{{ $t->waktu_transaksi }}</td>
                <td class="text-right">Rp {{ number_format($t->jumlah_dibayar, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-box">
        <tr>
            <td><strong>Total Kas Tunai (Loket):</strong></td>
            <td class="text-right">Rp {{ number_format($totalTunai, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Transfer Bank:</strong></td>
            <td class="text-right">Rp {{ number_format($totalTransfer, 0, ',', '.') }}</td>
        </tr>
        <tr style="background: #f3f4f6;">
            <td><strong>TOTAL KESELURUHAN:</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($totalSemua, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div class="ttd-area">
        <div class="ttd">
            <p>Mengetahui,<br>Kepala Sekolah</p>
            <div class="space"></div>
            <p><strong>{{ auth()->user()->name }}</strong></p>
        </div>
        <div class="ttd">
            <p>{{ now()->translatedFormat('d F Y') }}<br>Bendahara Sekolah</p>
            <div class="space"></div>
            <p><strong>Hj. Siti Aminah, S.E.</strong></p>
        </div>
    </div>
</body>
</html>