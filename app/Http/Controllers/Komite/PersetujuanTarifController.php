<?php

namespace App\Http\Controllers\Komite;

use App\Http\Controllers\Controller;
use App\Http\Requests\Komite\PersetujuanTarifRequest;
use App\Models\PersetujuanTarifKomite;
use App\Models\TarifSpp;
use App\Services\PersetujuanTarifService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PersetujuanTarifController extends Controller
{
    /**
     * Menampilkan daftar tarif SPP beserta status persetujuan komite.
     */
    public function index(Request $request): Response
    {
        $query = TarifSpp::with(['persetujuanKomite.ketuaKomite']);

        // Filter tahun ajaran jika dipilih
        if ($request->filled('tahun_ajaran')) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        $daftarTarif = $query->orderBy('tahun_ajaran', 'desc')
            ->orderBy('tingkat', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tahun_ajaran' => $item->tahun_ajaran,
                    'tingkat' => $item->tingkat,
                    'nominal_standar' => (float) $item->nominal_standar,
                    'keterangan' => $item->keterangan,
                    'persetujuan' => $item->persetujuanKomite ? [
                        'id' => $item->persetujuanKomite->id,
                        'nomor_berita_acara' => $item->persetujuanKomite->nomor_berita_acara,
                        'tanggal_kesepakatan' => $item->persetujuanKomite->tanggal_kesepakatan?->format('Y-m-d'),
                        'status_persetujuan' => $item->persetujuanKomite->status_persetujuan,
                        'catatan_komite' => $item->persetujuanKomite->catatan_komite,
                        'ada_dokumen' => ! empty($item->persetujuanKomite->dokumen_ba_pdf),
                        'nama_ketua' => $item->persetujuanKomite->ketuaKomite?->name,
                    ] : null,
                ];
            });

        // Ambil daftar unik tahun ajaran untuk filter
        $listTahunAjaran = TarifSpp::select('tahun_ajaran')->distinct()->pluck('tahun_ajaran');

        return Inertia::render('Komite/TarifSpp/Index', [
            'daftarTarif' => $daftarTarif,
            'listTahunAjaran' => $listTahunAjaran,
            'filters' => $request->only('tahun_ajaran'),
        ]);
    }

    /**
     * Memproses persetujuan / penolakan tarif oleh komite.
     */
    public function proses(
        PersetujuanTarifRequest $request,
        TarifSpp $tarifSpp,
        PersetujuanTarifService $service
    ) {
        $service->handle(
            $tarifSpp,
            $request->user(),
            $request->validated(),
            $request->file('dokumen_ba_pdf')
        );

        return redirect()->back()->with('success', 'Keputusan tarif berhasil diperbarui.');
    }

    /**
     * Melihat / mengunduh file Berita Acara PDF yang tersimpan.
     */
    public function downloadDokumenBa(PersetujuanTarifKomite $persetujuan): BinaryFileResponse
    {
        if (! $persetujuan->dokumen_ba_pdf || ! Storage::disk('public')->exists($persetujuan->dokumen_ba_pdf)) {
            abort(404, 'Berkas Berita Acara tidak ditemukan di storage.');
        }

        $cleanNomorBa = preg_replace('/[^A-Za-z0-9_\-]/', '_', $persetujuan->nomor_berita_acara);

        return response()->file(
            Storage::disk('public')->path($persetujuan->dokumen_ba_pdf),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="BA_'.$cleanNomorBa.'.pdf"',
            ]
        );
    }
}
