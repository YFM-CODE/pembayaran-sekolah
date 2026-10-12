<?php

namespace App\Http\Controllers;

use App\Filters\GlobalFilter;
use App\Handlers\TarifSpp\HapusTarifSppHandler;
use App\Handlers\TarifSpp\PerbaruiTarifSppHandler;
use App\Handlers\TarifSpp\ProsesPersetujuanKomiteHandler;
use App\Handlers\TarifSpp\SimpanTarifSppHandler;
use App\Http\Requests\SimpanPersetujuanTarifRequest;
use App\Interfaces\TarifSppInterface;
use App\Models\TarifSpp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TarifSppController extends Controller
{
    public function __construct(
        protected TarifSppInterface $tarifRepo,
        protected SimpanTarifSppHandler $simpanHandler,
        protected PerbaruiTarifSppHandler $perbaruiHandler,
        protected HapusTarifSppHandler $hapusHandler,
        protected ProsesPersetujuanKomiteHandler $komiteHandler
    ) {}

    public function index(GlobalFilter $filter): Response
    {
        return Inertia::render('TarifSpp/Index', [
            'tarif' => $this->tarifRepo->paginate($filter),
            'filters' => request()->all(['search', 'tingkat', 'tahun_ajaran']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'tingkat' => ['required', 'string', 'max:10'],
            'nominal_standar' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $this->simpanHandler->handle($validated);
            DB::commit();

            return redirect()->back()->with('success', 'Tarif SPP berhasil ditambahkan.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan tarif: '.$th->getMessage()]);
        }
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        // 1. Validasi proteksi Berita Acara Komite
        $tarif = TarifSpp::with('persetujuanKomite')->findOrFail($id);

        if ($tarif->persetujuanKomite?->status_persetujuan === 'disetujui') {
            return redirect()->back()->withErrors([
                'error' => 'Tarif SPP tidak dapat diubah karena telah disahkan melalui Berita Acara Komite.',
            ]);
        }

        $validated = $request->validate([
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'tingkat' => ['required', 'string', 'max:10'],
            'nominal_standar' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $this->perbaruiHandler->handle($id, $validated);
            DB::commit();

            return redirect()->back()->with('success', 'Tarif SPP berhasil diperbarui.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui tarif: '.$th->getMessage()]);
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $tarif = TarifSpp::with(['persetujuanKomite', 'tagihan'])->findOrFail($id);

        // 1. Cek proteksi Berita Acara
        if ($tarif->persetujuanKomite?->status_persetujuan === 'disetujui') {
            return redirect()->back()->withErrors([
                'error' => 'Tarif SPP tidak dapat dihapus karena telah disahkan melalui Berita Acara Komite.',
            ]);
        }

        // 2. Cek apakah tarif sudah digunakan oleh tagihan siswa
        if ($tarif->tagihan()->exists()) {
            return redirect()->back()->withErrors([
                'error' => 'Tarif SPP tidak dapat dihapus karena sudah terbit tagihan untuk siswa.',
            ]);
        }

        DB::beginTransaction();
        try {
            $this->hapusHandler->handle($id);
            DB::commit();

            return redirect()->back()->with('success', 'Tarif SPP berhasil dihapus.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal menghapus tarif: '.$th->getMessage()]);
        }
    }

    public function setujuiKomite(SimpanPersetujuanTarifRequest $request, int $tarifSpp): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $this->komiteHandler->handle(
                tarifSppId: $tarifSpp,
                ketuaKomiteUserId: $request->user()->id,
                data: $request->validated(),
                filePdf: $request->file('dokumen_ba_pdf')
            );
            DB::commit();

            return redirect()->back()->with('success', 'Keputusan berita acara komite berhasil disimpan.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $th) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal memproses persetujuan komite: '.$th->getMessage()]);
        }
    }
}
