<?php

use App\Enums\MetodePembayaran;
use App\Enums\PeranPengguna;
use App\Enums\StatusTagihanSpp;
use App\Models\DispensasiSpp;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TarifSpp;
use App\Models\TransaksiSpp;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest is redirected from student routes to login', function () {
    $this->get('/siswa/dashboard')->assertRedirect(route('login'));
    $this->get('/siswa/tagihan-saya')->assertRedirect(route('login'));
    $this->get('/siswa/dispensasi-saya')->assertRedirect(route('login'));
});

test('student can access dashboard and receives proper inertia props', function () {
    $user = User::factory()->create(['role' => PeranPengguna::SISWA]);
    $kelas = Kelas::create([
        'nama_kelas' => 'X RPL 1',
        'tingkat' => 'X',
        'jurusan' => 'RPL',
    ]);
    $siswa = Siswa::create([
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nis' => '12345',
        'nisn' => '0012345678',
        'nama_lengkap' => $user->name,
        'status_siswa' => 'aktif',
    ]);

    $tarif = TarifSpp::create([
        'tahun_ajaran' => '2026/2027',
        'tingkat' => 'X',
        'nominal_standar' => 250000,
        'keterangan' => 'Tarif SPP X',
    ]);

    $tagihan = TagihanSpp::create([
        'siswa_id' => $siswa->id,
        'tarif_spp_id' => $tarif->id,
        'bulan' => 10,
        'tahun' => 2026,
        'nominal_asli' => 250000,
        'nominal_potongan' => 0,
        'nominal_tagihan' => 250000,
        'total_terbayar' => 0,
        'sisa_tagihan' => 250000,
        'status' => StatusTagihanSpp::BELUM_BAYAR,
        'jatuh_tempo' => now()->addDays(10),
    ]);

    $response = $this->actingAs($user)->get('/siswa/dashboard');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Siswa/Dashboard')
            ->has('siswa')
            ->where('siswa.nama_lengkap', $user->name)
            ->where('siswa.nis', '12345')
            ->has('ringkasan')
            ->where('ringkasan.sisa_kewajiban', 250000)
            ->has('tagihanBelumLunas', 1)
            ->has('transaksiTerbaru')
        );
});

test('student can view their bill cards on tagihan-saya', function () {
    $user = User::factory()->create(['role' => PeranPengguna::SISWA]);
    $kelas = Kelas::create([
        'nama_kelas' => 'X TKJ 1',
        'tingkat' => 'X',
        'jurusan' => 'TKJ',
    ]);
    $siswa = Siswa::create([
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nis' => '54321',
        'nisn' => '0098765432',
        'nama_lengkap' => $user->name,
        'status_siswa' => 'aktif',
    ]);

    $response = $this->actingAs($user)->get('/siswa/tagihan-saya');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Siswa/TagihanSaya')
            ->has('tagihan')
            ->has('siswa')
            ->where('siswa.nis', '54321')
        );
});

test('student can view their dispensasi records scoped to their account', function () {
    $user = User::factory()->create(['role' => PeranPengguna::SISWA]);
    $otherUser = User::factory()->create(['role' => PeranPengguna::SISWA]);

    $kelas = Kelas::create(['nama_kelas' => 'X TKJ 2', 'tingkat' => 'X', 'jurusan' => 'TKJ']);

    $siswa1 = Siswa::create([
        'user_id' => $user->id,
        'kelas_id' => $kelas->id,
        'nis' => '11111',
        'nisn' => '0011111111',
        'nama_lengkap' => 'Siswa Satu',
        'status_siswa' => 'aktif',
    ]);

    $siswa2 = Siswa::create([
        'user_id' => $otherUser->id,
        'kelas_id' => $kelas->id,
        'nis' => '22222',
        'nisn' => '0022222222',
        'nama_lengkap' => 'Siswa Dua',
        'status_siswa' => 'aktif',
    ]);

    $tagihan1 = TagihanSpp::create([
        'siswa_id' => $siswa1->id,
        'bulan' => 1,
        'tahun' => 2026,
        'nominal_asli' => 200000,
        'nominal_tagihan' => 200000,
        'sisa_tagihan' => 200000,
        'status' => StatusTagihanSpp::BELUM_BAYAR,
    ]);

    $tagihan2 = TagihanSpp::create([
        'siswa_id' => $siswa2->id,
        'bulan' => 1,
        'tahun' => 2026,
        'nominal_asli' => 200000,
        'nominal_tagihan' => 200000,
        'sisa_tagihan' => 200000,
        'status' => StatusTagihanSpp::BELUM_BAYAR,
    ]);

    DispensasiSpp::create([
        'tagihan_spp_id' => $tagihan1->id,
        'pemohon_id' => $user->id,
        'tanggal_pengajuan' => now()->toDateString(),
        'batas_waktu_baru' => now()->addMonth()->toDateString(),
        'alasan_penundaan' => 'Kendala pribadi 1',
        'status' => 'menunggu_persetujuan',
    ]);

    DispensasiSpp::create([
        'tagihan_spp_id' => $tagihan2->id,
        'pemohon_id' => $otherUser->id,
        'tanggal_pengajuan' => now()->toDateString(),
        'batas_waktu_baru' => now()->addMonth()->toDateString(),
        'alasan_penundaan' => 'Kendala pribadi 2',
        'status' => 'menunggu_persetujuan',
    ]);

    $response = $this->actingAs($user)->get('/siswa/dispensasi-saya');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('DispensasiSpp/Index')
            ->has('dispensasi.data', 1)
            ->where('dispensasi.data.0.tagihan.siswa.nama_lengkap', 'Siswa Satu')
        );
});

