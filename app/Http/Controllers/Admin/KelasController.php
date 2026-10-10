<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KelasRequest;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class KelasController extends Controller
{
    public function index(Request $request): Response
    {
        $kelas = Kelas::query()
            ->withCount('siswa')
            ->when($request->search, function ($query, $search) {
                $query->where('nama_kelas', 'like', "%{$search}%")
                    ->orWhere('jurusan', 'like', "%{$search}%");
            })
            ->when($request->tingkat, function ($query, $tingkat) {
                $query->where('tingkat', $tingkat);
            })
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Kelas/Index', [
            'kelasList' => $kelas,
            'filters' => $request->only(['search', 'tingkat']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Kelas/Create');
    }

    public function store(KelasRequest $request): RedirectResponse
    {
        try {
            Kelas::create($request->validated());

            return redirect()->route('admin.kelas.index')
                ->with('success', 'Data kelas berhasil ditambahkan.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menambahkan kelas: '.$th->getMessage()]);
        }
    }

    public function edit(Kelas $kela): Response
    {
        return Inertia::render('Admin/Kelas/Edit', [
            'kelas' => $kela,
        ]);
    }

    public function update(KelasRequest $request, Kelas $kela): RedirectResponse
    {
        try {
            $kela->update($request->validated());

            return redirect()->route('admin.kelas.index')
                ->with('success', 'Data kelas berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal memperbarui kelas: '.$th->getMessage()]);
        }
    }

    public function destroy(Kelas $kela): RedirectResponse
    {
        try {
            if ($kela->siswa()->exists()) {
                return back()->withErrors([
                    'error' => 'Kelas tidak dapat dihapus karena masih memiliki data siswa terdaftar.',
                ]);
            }

            $kela->delete();

            return redirect()->route('admin.kelas.index')
                ->with('success', 'Data kelas berhasil dihapus.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menghapus kelas: '.$th->getMessage()]);
        }
    }
}
