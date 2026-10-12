<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\DispensasiSpp;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TransaksiSpp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiswaDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $siswa = Siswa::with(['kelas'])->where('user_id', $user->id)->first();
        $siswaId = $siswa?->id;

        $totalTagihanNominal = $siswaId ? (float) TagihanSpp::where('siswa_id', $siswaId)->sum('nominal_tagihan') : 0;
        $totalDibayarNominal = $siswaId ? (float) TagihanSpp::where('siswa_id', $siswaId)->sum('total_terbayar') : 0;
        $totalTunggakanNominal = $siswaId ? (float) TagihanSpp::where('siswa_id', $siswaId)->where('status', '!=', 'lunas')->sum('sisa_tagihan') : 0;

        $totalTagihanCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->count() : 0;
        $lunasCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->where('status', 'lunas')->count() : 0;
        $sebagianCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->where('status', 'sebagian')->count() : 0;
        $belumLunasCount = $siswaId ? TagihanSpp::where('siswa_id', $siswaId)->where('status', '!=', 'lunas')->count() : 0;

        $statusKeseluruhan = match (true) {
            $totalTagihanCount === 0 => 'belum_ada_tagihan',
            $belumLunasCount === 0 => 'lunas',
            $totalDibayarNominal > 0 => 'sebagian',
            default => 'belum_bayar',
        };

        $tagihanBelumLunas = $siswaId
            ? TagihanSpp::where('siswa_id', $siswaId)
                ->where('status', '!=', 'lunas')
                ->with(['tarif', 'keringanan'])
                ->orderBy('tahun', 'asc')
                ->orderBy('bulan', 'asc')
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'bulan' => $t->bulan,
                    'tahun' => $t->tahun,
                    'nominal_asli' => (float) $t->nominal_asli,
                    'nominal_potongan' => (float) $t->nominal_potongan,
                    'nominal_tagihan' => (float) $t->nominal_tagihan,
                    'total_terbayar' => (float) $t->total_terbayar,
                    'sisa_tagihan' => (float) $t->sisa_tagihan,
                    'status' => is_object($t->status) ? $t->status->value : (string) $t->status,
                    'jatuh_tempo' => $t->jatuh_tempo ? $t->jatuh_tempo->format('Y-m-d') : null,
                    'keterangan_tarif' => $t->tarif?->keterangan ?? ('Tarif SPP '.$t->tahun),
                    'nama_keringanan' => $t->keringanan?->nama_program,
                ])
                ->all()
            : [];

        $transaksiTerbaru = $siswaId
            ? TransaksiSpp::whereHas('tagihan', fn ($q) => $q->where('siswa_id', $siswaId))
                ->with(['tagihan', 'petugas'])
                ->latest('waktu_transaksi')
                ->limit(5)
                ->get()
                ->map(fn ($trx) => [
                    'id' => $trx->id,
                    'nomor_kuitansi' => $trx->nomor_kuitansi ?? ('#TRX-'.$trx->id),
                    'bulan' => $trx->tagihan?->bulan,
                    'tahun' => $trx->tagihan?->tahun,
                    'jumlah_dibayar' => (float) $trx->jumlah_dibayar,
                    'waktu_transaksi' => $trx->waktu_transaksi?->toISOString() ?? $trx->created_at?->toISOString(),
                    'metode' => is_object($trx->metode_pembayaran) ? $trx->metode_pembayaran->value : (string) $trx->metode_pembayaran,
                    'petugas' => $trx->petugas?->name ?? 'Kasir Loket TU',
                ])
                ->all()
            : [];

        $dispensasiAktif = $siswaId
            ? DispensasiSpp::whereHas('tagihan', fn ($q) => $q->where('siswa_id', $siswaId))
                ->with('tagihan')
                ->latest()
                ->first()
            : null;

        return Inertia::render('Siswa/Dashboard', [
            'siswa' => $siswa ? [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'kelas' => $siswa->kelas?->nama_kelas ?? '-',
                'status_siswa' => $siswa->status_siswa,
            ] : null,
            'ringkasan' => [
                'total_tagihan' => $totalTagihanNominal,
                'total_dibayar' => $totalDibayarNominal,
                'sisa_kewajiban' => $totalTunggakanNominal,
                'count_tagihan' => $totalTagihanCount,
                'count_lunas' => $lunasCount,
                'count_sebagian' => $sebagianCount,
                'count_belum_lunas' => $belumLunasCount,
                'status_keseluruhan' => $statusKeseluruhan,
            ],
            'tagihanBelumLunas' => $tagihanBelumLunas,
            'transaksiTerbaru' => $transaksiTerbaru,
            'dispensasiAktif' => $dispensasiAktif ? [
                'id' => $dispensasiAktif->id,
                'periode' => 'Bulan '.($dispensasiAktif->tagihan?->bulan ?? '-').' / '.($dispensasiAktif->tagihan?->tahun ?? '-'),
                'status' => $dispensasiAktif->status,
                'batas_waktu_baru' => $dispensasiAktif->batas_waktu_baru,
                'alasan' => $dispensasiAktif->alasan_penundaan,
                'catatan_pimpinan' => $dispensasiAktif->catatan_pimpinan,
            ] : null,
            'totalKewajiban' => $totalTunggakanNominal,
            'tagihanList' => $tagihanBelumLunas,
        ]);
    }
}
