<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Imunisasi extends Model
{
    protected $table = 'imunisasi';

    protected $fillable = [
        'anak_id',
        'jenis_imunisasi_id',
        'tanggal_imunisasi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_imunisasi' => 'date',
        ];
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function jenisImunisasi(): BelongsTo
    {
        return $this->belongsTo(JenisImunisasi::class);
    }
}
