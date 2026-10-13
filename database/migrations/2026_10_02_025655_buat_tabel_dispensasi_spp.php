<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensasi_spp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_spp_id')->constrained('tagihan_spp')->cascadeOnDelete();
            $table->foreignId('pemohon_id')->constrained('users')->cascadeOnDelete(); // Staf TU / Pemohon
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete(); // Kepala Sekolah

            $table->date('tanggal_pengajuan')->default(now());
            $table->date('batas_waktu_baru');
            $table->text('alasan_penundaan');
            $table->string('dokumen_pendukung')->nullable();
            $table->enum('status', ['menunggu_persetujuan', 'disetujui', 'ditolak'])->default('menunggu_persetujuan');
            $table->text('catatan_pimpinan')->nullable();
            $table->dateTime('tanggal_diproses')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensasi_spp');
    }
};
