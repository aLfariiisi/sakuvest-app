<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'user_id',
        'nama',
        'tipe',
    ];

    // Relasi: Kategori dimiliki oleh satu User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
