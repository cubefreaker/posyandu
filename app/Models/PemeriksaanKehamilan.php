<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemeriksaanKehamilan extends Model
{
    protected $table = 'pemeriksaan_kehamilan';

    protected $fillable = [
        'kehamilan_id',
        'tanggal_periksa',
        'usia_kehamilan_minggu',
        'trimester',
        'berat_badan',
        'kenaikan_bb',
        'tekanan_darah_sistol',
        'tekanan_darah_diastol',
        'lila',
        'tinggi_fundus',
        'djj',
        'letak_janin',
        'status_tt',
        'tablet_fe',
        'hb',
        'protein_urin',
        'gula_darah',
        'keluhan',
        'tindakan_nasihat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_periksa' => 'date',
            'berat_badan' => 'decimal:2',
            'kenaikan_bb' => 'decimal:2',
            'lila' => 'decimal:2',
            'tinggi_fundus' => 'decimal:2',
            'hb' => 'decimal:2',
        ];
    }

    public function kehamilan(): BelongsTo
    {
        return $this->belongsTo(Kehamilan::class);
    }

    /**
     * Tensi string format (cth: 120/80)
     */
    public function getTekananDarahAttribute(): string
    {
        if ($this->tekanan_darah_sistol && $this->tekanan_darah_diastol) {
            return "{$this->tekanan_darah_sistol}/{$this->tekanan_darah_diastol}";
        }
        return '-';
    }

    /**
     * Evaluasi risiko preeklampsia / hipertensi (Sistol >= 140 atau Diastol >= 90)
     */
    public function getStatusTensiAttribute(): string
    {
        if (!$this->tekanan_darah_sistol || !$this->tekanan_darah_diastol) return 'normal';
        if ($this->tekanan_darah_sistol >= 140 || $this->tekanan_darah_diastol >= 90) {
            return 'tinggi'; // Waspada Preeklampsia
        }
        if ($this->tekanan_darah_sistol < 90 || $this->tekanan_darah_diastol < 60) {
            return 'rendah'; // Hipotensi
        }
        return 'normal';
    }
}
