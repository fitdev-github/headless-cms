<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $fillable = [
        'content_type_id', 'name', 'display_name', 'type', 'options', 'localizable', 'sort_order',
    ];

    protected $casts = [
        'options'     => 'array',
        'localizable' => 'boolean',
    ];

    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    public function values()
    {
        return $this->hasMany(EntryValue::class);
    }

    public function isRequired(): bool
    {
        return (bool) ($this->options['required'] ?? false);
    }

    public function isPrivate(): bool
    {
        return (bool) ($this->options['private'] ?? false);
    }

    public function isLocalizable(): bool
    {
        // NULL means not yet set — default to true (localizable by default)
        return $this->localizable !== false;
    }

    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    public function getRelationContentType(): ?ContentType
    {
        $id = $this->getOption('relation_type_id');
        return $id ? ContentType::find($id) : null;
    }

    public function getValueColumn(): string
    {
        return match ($this->type) {
            'number'                                                       => 'value_number',
            'boolean'                                                      => 'value_boolean',
            'date', 'datetime'                                             => 'value_date',
            'media', 'relation', 'json', 'component', 'dynamiczone'       => 'value_json',
            default                                                        => 'value_text',
        };
    }
}
