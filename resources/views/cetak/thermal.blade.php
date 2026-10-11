<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk SPP - {{ $transaksi->nomor_kuitansi }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm;
            max-width: 58mm;
            margin: 0 auto;
            padding: 8px 6px;
            color: #000;
            font-size: 11px;
            line-height: 1.25;
            background-color: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .btn-panel {
            margin-bottom: 10px;
            text-align: center;
        }
        .btn-print {
            background: #111827;
            color: #fff;
            border: none;
            padding: 5px 12px;
            font-size: 11px;
            border-radius: 4px;
            cursor: pointer;
            font-family: Arial, sans-serif;
        }
        @media print {
            .btn-panel { display: none; }
            body { padding: 0; width: 100%; }
        }
    </style>
</head>
<body>

    @php
        $namaSekolah = env('NAMA_SEKOLAH', config('app.name', 'SEKOLAH'));
        $metodeBayar = is_object($transaksi->metode_pembayaran) && property_exists($transaksi->metode_pembayaran, 'value')
            ? $transaksi->metode_pembayaran->value
            : (string) $transaksi->metode_pembayaran;
        $siswa = $transaksi->tagihan?->siswa;
    @endphp

    <div class="btn-panel">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Struk</button>
    </div>

    <!-- Header Struk -->
    <div class="text-center">
        <span class="font-bold" style="font-size: 12px;">{{ strtoupper($namaSekolah) }}</span><br>
        BUKTI PEMBAYARAN SPP
    </div>

    <div class="separator"></div>

    <div class="row">
        <span>No:</span>
        <span class="font-bold">{{ $transaksi->nomor_kuitansi }}</span>
    </div>
    <div class="row">
        <span>Tgl:</span>
        <span>{{ \Carbon\Carbon::parse($transaksi->waktu_transaksi)->format('d/m/Y H:i') }}</span>
    </div>
    <div class="row">
        <span>Kasir:</span>
        <span>{{ $transaksi->petugas?->name ?? '-' }}</span>
    </div>

    <div class="separator"></div>

    <div class="row">
        <span>NIS:</span>
        <span>{{ $siswa?->nis ?? '-' }}</span>
    </div>
    <div>{{ substr($siswa?->nama_lengkap ?? '-', 0, 24) }}</div>
    <div class="row">
        <span>Kelas:</span>
        <span>{{ $siswa?->kelas?->nama_kelas ?? '-' }}</span>
    </div>
    <div class="row">
        <span>Periode:</span>
        <span>Bln {{ $transaksi->tagihan?->bulan }}/{{ $transaksi->tagihan?->tahun }}</span>
    </div>

    <div class="separator"></div>

    <div class="row font-bold">
        <span>BAYAR ({{ strtoupper($metodeBayar) }}):</span>
        <span>Rp {{ number_format($transaksi->jumlah_dibayar, 0, ',', '.') }}</span>
    </div>

    @if($transaksi->nomor_referensi_bank)
        <div style="font-size: 9px; word-break: break-all;">Ref: {{ $transaksi->nomor_referensi_bank }}</div>
    @endif

    <div class="row">
        <span>Sisa Tagihan:</span>
        <span>Rp {{ number_format($transaksi->tagihan?->sisa_tagihan ?? 0, 0, ',', '.') }}</span>
    </div>

    <div class="separator"></div>

    <div class="text-center" style="font-size: 9px; margin-top: 4px;">
        *** TERIMA KASIH ***<br>
        Simpan struk ini sebagai bukti pembayaran sah.
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>