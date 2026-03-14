<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entry extends Model
{
    use SoftDeletes;

    protected $fillable = ['content_type_id', 'status', 'created_by', 'published_at'];

    protected $casts = ['published_at' => 'datetime'];

    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function values()
    {
        return $this->hasMany(EntryValue::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
