<?php

namespace App\Services;

use App\Enums\PeranPengguna;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SiswaService
{
    public function buat(array $data): Siswa
    {
        return DB::transaction(function () use ($data) {
            $userId = null;

            if (! empty($data['email'])) {
                $user = User::create([
                    'name' => $data['nama_lengkap'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password'] ?? $data['nis']),
                    'role' => PeranPengguna::SISWA->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
                $userId = $user->id;
            }

            // Hapus email dan password agar tidak menyebabkan error kolom MySQL
            $dataSiswa = Arr::except($data, ['email', 'password']);
            $dataSiswa['user_id'] = $userId;

            return Siswa::create($dataSiswa);
        });
    }

    public function perbarui(Siswa $siswa, array $data): Siswa
    {
        return DB::transaction(function () use ($siswa, $data) {
            if (! empty($data['email'])) {
                if ($siswa->user) {
                    $updateData = [
                        'name' => $data['nama_lengkap'],
                        'email' => $data['email'],
                    ];
                    if (! empty($data['password'])) {
                        $updateData['password'] = Hash::make($data['password']);
                    }
                    $siswa->user->update($updateData);
                } else {
                    $user = User::create([
                        'name' => $data['nama_lengkap'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password'] ?? $data['nis']),
                        'role' => PeranPengguna::SISWA->value,
                        'is_active' => true,
                    ]);
                    $siswa->user_id = $user->id;
                }
            }

            // Hapus email dan password sebelum update model siswa
            $dataSiswa = Arr::except($data, ['email', 'password']);
            if (isset($siswa->user_id)) {
                $dataSiswa['user_id'] = $siswa->user_id;
            }

            $siswa->update($dataSiswa);

            return $siswa;
        });
    }

    public function hapus(Siswa $siswa): void
    {
        if ($siswa->tagihanSpp()->exists() || $siswa->transaksiSpp()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'Data siswa tidak dapat dihapus karena sudah memiliki riwayat tagihan atau transaksi.',
            ]);
        }

        DB::transaction(function () use ($siswa) {
            $user = $siswa->user;
            $siswa->delete();
            if ($user) {
                $user->delete();
            }
        });
    }

    public function perbaruiStatus(Siswa $siswa, string $status): void
    {
        $siswa->update(['status_siswa' => $status]);
    }
}
