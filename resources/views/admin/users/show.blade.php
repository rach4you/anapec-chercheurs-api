@extends('layouts.metronic')

@section('title', 'Détails utilisateur')

@push('styles')
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            flex: 0 0 auto;
            vertical-align: middle;
        }

        .switch input {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 1;
        }

        .switch .track {
            position: absolute;
            inset: 0;
            border-radius: 24px;
            background-color: var(--bs-secondary, #6c757d);
            transition: background-color 0.2s ease;
        }

        .switch .handle {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
            transition: transform 0.2s ease;
        }

        .switch input:checked~.track {
            background-color: var(--bs-primary, #1266f1);
        }

        .switch input:checked~.handle {
            transform: translateX(20px);
        }
    </style>
@endpush

@section('content')
    {{-- ── PAGE HEADING ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-between gap-4 mb-6 mb-xl-8">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.users') }}"
                class="btn btn-icon btn-light-primary btn-active-color-primary w-40px h-40px align-self-stretch d-flex flex-shrink-0"
                title="Retour aux utilisateurs" aria-label="Retour aux utilisateurs">
                <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-arrow-left fs-2"></i></span>
            </a>
            <div class="symbol symbol-50px bg-light-primary bg-opacity-100 flex-shrink-0">
                <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-user fs-2"></i></span>
            </div>
            <div>
                <h2 class="fw-bold text-dark fs-3 mb-0">Détails utilisateur</h2>
                <p class="text-muted fs-6 mb-0">Consultez et gérez les informations et les accès de cet utilisateur.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <button type="button" id="btn-reset-password" class="btn btn-light-secondary fw-semibold">
                <span class="svg-icon svg-icon-2"><i class="ki-outline ki-locked"></i></span>
                <span class="ms-2">Réinitialiser le mot de passe</span>
            </button>
            <button type="button" id="btn-edit-user" class="btn btn-primary fw-semibold">
                <span class="svg-icon svg-icon-2"><i class="ki-outline ki-edit"></i></span>
                <span class="ms-2">Modifier</span>
            </button>
        </div>
    </div>

    {{-- ── LOADING STATE ── --}}
    <div id="auth-loading" class="alert alert-info d-none" role="status">
        <div class="alert-body d-flex align-items-center">
            <span class="spinner spinner-sm spinner-primary me-3"></span>
            <div>
                <h4 class="fs-6 fw-bolder text-dark mb-1">Vérification des accès</h4>
                <p class="fs-7 text-gray-700 mb-0">Chargement des détails de l'utilisateur...</p>
            </div>
        </div>
    </div>

    {{-- ── ACCESS DENIED (403 / non-admin) ── --}}
    <div id="access-denied" class="alert alert-danger d-none" role="alert">
        <div class="alert-body">
            <div class="d-flex align-items-center">
                <span class="svg-icon svg-icon-2x svg-icon-danger me-3"><i class="ki-outline ki-shield"></i></span>
                <div>
                    <h4 class="fs-6 fw-bolder text-danger mb-1">Accès non autorisé</h4>
                    <p class="fs-7 text-gray-700 mb-0">Vous devez être administrateur pour consulter cette page.</p>
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
                <h4 class="fs-6 fw-bolder text-secondary mb-1">Utilisateur introuvable</h4>
                <p class="fs-7 text-gray-700 mb-0">Cet utilisateur n'existe pas ou a été supprimé.</p>
            </div>
            <div class="alert-actions">
                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-light-secondary">Retour à la liste</a>
            </div>
        </div>
    </div>

    {{-- ── LOAD ERROR + RETRY ── --}}
    <div id="load-error" class="alert alert-danger d-none" role="alert">
        <div class="alert-body">
            <div class="d-flex align-items-center">
                <span class="svg-icon svg-icon-2x svg-icon-danger me-3"><i class="ki-outline ki-cloud-off"></i></span>
                <div>
                    <h4 class="fs-6 fw-bolder text-danger mb-1">Impossible de charger les détails</h4>
                    <p class="fs-7 text-gray-700 mb-0">Vérifiez votre connexion et réessayez.</p>
                </div>
            </div>
            <div class="alert-actions">
                <button type="button" id="btn-retry" class="btn btn-sm btn-light-danger">Réessayer</button>
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT (hidden until loaded) ── --}}
    <div id="detail-root" class="d-none">
        {{-- ── 1. USER PROFILE CARD ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="profile-card">
            <div class="card-body p-6 p-md-8">
                <div class="d-flex flex-wrap align-items-start flex-column flex-md-row gap-5 gap-md-0 w-100">
                    <div class="d-flex flex-column align-items-center mb-5 mb-md-0">
                        <div id="profile-avatar" class="symbol symbol-100px mb-3">
                            <span id="profile-avatar-label" class="symbol-label text-start fs-3 fw-bolder">—</span>
                        </div>
                        <div id="profile-badges" class="d-flex flex-wrap justify-content-center gap-2"></div>
                    </div>
                    <div class="flex-grow-1 d-flex flex-column justify-content-center">
                        <h3 id="profile-name" class="fw-bold text-dark fs-2 mb-1">—</h3>
                        <div class="d-flex align-items-center text-muted fs-6 mb-5">
                            <span class="svg-icon svg-icon-2 me-2"><i class="ki-outline ki-email"></i></span>
                            <span id="profile-email">—</span>
                        </div>
                        <div class="row g-4" id="profile-meta">
                            <div class="col-sm-6 col-lg-3">
                                <span class="text-muted fs-7 d-block mb-1">Rôle</span>
                                <div id="profile-role"></div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <span class="text-muted fs-7 d-block mb-1">Statut</span>
                                <div id="profile-status"></div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <span class="text-muted fs-7 d-block mb-1">Créé le</span>
                                <span id="profile-created" class="text-dark fs-7 fw-semibold">—</span>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <span class="text-muted fs-7 d-block mb-1">Modifié le</span>
                                <span id="profile-updated" class="text-dark fs-7 fw-semibold">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 2. ACCESS SUMMARY ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="summary-card">
            <div class="card-header border-0">
                <h3 class="card-title fw-bold text-dark fs-4">
                    <span class="svg-icon svg-icon-2 text-primary me-2"><i class="ki-outline ki-shield"></i></span>
                    Résumé des accès Web Services
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="row g-0" id="summary-grid">
                    <div class="col-12 col-md-4 border-end border-bottom border-md-end-0 border-md-bottom border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Portée d'accès</span>
                            <div id="summary-scope" class="mb-2">
                                <span class="text-muted fs-6">—</span>
                            </div>
                            <span id="summary-scope-help" class="text-muted fs-7 d-block"></span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 border-bottom border-md-bottom-0 border-gray-200">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Accès effectif</span>
                            <span class="fs-3 fw-bolder text-success" id="summary-effective">—</span>
                            <span class="text-muted fs-7 d-block mt-1" id="summary-effective-help">—</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-6">
                            <span class="text-muted fs-7 d-block mb-2">Services configurés</span>
                            <span class="fs-3 fw-bolder text-primary" id="summary-configured">—</span>
                            <span class="text-muted fs-7 d-block mt-1" id="summary-configured-help">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. ACCESS BY DOMAIN ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="access-card">
            <div class="card-header border-0">
                <h3 class="card-title fw-bold text-dark fs-4">
                    <span class="svg-icon svg-icon-2 text-primary me-2"><i class="ki-outline ki-folder"></i></span>
                    Accès par domaine
                </h3>
                <div class="card-toolbar d-flex align-items-center gap-3">
                    <span class="text-muted fs-7" id="access-legend-note">
                        La source indique d'où vient l'accès, pas seulement s'il existe.
                    </span>
                </div>
            </div>
            <div class="card-body p-0" id="access-body"></div>
        </div>

        {{-- ── 4. OVERRIDES / EXCEPTIONS ── --}}
        <div class="card card-flush mb-6 mb-xl-8 d-none" id="overrides-card">
            <div class="card-header border-0">
                <h3 class="card-title fw-bold text-dark fs-4">
                    <span class="svg-icon svg-icon-2 text-warning me-2"><i class="ki-outline ki-alert"></i></span>
                    Exceptions Web Services
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="alert alert-light-warning mx-6 my-6 mb-0" style="border:0;border-radius:0.5rem">
                    <div class="d-flex align-items-start gap-3">
                        <span class="svg-icon svg-icon-2x svg-icon-warning flex-shrink-0">
                            <i class="ki-outline ki-info"></i>
                        </span>
                        <div class="fs-7 text-gray-700">
                            Une exception désactive un Web Service pour cet utilisateur, même quand la portée d'accès
                            « tous les services » s'appliquerait normalement.
                        </div>
                    </div>
                </div>
                <div id="overrides-body"></div>
            </div>
        </div>

        {{-- ── 5. DANGER ZONE ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="danger-card"
            style="border:1px solid var(--bs-danger, #dc3545)">
            <div class="card-header border-0" style="border-bottom:1px solid var(--bs-danger, #dc3545)">
                <h3 class="card-title fw-bold text-danger fs-4">
                    <span class="svg-icon svg-icon-2 text-danger me-2"><i class="ki-outline ki-trash"></i></span>
                    Zone de danger
                </h3>
            </div>
            <div class="card-body p-6 d-flex flex-wrap align-items-center justify-content-between gap-4">
                <div class="flex-grow-1">
                    <h4 class="fw-bold text-dark fs-6 mb-1">Révoquer toutes les permissions</h4>
                    <p class="text-muted fs-7 mb-0">
                        Supprime toutes les permissions Web Services explicites de cet utilisateur.
                        Cette action est irréversible.
                    </p>
                </div>
                <button type="button" id="btn-revoke-all" class="btn btn-danger fw-semibold">
                    <span class="svg-icon svg-icon-2"><i class="ki-outline ki-trash"></i></span>
                    <span class="ms-2" id="btn-revoke-all-text">Révoquer toutes les permissions</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── TOAST ── --}}
    <div id="toast" class="alert alert-light-success d-none position-fixed top-0 end-0 m-5 shadow" role="status"
        aria-live="polite">
        <p id="toast-msg" class="fs-7 fw-bold text-success mb-0"></p>
    </div>

    {{-- ── EDIT USER MODAL ── --}}
    <div id="edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="edit-modal-title">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-radius-something shadow-lg">
                <div class="modal-header border-0 pt-5 pb-2 px-7">
                    <h5 class="fs-5 fw-bolder text-dark" id="edit-modal-title">Modifier l'utilisateur</h5>
                    <button type="button" id="edit-modal-close" class="btn-close" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-7 pb-5">
                    <div id="edit-form-error" class="alert alert-danger py-3 d-none" role="alert"></div>
                    <form id="edit-user-form" novalidate>
                        <div class="mb-4">
                            <label for="ed-name" class="form-label fw-bold fs-7">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" id="ed-name" name="name" required maxlength="255"
                                class="form-control form-control-solid fs-6" placeholder="Prénom Nom" />
                            <p id="err-ed-name" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                        </div>
                        <div class="mb-4">
                            <label for="ed-email" class="form-label fw-bold fs-7">Email <span class="text-danger">*</span></label>
                            <input type="email" id="ed-email" name="email" required maxlength="255"
                                class="form-control form-control-solid fs-6" autocomplete="email" />
                            <p id="err-ed-email" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                        </div>
                        <div class="mb-4">
                            <label for="ed-role" class="form-label fw-bold fs-7">Rôle</label>
                            <select id="ed-role" name="role" class="form-select form-select-solid fs-6">
                                <option value="user">Utilisateur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                            <p id="err-ed-role" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                        </div>
                        <div class="mb-1">
                            <span class="form-label fw-bold fs-7 mb-0 d-block">Compte actif</span>
                            <div class="d-flex align-items-center gap-3 mt-3">
                                <label class="switch">
                                    <input type="checkbox" id="ed-active" name="is_active" />
                                    <span class="track"></span>
                                    <span class="handle"></span>
                                </label>
                                <span id="ed-active-label" class="text-muted fs-7">Inactif</span>
                            </div>
                            <p id="err-ed-is_active" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <button type="button" id="edit-modal-cancel" class="btn btn-light-secondary fw-semibold">Annuler</button>
                            <button type="submit" id="btn-save-user" class="btn btn-primary fw-semibold">
                                <span id="btn-save-user-spinner" class="spinner spinner-sm spinner-primary d-none me-2"></span>
                                <span id="btn-save-user-text">Enregistrer</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PASSWORD RESET MODAL ── --}}
    <div id="password-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="password-modal-title">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-radius-something shadow-lg">
                <div class="modal-header border-0 pt-5 pb-2 px-7">
                    <h5 class="fs-5 fw-bolder text-dark" id="password-modal-title">Réinitialiser le mot de passe</h5>
                    <button type="button" id="password-modal-close" class="btn-close" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-7 pb-5">
                    <div id="password-form-error" class="alert alert-danger py-3 d-none" role="alert"></div>
                    <form id="password-reset-form" novalidate>
                        <div class="mb-4">
                            <label for="psw-new" class="form-label fw-bold fs-7">Nouveau mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" id="psw-new" name="password" required minlength="8"
                                    class="form-control form-control-solid fs-6" placeholder="8 caractères minimum"
                                    autocomplete="new-password" />
                                <button type="button" id="psw-toggle" class="btn btn-icon btn-sm btn-light-primary"
                                    aria-label="Afficher ou masquer le mot de passe">
                                    <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-eye"></i></span>
                                </button>
                            </div>
                            <p id="err-psw-new" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                        </div>
                        <div class="mb-4">
                            <label for="psw-confirm" class="form-label fw-bold fs-7">Confirmation <span class="text-danger">*</span></label>
                            <input type="password" id="psw-confirm" name="password_confirmation" required minlength="8"
                                class="form-control form-control-solid fs-6" placeholder="Retapez le mot de passe"
                                autocomplete="new-password" />
                            <p id="err-psw-confirm" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-5">
                            <button type="button" id="password-modal-cancel" class="btn btn-light-secondary fw-semibold">Annuler</button>
                            <button type="submit" id="btn-reset-password-submit" class="btn btn-primary fw-semibold">
                                <span id="btn-reset-password-spinner" class="spinner spinner-sm spinner-primary d-none me-2"></span>
                                <span id="btn-reset-password-text">Réinitialiser</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ── REVOKE-ALL CONFIRM MODAL ── --}}
    <div id="revoke-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="revoke-modal-title">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-radius-something shadow-lg">
                <div class="modal-header border-0 pt-5 pb-2 px-7">
                    <h5 class="fs-5 fw-bolder text-danger" id="revoke-modal-title">
                        <span class="svg-icon svg-icon-2 text-danger me-2"><i class="ki-outline ki-trash"></i></span>
                        Révoquer toutes les permissions
                    </h5>
                    <button type="button" id="revoke-modal-close" class="btn-close" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-7 pb-5">
                    <p id="revoke-modal-message" class="fs-6 text-gray-700 mb-4"></p>
                    <div id="revoke-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>
                    <div class="d-flex justify-content-end gap-3">
                        <button type="button" id="revoke-modal-cancel" class="btn btn-light-secondary fw-semibold">Annuler</button>
                        <button type="button" id="btn-confirm-revoke" class="btn btn-danger fw-semibold">
                            <span id="btn-confirm-revoke-spinner" class="spinner spinner-sm spinner-danger d-none me-2"></span>
                            <span id="btn-confirm-revoke-text">Révoquer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
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

            var userId = @json($userId) || (window.location.pathname.match(/\/admin\/users\/(\d+)$/) || [])[1] || null;
            if (!userId) {
                window.location.href = '/admin/users';
                return;
            }

            // ── API helpers (Metronic layout does not load Vite's apiClient) ──
            function get(url) {
                return fetch(url, {
                    method: 'GET',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
            }

            function api() {
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
                        return res.json().catch(function () {
                            return {};
                        }).then(function (body) {
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

            // ── State ──
            var user = null;
            var accessScope = 'selected';
            var domainCatalog = [];   // [{code,name,is_active,web_services:[{code,name,is_active}]}]
            var userDomains = [];     // [{code,name,is_enabled}]
            var services = [];        // effective states for every Web Service
            var toggleBusy = {};

            // ── State helpers ──
            function activeServices() {
                return services.filter(function (s) { return s.global_is_active === true; });
            }

            function effectiveCount() {
                return services.filter(function (s) { return s.effective_access === true; }).length;
            }

            function configuredCount() {
                return services.filter(function (s) {
                    return s.is_enabled === true || s.override_disabled === true;
                }).length;
            }

            function sourceLabel(s) {
                if (s.override_disabled === true) return 'Exception utilisateur';
                if (s.grant_source === 'global') return 'Accès global';
                if (s.grant_source === 'direct') return 'Sélection explicite';
                if (s.is_enabled === false && s.grant_source === 'none') return 'Aucun accès';
                return 'Aucun accès';
            }

            function roleBadge(role) {
                return role === 'admin'
                    ? '<span class="badge badge-light-primary fw-semibold">Administrateur</span>'
                    : '<span class="badge badge-light-secondary fw-semibold">Utilisateur</span>';
            }

            function statusBadge(active) {
                return active
                    ? '<span class="badge badge-light-success fw-semibold"><span class="bullet bullet-dot bg-success me-1"></span>Actif</span>'
                    : '<span class="badge badge-light-danger fw-semibold"><span class="bullet bullet-dot bg-danger me-1"></span>Inactif</span>';
            }

            function serviceActiveBadge(active) {
                return active
                    ? '<span class="badge badge-light-success fw-semibold">Actif</span>'
                    : '<span class="badge badge-light-secondary fw-semibold">Inactif</span>';
            }

            function effectiveBadge(effective) {
                return effective
                    ? '<span class="badge badge-light-success fw-semibold"><span class="bullet bullet-dot bg-success me-1"></span>Autorisé</span>'
                    : '<span class="badge badge-light-danger fw-semibold"><span class="bullet bullet-dot bg-danger me-1"></span>Refusé</span>';
            }

            function scopeBadge(scope) {
                return scope === 'all'
                    ? '<span class="badge badge-light-success fw-semibold"><span class="bullet bullet-dot bg-success me-1"></span>Tous les Web Services</span>'
                    : '<span class="badge badge-light-primary fw-semibold"><span class="bullet bullet-dot bg-primary me-1"></span>Services sélectionnés</span>';
            }

            function avatarHash(name) {
                var hash = 0;
                for (var i = 0; i < (name || '').length; i++) hash = ((hash << 5) - hash) + name.charCodeAt(i);
                return Math.abs(hash);
            }

            function initialFromName(name) {
                var parts = (name || '').trim().split(/\s+/);
                if (!parts.length) return '?';
                if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
            }

            var AVATAR_BG = ['bg-light-primary bg-opacity-100', 'bg-light-info bg-opacity-100',
                'bg-light-success bg-opacity-100', 'bg-light-warning bg-opacity-100',
                'bg-light-danger bg-opacity-100', 'bg-light-purple bg-opacity-100'
            ];
            var AVATAR_TEXT = ['text-primary', 'text-info', 'text-success', 'text-warning', 'text-danger', 'text-purple'];

            function renderProfile() {
                var h = avatarHash(user.name);
                var avatar = document.getElementById('profile-avatar');
                var label = document.getElementById('profile-avatar-label');
                avatar.className = 'symbol symbol-100px mb-3 ' + AVATAR_BG[h % AVATAR_BG.length];
                label.className = 'symbol-label text-start fs-3 fw-bolder ' + AVATAR_TEXT[h % AVATAR_TEXT.length];
                label.textContent = initialFromName(user.name);

                document.getElementById('profile-name').textContent = user.name || '—';
                document.getElementById('profile-email').textContent = user.email || '—';
                document.getElementById('profile-role').innerHTML = roleBadge(user.role);
                document.getElementById('profile-status').innerHTML = statusBadge(user.is_active === true);
                document.getElementById('profile-created').textContent = fmtDate(user.created_at);
                document.getElementById('profile-updated').textContent = fmtDate(user.updated_at);

                var badges = document.getElementById('profile-badges');
                badges.innerHTML = roleBadge(user.role) + statusBadge(user.is_active === true);
            }

            function renderSummary() {
                var totalActive = activeServices().length;
                var eff = effectiveCount();
                var cfg = configuredCount();

                document.getElementById('summary-scope').innerHTML = scopeBadge(accessScope);
                document.getElementById('summary-scope-help').textContent = accessScope === 'all'
                    ? 'Chaque Web Service actif est accessible, sauf exception explicite.'
                    : 'Seuls les Web Services sélectionnés explicitement sont accessibles.';

                document.getElementById('summary-effective').textContent = eff + ' / ' + totalActive;
                document.getElementById('summary-effective-help').textContent =
                    'service' + (totalActive > 1 ? 's' : '') + ' accessible' + (totalActive > 1 ? 's' : '') + ' maintenant.';

                document.getElementById('summary-configured').textContent = cfg;
                document.getElementById('summary-configured-help').textContent =
                    'permission' + (cfg > 1 ? 's' : '') + ' explicite' + (cfg > 1 ? 's' : '') + ' enregistrée' + (cfg > 1 ? 's' : '') + '.';
            }

            function domainGranted(code) {
                for (var i = 0; i < userDomains.length; i++) {
                    if (userDomains[i].code === code) return userDomains[i].is_enabled === true;
                }
                return false;
            }

            function serviceByCode(code) {
                for (var i = 0; i < services.length; i++) {
                    if (services[i].code === code) return services[i];
                }
                return null;
            }

            function toggleButton(s) {
                var next = !s.is_enabled;
                var busy = toggleBusy[s.code] === true;
                var locked = next && s.global_is_active !== true;
                var label = next ? 'Activer' : 'Désactiver';
                var cls = next ? 'btn-light-success' : 'btn-light-danger';
                var icon = next ? 'ki-toggle-on' : 'ki-toggle-off';
                var title = locked ? 'Ce Web Service est inactif globalement : impossible de l\'activer.' : '';

                return '<button type="button" data-perm-code="' + esc(s.code) + '" data-next="' + next + '"' +
                    (busy || locked ? ' disabled' : '') +
                    ' title="' + esc(title) + '"' +
                    ' class="btn btn-sm ' + cls + ' fw-semibold flex-shrink-0">' +
                    (busy ? '<span class="spinner spinner-sm spinner-light-' + (next ? 'success' : 'danger') + ' me-1"></span>' :
                        '<span class="svg-icon svg-icon-2x me-1"><i class="ki-outline ' + icon + '"></i></span>') +
                    label +
                    '</button>';
            }

            function serviceRow(s) {
                var inException = s.override_disabled === true;
                return '<div class="d-flex flex-wrap align-items-center gap-3 p-4">' +
                    '<span class="svg-icon svg-icon-2x flex-shrink-0 ' +
                    (s.effective_access === true ? 'svg-icon-success' : 'svg-icon-secondary') + '">' +
                    '<i class="ki-outline ' + (s.effective_access === true ? 'ki-check' : 'ki-lock') + '"></i></span>' +
                    '<div class="flex-grow-1 min-w-0">' +
                    '<div class="d-flex flex-wrap align-items-center gap-2">' +
                    '<span class="font-mono text-dark fs-7 fw-bold">' + esc(s.code) + '</span>' +
                    serviceActiveBadge(s.global_is_active === true) +
                    effectiveBadge(s.effective_access === true) +
                    '</div>' +
                    '<div class="text-muted fs-7 mt-0.5 mb-1">' + esc(s.name) + '</div>' +
                    '<div class="text-muted fs-7 fs-8">' +
                    '<span class="text-muted">Source :</span> ' +
                    '<span class="fw-semibold ' + (inException ? 'text-warning' : 'text-dark') + '">' +
                    esc(sourceLabel(s)) + '</span>' +
                    '</div>' +
                    '</div>' +
                    '<div class="flex-shrink-0">' + toggleButton(s) + '</div>' +
                    '</div>';
            }

            function renderAccess() {
                var body = document.getElementById('access-body');

                if (!services.length) {
                    body.innerHTML = '<div class="p-8 text-center">' +
                        '<span class="svg-icon svg-icon-3x svg-icon-muted d-block mb-2"><i class="ki-outline ki-cloud-off"></i></span>' +
                        '<p class="text-gray-500 fs-6 fw-bold mb-0">Aucun Web Service disponible.</p></div>';
                    return;
                }

                var html = '';
                var grouped = false;

                domainCatalog.forEach(function (d) {
                    var dServices = (d.web_services || []).map(function (ws) {
                        return serviceByCode(ws.code) || null;
                    }).filter(Boolean);
                    if (!dServices.length) return;
                    grouped = true;

                    var granted = domainGranted(d.code);
                    html += '<div class="p-6 pb-2">' +
                        '<div class="d-flex flex-wrap align-items-center gap-2 mb-1">' +
                        '<span class="font-mono text-dark fs-7 fw-bold">' + esc(d.code) + '</span>' +
                        serviceActiveBadge(d.is_active === true) +
                        (granted
                            ? '<span class="badge badge-light-success fw-semibold">Domaine accordé</span>'
                            : '<span class="badge badge-light-secondary fw-semibold">Domaine non accordé</span>') +
                        '<span class="badge badge-light-info fw-semibold">' + dServices.length + ' Web Service' +
                        (dServices.length > 1 ? 's' : '') + '</span>' +
                        '</div>' +
                        '<div class="text-muted fs-7">' + esc(d.name) + '</div>' +
                        '</div>';

                    dServices.forEach(function (s, i) {
                        html += serviceRow(s) +
                            (i < dServices.length - 1 ? '<div class="border-b border-gray-200"></div>' : '');
                    });
                    html += '<div class="border-b border-gray-200"></div>';
                });

                var leftovers = services.filter(function (s) {
                    return !domainCatalog.some(function (d) {
                        return (d.web_services || []).some(function (ws) { return ws.code === s.code; });
                    });
                });
                if (leftovers.length) {
                    grouped = true;
                    html += '<div class="p-6 pb-2"><span class="text-dark fs-7 fw-bold">Autres Web Services</span></div>';
                    leftovers.forEach(function (s, i) {
                        html += serviceRow(s) +
                            (i < leftovers.length - 1 ? '<div class="border-b border-gray-200"></div>' : '');
                    });
                    html += '<div class="border-b border-gray-200"></div>';
                }

                if (!grouped) {
                    services.forEach(function (s, i) {
                        html += serviceRow(s) +
                            (i < services.length - 1 ? '<div class="border-b border-gray-200"></div>' : '');
                    });
                }

                body.innerHTML = html;
                body.querySelectorAll('[data-perm-code]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        togglePermission(btn.getAttribute('data-perm-code'),
                            btn.getAttribute('data-next') === 'true', btn);
                    });
                });
            }

            function renderOverrides() {
                var card = document.getElementById('overrides-card');
                var exceptions = services.filter(function (s) { return s.override_disabled === true; });
                if (!exceptions.length) {
                    card.classList.add('d-none');
                    return;
                }
                card.classList.remove('d-none');

                var html = '';
                exceptions.forEach(function (s) {
                    html += '<div class="d-flex flex-wrap align-items-center gap-3 p-4 border-b border-gray-200">' +
                        '<span class="svg-icon svg-icon-2x svg-icon-warning flex-shrink-0">' +
                        '<i class="ki-outline ki-close"></i></span>' +
                        '<div class="flex-grow-1 min-w-0">' +
                        '<div class="d-flex flex-wrap align-items-center gap-2">' +
                        '<span class="font-mono text-dark fs-7 fw-bold">' + esc(s.code) + '</span>' +
                        '<span class="badge badge-light-warning fw-semibold">Désactivé par exception</span>' +
                        '</div>' +
                        '<div class="text-muted fs-7 mt-0.5">' + esc(s.name) + '</div>' +
                        '</div>' +
                        '<div class="flex-shrink-0">' + toggleButton(s) + '</div>' +
                        '</div>';
                });
                document.getElementById('overrides-body').innerHTML = html;

                document.querySelectorAll('#overrides-body [data-perm-code]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        togglePermission(btn.getAttribute('data-perm-code'),
                            btn.getAttribute('data-next') === 'true', btn);
                    });
                });
            }

            function renderDanger() {
                var btn = document.getElementById('btn-revoke-all');
                var count = services.filter(function (s) { return s.is_enabled === true; }).length;
                if (count) {
                    btn.disabled = false;
                    document.getElementById('btn-revoke-all-text').textContent =
                        'Révoquer toutes les permissions (' + count + ')';
                } else {
                    btn.disabled = true;
                    document.getElementById('btn-revoke-all-text').textContent = 'Aucune permission à révoquer';
                }
            }

            function renderAll() {
                renderProfile();
                renderSummary();
                renderAccess();
                renderOverrides();
                renderDanger();
            }

            function refreshAfterChange() {
                renderSummary();
                renderAccess();
                renderOverrides();
                renderDanger();
            }

            // ── Permission toggle (existing PATCH endpoint) ──
            function togglePermission(code, next, btn) {
                var idx = -1;
                for (var i = 0; i < services.length; i++) {
                    if (services[i].code === code) { idx = i; break; }
                }
                if (idx === -1) return;

                // Snapshot a copy: the response merges fields into services[idx]
                // in place, so aliasing it would make the rollback a no-op.
                var prev = JSON.parse(JSON.stringify(services[idx]));
                toggleBusy[code] = true;
                refreshAfterChange();

                api().patch('/admin/users/' + userId + '/web-services/' + encodeURIComponent(code),
                    { is_enabled: next })
                    .then(function (res) {
                        var data = res.data && res.data.data;
                        if (data && typeof data.is_enabled === 'boolean') {
                            Object.keys(data).forEach(function (k) {
                                services[idx][k] = data[k];
                            });
                        } else {
                            services[idx] = prev;
                        }
                        toggleBusy[code] = false;
                        refreshAfterChange();
                        showToast(next ? 'Permission activée.' : 'Permission désactivée.');
                    })
                    .catch(function (err) {
                        services[idx] = prev;
                        toggleBusy[code] = false;
                        refreshAfterChange();
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        var msg = 'Une erreur est survenue. Veuillez réessayer.';
                        if (status === 403) msg = 'Accès non autorisé.';
                        else if (status === 404) msg = 'Web Service introuvable.';
                        else if (body && body.message) msg = body.message;
                        showToast(msg, 'error');
                    });
            }

            // ── Loading orchestration ──
            function showAuthLoading(on) { document.getElementById('auth-loading').classList.toggle('d-none', !on); }

            function showState(id) {
                ['auth-loading', 'access-denied', 'not-found', 'load-error'].forEach(function (s) {
                    document.getElementById(s).classList.add('d-none');
                });
                document.getElementById('detail-root').classList.add('d-none');
                if (id) document.getElementById(id).classList.remove('d-none');
            }

            function loadUser() {
                showAuthLoading(true);

                get('/api/v1/auth/me')
                    .then(function (res) {
                        if (res.status === 401) {
                            localStorage.removeItem('anapec_token');
                            localStorage.removeItem('anapec_user');
                            window.location.href = '/login';
                            return null;
                        }
                        if (!res.ok) {
                            showState('load-error');
                            return null;
                        }
                        return res.json().then(function (data) {
                            var me = data.data;
                            if (!me) { showState('load-error'); return null; }
                            localStorage.setItem('anapec_user', JSON.stringify(me));
                            if (me.role !== 'admin') { showState('access-denied'); return null; }
                            return get('/api/v1/admin/users/' + userId).then(function (uRes) {
                                if (uRes.status === 403) { showState('access-denied'); return; }
                                if (uRes.status === 404) { showState('not-found'); return; }
                                if (uRes.status === 401) {
                                    localStorage.removeItem('anapec_token');
                                    localStorage.removeItem('anapec_user');
                                    window.location.href = '/login';
                                    return;
                                }
                                if (!uRes.ok) { showState('load-error'); return; }
                                return uRes.json().then(function (uData) {
                                    if (!uData.success || !uData.data) { showState('load-error'); return; }
                                    user = uData.data;
                                    accessScope = user.access_scope || 'selected';
                                    showState(null);
                                    return loadAccess();
                                });
                            });
                        });
                    })
                    .catch(function () { showState('load-error'); });
            }

            function loadAccess() {
                var p1 = get('/api/v1/admin/users/' + userId + '/domains');
                // create-scope eager-loads each domain's web_services, which the
                // bare /admin/domains listing does not.
                var p2 = get('/api/v1/admin/users/create-scope');

                Promise.all([p1, p2]).then(function (res) {
                    document.getElementById('detail-root').classList.remove('d-none');
                    document.getElementById('access-body').innerHTML =
                        '<div class="p-8 text-center"><span class="spinner spinner-sm spinner-primary mb-2"></span>' +
                        '<p class="text-muted fs-7 mb-0">Chargement des accès...</p></div>';

                    if (res[0].status === 403) { showState('access-denied'); return; }
                    if (!res[0].ok) { showState('load-error'); return; }

                    return Promise.all([res[0].json(), res[1].ok ? res[1].json() : null]).then(function (parsed) {
                        var d1 = parsed[0];
                        var d2 = parsed[1];
                        var data = d1.data || {};

                        accessScope = data.access_scope || accessScope;
                        userDomains = Array.isArray(data.domains) ? data.domains : [];
                        services = Array.isArray(data.web_services) ? data.web_services : [];
                        domainCatalog = Array.isArray(d2 && d2.data && d2.data.data)
                            ? d2.data.data
                            : (Array.isArray(d2 && d2.data) ? d2.data : []);

                        renderAll();
                    });
                }).catch(function () { showState('load-error'); });
            }

            // ── Bindings ──
            document.getElementById('btn-retry').addEventListener('click', loadUser);

            // Edit modal
            var editModalEl = document.getElementById('edit-modal');
            var editForm = document.getElementById('edit-user-form');
            var editFormError = document.getElementById('edit-form-error');
            var btnSave = document.getElementById('btn-save-user');
            var btnSaveText = document.getElementById('btn-save-user-text');
            var btnSaveSpinner = document.getElementById('btn-save-user-spinner');
            var editSubmitting = false;

            function setEditError(name, msg) {
                var el = document.getElementById('err-ed-' + name);
                if (!el) return;
                el.textContent = msg;
                el.classList.remove('d-none');
            }

            function clearEditErrors() {
                ['name', 'email', 'role', 'is_active'].forEach(function (f) {
                    var el = document.getElementById('err-ed-' + f);
                    if (el) el.classList.add('d-none');
                });
                editFormError.classList.add('d-none');
            }

            function syncActiveLabel() {
                var active = document.getElementById('ed-active').checked;
                var el = document.getElementById('ed-active-label');
                el.textContent = active ? 'Actif' : 'Inactif';
                el.className = 'fs-7 ' + (active ? 'text-success' : 'text-muted');
            }

            document.getElementById('ed-active').addEventListener('change', syncActiveLabel);

            document.getElementById('btn-edit-user').addEventListener('click', function () {
                if (!user) return;
                document.getElementById('ed-name').value = user.name || '';
                document.getElementById('ed-email').value = user.email || '';
                document.getElementById('ed-role').value = user.role || 'user';
                document.getElementById('ed-active').checked = user.is_active === true;
                syncActiveLabel();
                clearEditErrors();
                bootstrap.Modal.getOrCreateInstance(editModalEl).show();
            });

            ['edit-modal-close', 'edit-modal-cancel'].forEach(function (id) {
                document.getElementById(id).addEventListener('click', function () {
                    bootstrap.Modal.getOrCreateInstance(editModalEl).hide();
                });
            });

            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (editSubmitting) return;
                clearEditErrors();

                var ok = true;
                var name = document.getElementById('ed-name').value.trim();
                var email = document.getElementById('ed-email').value.trim();
                if (!name) { setEditError('name', 'Le nom est requis.'); ok = false; }
                if (!email) { setEditError('email', "L'email est requis."); ok = false; }
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    setEditError('email', 'Format d\'email invalide.');
                    ok = false;
                }
                if (!ok) return;

                var payload = {
                    name: name,
                    email: email,
                    role: document.getElementById('ed-role').value,
                    is_active: document.getElementById('ed-active').checked === true
                };

                editSubmitting = true;
                btnSave.disabled = true;
                btnSaveSpinner.classList.remove('d-none');
                btnSaveText.textContent = 'Enregistrement...';

                function done() {
                    editSubmitting = false;
                    btnSave.disabled = false;
                    btnSaveSpinner.classList.add('d-none');
                    btnSaveText.textContent = 'Enregistrer';
                }

                var client = api();
                client.put('/admin/users/' + userId, {
                    name: payload.name, email: payload.email, role: payload.role
                }).then(function () {
                    return client.patch('/admin/users/' + userId + '/status', { is_active: payload.is_active });
                }).then(function () {
                    done();
                    bootstrap.Modal.getOrCreateInstance(editModalEl).hide();
                    showToast('Utilisateur modifié avec succès.');
                    loadUser();
                }).catch(function (err) {
                    done();
                    var status = err.status || (err.response ? err.response.status : null);
                    if (status === 401) {
                        localStorage.removeItem('anapec_token');
                        localStorage.removeItem('anapec_user');
                        window.location.href = '/login';
                        return;
                    }
                    if (status === 403) {
                        editFormError.textContent = 'Accès non autorisé.';
                        editFormError.classList.remove('d-none');
                        return;
                    }
                    var body = err.body || (err.response ? err.response.data : null);
                    if (status === 422 && body && body.errors) {
                        var mapped = { name: 'name', email: 'email', role: 'role', is_active: 'is_active' };
                        var has = false;
                        Object.keys(body.errors).forEach(function (f) {
                            if (mapped[f]) {
                                setEditError(mapped[f],
                                    Array.isArray(body.errors[f]) ? body.errors[f][0] : String(body.errors[f]));
                                has = true;
                            }
                        });
                        if (has) return;
                    }
                    editFormError.textContent = (body && body.message) || 'Erreur de validation.';
                    editFormError.classList.remove('d-none');
                });
            });

            // Password modal
            var pswModalEl = document.getElementById('password-modal');
            var pswForm = document.getElementById('password-reset-form');
            var pswFormError = document.getElementById('password-form-error');
            var pswBtn = document.getElementById('btn-reset-password-submit');
            var pswBtnText = document.getElementById('btn-reset-password-text');
            var pswBtnSpinner = document.getElementById('btn-reset-password-spinner');
            var pswSubmitting = false;

            document.getElementById('btn-reset-password').addEventListener('click', function () {
                document.getElementById('psw-new').value = '';
                document.getElementById('psw-confirm').value = '';
                document.getElementById('err-psw-new').classList.add('d-none');
                document.getElementById('err-psw-confirm').classList.add('d-none');
                pswFormError.classList.add('d-none');
                bootstrap.Modal.getOrCreateInstance(pswModalEl).show();
            });

            ['password-modal-close', 'password-modal-cancel'].forEach(function (id) {
                document.getElementById(id).addEventListener('click', function () {
                    bootstrap.Modal.getOrCreateInstance(pswModalEl).hide();
                });
            });

            document.getElementById('psw-toggle').addEventListener('click', function () {
                var el = document.getElementById('psw-new');
                el.type = el.type === 'password' ? 'text' : 'password';
            });

            pswForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (pswSubmitting) return;

                var psw = document.getElementById('psw-new').value;
                var confirm = document.getElementById('psw-confirm').value;
                var ok = true;
                document.getElementById('err-psw-new').classList.add('d-none');
                document.getElementById('err-psw-confirm').classList.add('d-none');
                pswFormError.classList.add('d-none');

                if (!psw) {
                    document.getElementById('err-psw-new').textContent = 'Le mot de passe est requis.';
                    document.getElementById('err-psw-new').classList.remove('d-none');
                    ok = false;
                } else if (psw.length < 8) {
                    document.getElementById('err-psw-new').textContent = 'Au moins 8 caractères requis.';
                    document.getElementById('err-psw-new').classList.remove('d-none');
                    ok = false;
                }
                if (psw !== confirm) {
                    document.getElementById('err-psw-confirm').textContent = 'Les mots de passe ne correspondent pas.';
                    document.getElementById('err-psw-confirm').classList.remove('d-none');
                    ok = false;
                }
                if (!ok) return;

                pswSubmitting = true;
                pswBtn.disabled = true;
                pswBtnSpinner.classList.remove('d-none');
                pswBtnText.textContent = 'Réinitialisation...';

                function done() {
                    pswSubmitting = false;
                    pswBtn.disabled = false;
                    pswBtnSpinner.classList.add('d-none');
                    pswBtnText.textContent = 'Réinitialiser';
                }

                api().post('/admin/users/' + userId + '/password', { password: psw })
                    .then(function () {
                        done();
                        bootstrap.Modal.getOrCreateInstance(pswModalEl).hide();
                        showToast('Mot de passe réinitialisé avec succès.');
                    })
                    .catch(function (err) {
                        done();
                        var status = err.status || (err.response ? err.response.status : null);
                        if (status === 401) {
                            localStorage.removeItem('anapec_token');
                            localStorage.removeItem('anapec_user');
                            window.location.href = '/login';
                            return;
                        }
                        if (status === 403) {
                            pswFormError.textContent = 'Accès non autorisé.';
                            pswFormError.classList.remove('d-none');
                            return;
                        }
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 422 && body && body.errors) {
                            var f = body.errors.password || body.errors;
                            pswFormError.textContent = Array.isArray(f) ? f[0] : 'Erreur de validation.';
                            pswFormError.classList.remove('d-none');
                            return;
                        }
                        pswFormError.textContent = (body && body.message) || 'Une erreur est survenue.';
                        pswFormError.classList.remove('d-none');
                    });
            });

            // Revoke-all modal
            var revokeModalEl = document.getElementById('revoke-modal');
            var revokeBtn = document.getElementById('btn-confirm-revoke');
            var revokeBtnText = document.getElementById('btn-confirm-revoke-text');
            var revokeBtnSpinner = document.getElementById('btn-confirm-revoke-spinner');
            var revokeError = document.getElementById('revoke-modal-error');
            var revoking = false;

            document.getElementById('btn-revoke-all').addEventListener('click', function () {
                var count = services.filter(function (s) { return s.is_enabled === true; }).length;
                if (!count) return;
                revokeError.classList.add('d-none');
                document.getElementById('revoke-modal-message').innerHTML =
                    '<strong>' + esc(user.name || 'cet utilisateur') + '</strong> possède actuellement ' +
                    '<strong>' + count + '</strong> permission' + (count > 1 ? 's' : '') +
                    ' Web Service active' + (count > 1 ? 's' : '') +
                    '. Voulez-vous toutes les révoquer ?';
                bootstrap.Modal.getOrCreateInstance(revokeModalEl).show();
            });

            ['revoke-modal-close', 'revoke-modal-cancel'].forEach(function (id) {
                document.getElementById(id).addEventListener('click', function () {
                    if (!revoking) bootstrap.Modal.getOrCreateInstance(revokeModalEl).hide();
                });
            });

            revokeBtn.addEventListener('click', function () {
                if (revoking) return;
                revoking = true;
                revokeBtn.disabled = true;
                revokeBtnSpinner.classList.remove('d-none');
                revokeBtnText.textContent = 'Révocation...';

                api().delete('/admin/users/' + userId + '/web-services')
                    .then(function () {
                        revoking = false;
                        revokeBtn.disabled = false;
                        revokeBtnSpinner.classList.add('d-none');
                        revokeBtnText.textContent = 'Révoquer';
                        bootstrap.Modal.getOrCreateInstance(revokeModalEl).hide();
                        showToast('Toutes les permissions ont été révoquées.');
                        loadAccess();
                    })
                    .catch(function (err) {
                        revoking = false;
                        revokeBtn.disabled = false;
                        revokeBtnSpinner.classList.add('d-none');
                        revokeBtnText.textContent = 'Révoquer';
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        var msg = 'Une erreur est survenue. Veuillez réessayer.';
                        if (status === 403) msg = 'Accès non autorisé.';
                        else if (body && body.message) msg = body.message;
                        revokeError.textContent = msg;
                        revokeError.classList.remove('d-none');
                    });
            });

            // ── Go ──
            loadUser();
        })();
    </script>
@endpush
