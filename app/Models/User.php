<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'tipe',
        'jurusan',
        'kelas',
        'no_wa',
        'profile_complete',
        'peringatan',
        'peringatan_at',
        'peringatan_dibaca_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'profile_complete' => 'boolean',
        'peringatan_at' => 'datetime',
        'peringatan_dibaca_at' => 'datetime',
    ];

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}