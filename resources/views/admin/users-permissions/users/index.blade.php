@extends('layouts.admin')
@section('title', 'API Users — Users & Permissions')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users & Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">Frontend / API users (not admin panel users)</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-5 border-b border-gray-200">
        <a href="{{ route('admin.up.roles') }}"
            class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">Roles</a>
        <a href="{{ route('admin.up.users') }}"
            class="px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">API Users</a>
    </div>

    {{-- Search --}}
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by email or username…"
            class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        @if(request('q'))
        <a href="{{ route('admin.up.users') }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">Clear</a>
        @endif
    </form>

    @if($users->isEmpty())
        <div class="bg-white border border-dashed border-gray-200 rounded-xl py-12 text-center">
            <p class="text-sm text-gray-400">No API users yet. They'll appear here after registering via <code class="bg-gray-100 px-1 rounded text-xs">POST /api/auth/local/register</code>.</p>
        </div>
    @else
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="relative px-5 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $user->username }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $user->email }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500">{{ $user->role?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $user->blocked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $user->blocked ? 'Blocked' : 'Active' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-400">{{ $user->created_at->diffForHumans() }}</td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('admin.up.users.block', $user->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-xs px-3 py-1.5 font-medium {{ $user->blocked ? 'text-green-600 hover:bg-green-50' : 'text-yellow-600 hover:bg-yellow-50' }} rounded-lg transition-colors">
                                    {{ $user->blocked ? 'Unblock' : 'Block' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.up.users.destroy', $user->id) }}"
                                onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-3 py-1.5 font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
