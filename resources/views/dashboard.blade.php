@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex items-center justify-between">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Dashboard</h2>
        <p class="mt-0.5 text-sm text-gray-500">
            Vue d'ensemble de votre espace de gestion des API ANAPEC.
        </p>
    </div>
</div>

{{-- ── WELCOME CARD ── --}}
<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex items-center gap-4">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-anapec-100 text-anapec-600">
            <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M8 4a.562.562 0 0 1 .128.034C9.958 4.82 12 6.359 12 8.625c0 1.768-.656 2.919-1.488 3.77-.413.421-.862.765-1.339 1.033l-.122.072a.5.5 0 0 1-.493 0l-.121-.072c-.477-.268-.926-.612-1.34-1.033C6.657 11.544 6 10.393 6 8.625c0-2.266 2.042-3.805 3.872-4.591A.562.562 0 0 1 8 4Zm4.444.294c-.534-.079-1.099-.11-1.444-.11a6.44 6.44 0 0 0-1.444.11C9.11 4.337 8.484 4.147 8 4v-.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v.5c-.484.147-1.11.337-1.556.294ZM15 8.625c0-2.266 2.042-3.805 3.872-4.591a.562.562 0 0 1 .128.034C19.958 4.82 22 6.359 22 8.625c0 1.768-.656 2.919-1.488 3.77a7.14 7.14 0 0 1-1.339 1.033l-.122.072a.5.5 0 0 1-.493 0l-.121-.072a7.14 7.14 0 0 1-1.34-1.033C15.657 11.544 15 10.393 15 8.625Z"/>
            </svg>
        </div>
        <div>
            <p class="text-base font-semibold text-gray-900">
                Bonjour, <span id="welcome-name" class="text-anapec-700">…</span>
            </p>
            <p class="mt-0.5 text-sm text-gray-500">
                Bienvenue sur le portail de gestion des API ANAPEC.
            </p>
        </div>
    </div>
</div>

{{-- ── SUMMARY CARDS ── --}}
<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    {{-- Utilisateurs (admin only) --}}
    <div id="card-users" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" data-visibility="admin">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500">Utilisateurs</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-anapec-50 text-anapec-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM4 10a6 6 0 1 1 12 0"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4 14a1 1 0 0 1 1-1h10a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Zm0 4a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Z"/>
                </svg>
            </span>
        </div>
        <p id="val-users" class="mt-3 text-3xl font-bold text-gray-900" data-placeholder="--">--</p>
    </div>

    {{-- Web Services (admin only) --}}
    <div id="card-services" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" data-visibility="admin">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500">Web Services</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-anapec-50 text-anapec-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M5 3.25a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75V5.5a.75.75 0 0 1-.75.75h-8.5A.75.75 0 0 1 5 5.5V3.25Zm0 4.5a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75v5.5a.75.75 0 0 1-.75.75H5.75a.75.75 0 0 1-.75-.75V7.75Z"/>
                </svg>
            </span>
        </div>
        <p id="val-services" class="mt-3 text-3xl font-bold text-gray-900" data-placeholder="--">--</p>
    </div>

    {{-- Services actifs (admin only) --}}
    <div id="card-active" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm" data-visibility="admin">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500">Services actifs</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-green-50 text-green-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M10 1.944A.75.75 0 0 1 10.75 2.7v.554a.75.75 0 0 1-1.5 0V2.7a.75.75 0 0 1 .75-.756ZM2.25 9.75a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Zm14.5-.75h-2.5a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5ZM2.25 13.75a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Zm14.5-.75h-2.5a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5ZM10 10.75a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V11.5a.75.75 0 0 1 .75-.75Z"/>
                </svg>
            </span>
        </div>
        <p id="val-active" class="mt-3 text-3xl font-bold text-green-600" data-placeholder="--">--</p>
    </div>

    {{-- Mes permissions --}}
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500">Mes permissions</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-anapec-50 text-anapec-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M2.875 5A1.75 1.75 0 0 0 4.625 3.25V3a1.75 1.75 0 0 0-1.75-1.75h1.5a.75.75 0 0 1 0 1.5H5v1.75c0 .621.504 1.125 1.125 1.125H14.5a.75.75 0 0 1 0 1.5h-.375A1.75 1.75 0 0 0 12.375 13h-1.5a.75.75 0 0 1 0-1.5h1.5a.25.25 0 0 0 .25-.25V9.75a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0 0 1.5h1.5c0-.16-.203-.25-.25-.25h-1.25a.75.75 0 0 0 0 1.5h1.25c.16 0 .25.203.25.25v1.5c0 .621-.504 1.125-1.125 1.125H5.75A1.75 1.75 0 0 1 4 13h-1.5a.75.75 0 0 1 0-1.5h1.5c.16 0 .25-.203.25-.25V11.5c0-.621-.504-1.125-1.125-1.125H4v.75a.75.75 0 0 1-.75.75H2.875Z"/>
                </svg>
            </span>
        </div>
        <p id="val-perms" class="mt-3 text-3xl font-bold text-gray-900" data-placeholder="--">--</p>
    </div>
</div>

{{-- ── ACCOUNT INFO ── --}}
<div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-gray-900">Informations de votre compte</h3>
    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <dt class="text-xs font-medium text-gray-500">Nom</dt>
            <dd id="acct-name" class="mt-1 text-sm font-medium text-gray-900">--</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Email</dt>
            <dd id="acct-email" class="mt-1 text-sm font-medium text-gray-900">--</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Rôle</dt>
            <dd id="acct-role" class="mt-1"></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Statut</dt>
            <dd id="acct-status" class="mt-1"></dd>
        </div>
    </dl>
