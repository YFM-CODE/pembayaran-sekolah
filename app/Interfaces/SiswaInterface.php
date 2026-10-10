<?php

namespace App\Interfaces;

use App\Models\Siswa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SiswaInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function cariBerdasarkanId(int $id): ?Siswa;

    public function buat(array $data): Siswa;

    public function perbarui(Siswa $siswa, array $data): bool;

    public function hapus(Siswa $siswa): bool;

    public function perbaruiStatus(Siswa $siswa, string $status): bool;

    public function tautkanUser(Siswa $siswa, int $userId): bool;

    public function memilikiTransaksiAtauTagihan(Siswa $siswa): bool;
}