<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TarifSpp extends Model
{
    use Filterable, HasFactory;

    protected $table = 'tarif_spp';

    protected $fillable = [
        'tahun_ajaran',
        'tingkat',
        'nominal_standar',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'nominal_standar' => 'decimal:2',
        ];
    }

    /**
     * Ambil 1 data persetujuan komite yang paling baru (objek tunggal).
     */
    public function persetujuanKomite(): HasOne
    {
        return $this->hasOne(PersetujuanTarifKomite::class)->latestOfMany();
    }

    /**
     * Riwayat seluruh persetujuan komite (jika butuh history penolakan/revisi).
     */
    public function riwayatPersetujuan(): HasMany
    {
        return $this->hasMany(PersetujuanTarifKomite::class)->latest();
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanSpp::class);
    }
}