</div>

{{-- ── API ERROR BANNER ── --}}
<div id="dashboard-error" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
     role="alert"></div>

<script>
(function () {
    var token = localStorage.getItem('anapec_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    var api = {
        get: function (url) {
            return fetch(url, {
                method: 'GET',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
        }
    };

    function setError(msg) {
        var el = document.getElementById('dashboard-error');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function roleBadge(role) {
        if (role === 'admin') {
            return '<span class="inline-flex items-center rounded-full bg-anapec-100 px-2.5 py-0.5 text-xs font-medium text-anapec-700">Administrateur</span>';
        }
        return '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Utilisateur</span>';
    }

    function statusBadge(active) {
        if (active) {
            return '<span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Actif</span>';
        }
        return '<span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">Inactif</span>';
    }

    function showSkeleton(id) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = '';
            el.innerHTML = '<span class="inline-block h-7 w-14 animate-pulse rounded bg-gray-200"></span>';
        }
    }

    function hideSkeleton(id, value) {
        var el = document.getElementById(id);
        if (!el) return;
        el.textContent = value;
        el.className = el.className.replace(/animate-pulse|rounded|bg-gray-200|inline-block|h-7|w-14|hidden/g, '').trim();
        if (id === 'val-active') el.className = 'mt-3 text-3xl font-bold text-green-600';
        else el.className = 'mt-3 text-3xl font-bold text-gray-900';
    }

    // ── Load user profile ──
    api.get('/api/v1/auth/me')
        .then(function (res) {
            if (res.status === 401) {
                localStorage.removeItem('anapec_token');
                localStorage.removeItem('anapec_user');
                window.location.href = '/login';
                return;
            }
            if (!res.ok) return res.json().then(function (d) { setError(d.message || 'Erreur inattendue.'); });
            return res.json().then(function (data) {
                if (!data.success) return;
                var user = data.data;

                // Refresh localStorage with the latest user profile
                localStorage.setItem('anapec_user', JSON.stringify(user));

                // Welcome
                document.getElementById('welcome-name').textContent = user.name || '';

                // Account info
                document.getElementById('acct-name').textContent = user.name || '--';
                document.getElementById('acct-email').textContent = user.email || '--';
                document.getElementById('acct-role').innerHTML = roleBadge(user.role);
                document.getElementById('acct-status').innerHTML = statusBadge(user.is_active);

                if (user.role === 'admin') {
                    loadAdminStats(user.id);
                } else {
                    // Regular user: hide admin-only cards
                    var adminCards = document.querySelectorAll('[data-visibility="admin"]');
                    adminCards.forEach(function (c) { c.style.display = 'none'; });
                    // No user-facing permissions endpoint exists yet → show "--"
                    document.getElementById('val-perms').textContent = '--';
                }
            });
        })
        .catch(function () {
            setError('Impossible de charger les données. Veuillez réessayer.');
        });

    // ── Admin stats ──
    function loadAdminStats(userId) {
        showSkeleton('val-users');
        showSkeleton('val-services');
        showSkeleton('val-active');
        showSkeleton('val-perms');

        // Users count
        api.get('/api/v1/admin/users')
            .then(function (res) {
                if (res.status === 403) { hideSkeleton('val-users', '--'); return; }
                if (!res.ok) { hideSkeleton('val-users', '--'); return; }
                return res.json().then(function (data) {
                    if (data.success && Array.isArray(data.data)) {
                        hideSkeleton('val-users', String(data.data.length));
                    } else {
                        hideSkeleton('val-users', '--');
                    }
                });
            })
            .catch(function () { hideSkeleton('val-users', '--'); });

        // Web Services
        api.get('/api/v1/admin/web-services')
            .then(function (res) {
                if (res.status === 403) {
                    hideSkeleton('val-services', '--');
                    hideSkeleton('val-active', '--');
                    return;
                }
                if (!res.ok) {
                    hideSkeleton('val-services', '--');
                    hideSkeleton('val-active', '--');
                    return;
                }
                return res.json().then(function (data) {
                    if (data.success && Array.isArray(data.data)) {
                        var total = data.data.length;
                        var active = data.data.filter(function (w) { return w.is_active === true; }).length;
                        hideSkeleton('val-services', String(total));
                        hideSkeleton('val-active', String(active));
                    } else {
                        hideSkeleton('val-services', '--');
                        hideSkeleton('val-active', '--');
                    }
                });
            })
            .catch(function () {
                hideSkeleton('val-services', '--');
                hideSkeleton('val-active', '--');
            });

        // My permissions (admin only)
        if (userId) {
            api.get('/api/v1/admin/users/' + userId + '/web-services')
                .then(function (res) {
                    if (res.status === 403 || res.status === 404) {
                        hideSkeleton('val-perms', '--');
                        return;
                    }
                    if (!res.ok) { hideSkeleton('val-perms', '--'); return; }
                    return res.json().then(function (data) {
                        if (data.success && Array.isArray(data.data)) {
                            // Count only effective permissions: globally active AND enabled
                            var effective = data.data.filter(function (p) {
                                return p.effective_access === true;
                            }).length;
                            hideSkeleton('val-perms', String(effective));
                        } else {
                            hideSkeleton('val-perms', '--');
                        }
                    });
                })
                .catch(function () { hideSkeleton('val-perms', '--'); });
        } else {
            hideSkeleton('val-perms', '--');
        }
    }
})();
</script>
@endsection
