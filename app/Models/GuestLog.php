<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Mengubah kolom jam menjadi objek Carbon (datetime)
     */
    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    /**
     * Relasi ke User (Staf) yang menjadi host/tuan rumah tamu
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }
}
