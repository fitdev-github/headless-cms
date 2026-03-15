<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'display_name', 'singular_name', 'plural_name',
        'type', 'description', 'icon', 'draft_publish', 'localized', 'sort_order',
    ];

    protected $casts = [
        'draft_publish' => 'boolean',
        'localized'     => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(Field::class)->orderBy('sort_order');
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function isCollection(): bool
    {
        return $this->type === 'collection';
    }

    public function isSingle(): bool
    {
        return $this->type === 'single';
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('plural_name', $slug)->first();
    }

    public static function getForSidebar()
    {
        return static::orderBy('sort_order')->orderBy('display_name')->get();
    }
}
