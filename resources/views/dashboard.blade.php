@extends('layouts.metronic')

@section('title', 'Tableau de bord')

@section('content')

{{-- ── WELCOME CARD ── --}}
<div class="card card-flush bg-gradient bg-gradient-info-100 mb-6 mb-xl-8">
    <div class="card-body p-6 p-lg-8">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-4">
            <div class="d-flex align-items-center gap-4 flex-shrink-0">
                <div class="symbol symbol-60px bg-light-info bg-opacity-25 flex-shrink-0">
                    <span class="svg-icon svg-icon-2x text-primary">
                        <span class="ki ki-shield-check"></span>
                    </span>
                </div>
                <div>
                    <p class="text-dark fw-bolder fs-4 mb-0">
                        Bonjour, <span id="welcome-name" class="text-info-700">…</span>
                    </p>
                    <p class="text-gray-600 fs-6">
                        Bienvenue sur le portail d'administration des API ANAPEC.
                    </p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('admin.web-services') }}" class="btn btn-color-mine btn-active btn-primary fw-semibold">
                    <span class="svg-icon svg-icon-2">
                        <span class="ki ki-plug"></span>
                    </span>
                    <span class="btn-text ms-2">Web Services</span>
                </a>
                <a href="{{ url('api/documentation') }}" target="_blank" class="btn btn-color-mine btn-active fw-semibold">
                    <span class="svg-icon svg-icon-2">
                        <span class="ki ki-book-open"></span>
                    </span>
                    <span class="btn-text ms-2">Documentation</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── KPI CARDS ── --}}
<div class="row g-4 g-xl-6 mb-6 mb-xl-8">

    {{-- Utilisateurs --}}
    <div id="card-users" class="col-xl-3 col-md-6 col-sm-6" data-visibility="admin">
        <a href="{{ route('admin.users') }}" class="text-decoration-none">
            <div class="card card-flush h-100">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between flex-wrap">
                        <span class="text-gray-600 fw-bold fs-6">Utilisateurs</span>
                        <span class="symbol symbol-50px bg-light-primary bg-opacity-100">
                            <span class="svg-icon svg-icon-2 text-primary">
                                <span class="ki ki-users"></span>
                            </span>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap mt-3">
                        <h3 class="fs-2x fw-bolder text-dark me-2 mb-0" id="val-users" data-placeholder="--">--</h3>
                    </div>
                    <p class="text-gray-600 fw-bold fs-7 mb-0 mt-1">Total comptes utilisateurs</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Web Services --}}
    <div id="card-services" class="col-xl-3 col-md-6 col-sm-6" data-visibility="admin">
        <a href="{{ route('admin.web-services') }}" class="text-decoration-none">
            <div class="card card-flush h-100">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between flex-wrap">
                        <span class="text-gray-600 fw-bold fs-6">Web Services</span>
                        <span class="symbol symbol-50px bg-light-info bg-opacity-100">
                            <span class="svg-icon svg-icon-2 text-info">
                                <span class="ki ki-plug"></span>
                            </span>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap mt-3">
                        <h3 class="fs-2x fw-bolder text-dark me-2 mb-0" id="val-services" data-placeholder="--">--</h3>
                    </div>
                    <p class="text-gray-600 fw-bold fs-7 mb-0 mt-1">Services publiés</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Domaines --}}
    <div id="card-domains" class="col-xl-3 col-md-6 col-sm-6" data-visibility="admin">
        <a href="{{ route('admin.web-service-domains') }}" class="text-decoration-none">
            <div class="card card-flush h-100">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between flex-wrap">
                        <span class="text-gray-600 fw-bold fs-6">Domaines</span>
                        <span class="symbol symbol-50px bg-light-success bg-opacity-100">
                            <span class="svg-icon svg-icon-2 text-success">
                                <span class="ki ki-server-network"></span>
                            </span>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap mt-3">
                        <h3 class="fs-2x fw-bolder text-dark me-2 mb-0" id="val-domains" data-placeholder="--">--</h3>
                    </div>
                    <p class="text-gray-600 fw-bold fs-7 mb-0 mt-1">Domaines d'accès</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Opérations --}}
    <div id="card-ops" class="col-xl-3 col-md-6 col-sm-6" data-visibility="admin">
        <div class="card card-flush h-100">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between flex-wrap">
                    <span class="text-gray-600 fw-bold fs-6">Opérations</span>
                    <span class="symbol symbol-50px bg-light-warning bg-opacity-100">
                        <span class="svg-icon svg-icon-2 text-warning">
                            <span class="ki ki-chart-line"></span>
                        </span>
                    </span>
                </div>
                <div class="d-flex flex-wrap mt-3">
                    <h3 class="fs-2x fw-bolder text-dark me-2 mb-0" id="val-ops" data-placeholder="--">--</h3>
                </div>
                <p class="text-gray-600 fw-bold fs-7 mb-0 mt-1">Activité API récente</p>
            </div>
        </div>
    </div>

