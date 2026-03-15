<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiPermission extends Model
{
    protected $fillable = ['role_id', 'subject', 'action', 'enabled'];

    protected $casts = ['enabled' => 'boolean'];

    public function role()
    {
        return $this->belongsTo(ApiRole::class, 'role_id');
    }
}
