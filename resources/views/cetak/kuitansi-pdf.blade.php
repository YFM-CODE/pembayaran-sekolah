<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi - {{ $transaksi->nomor_kuitansi }}</title>
    <style>
        @page {
            size: a5 landscape;
            margin: 8mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            font-size: 11.5px;
            line-height: 1.25;
        }
        
        .kuitansi-card {
            width: 100%;
            border: 1.5px solid #1f2937;
            padding: 8px;
        }

        .kuitansi-inner {
            border: 1px solid #9ca3af;
            padding: 10px 14px;
            position: relative;
        }

        /* Kop Surat */
        .kop-header {
            border-bottom: 2px double #111827;
            padding-bottom: 5px;
            margin-bottom: 8px;
            text-align: center;
        }
        .kop-header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-header p {
            margin: 2px 0 0;
            font-size: 10px;
            font-family: Arial, sans-serif;
            color: #4b5563;
        }

        /* Judul Tabel Header */
        .table-header {
            width: 100%;
            margin-bottom: 8px;
            border-bottom: 1px dashed #d1d5db;
            padding-bottom: 4px;
        }
        .judul-kuitansi {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-decoration: underline;
        }
        .no-kuitansi {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 11px;
        }

        /* Tabel Rincian */
        .tabel-rincian {
            width: 100%;
            border-collapse: collapse;
        }
        .tabel-rincian td {
            padding: 3px 2px;
            vertical-align: top;
        }
        .label-kolom {
            width: 160px;
            font-weight: bold;
        }
        .titik-dua {
            width: 12px;
            text-align: center;
        }

        /* Kotak Terbilang */
        .kotak-terbilang {
            background-color: #f9fafb;
            border: 1px dashed #9ca3af;
            padding: 4px 8px;
            font-style: italic;
            font-weight: bold;
            color: #1f2937;
            margin: 6px 0;
            font-family: Arial, sans-serif;
            font-size: 10.5px;
        }

        /* Badge Nominal & Cap Lunas */
        .badge-nominal {
            display: inline-block;
            background: #eef2ff;
            border: 1.5px solid #4338ca;
            padding: 4px 12px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            font-weight: bold;
            color: #312e81;
        }

        .stempel-lunas {
            display: inline-block;
            border: 2px solid #dc2626;
            color: #dc2626;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 13px;
            padding: 2px 10px;
            letter-spacing: 2px;
            margin-left: 15px;
        }

        /* Tabel Tanda Tangan */
        .tabel-ttd {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .tabel-ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .ttd-ruang {
            height: 38px;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Tombol Aksi Layar Browser */
        .aksi-bar {
            max-width: 820px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: space-between;
        }
        .btn-kembali {
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-cetak {
            background: #4f46e5;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        @media print {
            .aksi-bar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    @php
        if (!function_exists('konversiTerbilang')) {
            function konversiTerbilang($angka) {
                $angka = abs((int)$angka);
                $baca = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
                $hasil = "";
                if ($angka < 12) {
                    $hasil = " " . $baca[$angka];
                } else if ($angka < 20) {
                    $hasil = konversiTerbilang($angka - 10) . " Belas";
                } else if ($angka < 100) {
                    $hasil = konversiTerbilang((int)($angka / 10)) . " Puluh" . konversiTerbilang($angka % 10);
                } else if ($angka < 200) {
                    $hasil = " Seratus" . konversiTerbilang($angka - 100);
                } else if ($angka < 1000) {
                    $hasil = konversiTerbilang((int)($angka / 100)) . " Ratus" . konversiTerbilang($angka % 10);
                } else if ($angka < 2000) {
                    $hasil = " Seribu" . konversiTerbilang($angka - 1000);
                } else if ($angka < 1000000) {
                    $hasil = konversiTerbilang((int)($angka / 1000)) . " Ribu" . konversiTerbilang($angka % 1000);
                } else if ($angka < 1000000000) {
                    $hasil = konversiTerbilang((int)($angka / 1000000)) . " Juta" . konversiTerbilang($angka % 1000000);
                }
                return $hasil;
            }
        }

        $metodeBayar = is_object($transaksi->metode_pembayaran) && property_exists($transaksi->metode_pembayaran, 'value')
            ? $transaksi->metode_pembayaran->value
            : (string) $transaksi->metode_pembayaran;

        $statusTagihan = is_object($transaksi->tagihan?->status) && property_exists($transaksi->tagihan->status, 'value')
            ? $transaksi->tagihan->status->value
            : (string) ($transaksi->tagihan?->status ?? 'lunas');

        $siswa = $transaksi->tagihan?->siswa;
        $nominalBayar = (float) $transaksi->jumlah_dibayar;
        $sisaTagihan = (float) ($transaksi->tagihan?->sisa_tagihan ?? 0);

        $namaSekolah = env('NAMA_SEKOLAH', config('app.name', 'SISTEM KEUANGAN SPP'));
        $alamatSekolah = env('ALAMAT_SEKOLAH');
        $kontakSekolah = env('KONTAK_SEKOLAH');
        $kotaTerbit = env('KOTA_TERBIT');
    @endphp

    @if(empty($isPdfDownload))
    <div class="aksi-bar">
        <a href="javascript:window.close()" class="btn-kembali">← Tutup Tab</a>
        <div>
            <a href="{{ route('cetak-kuitansi.kuitansi', ['transaksiSpp' => $transaksi->id, 'action' => 'download']) }}" 
               class="btn-kembali" 
               style="background: #059669; color: #ffffff; border: none; margin-right: 6px;">
                📥 Download PDF
            </a>
            <button onclick="window.print()" class="btn-cetak">
                🖨️ Cetak Kuitansi
            </button>
        </div>
    </div>
    @endif

    <div class="kuitansi-card">
        <div class="kuitansi-inner">
            
            <!-- Kop Surat -->
            <div class="kop-header">
                <h1>{{ $namaSekolah }}</h1>
                @if($alamatSekolah)
                    <p>{{ $alamatSekolah }}</p>
                @endif
                @if($kontakSekolah)
                    <p>{{ $kontakSekolah }}</p>
                @endif
            </div>

            <!-- Baris Judul & No Kuitansi (Table-based layout) -->
            <table class="table-header">
                <tr>
                    <td class="judul-kuitansi">KUITANSI PEMBAYARAN</td>
                    <td class="no-kuitansi">NO: {{ $transaksi->nomor_kuitansi }}</td>
                </tr>
            </table>

            <!-- Tabel Isi Kuitansi -->
            <table class="tabel-rincian">
                <tr>
                    <td class="label-kolom">Telah Diterima Dari</td>
                    <td class="titik-dua">:</td>
                    <td>
                        <strong>{{ $siswa?->nama_lengkap ?? '-' }}</strong> 
                        &nbsp;(NIS: {{ $siswa?->nis ?? '-' }} / Kelas: {{ $siswa?->kelas?->nama_kelas ?? '-' }})
                    </td>
                </tr>
                <tr>
                    <td class="label-kolom">Untuk Pembayaran</td>
                    <td class="titik-dua">:</td>
                    <td>
                        SPP Bulan <strong>{{ $transaksi->tagihan?->bulan ?? '-' }}</strong> Tahun <strong>{{ $transaksi->tagihan?->tahun ?? '-' }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="label-kolom">Metode Transaksi</td>
                    <td class="titik-dua">:</td>
                    <td>
                        <strong style="text-transform: uppercase;">{{ $metodeBayar }}</strong>
                        @if($transaksi->nomor_referensi_bank)
                            &nbsp;(No. Ref: <code>{{ $transaksi->nomor_referensi_bank }}</code>)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label-kolom">Sisa Tagihan Periode Ini</td>
                    <td class="titik-dua">:</td>
                    <td>
                        Rp {{ number_format($sisaTagihan, 0, ',', '.') }} 
                        <strong style="text-transform: uppercase; color: {{ $sisaTagihan <= 0 ? '#059669' : '#dc2626' }};">
                            ({{ $statusTagihan }})
                        </strong>
                    </td>
                </tr>
            </table>

            <!-- Kotak Terbilang -->
            <div class="kotak-terbilang">
                Terbilang: # {{ trim(konversiTerbilang($nominalBayar)) }} Rupiah #
            </div>

            <!-- Nominal & Stempel Lunas (Sejajar) -->
            <table style="width: 100%; margin: 4px 0;">
                <tr>
                    <td style="vertical-align: middle;">
                        <span class="badge-nominal">
                            Rp {{ number_format($nominalBayar, 0, ',', '.') }},-
                        </span>
                        @if($sisaTagihan <= 0 || $statusTagihan === 'lunas')
                            <span class="stempel-lunas">LUNAS</span>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Area Tanda Tangan (Table-based layout) -->
            <table class="tabel-ttd">
                <tr>
                    <td>
                        <p style="margin: 0;">Penyetor / Orang Tua,</p>
                        <div class="ttd-ruang"></div>
                        <p class="ttd-nama" style="margin: 0;">{{ $siswa?->nama_lengkap ?? 'Wali Murid' }}</p>
                    </td>
                    <td>
                        <p style="margin: 0;">
                            {{ $kotaTerbit ? $kotaTerbit . ', ' : '' }}{{ \Carbon\Carbon::parse($transaksi->waktu_transaksi)->translatedFormat('d F Y') }}<br>
                            Kasir Loket Tata Usaha,
                        </p>
                        <div class="ttd-ruang"></div>
                        <p class="ttd-nama" style="margin: 0;">{{ $transaksi->petugas?->name ?? 'Kasir Loket' }}</p>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>
</html>