<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Agenda extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Mengubah kolom start_time dan end_time menjadi objek Carbon (datetime)
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Relasi ke User yang MEMBUAT agenda ini
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke SEMUA User yang MENGIKUTI agenda ini (via pivot table)
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agenda_user');
    }

    /**
     * Relasi ke SEMUA Aset/Ruangan yang di-booking oleh agenda ini
     */
    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'agenda_resource');
    }
}
