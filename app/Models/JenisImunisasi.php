<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisImunisasi extends Model
{
    protected $table = 'jenis_imunisasi';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'usia_pemberian',
        'urutan',
    ];

    public function imunisasi(): HasMany
    {
        return $this->hasMany(Imunisasi::class);
    }
}
