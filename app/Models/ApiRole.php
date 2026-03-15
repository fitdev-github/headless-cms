<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRole extends Model
{
    protected $fillable = ['name', 'description', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function permissions()
    {
        return $this->hasMany(ApiPermission::class, 'role_id');
    }

    public function users()
    {
        return $this->hasMany(ApiUser::class, 'role_id');
    }

    /** Check if this role can perform an action on a subject. */
    public function can(string $action, string $subject): bool
    {
        return $this->permissions()
            ->where('enabled', true)
            ->where('action', $action)
            ->where(function ($q) use ($subject) {
                $q->where('subject', $subject)->orWhere('subject', '*');
            })
            ->exists();
    }

    public static function public(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public static function authenticated(): ?self
    {
        return static::where('name', 'Authenticated')->first();
    }
}