</div>

{{-- ── STATUS CARDS ── --}}
<div class="row g-4 g-xl-6 mb-6 mb-xl-8" data-visibility="admin">

    {{-- Services actifs / désactivés --}}
    <div id="card-status-services" class="col-xl-6 col-md-12">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h3 class="fw-bold text-dark">État des Web Services</h3>
                <div class="card-toolbar">
                    <a href="{{ route('admin.web-services') }}" class="btn btn-sm btn-color-mine btn-active btn-light-primary fw-semibold">
                        Gérer
                    </a>
                </div>
            </div>
            <div class="card-body py-4 px-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bullet bullet-dot bg-success me-1"></span>
                        <span class="text-gray-600 fw-bold fs-7">Actifs</span>
                    </div>
                    <span class="fw-bolder text-success fs-5" id="stat-svc-active">--</span>
                </div>
                <div class="progress progress-sm w-100 mb-4">
                    <div id="bar-svc-active" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-label="Actifs"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bullet bullet-dot bg-secondary me-1"></span>
                        <span class="text-gray-600 fw-bold fs-7">Désactivés</span>
                    </div>
                    <span class="fw-bolder text-secondary fs-5" id="stat-svc-inactive">--</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Utilisateurs actifs / désactivés --}}
    <div id="card-status-users" class="col-xl-6 col-md-12">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h3 class="fw-bold text-dark">État des Utilisateurs</h3>
                <div class="card-toolbar">
                    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-color-mine btn-active btn-light-primary fw-semibold">
                        Gérer
                    </a>
                </div>
            </div>
            <div class="card-body py-4 px-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bullet bullet-dot bg-success me-1"></span>
                        <span class="text-gray-600 fw-bold fs-7">Actifs</span>
                    </div>
                    <span class="fw-bolder text-success fs-5" id="stat-user-active">--</span>
                </div>
                <div class="progress progress-sm w-100 mb-4">
                    <div id="bar-user-active" class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-label="Actifs"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bullet bullet-dot bg-secondary me-1"></span>
                        <span class="text-gray-600 fw-bold fs-7">Désactivés</span>
                    </div>
                    <span class="fw-bolder text-secondary fs-5" id="stat-user-inactive">--</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── CHART + MON COMPTE ── --}}
<div class="row g-4 g-xl-6 mb-6 mb-xl-8">

    {{-- Chart card --}}
    <div class="col-xl-8 col-md-12">
        <div class="card card-flush h-100">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold text-dark">Répartition des Web Services</h3>
                    <span class="text-muted fw-semibold fs-7">État des services</span>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-light-primary fw-semibold">
                            <span class="bullet bullet-dot bg-primary me-1"></span> Actif
                        </span>
                        <span class="badge badge-light-secondary fw-semibold">
                            <span class="bullet bullet-dot bg-secondary me-1"></span> Désactivé
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body py-4">
                <div style="position:relative;height:260px">
                    <canvas id="services-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Mon compte card --}}
    <div class="col-xl-4 col-md-12">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h3 class="fw-bold text-dark">Mon compte</h3>
            </div>
            <div class="card-body p-5">
                <div class="d-flex flex-column align-items-center text-center mb-4">
                    <div id="account-avatar" class="symbol symbol-50px bg-light-primary bg-opacity-100 mb-3">
                        <span id="account-initials" class="symbol-label fs-2 fw-bolder text-primary"></span>
                    </div>
                    <h3 id="acct-name" class="fw-bolder text-dark fs-4">--</h3>
                    <p id="acct-email" class="text-gray-600 fs-7">--</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                        <span id="acct-role" class="badge badge-light-primary fw-semibold"></span>
                        <span id="acct-status" class="badge badge-light-success fw-semibold"></span>
                    </div>
                </div>
                <div class="border-top border-dashed pt-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="svg-icon svg-icon-2 text-gray-500">
                                <span class="ki ki-clock"></span>
                            </span>
                            <span class="text-gray-600 fw-bold fs-7">Session</span>
                        </div>
                        <span class="text-success fw-bold fs-7">Actif</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="svg-icon svg-icon-2 text-gray-500">
                                <span class="ki ki-shield"></span>
                            </span>
                            <span class="text-gray-600 fw-bold fs-7">Authentification</span>
                        </div>
                        <span class="text-success fw-bold fs-7">Authentifié</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── ACTIVITÉ RÉCENTE + ACTIONS RAPIDES ── --}}
