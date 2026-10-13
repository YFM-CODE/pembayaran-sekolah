<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Models\Kelas;
use App\Models\KeringananSpp;
use App\Models\Siswa;
use App\Models\TarifSpp;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna berdasarkan Peran (PeranPengguna Enum)
        $this->call(UserSeeder::class);

        $userSiswa = User::where('email', 'siswa@sekolah.sch.id')->first();

        // 2. Data Master Kelas
        $kelas10Rpl = Kelas::firstOrCreate(
            ['nama_kelas' => 'X RPL 1'],
            ['tingkat' => 'X', 'jurusan' => 'Rekayasa Perangkat Lunak']
        );

        $kelas11Rpl = Kelas::firstOrCreate(
            ['nama_kelas' => 'XI RPL 1'],
            ['tingkat' => 'XI', 'jurusan' => 'Rekayasa Perangkat Lunak']
        );

        $kelas12Rpl = Kelas::firstOrCreate(
            ['nama_kelas' => 'XII RPL 1'],
            ['tingkat' => 'XII', 'jurusan' => 'Rekayasa Perangkat Lunak']
        );

        // 3. Data Master Siswa
        Siswa::firstOrCreate(
            ['nis' => '20261001'],
            [
                'user_id' => $userSiswa->id,
                'kelas_id' => $kelas10Rpl->id,
                'nisn' => '0089123456',
                'nama_lengkap' => 'Muhammad Rizky Pratama',
                'status_siswa' => 'aktif',
            ]
        );

        Siswa::firstOrCreate(
            ['nis' => '20261002'],
            [
                'user_id' => null,
                'kelas_id' => $kelas10Rpl->id,
                'nisn' => '0089123457',
                'nama_lengkap' => 'Aulia Siti Rahma',
                'status_siswa' => 'aktif',
            ]
        );

        // 4. Data Master Tarif SPP (Tahun Ajaran 2026/2027)
        TarifSpp::firstOrCreate(
            ['tahun_ajaran' => '2026/2027', 'tingkat' => 'X'],
            ['nominal_standar' => 250000, 'keterangan' => 'Tarif SPP Kelas 10 Tahun Ajaran 2026/2027']
        );

        TarifSpp::firstOrCreate(
            ['tahun_ajaran' => '2026/2027', 'tingkat' => 'XI'],
            ['nominal_standar' => 260000, 'keterangan' => 'Tarif SPP Kelas 11 Tahun Ajaran 2026/2027']
        );

        TarifSpp::firstOrCreate(
            ['tahun_ajaran' => '2026/2027', 'tingkat' => 'XII'],
            ['nominal_standar' => 275000, 'keterangan' => 'Tarif SPP Kelas 12 Tahun Ajaran 2026/2027']
        );

        // 5. Data Master Skema Keringanan / Bantuan
        KeringananSpp::firstOrCreate(
            ['nama_program' => 'Bantuan Siswa Yatim / Piatu'],
            [
                'tipe_potongan' => 'persentase',
                'nilai_potongan' => 100, // 100% bebas biaya
                'syarat_ketentuan' => 'Melampirkan akta kematian orang tua',
                'aktif' => true,
            ]
        );

        KeringananSpp::firstOrCreate(
            ['nama_program' => 'Keringanan SKTM (Kurang Mampu)'],
            [
                'tipe_potongan' => 'nominal',
                'nilai_potongan' => 100000, // Diskon Rp 100.000 / bulan
                'syarat_ketentuan' => 'Melampirkan SKTM dari kelurahan/desa setempat',
                'aktif' => true,
            ]
        );

        KeringananSpp::firstOrCreate(
            ['nama_program' => 'Anak Pendidik & Tenaga Kependidikan'],
            [
                'tipe_potongan' => 'persentase',
                'nilai_potongan' => 50, // 50% potongan
                'syarat_ketentuan' => 'SK Guru atau Karyawan aktif di yayasan/sekolah',
                'aktif' => true,
            ]
        );
    }
}
