<?php

namespace App\Services;

use App\Models\Field;

class FieldRenderer
{
    public function renderInput(Field $field, $value, string $namePrefix = ''): string
    {
        $name     = $namePrefix ? "{$namePrefix}[{$field->name}]" : $field->name;
        $id       = 'field_' . $field->id;
        $label    = e($field->display_name);
        $required = $field->isRequired() ? 'required' : '';
        $strVal   = $value !== null ? e((string) $value) : '';
        $reqMark  = $field->isRequired() ? ' <span class="text-red-500">*</span>' : '';

        $input = match ($field->type) {
            'text'        => $this->text($name, $id, $strVal, $required, $field),
            'textarea'    => $this->textarea($name, $id, $strVal, $required, $field),
            'richtext'    => $this->richtext($name, $id, $value, $field),
            'number'      => $this->number($name, $id, $strVal, $required, $field),
            'boolean'     => $this->boolean($name, $id, $value),
            'date'        => $this->dateinput($name, $id, $strVal, $required, 'date'),
            'datetime'    => $this->dateinput($name, $id, $strVal, $required, 'datetime-local'),
            'email'       => $this->email($name, $id, $strVal, $required),
            'password'    => $this->password($name, $id),
            'enumeration' => $this->enumeration($name, $id, $strVal, $required, $field),
            'uid'         => $this->uid($name, $id, $strVal, $required, $field),
            'media'       => $this->media($name, $id, $value, $field),
            'json'        => $this->json($name, $id, $value, $required),
            'relation'    => $this->relation($name, $id, $value, $field),
            default       => $this->text($name, $id, $strVal, $required, $field),
        };

        return <<<HTML
<div class="mb-6">
    <label for="{$id}" class="block text-sm font-medium text-gray-700 mb-1">{$label}{$reqMark}</label>
    {$input}
</div>
HTML;
    }

    private function inputClass(): string
    {
        return 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent';
    }

    private function text(string $name, string $id, string $value, string $req, Field $field): string
    {
        $max = $field->getOption('maxLength') ? "maxlength=\"{$field->getOption('maxLength')}\"" : '';
        $cls = $this->inputClass();
        return "<input type=\"text\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" {$req} {$max} class=\"{$cls}\">";
    }

    private function textarea(string $name, string $id, string $value, string $req, Field $field): string
    {
        $rows = $field->getOption('rows', 4);
        $cls  = $this->inputClass();
        return "<textarea name=\"{$name}\" id=\"{$id}\" rows=\"{$rows}\" {$req} class=\"{$cls}\">{$value}</textarea>";
    }

    private function richtext(string $name, string $id, $value, Field $field): string
    {
        $escaped  = e((string)($value ?? ''));
        $initId   = 'tiptap_' . $field->id;
        $fnName   = 'rtEditor_' . $field->id;
        return <<<HTML
<div x-data="{$fnName}()" x-init="init()" class="border border-gray-300 rounded-lg overflow-hidden">
    <div id="{$initId}" class="min-h-[200px] p-3 prose max-w-none focus:outline-none [&_.ProseMirror]:outline-none"></div>
    <input type="hidden" name="{$name}" id="{$id}" value="{$escaped}" x-ref="hidden">
</div>
<script>
function {$fnName}() {
    return {
        editor: null,
        init() {
            const el = document.getElementById('{$initId}');
            const hidden = this.\$refs.hidden;
            import('https://esm.sh/@tiptap/core@2').then(({ Editor }) => {
                import('https://esm.sh/@tiptap/starter-kit@2').then(({ default: StarterKit }) => {
                    this.editor = new Editor({
                        element: el,
                        extensions: [StarterKit],
                        content: hidden.value || '',
                        onUpdate({ editor }) { hidden.value = editor.getHTML(); }
                    });
                });
            });
        }
    };
}
</script>
HTML;
    }

    private function number(string $name, string $id, string $value, string $req, Field $field): string
    {
        $min = $field->getOption('min') !== null ? "min=\"{$field->getOption('min')}\"" : '';
        $max = $field->getOption('max') !== null ? "max=\"{$field->getOption('max')}\"" : '';
        $cls = $this->inputClass();
        return "<input type=\"number\" step=\"any\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" {$req} {$min} {$max} class=\"{$cls}\">";
    }

    private function boolean(string $name, string $id, $value): string
    {
        $isOn   = ($value === true || $value === 1 || $value === '1') ? 'true' : 'false';
        $strVal = $isOn === 'true' ? '1' : '0';
        return <<<HTML
<div class="flex items-center gap-3" x-data="{ on: {$isOn} }">
    <button type="button" @click="on = !on; \$refs.inp.value = on ? '1' : '0'"
        :class="on ? 'bg-blue-600' : 'bg-gray-300'"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
        <span :class="on ? 'translate-x-6' : 'translate-x-1'"
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow"></span>
    </button>
    <input type="hidden" name="{$name}" id="{$id}" value="{$strVal}" x-ref="inp">
    <span class="text-sm text-gray-600" x-text="on ? 'True' : 'False'"></span>
</div>
HTML;
    }

    private function dateinput(string $name, string $id, string $value, string $req, string $type): string
    {
        if ($value && $type === 'datetime-local' && str_contains($value, 'T')) {
            $value = substr($value, 0, 16);
        }
        $cls = $this->inputClass();
        return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" {$req} class=\"{$cls}\">";
    }