<div class="row g-4 g-xl-6">

    {{-- Activité récente --}}
    <div class="col-xl-5 col-md-12">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h3 class="fw-bold text-dark">Activité récente</h3>
                <div class="card-toolbar">
                    <span class="badge badge-light-secondary fw-semibold">Bientôt disponible</span>
                </div>
            </div>
            <div class="card-body py-6">
                <div class="d-flex flex-column align-items-center text-center">
                    <span class="symbol symbol-50px bg-light-secondary bg-opacity-50 mb-3">
                        <span class="svg-icon svg-icon-2x text-gray-400">
                            <span class="ki ki-clock"></span>
                        </span>
                    </span>
                    <p class="text-gray-600 fw-semibold fs-6 mb-1">Aucune activité récente</p>
                    <p class="text-gray-500 fs-7 mb-0">Le journal d'audit sera bientôt disponible.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="col-xl-7 col-md-12">
        <div class="row g-3 h-100">
            <div class="col-xl-4 col-md-4" data-admin-only>
                <a href="{{ route('admin.users') }}" class="text-decoration-none h-100">
                    <div class="card card-flush h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <span class="symbol symbol-50px bg-light-primary bg-opacity-100 mb-3">
                                <span class="svg-icon svg-icon-2 text-primary">
                                    <span class="ki ki-user-add"></span>
                                </span>
                            </span>
                            <div>
                                <h4 class="fw-bold text-dark fs-6 mb-0">Utilisateurs</h4>
                                <p class="text-gray-600 fw-semibold fs-7">Gérer les comptes</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-md-4" data-admin-only>
                <a href="{{ route('admin.web-services') }}" class="text-decoration-none h-100">
                    <div class="card card-flush h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <span class="symbol symbol-50px bg-light-info bg-opacity-100 mb-3">
                                <span class="svg-icon svg-icon-2 text-info">
                                    <span class="ki ki-server"></span>
                                </span>
                            </span>
                            <div>
                                <h4 class="fw-bold text-dark fs-6 mb-0">Web Services</h4>
                                <p class="text-gray-600 fw-semibold fs-7">Publier et gérer</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-4 col-md-4" data-admin-only>
                <a href="{{ route('admin.web-service-domains') }}" class="text-decoration-none h-100">
                    <div class="card card-flush h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <span class="symbol symbol-50px bg-light-success bg-opacity-100 mb-3">
                                <span class="svg-icon svg-icon-2 text-success">
                                    <span class="ki ki-server-network"></span>
                                </span>
                            </span>
                            <div>
                                <h4 class="fw-bold text-dark fs-6 mb-0">Domaines</h4>
                                <p class="text-gray-600 fw-semibold fs-7">Organiser l'accès</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ── API ERROR BANNER ── --}}
