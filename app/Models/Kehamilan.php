<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kehamilan extends Model
{
    protected $table = 'kehamilan';

    protected $fillable = [
        'ibu_id',
        'kehamilan_ke',
        'hpht',
        'hpl',
        'bb_sebelum_hamil',
        'tinggi_badan',
        'imt_pra_hamil',
        'kategori_imt',
        'lila_awal',
        'status_kek',
        'status_kehamilan',
        'catatan_risiko',
    ];

    protected function casts(): array
    {
        return [
            'hpht' => 'date',
            'hpl' => 'date',
            'bb_sebelum_hamil' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
            'imt_pra_hamil' => 'decimal:2',
            'lila_awal' => 'decimal:2',
            'status_kek' => 'boolean',
        ];
    }

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(Ibu::class);
    }

    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(PemeriksaanKehamilan::class)->orderBy('tanggal_periksa');
    }

    /**
     * Hitung IMT pra-hamil
     */
    public static function hitungImt(float $bb, float $tbCm): float
    {
        if ($tbCm <= 0) return 0;
        $tbM = $tbCm / 100;
        return round($bb / ($tbM * $tbM), 2);
    }

    /**
     * Tentukan kategori IMT Kemenkes
     */
    public static function tentukanKategoriImt(float $imt): string
    {
        if ($imt < 18.5) return 'kurus';
        if ($imt <= 24.9) return 'normal';
        if ($imt <= 29.9) return 'lebih';
        return 'obesitas';
    }

    /**
     * Hitung HPL otomatis dari HPHT (Rumus Naegele / 280 hari)
     */
    public static function hitungHpl(string $hphtDate): string
    {
        return Carbon::parse($hphtDate)->addDays(280)->format('Y-m-d');
    }

    /**
     * Usia kehamilan saat ini dalam minggu
     */
    public function getUsiaMingguAttribute(): int
    {
        if (!$this->hpht) return 0;
        $minggu = Carbon::parse($this->hpht)->diffInWeeks(now());
        return max(0, min(42, (int) $minggu));
    }

    /**
     * Trimester saat ini
     */
    public function getTrimesterSaatIniAttribute(): int
    {
        $minggu = $this->usia_minggu;
        if ($minggu <= 13) return 1;
        if ($minggu <= 27) return 2;
        return 3;
    }

    /**
     * Label Kategori IMT
     */
    public function getLabelKategoriImtAttribute(): string
    {
        return match($this->kategori_imt) {
            'kurus'    => 'Kurus (< 18.5)',
            'normal'   => 'Normal (18.5 - 24.9)',
            'lebih'    => 'Kelebihan BB (25.0 - 29.9)',
            'obesitas' => 'Obesitas (≥ 30.0)',
            default    => '-',
        };
    }
}
