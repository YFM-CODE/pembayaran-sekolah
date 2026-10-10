<?php

namespace App\Interfaces;

use App\Models\Kelas;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface KelasInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function cariBerdasarkanId(int $id): ?Kelas;

    public function semua(): Collection;

    public function buat(array $data): Kelas;

    public function perbarui(Kelas $kelas, array $data): bool;

    public function hapus(Kelas $kelas): bool;

    public function memilikiSiswa(Kelas $kelas): bool;
}