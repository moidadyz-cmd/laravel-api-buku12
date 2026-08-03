<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Buku extends Model
{
    protected $table = 'buku';

    protected $fillable = [
        'judul',
        'pengarang',
        'tanggal_publikasi',
        'gambar'
    ];

    protected $appends = [
        'gambar_url'
    ];

    protected function gambarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->gambar
                ? asset('storage/' . $this->gambar)
                : null
        );
    }
}
