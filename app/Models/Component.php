<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'display_name', 'icon', 'category'];

    public function fields()
    {
        return $this->hasMany(ComponentField::class)->orderBy('sort_order');
    }

    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /** All components grouped by category for the index page. */
    public static function grouped(): array
    {
        return static::orderBy('category')->orderBy('display_name')
            ->get()
            ->groupBy('category')
            ->toArray();
    }
}
