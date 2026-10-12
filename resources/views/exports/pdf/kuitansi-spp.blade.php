<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran SPP - {{ $nomor_kuitansi }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #183330;
            background-color: #f7faf9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #d3dcdb;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(24, 51, 48, 0.08);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0e6b63;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .logo-box {
            width: 48px;
            height: 48px;
            background-color: #0e6b63;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
            font-size: 18px;
        }
        .school-info h1 {
            font-size: 18px;
            font-weight: 800;
            color: #183330;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .school-info p {
            font-size: 11px;
            color: #5b6d69;
            margin: 2px 0 0;
        }
        .receipt-title {
            text-align: right;
        }
        .receipt-title h2 {
            font-size: 16px;
            font-weight: 800;
            color: #0e6b63;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .receipt-title .receipt-number {
            font-size: 13px;
            font-weight: 700;
            color: #183330;
            margin-top: 4px;
        }
        .badge-lunas {
            display: inline-block;
            background: #eef8f5;
            color: #18794e;
            border: 1px solid #2f9e83;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
            background: #f7faf9;
            border: 1px solid #d3dcdb;
            border-radius: 8px;
            padding: 16px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #5b6d69;
            font-weight: 600;
        }
        .info-val {
            font-size: 13px;
            font-weight: 600;
            color: #183330;
            margin-top: 2px;
        }
        .table-payment {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .table-payment th {
            background-color: #eef8f5;
            color: #183330;
            font-weight: 700;
            font-size: 12px;
            text-align: left;
            padding: 10px 14px;
            border-bottom: 1px solid #d3dcdb;
        }
        .table-payment td {
            padding: 12px 14px;
            border-bottom: 1px solid #d3dcdb;
            color: #183330;
        }
        .amount-highlight {
            background: #eef8f5;
            border: 1px solid #d3dcdb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .amount-highlight .nominal {
            font-size: 22px;
            font-weight: 800;
            color: #0e6b63;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 36px;
            padding-top: 16px;
            text-align: center;
        }
        .signature-box p {
            margin: 0;
            color: #5b6d69;
            font-size: 12px;
        }
        .signature-box .signature-space {
            height: 60px;
        }
        .signature-box .signer-name {
            font-weight: 700;
            color: #183330;
            text-decoration: underline;
        }
        .toolbar {
            max-width: 800px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .btn-print {
            background-color: #0e6b63;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover {
            background-color: #0a4f49;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .toolbar {
                display: none !important;
            }
            .container {
                border: none;
                box-shadow: none;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zm1 2h4v2H6V3zm6 8v2H4v-2h8z"/>
            </svg>
            Cetak Kuitansi
        </button>
    </div>

    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="header-brand">
                <div class="logo-box">SPP</div>
                <div class="school-info">
                    <h1>SISTEM INFORMASI SPP SEKOLAH</h1>
                    <p>Bukti Tanda Terima Pembayaran Iuran Resmi</p>
                </div>
            </div>
            <div class="receipt-title">
                <h2>Kuitansi Pembayaran</h2>
                <div class="receipt-number">No: {{ $nomor_kuitansi }}</div>
                <div class="badge-lunas">BUKTI SAH SISTEM</div>
            </div>
        </div>

        <!-- IDENTITAS SISWA & TRANSAKSI -->
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nama Siswa</span>
                <span class="info-val">{{ $nama_siswa }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Waktu Transaksi</span>
                <span class="info-val">{{ $tanggal_bayar }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nomor Induk Siswa (NIS / NISN)</span>
                <span class="info-val">{{ $nis }} / {{ $nisn ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Metode Pembayaran</span>
                <span class="info-val">{{ strtoupper(is_object($metode) ? $metode->value : $metode) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Kelas / Rombel</span>
                <span class="info-val">{{ $kelas }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Kasir / Petugas Penerima</span>
                <span class="info-val">{{ $petugas }}</span>
            </div>
        </div>

        <!-- DETAIL TAGIHAN -->
        <table class="table-payment">
            <thead>
                <tr>
                    <th>Deskripsi Pembayaran</th>
                    <th>Periode SPP</th>
                    <th style="text-align: right;">Jumlah Dibayar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Iuran Penyelenggaraan Pendidikan (SPP)</strong>
                        @if(!empty($catatan))
                            <div style="font-size: 11px; color: #5b6d69; margin-top: 2px;">Catatan: {{ $catatan }}</div>
                        @endif
                    </td>
                    <td>{{ $periode }}</td>
                    <td style="text-align: right; font-weight: 700; color: #0e6b63;">
                        Rp {{ number_format($jumlah_dibayar, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- JUMLAH DIBAYAR & SISA -->
        <div class="amount-highlight">
            <div>
                <span class="info-label">Total Pembayaran Diterima</span>
                <div class="nominal">Rp {{ number_format($jumlah_dibayar, 0, ',', '.') }}</div>
            </div>
            <div style="text-align: right;">
                <span class="info-label">Sisa Tagihan Periode Ini</span>
                <div style="font-size: 15px; font-weight: 700; color: {{ $sisa_tagihan > 0 ? '#b42318' : '#18794e' }};">
                    {{ $sisa_tagihan > 0 ? 'Rp ' . number_format($sisa_tagihan, 0, ',', '.') : 'LUNAS' }}
                </div>
            </div>
        </div>

        <!-- TANDA TANGAN -->
        <div class="signatures">
            <div class="signature-box">
                <p>Penyetor / Siswa</p>
                <div class="signature-space"></div>
                <div class="signer-name">{{ $nama_siswa }}</div>
            </div>
            <div class="signature-box">
                <p>Petugas Kasir Tata Usaha</p>
                <div class="signature-space"></div>
                <div class="signer-name">{{ $petugas }}</div>
            </div>
        </div>
    </div>

</body>
</html>
