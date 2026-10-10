<?php

namespace App\Services;

use App\Enums\PeranPengguna;
use App\Interfaces\SiswaRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SiswaService
{
    public function __construct(
        protected SiswaRepositoryInterface $siswaRepo,
        protected UserRepositoryInterface $userRepo
    ) {}

    public function simpan(array $data): Siswa
    {
        return $this->siswaRepo->create($data);
    }

    public function perbarui(Siswa $siswa, array $data): bool
    {
        return $this->siswaRepo->update($siswa, $data);
    }

    public function ubahStatus(Siswa $siswa, string $status): bool
    {
        return $this->siswaRepo->update($siswa, ['status' => $status]);
    }

    public function tautkanKeAkunUser(Siswa $siswa, int $userId): bool
    {
        $targetUser = $this->userRepo->findById($userId);

        if (! $targetUser) {
            throw ValidationException::withMessages([
                'user_id' => 'Pengguna tidak ditemukan.',
            ]);
        }

        $userRoleValue = $targetUser->role instanceof PeranPengguna
            ? $targetUser->role->value
            : (string) $targetUser->role;

        if ($userRoleValue !== PeranPengguna::WALI_MURID->value) {
            throw ValidationException::withMessages([
                'user_id' => 'Akun yang dipilih harus berstatus peran Wali Murid / Siswa.',
            ]);
        }

        return DB::transaction(function () use ($siswa, $userId) {
            return $this->siswaRepo->update($siswa, ['user_id' => $userId]);
        });
    }

    public function hapus(Siswa $siswa): bool
    {
        return $this->siswaRepo->delete($siswa);
    }
}
