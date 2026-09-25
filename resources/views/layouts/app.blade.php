<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ANAPEC — API Management')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="h-full bg-gray-50 font-sans text-gray-900 antialiased overflow-x-hidden">

<div class="flex h-full">

    {{-- ── SIDEBAR (desktop) ── --}}
    <aside id="app-sidebar"
           class="hidden lg:flex w-60 shrink-0 flex-col border-r border-gray-200 bg-white"
           style="position: static;">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 pt-5 pb-4">
            <img src="{{ asset('images/logo.png') }}" alt="ANAPEC" class="h-8 w-auto" style="height:32px">
            <div>
                <p class="text-sm font-semibold text-gray-900 leading-tight">ANAPEC</p>
                <p class="text-xs text-gray-500 leading-tight">API Management</p>
            </div>
        </div>

        <nav class="mt-2 flex-1 space-y-0.5 px-3" aria-label="Navigation principale">

            <a href="{{ route('dashboard') }}"
               data-nav="dashboard"
               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.5 4A1.5 1.5 0 0 1 4 2.5h4A1.5 1.5 0 0 1 9.5 4v4A1.5 1.5 0 0 1 8 9.5H4A1.5 1.5 0 0 1 2.5 8V4Zm8 0A1.5 1.5 0 0 1 12 2.5h4A1.5 1.5 0 0 1 17.5 4v4A1.5 1.5 0 0 1 16 9.5h-4A1.5 1.5 0 0 1 10.5 8V4Zm0 8A1.5 1.5 0 0 1 12 10.5h4A1.5 1.5 0 0 1 17.5 12v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4ZM2.5 12a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 8 16.5H4A1.5 1.5 0 0 1 2.5 15v-4Z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.users') }}" data-nav="users" data-admin-only
                class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                       text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                       data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM4 10a6 6 0 1 1 12 0"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4 14a1 1 0 0 1 1-1h10a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Zm0 4a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Z"/>
                </svg>
                <span>Utilisateurs</span>
            </a>

            <a href="{{ route('admin.web-services') }}" data-nav="web-services" data-admin-only
               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M5 3.25a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75V5.5a.75.75 0 0 1-.75.75h-8.5A.75.75 0 0 1 5 5.5V3.25Zm0 4.5a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75v5.5a.75.75 0 0 1-.75.75H5.75a.75.75 0 0 1-.75-.75V7.75Z"/>
                </svg>
                <span>Web Services</span>
            </a>

            <a href="{{ route('admin.web-service-domains') }}" data-nav="web-service-domains" data-admin-only
                class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                       text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                       data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.5 3.25a.75.75 0 0 1 .75-.75h13.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H3.25a.75.75 0 0 1-.75-.75V3.25Zm1.5.75v12h12v-12h-12Z"/>
                    <path d="M5.5 7h9v2h-9V7Zm0 3.5h9v2h-9v-2Zm0 3.5h5v2h-5v-2Z"/>
                </svg>
                <span>Domaines</span>
            </a>

            <a href="{{ url('api/documentation') }}" target="_blank" data-nav="docs"
               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M3 4.25a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-2.25H4.5A.75.75 0 0 1 3.75 4.25H3Zm-1 0A1.75 1.75 0 0 1 3.75 2.5h12.5A1.75 1.75 0 0 1 18 4.25v3A1.75 1.75 0 0 1 16.25 9h-4.5A1.75 1.75 0 0 1 10 7.25V5h-5.5A.25.25 0 0 0 4.25 5.25v2A1.75 1.75 0 0 1 2.5 9h-.75A.75.75 0 0 1 1 8.25v-4Z"/>
                    <path d="M2.5 11.5h15a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-.75.75h-5a.75.75 0 0 1-.75-.75v-2.25h-3v2.25a.75.75 0 0 1-.75.75h-5a.75.75 0 0 1-.75-.75v-3.75a.75.75 0 0 1 .75-.75Z"/>
                </svg>
                <span>Documentation API</span>
            </a>

            <a href="#" data-nav="settings"
               class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M9.53 1.701a.75.75 0 0 1 .81 0 8.95 8.95 0 0 0 3.291 2.353.75.75 0 0 1-.445 1.396 7.45 7.45 0 0 1-2.756-1.973.75.75 0 0 1 .101-1.525A12.14 12.14 0 0 1 10.5 1.25v-.25a.75.75 0 0 0-.97-.717ZM15.94 5.16a.75.75 0 0 1 1.02.25l.75 1.3a.75.75 0 0 1-.25.75 8.95 8.95 0 0 1-2.75 1.973.75.75 0 0 1-.445-1.396 7.45 7.45 0 0 0 1.98-1.75.75.75 0 0 1-.305-.127ZM18.03 13.75a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75Z"/>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M4.57 2.41a.75.75 0 0 1 1.03 1.03 8.95 8.95 0 0 0 0 13.12.75.75 0 1 1-1.03 1.03 10.45 10.45 0 0 1 0-15.18.75.75 0 0 1 1.03 0Z"/>
                </svg>
                <span>Paramètres</span>
            </a>
        </nav>

        <div class="px-5 py-4 border-t border-gray-100">
            <p class="text-xs text-gray-400">ANAPEC API Portal</p>
            <p class="text-xs text-gray-400">v1.0.0</p>
        </div>
    </aside>

    {{-- ── MOBILE SIDEBAR (fixed overlay) ── --}}
    <aside id="mobile-sidebar"
           class="fixed inset-y-0 left-0 z-50 hidden w-60 flex-col border-r border-gray-200 bg-white shadow-lg lg:hidden">

        <div class="flex items-center justify-between px-5 pt-5 pb-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="ANAPEC" style="height:28px" class="w-auto">
                <div>
                    <p class="text-sm font-semibold text-gray-900 leading-tight">ANAPEC</p>
                    <p class="text-xs text-gray-500 leading-tight">API Management</p>
                </div>
            </div>
            <button id="mobile-sidebar-close" type="button" class="p-1 text-gray-400 hover:text-gray-600"
                    aria-label="Fermer le menu">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>

        <nav class="mt-2 flex-1 space-y-0.5 px-3" aria-label="Navigation principale mobile">
            <a href="{{ route('dashboard') }}"
               data-mobile-nav="dashboard"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.5 4A1.5 1.5 0 0 1 4 2.5h4A1.5 1.5 0 0 1 9.5 4v4A1.5 1.5 0 0 1 8 9.5H4A1.5 1.5 0 0 1 2.5 8V4Zm8 0A1.5 1.5 0 0 1 12 2.5h4A1.5 1.5 0 0 1 17.5 4v4A1.5 1.5 0 0 1 16 9.5h-4A1.5 1.5 0 0 1 10.5 8V4Zm0 8A1.5 1.5 0 0 1 12 10.5h4A1.5 1.5 0 0 1 17.5 12v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4ZM2.5 12a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 8 16.5H4A1.5 1.5 0 0 1 2.5 15v-4Z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.users') }}" data-mobile-nav="users" data-admin-only
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM4 10a6 6 0 1 1 12 0"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4 14a1 1 0 0 1 1-1h10a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Zm0 4a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Z"/>
                </svg>
                <span>Utilisateurs</span>
            </a>

            <a href="{{ route('admin.web-services') }}" data-mobile-nav="web-services" data-admin-only
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M5 3.25a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75V5.5a.75.75 0 0 1-.75.75h-8.5A.75.75 0 0 1 5 5.5V3.25Zm0 4.5a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75v5.5a.75.75 0 0 1-.75.75H5.75a.75.75 0 0 1-.75-.75V7.75Z"/>
                </svg>
                <span>Web Services</span>
            </a>

            <a href="{{ route('admin.web-service-domains') }}" data-mobile-nav="web-service-domains" data-admin-only
                class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                       text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                       data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.5 3.25a.75.75 0 0 1 .75-.75h13.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H3.25a.75.75 0 0 1-.75-.75V3.25Zm1.5.75v12h12v-12h-12Z"/>
                    <path d="M5.5 7h9v2h-9V7Zm0 3.5h9v2h-9v-2Zm0 3.5h5v2h-5v-2Z"/>
                </svg>
                <span>Domaines</span>
            </a>

            <a href="{{ url('api/documentation') }}" target="_blank" data-mobile-nav="docs"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M3 4.25a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-2.25H4.5A.75.75 0 0 1 3.75 4.25H3Zm-1 0A1.75 1.75 0 0 1 3.75 2.5h12.5A1.75 1.75 0 0 1 18 4.25v3A1.75 1.75 0 0 1 16.25 9h-4.5A1.75 1.75 0 0 1 10 7.25V5h-5.5A.25.25 0 0 0 4.25 5.25v2A1.75 1.75 0 0 1 2.5 9h-.75A.75.75 0 0 1 1 8.25v-4Z"/>
                    <path d="M2.5 11.5h15a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-.75.75h-5a.75.75 0 0 1-.75-.75v-2.25h-3v2.25a.75.75 0 0 1-.75.75h-5a.75.75 0 0 1-.75-.75v-3.75a.75.75 0 0 1 .75-.75Z"/>
                </svg>
                <span>Documentation API</span>
            </a>

            <a href="#" data-mobile-nav="settings"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                      text-gray-700 hover:bg-anapec-50 hover:text-anapec-700
                      data-[active=true]:bg-anapec-100 data-[active=true]:text-anapec-700">
                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-anapec-600 data-[active=true]:text-anapec-600"
                     viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M9.53 1.701a.75.75 0 0 1 .81 0 8.95 8.95 0 0 0 3.291 2.353.75.75 0 0 1-.445 1.396 7.45 7.45 0 0 1-2.756-1.973.75.75 0 0 1 .101-1.525A12.14 12.14 0 0 1 10.5 1.25v-.25a.75.75 0 0 0-.97-.717ZM15.94 5.16a.75.75 0 0 1 1.02.25l.75 1.3a.75.75 0 0 1-.25.75 8.95 8.95 0 0 1-2.75 1.973.75.75 0 0 1-.445-1.396 7.45 7.45 0 0 0 1.98-1.75.75.75 0 0 1-.305-.127ZM18.03 13.75a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75Z"/>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M4.57 2.41a.75.75 0 0 1 1.03 1.03 8.95 8.95 0 0 0 0 13.12.75.75 0 1 1-1.03 1.03 10.45 10.45 0 0 1 0-15.18.75.75 0 0 1 1.03 0Z"/>
                </svg>
                <span>Paramètres</span>
            </a>
        </nav>

        <div class="px-5 py-4 border-t border-gray-100">
            <p class="text-xs text-gray-400">ANAPEC API Portal</p>
            <p class="text-xs text-gray-400">v1.0.0</p>
        </div>
    </aside>

    {{-- ── BACKDROP ── --}}
    <div id="mobile-backdrop"
         class="fixed inset-0 z-40 hidden bg-black/40 lg:hidden"
         aria-hidden="true"></div>

    {{-- ── MAIN ── --}}
    <div class="flex min-w-0 flex-1 flex-col">

        {{-- Top header --}}
        <header class="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4 lg:px-6">
            {{-- Mobile menu toggle --}}
            <button id="sidebar-toggle" type="button"
                    class="lg:hidden flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900"
                    aria-label="Ouvrir le menu" aria-expanded="false">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 15.25Z"/>
                </svg>
            </button>

            {{-- Breadcrumb / page title --}}
            <div class="hidden lg:block">
                <h1 class="text-sm font-semibold text-gray-900">@yield('title', 'Dashboard')</h1>
            </div>

            {{-- User area --}}
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2">
                    <span id="header-user-name" class="text-sm text-gray-700"></span>
                    <span id="header-user-role" class="rounded-full bg-anapec-100 px-2 py-0.5 text-xs font-medium text-anapec-700"></span>
                </div>
                <div id="user-avatar" class="flex h-8 w-8 items-center justify-center rounded-full bg-anapec-600 text-xs font-bold text-white"
                     aria-label="Avatar utilisateur"></div>
                <button id="logout-btn" type="button"
                        class="text-sm text-gray-500 hover:text-gray-900 transition-colors"
                        aria-label="Déconnexion">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2.25a.75.75 0 0 1-1.5 0V4.25c0-.414-.336-.75-.75-.75h-5.5c-.414 0-.75.336-.75.75v11.5c0 .414.336.75.75.75h5.5c.414 0 .75-.336.75-.75v-2.25a.75.75 0 0 1 1.5 0v2.25A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z"/>
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M15 8.25a.75.75 0 0 0-1.5 0v2.25a.75.75 0 0 0 .53.714l2.5.75a.75.75 0 1 0 .44-1.424L15 10.125V8.25Z"/>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @yield('content')
        </main>
    </div>

