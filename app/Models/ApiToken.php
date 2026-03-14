<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiToken extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'type', 'token_hash',
        'abilities', 'duration_days', 'last_used_at', 'expires_at',
    ];

    protected $casts = [
        'abilities'    => 'array',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    protected $hidden = ['token_hash'];

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isFullAccess(): bool
    {
        return $this->type === 'full-access';
    }

    public function isReadOnly(): bool
    {
        return $this->type === 'read-only';
    }

    public function isCustom(): bool
    {
        return $this->type === 'custom';
    }

    /**
     * Check if this token can perform an action on a content type slug.
     * Actions: find, findOne, create, update, delete, upload.find, upload.create, upload.delete
     */
    public function can(string $action, string $slug = '*'): bool
    {
        if ($this->isExpired()) return false;
        if ($this->isFullAccess()) return true;

        if ($this->isReadOnly()) {
            return in_array($action, ['find', 'findOne', 'upload.find', 'upload.findOne']);
        }

        // Custom: check abilities per slug
        $abilities = $this->abilities ?? [];
        if (in_array($action, $abilities['*'] ?? [])) return true;
        return in_array($action, $abilities[$slug] ?? []);
    }

    public static function findByRawToken(string $rawToken): ?self
    {
        $hash = hash('sha256', $rawToken);
        return static::where('token_hash', $hash)->whereNull('deleted_at')->first();
    }
}
