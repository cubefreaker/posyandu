<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penimbangan extends Model
{
    protected $table = 'penimbangan';

    protected $fillable = [
        'anak_id',
        'tanggal_pelayanan',
        'berat_badan',
        'tinggi_badan',
        'lingkar_kepala',
        'lila',
        'zscore_bbu',
        'zscore_tbu',
        'zscore_bbtb',
        'status_bbu',
        'status_tbu',
        'status_bbtb',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelayanan' => 'date',
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
            'lingkar_kepala' => 'decimal:2',
            'lila' => 'decimal:2',
            'zscore_bbu' => 'decimal:2',
            'zscore_tbu' => 'decimal:2',
            'zscore_bbtb' => 'decimal:2',
        ];
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    /**
     * Label status gizi BB/U yang readable.
     */
    public function getLabelStatusBbuAttribute(): string
    {
        return match ($this->status_bbu) {
            'buruk' => 'Gizi Buruk',
            'kurang' => 'Gizi Kurang',
            'baik' => 'Gizi Baik',
            'lebih' => 'Gizi Lebih',
            default => '-',
        };
    }

    /**
     * Label status gizi TB/U yang readable.
     */
    public function getLabelStatusTbuAttribute(): string
    {
        return match ($this->status_tbu) {
            'sangat_pendek' => 'Sangat Pendek',
            'pendek' => 'Pendek',
            'normal' => 'Normal',
            'tinggi' => 'Tinggi',
            default => '-',
        };
    }

    /**
     * Label status gizi BB/TB yang readable.
     */
    public function getLabelStatusBbtbAttribute(): string
    {
        return match ($this->status_bbtb) {
            'gizi_buruk' => 'Gizi Buruk',
            'gizi_kurang' => 'Gizi Kurang',
            'gizi_baik' => 'Gizi Baik',
            'gizi_lebih' => 'Gizi Lebih',
            default => '-',
        };
    }
}
