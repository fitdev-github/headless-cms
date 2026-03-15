<?php

namespace App\Services;

use App\Models\ComponentField;
use App\Models\Field;

class FieldRenderer
{
    public function renderInput(Field|ComponentField $field, $value, string $namePrefix = ''): string
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
            'component'   => $this->renderComponent($field, $value),
            'dynamiczone' => $this->renderDynamicZone($field, $value),
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

    // ─── Component & Dynamic Zone ─────────────────────────────────────────────

    private function renderComponent(Field|ComponentField $field, $value): string
    {
        $componentId = (int) $field->getOption('component_id');
        $repeatable  = (bool) $field->getOption('repeatable', false);

        $component = \App\Models\Component::with(['fields' => fn($q) => $q->orderBy('sort_order')])->find($componentId);
        if (!$component) {
            return '<p class="text-sm text-red-500 p-2 bg-red-50 rounded">Component not found. <a href="/admin/content-type-builder/components" class="underline">Manage components →</a></p>';
        }

        if (!$repeatable) {
            // Non-repeatable: PHP-render sub-fields directly with name prefix
            $subValues = is_array($value) ? $value : [];
            $subHtml   = '';
            foreach ($component->fields as $cf) {
                $subVal   = $subValues[$cf->name] ?? null;
                $subHtml .= $this->renderInput($cf, $subVal, $field->name);
            }
            $compLabel = e($component->display_name);
            return <<<HTML
<div class="border border-gray-200 rounded-xl overflow-hidden" x-data="{open: true}">
    <button type="button" class="flex items-center gap-2 w-full px-4 py-3 bg-gray-50 text-left hover:bg-gray-100 transition-colors" @click="open = !open">
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-150 flex-shrink-0" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm font-medium text-gray-700">{$compLabel}</span>
    </button>
    <div x-show="open" x-cloak class="p-4 border-t border-gray-100">
        {$subHtml}
    </div>
</div>
HTML;
        }

        // Repeatable: Alpine.js manages an array of items
        $fieldName  = $field->name;
        $cid        = $component->id;
        $compLabel  = e($component->display_name);
        $uid        = 'rcomp_' . $field->id;

        $items = [];
        foreach ((array) $value as $item) {
            $items[] = [
                '_key'        => $item['_key'] ?? ('k' . uniqid()),
                'componentId' => $cid,
                'open'        => true,
                'fields'      => $item['fields'] ?? [],
            ];
        }
        $itemsJson   = e(json_encode($items));
        $subFieldsHtml = $this->buildRepeatableSubFields($component->fields->all(), $fieldName);

        return <<<HTML
<div x-data="repeatableComp('{$uid}', {$itemsJson}, {$cid})" class="space-y-2">
    <template x-for="(item, idx) in items" :key="item._key">
        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
            <div class="flex items-center gap-2 px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                <button type="button" @click="item.open = !item.open" class="flex items-center gap-1.5 flex-1 text-left min-w-0">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-150" :class="item.open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-sm font-medium text-gray-700">{$compLabel} <span class="text-gray-400 font-normal text-xs" x-text="'#' + (idx+1)"></span></span>
                </button>
                <button type="button" @click="removeItem(idx)" class="text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div x-show="item.open" class="p-4 space-y-3">
                <input type="hidden" :name="'{$fieldName}['+idx+'][_key]'" :value="item._key">
                <input type="hidden" :name="'{$fieldName}['+idx+'][componentId]'" value="{$cid}">
                {$subFieldsHtml}
            </div>
        </div>
    </template>
    <button type="button" @click="addItem()"
        class="flex items-center gap-2 w-full px-4 py-2.5 border border-dashed border-gray-300 rounded-xl text-sm text-gray-500 hover:border-blue-400 hover:text-blue-600 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add {$compLabel}
    </button>
</div>
<script>
if (typeof repeatableComp === 'undefined') {
    window.repeatableComp = function(uid, existing, cid) {
        return {
            items: existing,
            addItem() { this.items.push({ _key: 'k' + Date.now(), componentId: cid, open: true, fields: {} }); },
            removeItem(idx) { if (confirm('Remove this item?')) this.items.splice(idx, 1); }
        };
    };
}
</script>
HTML;
    }

