@extends('layouts.admin')
@section('title', 'Edit Role — '.$role->name)

@section('content')
<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.up.roles') }}" class="text-sm text-gray-400 hover:text-gray-600">Roles</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm text-gray-900 font-medium">{{ $role->name }}</span>
    </div>

    <form method="POST" action="{{ route('admin.up.roles.update', $role->id) }}">
        @csrf @method('PUT')

        {{-- Content Types permissions --}}
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden mb-4">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Content Types</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-400 uppercase">Content Type</th>
                            @foreach($actions as $a)
                            <th class="px-4 py-2.5 text-center text-xs font-medium text-gray-400 uppercase w-20">{{ $a }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($contentTypes as $ct)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium text-gray-900 text-sm">
                                {{ $ct->display_name }}
                                <span class="text-gray-400 text-xs ml-1 font-mono">{{ $ct->plural_name }}</span>
                            </td>
                            @foreach($actions as $a)
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox"
                                    name="permissions[{{ $ct->plural_name }}][{{ $a }}]"
                                    value="1"
                                    {{ ($permissions[$ct->plural_name][$a] ?? false) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        @if($contentTypes->isEmpty())
                        <tr><td colspan="{{ count($actions) + 1 }}" class="px-5 py-4 text-sm text-gray-400 text-center">No content types defined yet.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Upload permissions --}}
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden mb-4">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Media Library (Upload)</h3>
            </div>
            <div class="px-5 py-4 flex flex-wrap gap-4">
                @foreach($uploadActions as $a)
                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox"
                        name="permissions[upload][{{ $a }}]"
                        value="1"
                        {{ ($permissions['upload'][$a] ?? false) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="font-mono text-xs text-gray-700">{{ $a }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.up.roles') }}"
                class="flex-1 py-2.5 text-sm font-medium text-center text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="flex-1 py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Save Permissions
            </button>
        </div>
    </form>
</div>
@endsection
