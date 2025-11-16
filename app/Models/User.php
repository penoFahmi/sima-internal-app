<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Agenda yang DIBUAT oleh User ini
     */
    public function createdAgendas(): HasMany
    {
        return $this->hasMany(Agenda::class, 'user_id');
    }

    /**
     * Agenda yang DIHADIRI oleh User ini (via pivot table)
     */
    public function agendas(): BelongsToMany
    {
        return $this->belongsToMany(Agenda::class, 'agenda_user');
    }

    /**
     * Pengajuan Cuti yang DIBUAT oleh User ini
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    /**
     * Pengajuan Cuti yang DISETUJUI oleh User ini (jika dia Pimpinan)
     */
    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approver_id');
    }

    /**
     * Tamu yang DITEMUI oleh User ini (dia sebagai host)
     */
    public function hostedGuests(): HasMany
    {
        return $this->hasMany(GuestLog::class, 'host_user_id');
    }
}