    private function renderDynamicZone(Field|ComponentField $field, $value): string
    {
        $allowedIds = (array) $field->getOption('allowed_component_ids', []);
        $fieldName  = $field->name;

        if (empty($allowedIds)) {
            return '<p class="text-sm text-amber-600 p-2 bg-amber-50 rounded border border-amber-200">No components configured. Edit this field in Content-Type Builder.</p>';
        }

        $components = \App\Models\Component::with(['fields' => fn($q) => $q->orderBy('sort_order')])
            ->whereIn('id', $allowedIds)
            ->get();

        if ($components->isEmpty()) {
            return '<p class="text-sm text-red-500 p-2 bg-red-50 rounded">No components found for allowed IDs.</p>';
        }

        $items = [];
        foreach ((array) $value as $item) {
            $items[] = [
                '_key'        => $item['_key'] ?? ('k' . uniqid()),
                '__component' => $item['__component'] ?? '',
                'componentId' => (int) ($item['componentId'] ?? 0),
                'open'        => true,
                'fields'      => $item['fields'] ?? [],
            ];
        }
        $itemsJson = e(json_encode($items));

        $compsData = $components->map(fn($c) => [
            'id' => $c->id, 'name' => $c->name, 'display_name' => $c->display_name,
        ])->values()->toArray();
        $compsJson = e(json_encode($compsData));

        // Per-component-type field sections (shown via x-show)
        $compSections = '';
        foreach ($components as $comp) {
            $cid     = $comp->id;
            $subHtml = $this->buildRepeatableSubFields($comp->fields->all(), $fieldName);
            $compSections .= <<<HTML
<div x-show="item.componentId === {$cid}">
    {$subHtml}
</div>
HTML;
        }

        // Picker dropdown buttons
        $pickerBtns = '';
        foreach ($components as $comp) {
            $compJson  = e(json_encode(['id' => $comp->id, 'name' => $comp->name, 'display_name' => $comp->display_name]));
            $compLabel = e($comp->display_name);
            $pickerBtns .= <<<HTML
<button type="button" @click="addComponent({$compJson}); open = false"
    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-colors w-full text-left">
    <span class="w-5 h-5 flex items-center justify-center bg-purple-100 text-purple-600 rounded text-xs font-bold flex-shrink-0">+</span>
    {$compLabel}
</button>
HTML;
        }

        $uid = 'dz_' . $field->id;

        return <<<HTML
<div x-data="dynamicZone('{$uid}', {$itemsJson}, {$compsJson})" class="space-y-2">
    <template x-for="(item, idx) in items" :key="item._key">
        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white">
            <div class="flex items-center gap-2 px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                <button type="button" @click="item.open = !item.open" class="flex items-center gap-1.5 flex-1 text-left min-w-0">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="item.open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-sm font-medium text-gray-700" x-text="compDisplayName(item.componentId)"></span>
                    <code class="text-xs text-gray-400 ml-1" x-text="item.__component"></code>
                </button>
                <button type="button" @click="removeItem(idx)" class="text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div x-show="item.open" class="p-4 space-y-3">
                <input type="hidden" :name="'{$fieldName}['+idx+'][_key]'" :value="item._key">
                <input type="hidden" :name="'{$fieldName}['+idx+'][__component]'" :value="item.__component">
                <input type="hidden" :name="'{$fieldName}['+idx+'][componentId]'" :value="item.componentId">
                {$compSections}
            </div>
        </div>
    </template>

    <div class="relative" x-data="{open: false}">
        <button type="button" @click="open = !open"
            class="flex items-center gap-2 w-full px-4 py-2.5 border border-dashed border-gray-300 rounded-xl text-sm text-gray-500 hover:border-purple-400 hover:text-purple-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add a component
            <svg class="w-3 h-3 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" @click.outside="open = false" x-cloak
            class="absolute left-0 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg p-1 z-10">
            {$pickerBtns}
        </div>
    </div>
</div>
<script>
if (typeof dynamicZone === 'undefined') {
    window.dynamicZone = function(uid, existing, comps) {
        return {
            items: existing,
            components: comps,
            compDisplayName(id) { const c = this.components.find(c => c.id === id); return c ? c.display_name : 'Unknown'; },
            addComponent(comp) { this.items.push({ _key: 'k' + Date.now(), __component: comp.name, componentId: comp.id, open: true, fields: {} }); },
            removeItem(idx) { if (confirm('Remove this block?')) this.items.splice(idx, 1); }
        };
    };
}
</script>
HTML;
    }

