<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiswaRequest;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\SiswaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SiswaController extends Controller
{
    public function __construct(
        protected SiswaService $siswaService
    ) {}

    public function index(Request $request): Response
    {
        $siswa = Siswa::query()
            ->with(['kelas', 'user'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($request->kelas_id, function ($query, $kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status_siswa', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Siswa/Index', [
            'siswaList' => $siswa,
            'kelasList' => Kelas::select('id', 'nama_kelas')->get(),
            'filters' => $request->only(['search', 'kelas_id', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Siswa/Create', [
            'kelasList' => Kelas::select('id', 'nama_kelas')->get(),
        ]);
    }

    public function store(SiswaRequest $request): RedirectResponse
    {
        try {
            $this->siswaService->buat($request->validated());

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil ditambahkan.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menambahkan data siswa: '.$th->getMessage()]);
        }
    }

    public function edit(Siswa $siswa): Response
    {
        return Inertia::render('Admin/Siswa/Edit', [
            'siswa' => $siswa->load(['kelas', 'user']),
            'kelasList' => Kelas::select('id', 'nama_kelas')->get(),
        ]);
    }

    public function update(SiswaRequest $request, Siswa $siswa): RedirectResponse
    {
        try {
            $this->siswaService->perbarui($siswa, $request->validated());

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal memperbarui data siswa: '.$th->getMessage()]);
        }
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        try {
            $this->siswaService->hapus($siswa);

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil dihapus.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menghapus data: '.$th->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'status_siswa' => ['required', Rule::in(['aktif', 'lulus', 'pindah', 'keluar'])],
        ]);

        try {
            $this->siswaService->perbaruiStatus($siswa, $validated['status_siswa']);

            return back()->with('success', 'Status siswa berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal memperbarui status: '.$th->getMessage()]);
        }
    }
}
