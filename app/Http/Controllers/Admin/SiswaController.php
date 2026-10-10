<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PeranPengguna;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiswaRequest;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SiswaController extends Controller
{
    public function index(Request $request): Response
    {
        $siswa = Siswa::query()
            ->with(['kelas', 'user'])
            ->when($request->search, function ($query, $search) {
                $query->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->when($request->kelas_id, function ($query, $kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
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
        'users' => User::where('role', PeranPengguna::WALI_MURID->value)
            ->whereDoesntHave('siswa')
            ->select('id', 'name', 'email')
            ->get(),
    ]);
}

    public function store(SiswaRequest $request): RedirectResponse
    {
        try {
            Siswa::create($request->validated());

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Siswa baru berhasil ditambahkan.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menambahkan data: '.$th->getMessage()]);
        }
    }

    public function edit(Siswa $siswa): Response
    {
        return Inertia::render('Admin/Siswa/Edit', [
            'siswa' => $siswa->load(['kelas', 'user']),
            'kelasList' => Kelas::select('id', 'nama_kelas')->get(),
            'users' => User::where('role', PeranPengguna::SISWA->value)
                ->where(function ($q) use ($siswa) {
                    $q->whereDoesntHave('siswa')
                        ->orWhere('id', $siswa->user_id);
                })
                ->select('id', 'name', 'email')
                ->get(),
        ]);
    }

    public function update(SiswaRequest $request, Siswa $siswa): RedirectResponse
    {
        try {
            $siswa->update($request->validated());

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal memperbarui data: '.$th->getMessage()]);
        }
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        try {
            if ($siswa->tagihanSpp()->exists() || $siswa->transaksiSpp()->exists()) {
                return back()->withErrors([
                    'error' => 'Data siswa tidak dapat dihapus karena sudah memiliki riwayat tagihan atau transaksi.',
                ]);
            }

            $siswa->delete();

            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil dihapus.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menghapus data: '.$th->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'lulus', 'pindah', 'keluar'])],
        ]);

        try {
            $siswa->update(['status' => $validated['status']]);

            return back()->with('success', 'Status siswa berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal memperbarui status: '.$th->getMessage()]);
        }
    }

    public function tautkanUser(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('siswa', 'user_id')->ignore($siswa->id),
            ],
        ]);

        try {
            $siswa->update(['user_id' => $validated['user_id']]);

            return back()->with('success', 'Akun login berhasil ditautkan ke siswa.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menautkan akun: '.$th->getMessage()]);
        }
    }
}