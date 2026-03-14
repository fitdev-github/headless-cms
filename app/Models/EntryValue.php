<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntryValue extends Model
{
    protected $fillable = [
        'entry_id', 'field_id',
        'value_text', 'value_number', 'value_boolean',
        'value_date', 'value_json',
    ];

    protected $casts = [
        'value_json'    => 'array',
        'value_boolean' => 'boolean',
        'value_date'    => 'datetime',
    ];

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
