<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispensasiSpp extends Model
{
    use Filterable, HasFactory;

    protected $table = 'dispensasi_spp';

    protected $fillable = [
        'tagihan_spp_id',
        'pemohon_id',
        'disetujui_oleh',
        'tanggal_pengajuan',
        'batas_waktu_baru',
        'alasan_penundaan',
        'dokumen_pendukung',
        'status',
        'catatan_pimpinan',
        'tanggal_diproses',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'batas_waktu_baru' => 'date',
            'tanggal_diproses' => 'datetime',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(TagihanSpp::class, 'tagihan_spp_id');
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function kepalaSekolah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
