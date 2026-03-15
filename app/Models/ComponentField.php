<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentField extends Model
{
    protected $fillable = [
        'component_id', 'name', 'display_name', 'type', 'options', 'sort_order',
    ];

    protected $casts = ['options' => 'array'];

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    /** Mirror the Field interface so FieldRenderer can accept ComponentField too. */
    public function getOption(string $key, $default = null): mixed
    {
        return ($this->options ?? [])[$key] ?? $default;
    }

    public function isRequired(): bool
    {
        return (bool) $this->getOption('required', false);
    }

    public function isPrivate(): bool
    {
        return (bool) $this->getOption('private', false);
    }

    /** Relations are not supported in component fields. */
    public function getRelationContentType(): ?ContentType
    {
        return null;
    }
}
