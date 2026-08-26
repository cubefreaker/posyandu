<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Anak extends Model
{
    protected $table = 'anak';

    protected $fillable = [
        'ibu_id',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    // Relationships

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(Ibu::class);
    }

    public function penimbangan(): HasMany
    {
        return $this->hasMany(Penimbangan::class);
    }

    public function imunisasi(): HasMany
    {
        return $this->hasMany(Imunisasi::class);
    }

    public function vitamin(): HasMany
    {
        return $this->hasMany(Vitamin::class);
    }

    // Accessors

    /**
     * Hitung usia anak dalam bulan dan tahun.
     */
    protected function usia(): Attribute
    {
        return Attribute::get(function () {
            $lahir = $this->tanggal_lahir;
            if (!$lahir) return '-';

            $now = Carbon::now();
            $totalBulan = $lahir->diffInMonths($now);

            if ($totalBulan < 1) {
                $hari = $lahir->diffInDays($now);
                return "{$hari} hari";
            }

            $tahun = intdiv((int) $totalBulan, 12);
            $bulan = (int) $totalBulan % 12;

            if ($tahun > 0 && $bulan > 0) {
                return "{$tahun} tahun {$bulan} bulan";
            } elseif ($tahun > 0) {
                return "{$tahun} tahun";
            }

            return "{$bulan} bulan";
        });
    }

    /**
     * Usia anak dalam bulan (untuk kalkulasi z-score).
     */
    protected function usiaInBulan(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->tanggal_lahir) return 0;
            return $this->tanggal_lahir->diffInMonths(Carbon::now());
        });
    }

    /**
     * Penimbangan terakhir.
     */
    protected function penimbanganTerakhir(): Attribute
    {
        return Attribute::get(function () {
            return $this->penimbangan()->latest('tanggal_pelayanan')->first();
        });
    }
}