test('student cannot view receipt of another student transaction', function () {
    $user1 = User::factory()->create(['role' => PeranPengguna::SISWA]);
    $user2 = User::factory()->create(['role' => PeranPengguna::SISWA]);

    $petugas = User::factory()->create(['role' => PeranPengguna::STAF_TU]);

    $kelas = Kelas::create(['nama_kelas' => 'XI RPL 1', 'tingkat' => 'XI', 'jurusan' => 'RPL']);

    $siswa1 = Siswa::create(['user_id' => $user1->id, 'kelas_id' => $kelas->id, 'nis' => '33333', 'nisn' => '0033333333', 'nama_lengkap' => 'Siswa Satu', 'status_siswa' => 'aktif']);
    $siswa2 = Siswa::create(['user_id' => $user2->id, 'kelas_id' => $kelas->id, 'nis' => '44444', 'nisn' => '0044444444', 'nama_lengkap' => 'Siswa Dua', 'status_siswa' => 'aktif']);

    $tagihan2 = TagihanSpp::create([
        'siswa_id' => $siswa2->id,
        'bulan' => 2,
        'tahun' => 2026,
        'nominal_asli' => 250000,
        'nominal_tagihan' => 250000,
        'sisa_tagihan' => 0,
        'total_terbayar' => 250000,
        'status' => StatusTagihanSpp::LUNAS,
    ]);

    $trx2 = TransaksiSpp::create([
        'nomor_kuitansi' => 'KW-SPP-2026-0002',
        'tagihan_spp_id' => $tagihan2->id,
        'petugas_id' => $petugas->id,
        'jumlah_dibayar' => 250000,
        'waktu_transaksi' => now(),
        'metode_pembayaran' => MetodePembayaran::TUNAI,
    ]);

    // Student 1 tries to access Student 2's receipt
    $response = $this->actingAs($user1)->get(route('cetak-kuitansi.kuitansi', $trx2->id));
    $response->assertStatus(403);
});

test('student can view their own receipt', function () {
    $user = User::factory()->create(['role' => PeranPengguna::SISWA]);
    $petugas = User::factory()->create(['role' => PeranPengguna::STAF_TU]);
    $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1', 'tingkat' => 'XII', 'jurusan' => 'RPL']);

    $siswa = Siswa::create(['user_id' => $user->id, 'kelas_id' => $kelas->id, 'nis' => '55555', 'nisn' => '0055555555', 'nama_lengkap' => 'Siswa Sendiri', 'status_siswa' => 'aktif']);

    $tagihan = TagihanSpp::create([
        'siswa_id' => $siswa->id,
        'bulan' => 3,
        'tahun' => 2026,
        'nominal_asli' => 275000,
        'nominal_tagihan' => 275000,
        'sisa_tagihan' => 0,
        'total_terbayar' => 275000,
        'status' => StatusTagihanSpp::LUNAS,
    ]);

    $trx = TransaksiSpp::create([
        'nomor_kuitansi' => 'KW-SPP-2026-0003',
        'tagihan_spp_id' => $tagihan->id,
        'petugas_id' => $petugas->id,
        'jumlah_dibayar' => 275000,
        'waktu_transaksi' => now(),
        'metode_pembayaran' => MetodePembayaran::TUNAI,
    ]);

    $response = $this->actingAs($user)->get(route('cetak-kuitansi.kuitansi', $trx->id));
    $response->assertOk();
    $response->assertSee('KW-SPP-2026-0003');
    $response->assertSee('Siswa Sendiri');
});
