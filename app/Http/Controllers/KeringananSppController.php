<?php

namespace App\Http\Controllers;

use App\Filters\GlobalFilter;
use App\Http\Requests\Keringanan\SimpanProgramKeringananRequest;
use App\Http\Requests\Keringanan\TetapkanKeringananSiswaRequest;
use App\Models\Kelas;
use App\Models\KeringananSpp;
use App\Models\Siswa;
use App\Models\TarifSpp;
use App\Services\KeringananSppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class KeringananSppController extends Controller
{
    public function __construct(
        protected KeringananSppService $keringananService
    ) {}

    public function index(Request $request, GlobalFilter $filter): Response
    {
        // Default tahun ajaran aktif diambil dari usulan tarif terbaru atau tahun ajaran aktif
        $tahunAjaranAktif = $request->input(
            'tahun_ajaran',
            TarifSpp::latest('tahun_ajaran')->value('tahun_ajaran') ?? '2026/2027'
        );

        // 1. Data Program Keringanan
        $programList = KeringananSpp::withCount('penerima')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Data Siswa Penerima Keringanan
        $siswaQuery = Siswa::with([
            'kelas',
            'keringanan' => function ($q) use ($tahunAjaranAktif) {
                $q->where('tahun_ajaran', $tahunAjaranAktif)->with('keringanan');
            },
        ])->where('status_siswa', 'aktif');

        if ($request->filled('search')) {
            $search = $request->search;
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $siswaQuery->where('kelas_id', $request->kelas_id);
        }

        $daftarSiswa = $siswaQuery->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('KeringananSpp/Index', [
            'programList' => $programList,
            'daftarSiswa' => $daftarSiswa,
            'listKelas' => Kelas::select('id', 'tingkat', 'nama_kelas')->get(),
            'listTahunAjaran' => TarifSpp::select('tahun_ajaran')->distinct()->pluck('tahun_ajaran'),
            'tahunAjaranAktif' => $tahunAjaranAktif,
            'filters' => $request->only(['search', 'kelas_id', 'tahun_ajaran']),
        ]);
    }

    public function store(SimpanProgramKeringananRequest $request): RedirectResponse
    {
        try {
            $this->keringananService->simpanProgram($request->validated());

            return back()->with('success', 'Program keringanan berhasil dibuat.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal membuat program: '.$th->getMessage()]);
        }
    }

    public function update(SimpanProgramKeringananRequest $request, KeringananSpp $keringananSpp): RedirectResponse
    {
        try {
            $this->keringananService->perbaruiProgram($keringananSpp, $request->validated());

            return back()->with('success', 'Program keringanan berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal memperbarui program: '.$th->getMessage()]);
        }
    }

    public function destroy(KeringananSpp $keringananSpp): RedirectResponse
    {
        try {
            $this->keringananService->hapusProgram($keringananSpp);

            return back()->with('success', 'Program keringanan berhasil dihapus.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => $th->getMessage()]);
        }
    }

    public function tetapkanSiswa(TetapkanKeringananSiswaRequest $request): RedirectResponse
    {
        try {
            $this->keringananService->tetapkanSiswa($request->validated());

            return back()->with('success', 'Penetapan SK keringanan siswa berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menetapkan keringanan ke siswa: '.$th->getMessage()]);
        }
    }
}
