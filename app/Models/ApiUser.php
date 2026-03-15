<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class ApiUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'username', 'email', 'password', 'confirmed',
        'blocked', 'provider', 'role_id',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'confirmed' => 'boolean',
        'blocked'   => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(ApiRole::class, 'role_id');
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function checkPassword(string $plain): bool
    {
        return Hash::check($plain, $this->password);
    }

    public function toPublicArray(): array
    {
        return [
            'id'        => $this->id,
            'username'  => $this->username,
            'email'     => $this->email,
            'confirmed' => $this->confirmed,
            'blocked'   => $this->blocked,
            'role'      => $this->role ? ['id' => $this->role->id, 'name' => $this->role->name] : null,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
