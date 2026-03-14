<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Setup') — HeadlessCMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full">

<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    {{-- Logo / Header --}}
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
        </div>
        <h2 class="mt-4 text-center text-3xl font-bold tracking-tight text-gray-900">
            HeadlessCMS
        </h2>
        <p class="mt-1 text-center text-sm text-gray-500">@yield('subtitle', 'Installation Wizard')</p>
    </div>

    {{-- Steps progress --}}
    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="flex items-center justify-center gap-2 mb-6">
            @php
                $steps = ['Welcome', 'Database', 'Account', 'Settings', 'Complete'];
                $current = (int) ($currentStep ?? 1);
            @endphp
            @foreach($steps as $i => $step)
                @php $num = $i + 1; @endphp
                <div class="flex items-center {{ $num < count($steps) ? 'flex-1' : '' }}">
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold
                            {{ $num < $current ? 'bg-blue-600 text-white' : ($num === $current ? 'bg-blue-600 text-white ring-4 ring-blue-100' : 'bg-gray-200 text-gray-500') }}">
                            @if($num < $current)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="text-xs {{ $num === $current ? 'text-blue-700 font-medium' : 'text-gray-400' }} hidden sm:inline">{{ $step }}</span>
                    </div>
                    @if($num < count($steps))
                        <div class="flex-1 h-px mx-2 {{ $num < $current ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Card --}}
        <div class="bg-white py-8 px-6 shadow-sm rounded-2xl border border-gray-100">
            @yield('content')
        </div>
    </div>
</div>

</body>
</html>
