<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'url', 'events', 'headers', 'enabled'];

    protected $casts = [
        'events'  => 'array',
        'headers' => 'array',
        'enabled' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(WebhookLog::class)->latest()->limit(10);
    }

    public function latestLog()
    {
        return $this->hasOne(WebhookLog::class)->latest();
    }

    /** Return only enabled webhooks that listen to this event */
    public static function forEvent(string $event)
    {
        return static::where('enabled', true)
            ->whereNull('deleted_at')
            ->get()
            ->filter(function ($wh) use ($event) {
                return in_array($event, $wh->events ?? []);
            });
    }
}
