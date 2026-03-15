<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_id', 'event', 'payload', 'status_code',
        'response', 'success', 'delivered_at',
    ];

    protected $casts = [
        'success'      => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
