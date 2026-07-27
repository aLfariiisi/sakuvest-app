<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingTarget extends Model
{
    protected $fillable = [
        'user_id',
        'nama_target',
        'target_nominal',
        'terkumpul',
        'tenggat_waktu',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
