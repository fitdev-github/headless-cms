<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['is_active' => 'boolean'];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin',
            'editor'     => 'Editor',
            default      => ucfirst($this->role),
        };
    }
}
