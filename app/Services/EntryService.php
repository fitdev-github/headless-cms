<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\EntryValue;
use App\Models\Field;
use Illuminate\Support\Facades\Hash;

class EntryService
{
    /**
     * Get all field values for an entry as [field_name => typed_value].
     */
    public function getValues(Entry $entry): array
    {
        if (!$entry->relationLoaded('values')) {
            $entry->load('values.field');
        }

        $result = [];
        foreach ($entry->values as $ev) {
            if (!$ev->field) continue;
            $result[$ev->field->name] = $this->getTypedValue($ev, $ev->field);
        }

        return $result;
    }

    /**
     * Save (upsert) all field values for an entry from form data.
     */
    public function saveValues(Entry $entry, array $data, $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field->name, $data)) continue;

            $value = $data[$field->name];

            $ev = EntryValue::firstOrNew([
                'entry_id' => $entry->id,
                'field_id' => $field->id,
            ]);

            $ev->value_text    = null;
            $ev->value_number  = null;
            $ev->value_boolean = null;
            $ev->value_date    = null;
            $ev->value_json    = null;

            $this->setTypedValue($ev, $field, $value);
            $ev->save();
        }
    }

    public function getTypedValue(EntryValue $ev, Field $field)
    {
        switch ($field->type) {
            case 'number':
                return $ev->value_number !== null ? (float) $ev->value_number : null;
            case 'boolean':
                return $ev->value_boolean !== null ? (bool) $ev->value_boolean : null;
            case 'date':
            case 'datetime':
                return $ev->value_date?->toISOString();
            case 'media':
            case 'relation':
            case 'json':
            case 'component':
            case 'dynamiczone':
                return $ev->value_json;
            case 'password':
                return null;
            default:
                return $ev->value_text;
        }
    }

    public function setTypedValue(EntryValue $ev, Field $field, $value): void
    {
        switch ($field->type) {
            case 'number':
                $ev->value_number = ($value !== '' && $value !== null) ? (float) $value : null;
                break;
            case 'boolean':
                $ev->value_boolean = ($value !== null) ? (int)(bool) $value : null;
                break;
            case 'date':
            case 'datetime':
                $ev->value_date = $value ?: null;
                break;
            case 'media':
            case 'json':
            case 'component':
            case 'dynamiczone':
                $ev->value_json = is_string($value) ? json_decode($value, true) : $value;
                break;
            case 'relation':
                // Support array (many) or single ID
                if (is_array($value)) {
                    $ev->value_json = array_map('intval', $value);
                } elseif ($value !== null && $value !== '') {
                    $ev->value_json = (int) $value;
                }
                break;
            case 'password':
                if ($value) {
                    $ev->value_text = Hash::make($value);
                }
                break;
            default:
                $ev->value_text = $value;
        }
    }

    /**
     * Get the first published entry for a single-type content type.
     */
    public function getSingleEntry(\App\Models\ContentType $contentType): ?Entry
    {
        return Entry::where('content_type_id', $contentType->id)
                    ->where('status', 'published')
                    ->first();
    }
}
