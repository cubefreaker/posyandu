<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ibu extends Model
{
    protected $table = 'ibu';

    protected $fillable = [
        'nik',
        'nama',
        'tanggal_lahir',
        'alamat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function anak(): HasMany
    {
        return $this->hasMany(Anak::class);
    }
}