<div id="dashboard-error" class="alert alert-danger d-none fw-semibold mt-4" role="alert"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
        el.classList.remove('d-none');
    }

    function roleBadge(role) {
        if (role === 'admin') {
            return '<span class="badge badge-light-primary fw-semibold">Administrateur</span>';
        }
        return '<span class="badge badge-light-secondary fw-semibold">Utilisateur</span>';
    }

    function statusBadge(active) {
        if (active) {
            return '<span class="badge badge-light-success fw-semibold">Actif</span>';
        }
        return '<span class="badge badge-light-danger fw-semibold">Inactif</span>';
    }

    function showSkeleton(id) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = '';
            el.innerHTML = '<span class="inline-block h-25px w-35px animate-pulse rounded bg-gray-200"></span>';
        }
    }

    function hideSkeleton(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function getInitials(name) {
        var parts = (name || '').trim().split(/\s+/);
        if (parts.length === 0) return '?';
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function pickAvatarColor(name) {
        var colors = ['primary', 'info', 'success', 'warning', 'danger', 'purple'];
        var hash = 0;
        var src = name || '';
        for (var i = 0; i < src.length; i++) hash = ((hash << 5) - hash) + src.charCodeAt(i);
        return colors[Math.abs(hash) % colors.length];
    }

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
                localStorage.setItem('anapec_user', JSON.stringify(user));

                document.getElementById('welcome-name').textContent = user.name || '';

                document.getElementById('acct-name').textContent = user.name || '--';
                document.getElementById('acct-email').textContent = user.email || '--';
                document.getElementById('acct-role').innerHTML = roleBadge(user.role);
                document.getElementById('acct-status').innerHTML = statusBadge(user.is_active);

                var initials = getInitials(user.name);
                document.getElementById('account-initials').textContent = initials;
                var colorClass = 'bg-light-' + pickAvatarColor(user.name);
                document.getElementById('account-avatar').className = 'symbol symbol-50px ' + colorClass + ' bg-opacity-100 mb-3';

                if (user.role === 'admin') {
                    loadAdminStats(user.id);
                } else {
                    var adminCards = document.querySelectorAll('[data-visibility="admin"]');
                    adminCards.forEach(function (c) { c.style.display = 'none'; });
                }
            });
        })
        .catch(function () {
            setError('Impossible de charger les données. Veuillez réessayer.');
        });

    function loadAdminStats(userId) {
        showSkeleton('val-users');
        showSkeleton('val-services');
        showSkeleton('val-domains');

        var usersData = null;
        var servicesData = null;

        var p1 = api.get('/api/v1/admin/users').then(function (res) {
            if (!res.ok) { hideSkeleton('val-users', '--'); return null; }
            return res.json().then(function (data) {
                if (data.success && Array.isArray(data.data)) {
                    hideSkeleton('val-users', String(data.data.length));
                    usersData = data.data;
                    return data.data;
                }
                hideSkeleton('val-users', '--');
                return null;
            });
        }).catch(function () { hideSkeleton('val-users', '--'); return null; });

        var p2 = api.get('/api/v1/admin/web-services').then(function (res) {
            if (!res.ok) {
                hideSkeleton('val-services', '--');
                renderChartFallback();
                return null;
            }
            return res.json().then(function (data) {
                if (data.success && Array.isArray(data.data)) {
                    hideSkeleton('val-services', String(data.data.length));
                    servicesData = data.data;
                    renderChart(data.data);
                    return data.data;
                }
                hideSkeleton('val-services', '--');
                renderChartFallback();
                return null;
            });
        }).catch(function () {
            hideSkeleton('val-services', '--');
            renderChartFallback();
            return null;
        });

        var p3 = api.get('/api/v1/admin/domains').then(function (res) {
            if (!res.ok) { hideSkeleton('val-domains', '--'); return null; }
            return res.json().then(function (data) {
                if (data.success && Array.isArray(data.data)) {
                    hideSkeleton('val-domains', String(data.data.length));
                    return data.data;
                }
                hideSkeleton('val-domains', '--');
                return null;
            });
        }).catch(function () { hideSkeleton('val-domains', '--'); return null; });

        Promise.all([p1, p2, p3]).then(function () {
            if (usersData && Array.isArray(usersData)) {
                var activeUsers = usersData.filter(function (u) { return u.is_active === true; }).length;
                var inactiveUsers = usersData.length - activeUsers;
                document.getElementById('stat-user-active').textContent = String(activeUsers);
                document.getElementById('stat-user-inactive').textContent = String(inactiveUsers);
                var totalUsers = usersData.length;
                var userActivePct = totalUsers > 0 ? Math.round((activeUsers / totalUsers) * 100) : 0;
                document.getElementById('bar-user-active').style.width = userActivePct + '%';
            }
            if (servicesData && Array.isArray(servicesData)) {
                var activeSvc = servicesData.filter(function (w) { return w.is_active === true; }).length;
                var inactiveSvc = servicesData.length - activeSvc;
                document.getElementById('stat-svc-active').textContent = String(activeSvc);
                document.getElementById('stat-svc-inactive').textContent = String(inactiveSvc);
                var totalSvc = servicesData.length;
                var svcActivePct = totalSvc > 0 ? Math.round((activeSvc / totalSvc) * 100) : 0;
                document.getElementById('bar-svc-active').style.width = svcActivePct + '%';
            }
        });
    }

    function renderChart(services) {
        var ctx = document.getElementById('services-chart');
        if (!ctx || typeof Chart === 'undefined') { renderChartFallback(); return; }

        var active = services.filter(function (w) { return w.is_active === true; }).length;
        var inactive = services.length - active;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Actif', 'Désactivé'],
                datasets: [{
                    label: 'Web Services',
                    data: [active, inactive],
                    backgroundColor: ['#0EA5E9', '#E5E7EB'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        align: 'center',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                            color: '#4B5563',
                            font: { weight: '600', size: 13 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        cornerRadius: 8,
                        callbacks: {
                            label: function (c) { return ' ' + c.label + ': ' + c.parsed + ' service(s)'; }
                        }
                    }
                }
            }
        });
    }

    function renderChartFallback() {
        var ctx = document.getElementById('services-chart');
        if (!ctx || typeof Chart === 'undefined') return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Données', 'Disponibles'],
                datasets: [{
                    data: [0, 1],
                    backgroundColor: ['#E5E7EB', '#E5E7EB'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { display: false } }
            }
        });
    }
})();
</script>
@endpush