</div>

<script>
(function () {
    // Populate header user info from localStorage
    var user = null;
    try { user = JSON.parse(localStorage.getItem('anapec_user')); } catch(e) {}
    if (user) {
        var nameEl = document.getElementById('header-user-name');
        var roleEl = document.getElementById('header-user-role');
        var avatarEl = document.getElementById('user-avatar');
        if (nameEl) nameEl.textContent = user.name || '';
        if (roleEl) roleEl.textContent = user.role || '';
        if (avatarEl) {
            var initials = (user.name || '?').split(' ').map(function(p){return p[0]||'';}).join('').toUpperCase().slice(0,2);
            avatarEl.textContent = initials;
        }
    }

    // Hide admin-only navigation items for non-admin users
    var isAdmin = !!(user && user.role === 'admin');
    document.querySelectorAll('[data-admin-only]').forEach(function (el) {
        if (!isAdmin) el.style.display = 'none';
    });

    // Highlight active nav item
    var path = window.location.pathname;
    var activeNav;
    if (path === '/' || path.indexOf('/dashboard') !== -1) {
        activeNav = 'dashboard';
    } else if (path.indexOf('/admin/users') !== -1) {
        activeNav = 'users';
    } else if (path.indexOf('/admin/web-services') !== -1) {
        activeNav = 'web-services';
    } else {
        activeNav = null;
    }
    document.querySelectorAll('[data-nav]').forEach(function (el) {
        if (el.dataset.nav === activeNav) el.setAttribute('data-active', 'true');
    });
    document.querySelectorAll('[data-mobile-nav]').forEach(function (el) {
        if (el.dataset.mobileNav === activeNav) el.setAttribute('data-active', 'true');
    });

    // Logout
    var logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function () {
            var token = localStorage.getItem('anapec_token');
            if (token) {
                try {
                    await fetch('/api/v1/auth/logout', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                    });
                } catch (e) { /* ignore network errors */ }
            }
            localStorage.removeItem('anapec_token');
            localStorage.removeItem('anapec_user');
            window.location.href = '/login';
        });
    }

    // ── Mobile sidebar ──
    var toggle = document.getElementById('sidebar-toggle');
    var mobileSidebar = document.getElementById('mobile-sidebar');
    var mobileBackdrop = document.getElementById('mobile-backdrop');
    var mobileCloseBtn = document.getElementById('mobile-sidebar-close');

    var isOpen = false;

    function openMobileSidebar() {
        if (isOpen || !mobileSidebar) return;
        isOpen = true;
        mobileSidebar.classList.remove('hidden');
        mobileBackdrop.classList.remove('hidden');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        if (!isOpen || !mobileSidebar) return;
        isOpen = false;
        mobileSidebar.classList.add('hidden');
        mobileBackdrop.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            if (isOpen) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        });
    }

    if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', closeMobileSidebar);
    }

    if (mobileBackdrop) {
        mobileBackdrop.addEventListener('click', closeMobileSidebar);
    }

    // Close on nav link click
    if (mobileSidebar) {
        var navLinks = mobileSidebar.querySelectorAll('a[data-mobile-nav]');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (link.href === '#') {
                    e.preventDefault();
                }
                closeMobileSidebar();
            });
        });
    }

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            closeMobileSidebar();
            toggle.focus();
        }
    });
})();
</script>

</body>
</html>
