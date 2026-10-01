@push('styles')
    <style>
        .hidden {
            display: none !important;
        }

        .code-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            letter-spacing: .02em;
        }
    </style>
@endpush

@extends('layouts.metronic')

@section('title', 'Web Service')

@section('content')
    {{-- ── PAGE HEADING ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-between gap-4 mb-6 mb-xl-8">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.web-services') }}"
                class="btn btn-icon btn-light-primary btn-active-color-primary w-40px h-40px align-self-stretch d-flex flex-shrink-0"
                title="Retour aux Web Services" aria-label="Retour aux Web Services">
                <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-arrow-left fs-2"></i></span>
            </a>
            <div class="symbol symbol-50px bg-light-primary bg-opacity-100 flex-shrink-0">
                <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-element-11 fs-2"></i></span>
            </div>
            <div>
                <h2 class="fw-bold text-dark fs-3 mb-0">Web Service</h2>
                <p class="text-muted fs-6 mb-0">Détails du service et de son accès global.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('admin.web-services') }}" class="btn btn-light-secondary fw-semibold">
                <span class="svg-icon svg-icon-2"><i class="ki-outline ki-arrow-left"></i></span>
                <span class="ms-2">Retour aux Web Services</span>
            </a>
            <button type="button" id="btn-edit-ws" class="btn btn-primary fw-semibold">
                <span class="svg-icon svg-icon-2"><i class="ki-outline ki-pencil"></i></span>
                <span class="ms-2">Modifier</span>
            </button>
            <button type="button" id="btn-toggle-ws" class="btn btn-light-danger fw-semibold">
                <span id="btn-toggle-ws-spinner"
                    class="spinner-border spinner-border-sm text-danger d-none me-2" role="status"
                    aria-hidden="true"></span>
                <span id="btn-toggle-ws-icon"
                    class="svg-icon svg-icon-2 text-danger me-2"><i class="ki-outline ki-toggle-off"></i></span>
                <span id="btn-toggle-ws-text">Désactiver</span>
            </button>
        </div>
    </div>

    {{-- ── LOADING STATE ── --}}
    <div id="auth-loading" class="alert alert-info" role="status">
        <div class="alert-body d-flex align-items-center">
            <span class="spinner-border spinner-border-sm text-primary me-3" role="status" aria-hidden="true"></span>
            <div>
                <h4 class="fs-6 fw-bolder text-dark mb-1">Vérification des accès</h4>
                <p class="fs-7 text-muted mb-0">Chargement des détails du Web Service...</p>
            </div>
        </div>
    </div>

    {{-- ── ACCESS DENIED (403 / non-admin) ── --}}
    <div id="access-denied" class="alert alert-danger d-none" role="alert">
        <div class="alert-body">
            <div class="d-flex align-items-center">
                <span class="svg-icon svg-icon-2x svg-icon-danger me-3"><i class="ki-outline ki-shield"></i></span>
                <div>
                    <h4 class="fs-6 fw-bolder text-white mb-1">Accès non autorisé</h4>
                    <p class="fs-7 text-white opacity-75 mb-0">Vous devez être administrateur pour consulter cette page.</p>
                </div>
            </div>
            <div class="alert-actions">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light-danger">Retour au Dashboard</a>
            </div>
        </div>
    </div>

    {{-- ── 404 / NOT FOUND ── --}}
    <div id="not-found" class="alert alert-light-secondary d-none" role="alert">
        <div class="alert-body d-flex align-items-center">
            <span class="svg-icon svg-icon-2x svg-icon-secondary me-3"><i class="ki-outline ki-search"></i></span>
            <div>
                <h4 class="fs-6 fw-bolder text-secondary mb-1">Web Service introuvable</h4>
                <p class="fs-7 text-gray-700 mb-0">Ce service n'existe pas ou a été retiré.</p>
            </div>
            <div class="alert-actions">
                <a href="{{ route('admin.web-services') }}" class="btn btn-sm btn-light-secondary">Retour à la liste</a>
            </div>
        </div>
    </div>

    {{-- ── LOAD ERROR + RETRY ── --}}
    <div id="load-error" class="alert alert-danger d-none" role="alert">
        <div class="alert-body">
            <div class="d-flex align-items-center">
                <span class="svg-icon svg-icon-2x svg-icon-danger me-3"><i class="ki-outline ki-cloud-off"></i></span>
                <div>
                    <h4 class="fs-6 fw-bolder text-white mb-1">Impossible de charger ce Web Service</h4>
                    <p class="fs-7 text-white opacity-75 mb-0">Vérifiez votre connexion en cliquant sur « Réessayer ».</p>
                </div>
            </div>
            <div class="alert-actions">
                <button type="button" id="btn-retry" class="btn btn-sm btn-light-danger">Réessayer</button>
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT (hidden until loaded) ── --}}
    <div id="admin-ws-show-root" class="hidden">
        {{-- ── 1. SERVICE PROFILE CARD ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="profile-card">
            <div class="card-body p-6 p-md-8">
                <div class="d-flex flex-wrap align-items-start flex-column flex-md-row gap-5 gap-md-0 w-100">
                    <div class="d-flex flex-column align-items-center mb-5 mb-md-0">
                        <div id="service-avatar" class="symbol symbol-100px mb-3">
                            <span id="service-avatar-label" class="symbol-label text-start fs-3 fw-bolder">—</span>
                        </div>
                        <div id="service-badges" class="d-flex flex-wrap justify-content-center gap-2"></div>
                    </div>
                    <div class="flex-grow-1 d-flex flex-column justify-content-center">
                        <h3 id="profile-name" class="fw-bold text-dark fs-2 mb-1">—</h3>
                        <div class="d-flex align-items-center text-muted fs-6 mb-5">
                            <span class="svg-icon svg-icon-2 me-2"><i class="ki-outline ki-code"></i></span>
                            <span id="profile-code" class="code-badge">—</span>
                        </div>
                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <span class="text-muted fs-7 d-block mb-1">Statut global</span>
                                <div id="profile-status"></div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <span class="text-muted fs-7 d-block mb-1">Description</span>
                                <span id="profile-description" class="text-dark fs-7 fw-semibold">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 2. SERVICE INFORMATION CARD ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="details-card">
            <div class="card-header border-0">
                <h3 class="card-title fw-bold text-dark fs-4">
                    <span class="svg-icon svg-icon-2 text-primary me-2"><i class="ki-outline ki-info"></i></span>
                    Informations du service
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-12 col-md-6 border-end border-bottom border-md-bottom border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Code</span>
                            <span id="detail-code" class="code-badge fs-6 fw-bolder text-dark">—</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 border-bottom border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Nom</span>
                            <span id="detail-name" class="fs-6 fw-bolder text-dark">—</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-12 border-bottom border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Description</span>
                            <p id="detail-description" class="fs-7 text-dark mb-0" style="white-space: pre-wrap;">—</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 border-end border-bottom border-md-bottom border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Statut global</span>
                            <div id="detail-status">—</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 border-bottom border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Domaines</span>
                            <div id="detail-domains">—</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 border-end border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Créé le</span>
                            <span id="detail-created" class="text-dark fs-7 fw-semibold">—</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Modifié le</span>
                            <span id="detail-updated" class="text-dark fs-7 fw-semibold">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TOAST ── --}}
    <div id="toast" class="alert alert-light-success d-none position-fixed top-0 end-0 m-5 shadow" role="status"
        aria-live="polite">
        <p id="toast-msg" class="fs-7 fw-bold text-success mb-0"></p>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var token = localStorage.getItem('anapec_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            var code = @json($code ?? null) || (window.location.pathname.match(/\/admin\/web-services\/([A-Za-z0-9_]+)$/) || [])[1] || null;

            function get(url) {
                return fetch(url, {
                    method: 'GET',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
            }

            function api() {
                if (window.apiClient) return window.apiClient;
                function req(method, url, data) {
                    return fetch('/api/v1' + url, {
                        method: method,
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: data ? JSON.stringify(data) : undefined
                    }).then(function (res) {
                        return res.json().catch(function () { return {}; }).then(function (body) {
                            if (res.ok) return { data: body, status: res.status };
                            var err = new Error('HTTP ' + res.status);
                            err.status = res.status;
                            err.body = body;
                            err.response = { status: res.status, data: body };
                            throw err;
                        });
                    });
                }
                return {
                    get: function (url) { return req('GET', url); },
                    post: function (url, data) { return req('POST', url, data); },
                    put: function (url, data) { return req('PUT', url, data); },
                    patch: function (url, data) { return req('PATCH', url, data); },
                    delete: function (url, data) { return req('DELETE', url, data); }
                };
            }

            function esc(v) {
                if (v === null || v === undefined) return '—';
                var d = document.createElement('div');
                d.textContent = String(v);
                return d.innerHTML;
            }

            function fmtDate(iso) {
                if (!iso) return '—';
                var d = new Date(iso);
                if (isNaN(d.getTime())) return '—';
                return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }

            function showToast(msg, type) {
                var t = document.getElementById('toast');
                var msgEl = document.getElementById('toast-msg');
                msgEl.textContent = msg;
                t.className = 'alert position-fixed top-0 end-0 m-5 shadow ' +
                    (type === 'error' ? 'alert-danger' : 'alert-light-success');
                msgEl.className = 'fs-7 fw-bold mb-0 ' + (type === 'error' ? 'text-danger' : 'text-success');
                t.classList.remove('d-none');
                clearTimeout(t._timer);
                t._timer = setTimeout(function () { t.classList.add('d-none'); }, 4000);
            }

            function statusBadge(active) {
                return active
                    ? '<span class="badge badge-light-success fw-semibold"><span class="bullet bullet-dot bg-success me-1"></span>Actif</span>'
                    : '<span class="badge badge-light-secondary fw-semibold"><span class="bullet bullet-dot bg-secondary me-1"></span>Désactivé</span>';
            }

            function domainBadges(domains) {
                if (!domains.length) {
                    return '<span class="text-muted fs-7">—</span>';
                }
                var html = '<div class="d-flex flex-wrap gap-2">';
                domains.forEach(function (d) {
                    html += '<span class="badge badge-light-info fw-semibold code-badge">' +
                        esc(d.code) + '</span>';
                });
                html += '</div>';
                return html;
            }

            function avatarHash(name) {
                var hash = 0;
                for (var i = 0; i < (name || '').length; i++) hash = ((hash << 5) - hash) + name.charCodeAt(i);
                return Math.abs(hash);
            }

            function serviceInitials(s) {
                var parts = ((s.name || '') + ' ' + (s.code || '')).trim().split(/\s+/).filter(function (p) {
                    return p.length > 0;
                });
                if (!parts.length) return '?';
                if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
            }

            var AVATAR_BG = ['bg-light-primary bg-opacity-100', 'bg-light-info bg-opacity-100',
                'bg-light-success bg-opacity-100', 'bg-light-warning bg-opacity-100',
                'bg-light-danger bg-opacity-100', 'bg-light-purple bg-opacity-100'
            ];
            var AVATAR_TEXT = ['text-primary', 'text-info', 'text-success', 'text-warning', 'text-danger', 'text-purple'];

            var currentService = null;
            var toggleBusy = false;

            var root = document.getElementById('admin-ws-show-root');
            var authLoading = document.getElementById('auth-loading');
            var accessDenied = document.getElementById('access-denied');
            var notFound = document.getElementById('not-found');
            var loadError = document.getElementById('load-error');
            var toggleBtn = document.getElementById('btn-toggle-ws');
            var toggleBtnText = document.getElementById('btn-toggle-ws-text');
            var toggleBtnIcon = document.getElementById('btn-toggle-ws-icon');
            var toggleBtnSpinner = document.getElementById('btn-toggle-ws-spinner');

            function showOnly(id) {
                [authLoading, accessDenied, notFound, loadError, root].forEach(function (el) {
                    if (!el) return;
                    el.classList.add('d-none');
                    el.classList.add('hidden');
                });
                if (id === 'auth-loading') {
                    authLoading.classList.remove('d-none');
                    authLoading.classList.remove('hidden');
                } else if (id === 'access-denied') {
                    accessDenied.classList.remove('d-none');
                    accessDenied.classList.remove('hidden');
                } else if (id === 'not-found') {
                    notFound.classList.remove('d-none');
                    notFound.classList.remove('hidden');
                } else if (id === 'load-error') {
                    loadError.classList.remove('d-none');
                    loadError.classList.remove('hidden');
                } else if (id === 'details') {
                    root.classList.remove('d-none');
                    root.classList.remove('hidden');
                }
            }

            function updateToggleButton() {
                var active = currentService.is_active === true;
                toggleBtnText.textContent = active ? 'Désactiver' : 'Activer';
                if (active) {
                    toggleBtn.classList.remove('btn-light-success');
                    toggleBtn.classList.add('btn-light-danger');
                    toggleBtnIcon.className = 'svg-icon svg-icon-2 text-danger me-2';
                    toggleBtnIcon.innerHTML = '<i class="ki-outline ki-toggle-off"></i>';
                    toggleBtnSpinner.className = 'spinner-border spinner-border-sm text-danger d-none me-2';
                } else {
                    toggleBtn.classList.remove('btn-light-danger');
                    toggleBtn.classList.add('btn-light-success');
                    toggleBtnIcon.className = 'svg-icon svg-icon-2 text-success me-2';
                    toggleBtnIcon.innerHTML = '<i class="ki-outline ki-toggle-on"></i>';
                    toggleBtnSpinner.className = 'spinner-border spinner-border-sm text-success d-none me-2';
                }
            }

            function renderDetails() {
                var svc = currentService;
                var name = svc.name || svc.code || '—';
                var h = avatarHash(name);
                var avatar = document.getElementById('service-avatar');
                var label = document.getElementById('service-avatar-label');
                avatar.className = 'symbol symbol-100px mb-3 ' + AVATAR_BG[h % AVATAR_BG.length];
                label.className = 'symbol-label text-start fs-3 fw-bolder ' + AVATAR_TEXT[h % AVATAR_TEXT.length];
                label.textContent = serviceInitials(svc);

                document.getElementById('profile-name').textContent = svc.name || '—';
                document.getElementById('profile-code').textContent = svc.code || '—';
                document.getElementById('profile-status').innerHTML = statusBadge(svc.is_active === true);
                document.getElementById('profile-description').textContent = svc.description || '—';
                document.getElementById('service-badges').innerHTML = statusBadge(svc.is_active === true);

                document.getElementById('detail-code').textContent = svc.code || '—';
                document.getElementById('detail-name').textContent = svc.name || '—';
                document.getElementById('detail-description').textContent = svc.description || '—';
                document.getElementById('detail-status').innerHTML = statusBadge(svc.is_active === true);
                var domains = Array.isArray(svc.domains) ? svc.domains : [];
                document.getElementById('detail-domains').innerHTML = domainBadges(domains);
                document.getElementById('detail-created').textContent = fmtDate(svc.created_at);
                document.getElementById('detail-updated').textContent = fmtDate(svc.updated_at);

                updateToggleButton();
                showOnly('details');
            }

            // ── Auth gate (same pattern as the Web Services list) ──
            get('/api/v1/auth/me')
                .then(function (res) {
                    if (res.status === 401) {
                        localStorage.removeItem('anapec_token');
                        localStorage.removeItem('anapec_user');
                        window.location.href = '/login';
                        return;
                    }
                    if (!res.ok) {
                        showOnly('load-error');
                        return;
                    }
                    return res.json().then(function (data) {
                        if (!data.success || !data.data) {
                            showOnly('load-error');
                            return;
                        }
                        var user = data.data;
                        localStorage.setItem('anapec_user', JSON.stringify(user));

                        if (user.role !== 'admin') {
                            showOnly('access-denied');
                            setTimeout(function () { window.location.replace('/dashboard'); }, 1200);
                            return;
                        }

                        loadDetails();
                    });
                })
                .catch(function () {
                    showOnly('load-error');
                });

            function loadDetails() {
                if (!code) {
                    showOnly('not-found');
                    return;
                }
                showOnly('auth-loading');

                get('/api/v1/admin/web-services/' + encodeURIComponent(code))
                    .then(function (res) {
                        if (res.status === 404) { showOnly('not-found'); return; }
                        if (res.status === 403) { showOnly('access-denied'); return; }
                        if (res.status === 401) {
                            localStorage.removeItem('anapec_token');
                            localStorage.removeItem('anapec_user');
                            window.location.href = '/login';
                            return;
                        }
                        if (!res.ok) { showOnly('load-error'); return; }
                        return res.json().then(function (data) {
                            if (!data.success || !data.data) { showOnly('load-error'); return; }
                            currentService = data.data;
                            renderDetails();
                        });
                    })
                    .catch(function () {
                        showOnly('load-error');
                    });
            }

            // ── Retry ──
            var retry = document.getElementById('btn-retry');
            if (retry) retry.addEventListener('click', loadDetails);

            // ── Modifier: navigate to the list page and open the edit flow there ──
            var editBtn = document.getElementById('btn-edit-ws');
            if (editBtn) {
                editBtn.addEventListener('click', function () {
                    if (!currentService) return;
                    window.location.href = '/admin/web-services?edit=' + encodeURIComponent(currentService.code);
                });
            }

            // ── Enable/Disable: reuse the existing status endpoint ──
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    if (!currentService || toggleBusy) return;
                    var nextActive = currentService.is_active !== true;
                    var client = api();
                    if (!client) {
                        showToast('Client API introuvable.', 'error');
                        return;
                    }
                    toggleBusy = true;
                    toggleBtn.disabled = true;
                    toggleBtnSpinner.classList.remove('d-none');
                    toggleBtnText.textContent = 'Mise à jour...';

                    client.patch('/admin/web-services/' + encodeURIComponent(currentService.code) + '/status',
                        { is_active: nextActive })
                        .then(function () {
                            currentService.is_active = nextActive;
                            toggleBusy = false;
                            toggleBtn.disabled = false;
                            toggleBtnSpinner.classList.add('d-none');
                            updateToggleButton();
                            document.getElementById('detail-status').innerHTML = statusBadge(nextActive);
                            document.getElementById('profile-status').innerHTML = statusBadge(nextActive);
                            document.getElementById('service-badges').innerHTML = statusBadge(nextActive);
                            document.getElementById('detail-updated').textContent = '—';
                            showToast(nextActive ? 'Web Service activé.' : 'Web Service désactivé.', 'success');
                        })
                        .catch(function (err) {
                            toggleBusy = false;
                            toggleBtn.disabled = false;
                            toggleBtnSpinner.classList.add('d-none');
                            updateToggleButton();
                            var status = err.status || (err.response ? err.response.status : null);
                            if (status === 401) {
                                localStorage.removeItem('anapec_token');
                                localStorage.removeItem('anapec_user');
                                window.location.href = '/login';
                                return;
                            }
                            if (status === 403) { showToast('Accès non autorisé.', 'error'); return; }
                            showToast('Une erreur est survenue. Veuillez réessayer.', 'error');
                        });
                });
            }
        })();
    </script>
@endpush
