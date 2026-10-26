<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'nama',
        'email',
        'telepon',
        'role',
        'aktif',
        'nasabah_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Administrator';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'Petugas';
    }

    public function isNasabah(): bool
    {
        return $this->role === 'Nasabah';
    }

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(Nasabah::class);
    }

    public function homeRoute(): string
    {
        return $this->isNasabah() ? route('akun.saya') : route('dashboard');
    }
}