    /** Generate Alpine-bound inputs for repeatable/dynamiczone sub-fields. */
    private function buildRepeatableSubFields(array $componentFields, string $fieldName): string
    {
        $html = '';
        foreach ($componentFields as $cf) {
            $label   = e($cf->display_name);
            $reqMark = $cf->isRequired() ? ' <span class="text-red-500">*</span>' : '';
            $input   = $this->buildAlpineInput($cf, $fieldName);
            $html   .= <<<HTML
<div class="mb-3">
    <label class="block text-xs font-medium text-gray-700 mb-1">{$label}{$reqMark}</label>
    {$input}
</div>
HTML;
        }
        return $html;
    }

    /** Generate a single Alpine-bound input for a ComponentField inside a repeatable block. */
    private function buildAlpineInput(ComponentField $cf, string $fieldName): string
    {
        $name = $cf->name;
        $cls  = $this->inputClass();

        return match ($cf->type) {
            'text', 'uid', 'email' =>
                "<input type=\"text\" :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" class=\"{$cls}\">",
            'textarea' =>
                "<textarea :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" rows=\"3\" class=\"{$cls}\"></textarea>",
            'richtext' =>
                "<textarea :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" rows=\"5\" class=\"{$cls} font-mono text-xs\"></textarea>",
            'number' =>
                "<input type=\"number\" step=\"any\" :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" class=\"{$cls}\">",
            'date' =>
                "<input type=\"date\" :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" class=\"{$cls}\">",
            'datetime' =>
                "<input type=\"datetime-local\" :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" class=\"{$cls}\">",
            'boolean' => $this->buildAlpineBoolInput($cf, $fieldName),
            'enumeration' => $this->buildAlpineEnumInput($cf, $fieldName),
            default =>
                "<input type=\"text\" :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" class=\"{$cls}\">",
        };
    }

    private function buildAlpineBoolInput(ComponentField $cf, string $fieldName): string
    {
        $name = $cf->name;
        return <<<HTML
<div class="flex items-center gap-3">
    <button type="button" @click="item.fields.{$name} = !item.fields.{$name}"
        :class="item.fields.{$name} ? 'bg-blue-600' : 'bg-gray-300'"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
        <span :class="item.fields.{$name} ? 'translate-x-6' : 'translate-x-1'"
              class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"></span>
    </button>
    <input type="hidden" :name="'{$fieldName}['+idx+'][fields][{$name}]'" :value="item.fields.{$name} ? '1' : '0'">
    <span class="text-sm text-gray-600" x-text="item.fields.{$name} ? 'True' : 'False'"></span>
</div>
HTML;
    }

    private function buildAlpineEnumInput(ComponentField $cf, string $fieldName): string
    {
        $name    = $cf->name;
        $opts    = $cf->getOption('enum_values', []);
        $options = '<option value="">-- Select --</option>';
        foreach ((array) $opts as $opt) {
            $esc      = e($opt);
            $options .= "<option value=\"{$esc}\">{$esc}</option>";
        }
        $cls = $this->inputClass();
        return "<select :name=\"'{$fieldName}['+idx+'][fields][{$name}]'\" x-model=\"item.fields.{$name}\" class=\"{$cls}\">{$options}</select>";
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