    private function email(string $name, string $id, string $value, string $req): string
    {
        $cls = $this->inputClass();
        return "<input type=\"email\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" {$req} class=\"{$cls}\">";
    }

    private function password(string $name, string $id): string
    {
        $cls = $this->inputClass();
        return "<input type=\"password\" name=\"{$name}\" id=\"{$id}\" placeholder=\"Leave blank to keep unchanged\" class=\"{$cls}\">";
    }

    private function enumeration(string $name, string $id, string $value, string $req, Field $field): string
    {
        $enumVals = $field->getOption('enum_values', []);
        $options  = '<option value="">-- Select --</option>';
        foreach ((array) $enumVals as $opt) {
            $sel      = ($opt === $value) ? 'selected' : '';
            $escaped  = e($opt);
            $options .= "<option value=\"{$escaped}\" {$sel}>{$escaped}</option>";
        }
        $cls = $this->inputClass();
        return "<select name=\"{$name}\" id=\"{$id}\" {$req} class=\"{$cls}\">{$options}</select>";
    }

    private function uid(string $name, string $id, string $value, string $req, Field $field): string
    {
        $from = e($field->getOption('uid_field', ''));
        $cls  = $this->inputClass();
        return <<<HTML
<input type="text" name="{$name}" id="{$id}" value="{$value}" {$req}
       data-uid-from="{$from}" class="{$cls}">
<p class="text-xs text-gray-400 mt-1">Auto-generated from <em>{$from}</em>. You can edit it manually.</p>
HTML;
    }

    private function media(string $name, string $id, $value, Field $field): string
    {
        $json   = e(json_encode($value));
        $fid    = $field->id;
        $thumb  = ($value && isset($value['url'])) ? $value['url'] : '';
        $fname  = ($value && isset($value['name'])) ? e($value['name']) : '';
        return <<<HTML
<div x-data="mediaField{$fid}()" class="space-y-2">
    <input type="hidden" name="{$name}" id="{$id}" :value="JSON.stringify(sel)" x-ref="hidden">
    <template x-if="sel">
        <div class="flex items-center gap-3 p-2 bg-gray-50 border border-gray-200 rounded-lg">
            <img :src="sel.url" x-show="sel.url" class="w-12 h-12 object-cover rounded">
            <span class="text-sm text-gray-700 truncate flex-1" x-text="sel.original_name || sel.name"></span>
            <button type="button" @click="sel = null" class="text-red-400 hover:text-red-600 text-xs">Remove</button>
        </div>
    </template>
    <button type="button" @click="pick()"
        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Choose from Media Library
    </button>
</div>
<script>
function mediaField{$fid}() {
    return {
        sel: {$json},
        pick() {
            const w = window.open('/admin/media-library?picker=1', 'mediapicker_{$fid}', 'width=900,height=620');
            const handler = (e) => {
                if (e.data?.type === 'media_selected') {
                    this.sel = e.data.media;
                    window.removeEventListener('message', handler);
                    w.close();
                }
            };
            window.addEventListener('message', handler);
        }
    };
}
</script>
HTML;
    }

    private function json(string $name, string $id, $value, string $req): string
    {
        $escaped = e(json_encode($value, JSON_PRETTY_PRINT));
        $cls     = $this->inputClass();
        return "<textarea name=\"{$name}\" id=\"{$id}\" rows=\"6\" {$req} class=\"font-mono text-xs {$cls}\">{$escaped}</textarea>";
    }

    private function relation(string $name, string $id, $value, Field $field): string
    {
        $relType = $field->getRelationContentType();
        if (!$relType) {
            return '<p class="text-sm text-red-500 p-2 bg-red-50 rounded">Related content type not found.</p>';
        }

        $relDisplayField = $field->getOption('relation_display_field', 'id');
        $entries = \App\Models\Entry::where('content_type_id', $relType->id)
                                    ->where('status', 'published')
                                    ->with('values.field')
                                    ->get();

        $isMany      = $field->getOption('relation_kind') === 'manyToMany';
        $selectedIds = is_array($value) ? array_map('intval', $value) : ($value ? [(int)$value] : []);

        $cls = $this->inputClass();

        if ($isMany) {
            $options = '';
            foreach ($entries as $e) {
                $vals    = (new EntryService())->getValues($e);
                $label   = e((string) ($vals[$relDisplayField] ?? "Entry #{$e->id}"));
                $sel     = in_array($e->id, $selectedIds) ? 'selected' : '';
                $options .= "<option value=\"{$e->id}\" {$sel}>{$label}</option>";
            }
            return "<select name=\"{$name}[]\" id=\"{$id}\" multiple class=\"h-36 {$cls}\">{$options}</select>"
                 . "<p class=\"text-xs text-gray-400 mt-1\">Hold Ctrl / Cmd to select multiple.</p>";
        }

        $options = '<option value="">-- None --</option>';
        foreach ($entries as $e) {
            $vals    = (new EntryService())->getValues($e);
            $label   = e((string) ($vals[$relDisplayField] ?? "Entry #{$e->id}"));
            $sel     = in_array($e->id, $selectedIds) ? 'selected' : '';
            $options .= "<option value=\"{$e->id}\" {$sel}>{$label}</option>";
        }
        return "<select name=\"{$name}\" id=\"{$id}\" class=\"{$cls}\">{$options}</select>";
    }
}
