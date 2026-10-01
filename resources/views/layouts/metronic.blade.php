<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'ANAPEC - Admin')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-16x16.png') }}" />
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/android-icon-192x192.png') }}" />
    <link rel="apple-icon" sizes="57x57" href="{{ asset('images/apple-icon-57x57.png') }}" />
    <link rel="apple-icon" sizes="114x114" href="{{ asset('images/apple-icon-114x114.png') }}" />
    <link href="{{ asset('metronic/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('metronic/assets/plugins/global/fonts/keenicons/keenicons.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('metronic/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    @stack('stylesheets')
    @stack('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @endif
    {{--
        Dark navy sidebar styling. Metronic's default collapsed sidebar hides the
        menu icons, so these custom rules guarantee the icons and logo stay
        visible and centered when the sidebar is minimised.
    --}}
    <style>
        /* Dark navy sidebar - high specificity to override Metronic defaults */
        #kt_app_sidebar.app-sidebar {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }

        #kt_app_sidebar .app-sidebar-logo {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 16px 24px !important;
        }

        #kt_app_sidebar .app-sidebar-logo a {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
        }

        #kt_app_sidebar .app-sidebar-logo img {
            height: 32px !important;
        }

        /* Centered logo when sidebar is collapsed */
        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .app-sidebar-logo {
            padding: 16px 0 !important;
            justify-content: center !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .app-sidebar-logo a {
            justify-content: center !important;
            gap: 0 !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .app-sidebar-logo img {
            height: 28px !important;
            max-width: 50px !important;
            width: auto !important;
        }

        #kt_app_sidebar .sidebar-brand-text {
            display: flex !important;
            flex-direction: column !important;
        }

        #kt_app_sidebar .sidebar-brand-title {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            line-height: 1.2 !important;
        }

        #kt_app_sidebar .sidebar-brand-subtitle {
            font-size: 11px !important;
            font-weight: 500 !important;
            color: #94a3b8 !important;
            line-height: 1.2 !important;
        }

        /* Menu items - ensure readable text on dark background */
        #kt_app_sidebar .menu .menu-item .menu-link {
            color: #e2e8f0 !important;
            padding: 10px 16px !important;
            margin: 2px 8px !important;
            border-radius: 8px !important;
            transition: all 0.2s ease !important;
            text-decoration: none !important;
        }

        #kt_app_sidebar .menu .menu-item .menu-link:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }

        #kt_app_sidebar .menu .menu-item .menu-link.active {
            background-color: #3b82f6 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3) !important;
        }

        #kt_app_sidebar .menu .menu-item .menu-link .menu-icon {
            margin-right: 12px !important;
            color: #cbd5e1 !important;
        }

        #kt_app_sidebar .menu .menu-item .menu-link:hover .menu-icon {
            color: #ffffff !important;
        }

        #kt_app_sidebar .menu .menu-item .menu-link.active .menu-icon {
            color: #ffffff !important;
        }

        #kt_app_sidebar .menu .menu-item .menu-link .menu-icon i {
            font-size: 18px !important;
        }

        #kt_app_sidebar .menu .menu-item .menu-link .menu-title {
            font-size: 14px !important;
            font-weight: 500 !important;
            color: inherit !important;
        }

        #kt_app_sidebar .app-sidebar-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8 !important;
        }

        /* Circular collapse button */
        #kt_app_sidebar_toggle {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15) !important;
            color: #1e293b !important;
            width: 32px !important;
            height: 32px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 0 !important;
            text-align: center !important;
            overflow: visible !important;
            position: absolute !important;
            top: 50% !important;
            right: -16px !important;
            transform: translateY(-50%) rotate(0deg) !important;
            transition: all 0.3s ease !important;
            z-index: 100 !important;
        }

        #kt_app_sidebar_toggle:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }

        #kt_app_sidebar_toggle>svg {
            width: 19px !important;
            height: 19px !important;
            min-width: 19px !important;
            min-height: 19px !important;
            flex: 0 0 auto !important;
            margin: 0 !important;
            padding: 0 !important;
            display: block !important;
            fill: none !important;
            stroke: #1e293b !important;
            stroke-width: 2.5 !important;
            stroke-linecap: round !important;
            stroke-linejoin: round !important;
            opacity: 1 !important;
            color: #1e293b !important;
            filter: none !important;
            transition: stroke 0.2s ease !important;
        }

        #kt_app_sidebar_toggle:hover>svg {
            stroke: #0f172a !important;
            color: #0f172a !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar_toggle {
            transform: translateY(-50%) rotate(180deg) !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar {
            width: 75px !important;
        }

        /* Hide desktop toggle on mobile (mobile has its own toggle in header) */
        @media (max-width: 991.98px) {
            #kt_app_sidebar_toggle {
                display: none !important;
            }
        }

        /* Show logo in mobile drawer (Metronic hides it by default) */
        @media (max-width: 991.98px) {
            #kt_app_sidebar .app-sidebar-logo {
                display: flex !important;
            }
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .sidebar-brand-text {
            display: none !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-title {
            display: none !important;
        }

        /*
         * Collapsed sidebar: keep ONLY the icons, centered in the 75px strip.
         * Root cause: Metronic keeps .app-sidebar-wrapper at 265px even when the
         * sidebar is 75px (hoverable layout), so a centered flex link put the icon
         * at x~119 while .app-sidebar-menu clips at 74px -> icons were invisible.
         * Fix: constrain each item/link to a 51px box centered in the 75px strip
         * (12px gutter left/right), so justify-content:center is centered on the
         * visible sidebar instead of on the hidden 265px wrapper.
         */
        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-item {
            width: 51px !important;
            min-width: 51px !important;
            max-width: 51px !important;
            margin: 2px 0 2px 4px !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-item .menu-link {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 100% !important;
            min-width: 100% !important;
            margin: 0 !important;
            padding: 12px 0 !important;
            border-radius: 8px !important;
            overflow: visible !important;
            box-sizing: border-box !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-item .menu-link .menu-icon {
            display: inline-flex !important;
            justify-content: center !important;
            align-items: center !important;
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-item .menu-link .menu-icon i {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            font-size: 18px !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-item .menu-link:hover .menu-icon i {
            color: #ffffff !important;
        }

        [data-kt-app-sidebar-minimize="on"] #kt_app_sidebar .menu .menu-item .menu-link.active .menu-icon i {
            color: #ffffff !important;
        }
    </style>
</head>
@php
    // Route-based active nav detection: highlight the sidebar item whose
    // prefix matches the current path. Each nav item maps to a route name.
    $navItems = [
        'dashboard' => [
            'route' => 'dashboard',
            'path' => '/dashboard',
            'label' => 'Tableau de bord',
            'icon' => 'ki-home',
        ],
        'users' => [
            'route' => 'admin.users',
            'path' => '/admin/users',
            'label' => 'Utilisateurs',
            'icon' => 'ki-users',
        ],
        'web-services' => [
            'route' => 'admin.web-services',
            'path' => '/admin/web-services',
            'label' => 'Web Services',
            'icon' => 'ki-element-11',
        ],
        'domains' => [
            'route' => 'admin.web-service-domains',
            'path' => '/admin/web-service-domains',
            'label' => 'Domaines',
            'icon' => 'ki-folder',
        ],
    ];
    // Request paths never carry a leading slash, so normalise both sides.
    $currentPath = ltrim(request()->path(), '/');
    $activeNav = '';
    foreach ($navItems as $key => $item) {
        $navPath = ltrim($item['path'], '/');
        if ($currentPath === $navPath || str_starts_with($currentPath, $navPath)) {
            $activeNav = $key;
            break;
        }
    }
@endphp

<body id="kt_app_body" data-kt-app-layout="light-sidebar" data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">

    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!--begin::Header-->
            <div id="kt_app_header" class="app-header" data-kt-sticky="true"
                data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize"
                data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
                <!--begin::Header container-->
                <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
                    id="kt_app_header_container">
                    <!--begin::Sidebar mobile toggle-->
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px"
                            id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-outline ki-abstract-14 fs-2 fs-md-1"></i>
                        </div>
                    </div>
                    <!--end::Sidebar mobile toggle-->
                    <!--begin::Mobile logo-->
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="{{ route('dashboard') }}" class="d-lg-none">
                            <img alt="Logo" src="{{ asset('images/logo.png') }}" class="h-30px" />
                        </a>
                    </div>
                    <!--end::Mobile logo-->
                    <!--begin::Header wrapper-->
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1"
                        id="kt_app_header_wrapper">
                        <!--begin::Menu wrapper-->
                        <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true"
                            data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}"
                            data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end"
                            data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true"
                            data-kt-swapper-mode="{default: 'append', lg: 'prepend'}"
                            data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                            <!--begin::Menu-->
                            <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0"
                                id="kt_app_header_menu" data-kt-menu="true">
                                <!--begin:Menu item-->
                                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                    data-kt-menu-placement="bottom-start"
                                    class="menu-item menu-here-bg menu-lg-down-accordion me-0 me-lg-2">
                                    <!--begin:Menu link-->
                                    <span class="menu-link">
                                        <span class="menu-title">@yield('title', 'Tableau de bord')</span>
                                        <span class="menu-arrow d-lg-none"></span>
                                    </span>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                            </div>
                            <!--end::Menu-->
                        </div>
                        <!--end::Menu wrapper-->
                        <!--begin::Actions-->
                        <div class="d-flex align-items-stretch justify-content-end flex-shrink-0">
                            <!--begin::User dropdown-->
                            <div class="d-flex align-items-stretch">
                                <div class="d-flex align-items-stretch">
                                    <div class="dropdown dropdown-lg-end">
                                        <button class="btn btn-icon btn-active-light-primary w-35px h-35px"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <img id="header-avatar" class="rounded-circle symbol-50 symbol-50b"
                                                src="{{ asset('metronic/assets/media/avatars/300-11.jpg') }}"
                                                alt="avatar" />
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end py-2 fw-normal w-250px">
                                            <div class="d-flex align-items-center mb-3 px-3 pb-2">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="symbol symbol-45px">
                                                        <img id="user-menu-avatar"
                                                            src="{{ asset('metronic/assets/media/avatars/300-11.jpg') }}"
                                                            alt="avatar" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 flex-shrink-0">
                                                    <span id="user-menu-name"
                                                        class="fw-bold text-dark fs-6 d-block">Utilisateur</span>
                                                    <span id="user-menu-role" class="text-muted fs-7">Role</span>
                                                </div>
                                            </div>
                                            <div class="border-t border-gray-200 border-dashed">
                                                <a href="{{ route('dashboard') }}"
                                                    class="d-flex align-items-center w-100 px-3 py-2 text-decoration-none text-dark hover-bg-light hover-color-dark rounded">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" class="me-3"
                                                        style="width: 18px; height: 18px; flex-shrink: 0;">
                                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="12" cy="7" r="4"></circle>
                                                    </svg>
                                                    <span class="fw-semibold">Mon compte</span>
                                                </a>
                                                <button id="logout-btn"
                                                    class="d-flex align-items-center w-100 px-3 py-2 border-0 bg-transparent text-left text-dark hover-bg-light hover-color-dark rounded">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round" class="me-3"
                                                        style="width: 18px; height: 18px; flex-shrink: 0;">
                                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                                        <polyline points="16 17 21 12 16 7"></polyline>
                                                        <line x1="21" y1="12" x2="9"
                                                            y2="12"></line>
                                                    </svg>
                                                    <span class="fw-semibold">Déconnexion</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::User dropdown-->
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Header wrapper-->
                </div>
                <!--end::Header container-->
            </div>
            <!--end::Header-->
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <!--begin::Sidebar-->
                <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true"
                    data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}"
                    data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start"
                    data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
                    <!--begin::Logo-->
                    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
                        <!--begin::Logo and text-->
                        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-3">
                            <img alt="Logo" src="{{ asset('images/logo.png') }}"
                                class="h-8 app-sidebar-logo-default" />
                            <img alt="Logo" src="{{ asset('images/logo.png') }}"
                                class="h-8 app-sidebar-logo-minimize" />
                            <div class="sidebar-brand-text">
                                <span class="sidebar-brand-title">ANAPEC API</span>
                                <span class="sidebar-brand-subtitle">Console d'administration</span>
                            </div>
                        </a>
                        <!--end::Logo and text-->
                        <!--begin::Sidebar toggle-->
                        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon" data-kt-toggle="true"
                            data-kt-toggle-state="active" data-kt-toggle-target="body"
                            data-kt-toggle-name="app-sidebar-minimize">
                            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19"
                                viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round" opacity="1" aria-hidden="true"
                                focusable="false">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                        </div>
                        <!--end::Sidebar toggle-->
                    </div>
                    <!--end::Logo-->
                    <!--begin::sidebar menu-->
                    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
                        <!--begin::Menu wrapper-->
                        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
                            <!--begin::Scroll wrapper-->
                            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-3 mx-2" data-kt-scroll="true"
                                data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                                data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                                data-kt-scroll-save-state="true">
                                <!--begin::Menu-->
                                <div class="menu menu-column fw-semibold fs-6" id="kt_app_sidebar_menu"
                                    data-kt-menu="true" data-kt-menu-expand="false">
                                    @foreach ($navItems as $key => $item)
                                        <div class="menu-item">
                                            <a class="menu-link {{ $activeNav === $key ? 'active' : '' }}"
                                                href="{{ route($item['route']) }}">
                                                <span class="menu-icon">
                                                    <i class="ki-outline {{ $item['icon'] }} fs-2"></i>
                                                </span>
                                                <span class="menu-title">{{ $item['label'] }}</span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Scroll wrapper-->
                        </div>
                        <!--end::Menu wrapper-->
                    </div>
                    <!--end::sidebar menu-->
                    <!--begin::Footer-->
                    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
                        <div class="text-center">
                            <p class="text-white text-sm fw-bold mb-0 opacity-75">ANAPEC API Portal</p>
                            <p class="text-white text-xs mt-1 opacity-50">v1.0.0</p>
                        </div>
                    </div>
                    <!--end::Footer-->
                </div>
                <!--end::Sidebar-->
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">
                        <!--begin::Content-->
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <!--begin::Content container-->
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @yield('content')
                            </div>
                            <!--end::Content container-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Content wrapper-->
                    <!--begin::Footer-->
                    <div id="kt_app_footer" class="app-footer">
                        <!--begin::Footer container-->
                        <div
                            class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                            <!--begin::Copyright-->
                            <div class="text-gray-900 order-2 order-md-1">
                                <span class="text-muted fw-semibold me-1">{{ date('Y') }}&copy;</span>
                                <a href="#" class="text-gray-800 text-hover-primary">ANAPEC</a>
                            </div>
                            <!--end::Copyright-->
                            <!--begin::Menu-->
                            <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
                                <li class="menu-item">
                                    <a href="#" class="menu-link px-2">Agence Nationale Pour l'Emploi</a>
                                </li>
                            </ul>
                            <!--end::Menu-->
                        </div>
                        <!--end::Footer container-->
                    </div>
                    <!--end::Footer-->
                </div>
                <!--end::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->
    <!--begin::Scroll-to-top button-->
    <div id="kt_scroll_top" class="scrolltop btn btn-icon btn-dark btn-active-light-primary btn-sm shadow-none"
        data-kt-scrolltop="true">
        <span class="svg-icon svg-icon-2x">
            <span class="ki-outline ki-arrow-up"></span>
        </span>
    </div>
    <!--end::Scroll-to-top button-->
    <!--begin::Javascript-->
    <script src="{{ asset('metronic/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('metronic/assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('metronic/assets/js/widgets.bundle.js') }}"></script>
    @stack('scripts')
    <script>
        (function() {
            var user = null;
            try {
                user = JSON.parse(localStorage.getItem('anapec_user'));
            } catch (e) {}
            var token = localStorage.getItem('anapec_token');
            var nameEl = document.getElementById('user-menu-name');
            var roleEl = document.getElementById('user-menu-role');
            var avatarEl = document.getElementById('header-avatar');
            var menuAvatarEl = document.getElementById('user-menu-avatar');
            if (user) {
                if (nameEl) nameEl.textContent = user.name || 'Utilisateur';
                if (roleEl) roleEl.textContent = user.role === 'admin' ? 'Administrateur' : (user.role ||
                'Utilisateur');
                var avatars = ['300-11.jpg', '300-12.jpg', '300-15.jpg', '300-16.jpg', '300-2.jpg', '300-3.jpg'];
                var hash = 0;
                for (var i = 0; i < (user.name || '').length; i++) hash = ((hash << 5) - hash) + (user.name.charCodeAt(
                    i));
                hash = Math.abs(hash);
                var avatarSrc = '/metronic/assets/media/avatars/' + avatars[hash % avatars.length];
                if (avatarEl) avatarEl.src = avatarSrc;
                if (menuAvatarEl) menuAvatarEl.src = avatarSrc;
            }
            var logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async function() {
                    var t = localStorage.getItem('anapec_token');
                    if (t) {
                        try {
                            await fetch('/api/v1/auth/logout', {
                                method: 'POST',
                                headers: {
                                    'Authorization': 'Bearer ' + t,
                                    'Accept': 'application/json'
                                }
                            });
                        } catch (e) {}
                    }
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                    window.location.href = '/login';
                });
            }
        })();
    </script>
    <!--end::Javascript-->
</body>

</html>
