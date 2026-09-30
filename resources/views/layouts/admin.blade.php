<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <title>@yield('title', 'ANAPEC - Admin')</title>
    
    <!-- Vite CSS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    
    <!-- Page specific CSS -->
    @stack('stylesheets')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex flex-col min-h-screen">
        {{-- Header --}}
        @include('partials.header')
        
        <div class="flex flex-1">
            {{-- Sidebar --}}
            @include('partials.sidebar')
            
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
        
        {{-- Footer --}}
        @include('partials.footer')
    </div>

    <!-- Vite JS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif
    
    <!-- Page specific JS -->
    @stack('scripts')
</body>
</html>