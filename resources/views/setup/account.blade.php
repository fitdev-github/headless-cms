@extends('layouts.setup', ['currentStep' => 3])
@section('title', 'Admin Account')
@section('subtitle', 'Step 3 of 5 — Create Administrator')

@section('content')
<h3 class="text-xl font-semibold text-gray-900 mb-1">Create Admin Account</h3>
<p class="text-sm text-gray-500 mb-6">Set up your administrator credentials.</p>

@if($errors->any())
    <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('setup.account.save') }}">
    @csrf

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Administrator"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-400 @enderror">
    </div>

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-400 @enderror">
    </div>

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
        <input type="password" name="password" placeholder="Min. 8 characters"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-400 @enderror">
    </div>

    <div class="mb-5">
        <label class="block text-xs font-medium text-gray-600 mb-1">Confirm Password</label>
        <input type="password" name="password_confirmation"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <button type="submit"
        class="block w-full py-2.5 px-4 text-center font-medium rounded-lg transition-colors text-sm bg-blue-600 hover:bg-blue-700 text-white">
        Continue to Site Settings →
    </button>
</form>
@endsection
