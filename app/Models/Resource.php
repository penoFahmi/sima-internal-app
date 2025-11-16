<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Resource extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relasi ke SEMUA agenda yang mem-booking Aset/Ruangan ini
     */
    public function agendas(): BelongsToMany
    {
        return $this->belongsToMany(Agenda::class, 'agenda_resource');
    }
}
