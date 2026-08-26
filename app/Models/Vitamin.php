<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Vitamin extends Model
{
    protected $table = 'vitamin';

    protected $fillable = [
        'anak_id',
        'tanggal_pemberian',
        'jenis_vitamin',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pemberian' => 'date',
        ];
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    /**
     * Label jenis vitamin yang readable.
     */
    protected function labelJenisVitamin(): Attribute
    {
        return Attribute::get(fn () => match ($this->jenis_vitamin) {
            'kapsul_biru' => 'Kapsul Biru (6-11 bln)',
            'kapsul_merah' => 'Kapsul Merah (12-59 bln)',
            default => '-',
        });
    }
}
