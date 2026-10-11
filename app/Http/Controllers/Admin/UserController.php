<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PeranPengguna;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => PeranPengguna::cases(),
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => PeranPengguna::cases(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $data['email_verified_at'] = now(); // Otomatis terverifikasi saat dibuat oleh Admin

            User::create($data);

            return redirect()->route('admin.users.index')
                ->with('success', 'Pengguna berhasil ditambahkan.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menambahkan data: '.$th->getMessage()]);
        }
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => PeranPengguna::cases(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        try {
            $data = $request->validated();

            if (! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return redirect()->route('admin.users.index')
                ->with('success', 'Data pengguna berhasil diperbarui.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal memperbarui data: '.$th->getMessage()]);
        }
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        try {
            $user->update([
                'is_active' => ! $user->is_active,
            ]);

            return back()->with('success', 'Status pengguna berhasil diubah.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal mengubah status: '.$th->getMessage()]);
        }
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::default()],
        ]);

        try {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return back()->with('success', 'Kata sandi berhasil disetel ulang.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal mereset kata sandi: '.$th->getMessage()]);
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        // 1. Mencegah akun yang sedang login dihapus
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun yang sedang Anda gunakan saat ini.']);
        }

        try {
            DB::transaction(function () use ($user) {
                // Lepaskan relasi akun jika terhubung ke data siswa
                if ($user->siswa()->exists()) {
                    $user->siswa()->update(['user_id' => null]);
                }

                $user->delete();
            });

            return redirect()->route('admin.users.index')
                ->with('success', 'Pengguna berhasil dihapus.');
        } catch (Throwable $th) {
            return back()->withErrors(['error' => 'Gagal menghapus pengguna: ' . $th->getMessage()]);
        }
    }
}