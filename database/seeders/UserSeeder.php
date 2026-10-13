<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $passwordStandar = Hash::make('password');

        // Akun Pengguna berdasarkan Peran (PeranPengguna Enum)
        User::firstOrCreate(
            ['email' => 'admin@sekolah.sch.id'],
            [
                'name' => 'Administrator Utama',
                'password' => $passwordStandar,
                'role' => PeranPengguna::ADMIN,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'bendahara@sekolah.sch.id'],
            [
                'name' => 'Hj. Siti Aminah, S.E. (Bendahara)',
                'password' => $passwordStandar,
                'role' => PeranPengguna::BENDAHARA,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'tu@sekolah.sch.id'],
            [
                'name' => 'Budi Santoso (Kasir Loket TU)',
                'password' => $passwordStandar,
                'role' => PeranPengguna::STAF_TU,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'kepsek@sekolah.sch.id'],
            [
                'name' => 'Drs. H. Ahmad Fauzi, M.Pd. (Kepala Sekolah)',
                'password' => $passwordStandar,
                'role' => PeranPengguna::KEPALA_SEKOLAH,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'komite@sekolah.sch.id'],
            [
                'name' => 'Ir. Bambang Wijaya (Ketua Komite)',
                'password' => $passwordStandar,
                'role' => PeranPengguna::KOMITE_SEKOLAH,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'siswa@sekolah.sch.id'],
            [
                'name' => 'Muhammad Rizky Pratama',
                'password' => $passwordStandar,
                'role' => PeranPengguna::SISWA,
                'email_verified_at' => now(),
            ]
        );
    }
}
