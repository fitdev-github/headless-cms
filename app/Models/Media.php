<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'filename', 'original_name', 'mime_type', 'size',
        'width', 'height', 'path', 'alt', 'caption', 'folder',
    ];

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function toApiArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->original_name,
            'url'       => $this->url,
            'mime'      => $this->mime_type,
            'size'      => $this->size,
            'width'     => $this->width,
            'height'    => $this->height,
            'alt'       => $this->alt,
            'caption'   => $this->caption,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
