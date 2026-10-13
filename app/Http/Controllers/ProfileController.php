<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user()->loadMissing('siswa.kelas');
        $siswa = $user->siswa;

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'infoAkun' => [
                'id' => $user->id,
                'is_active' => (bool) ($user->is_active ?? true),
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'terdaftar_pada' => $user->created_at?->toIso8601String(),
                'diperbarui_pada' => $user->updated_at?->toIso8601String(),
            ],
            'profilSiswa' => $siswa ? [
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama_lengkap' => $siswa->nama_lengkap,
                'status_siswa' => $siswa->status_siswa,
                'nama_kelas' => $siswa->kelas?->nama_kelas,
                'tingkat' => $siswa->kelas?->tingkat,
                'jurusan' => $siswa->kelas?->jurusan,
            ] : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
