@push('styles')
    <style>
        @font-face {
            font-family: 'KeenIcons';
            font-display: swap;
            src: url('{{ asset('metronic/assets/plugins/global/fonts/keenicons/keenicons.ttf') }}') format('truetype');
        }

        .ki {
            font-family: 'KeenIcons';
            font-style: normal;
            font-weight: normal;
            line-height: 1;
            letter-spacing: 0;
            text-transform: none;
            display: inline-block;
            vertical-align: -0.125em;
        }

        .list.list-flush {
            gap: 0.5rem;
        }

        .list.list-flush>div {
            display: flex;
        }

        .list.list-flush .list-item {
            display: flex;
            width: 100%;
        }

        .domain-checkbox {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            margin: 0;
        }

        .domain-box {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            border: 1px solid var(--bs-border-color, #e5e7eb);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease;
            background-color: #fff;
        }

        .domain-box:hover {
            border-color: var(--bs-primary, #1266f1);
        }

        .domain-box:has(.domain-checkbox:checked) {
            border-color: var(--bs-primary, #1266f1) !important;
            background-color: #f0f6ff !important;
        }

        .domain-box .checkbox-circle {
            width: 1.15rem;
            height: 1.15rem;
            border-radius: 50%;
            border: 2px solid var(--bs-border-color, #d1d5db);
            flex: 0 0 auto;
            transition: border-color 0.15s ease, background-color 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .domain-box:has(.domain-checkbox:checked) .checkbox-circle {
            border-color: var(--bs-primary, #1266f1);
            background-color: var(--bs-primary, #1266f1);
        }

        .domain-box:has(.domain-checkbox:checked) .checkbox-circle::after {
            content: '';
            width: 6px;
            height: 10px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
            margin-top: -2px;
        }

        .domain-box:has(.domain-checkbox:indeterminate) {
            border-color: var(--bs-primary, #1266f1) !important;
            background-color: #f0f6ff !important;
        }

        .domain-box:has(.domain-checkbox:indeterminate) .checkbox-circle {
            border-color: var(--bs-primary, #1266f1);
            background-color: #fff;
        }

        .domain-box:has(.domain-checkbox:indeterminate) .checkbox-circle::after {
            content: '';
            width: 10px;
            height: 2px;
            background-color: var(--bs-primary, #1266f1);
            margin: 0;
        }

        .domain-box .domain-name {
            font-weight: 600;
            color: #111827;
            font-size: 0.875rem;
        }

        .domain-box .domain-meta {
            color: #6b7280;
            font-size: 0.75rem;
        }

        .domain-box .domain-code {
            font-family: ui-monospace, monospace;
        }

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

        .users-pagination {
            list-style: none;
            display: flex;
            gap: 0.25rem;
            padding: 0;
            margin: 0;
        }

        .users-pagination li {
            display: flex;
        }

        .users-pagination button {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
            height: 2.25rem;
            padding: 0 0.5rem;
            border: 1px solid var(--bs-border-color, #e5e7eb);
            border-radius: 0.375rem;
            background: #fff;
            color: #374151;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .users-pagination button:hover:not(:disabled) {
            border-color: var(--bs-primary, #1266f1);
            color: var(--bs-primary, #1266f1);
        }

        .users-pagination button.active {
            background-color: var(--bs-primary, #1266f1);
            border-color: var(--bs-primary, #1266f1);
            color: #fff;
        }

        .users-pagination button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
    </style>
@endpush

@extends('layouts.metronic')

@section('title', 'Utilisateurs')

@section('content')
    {{-- ── PAGE HEADING (Metronic card header style) ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-between gap-4 mb-6 mb-xl-8">
        <div class="d-flex align-items-center gap-3">
            <div class="symbol symbol-50px bg-light-primary bg-opacity-100 flex-shrink-0">
                <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-user fs-2"></i></span></span>
            </div>
            <div>
                <h2 class="fw-bold text-dark fs-3 mb-0">Utilisateurs</h2>
                <p class="text-muted fs-6 mb-0">Gérez les comptes, leurs rôles et leurs accès aux Web Services.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <button type="button" id="btn-new-user" class="btn btn-color-mine btn-active btn-primary fw-semibold">
                <span class="svg-icon svg-icon-2"><i class="ki-outline ki-user-square"></i></span>
                <span class="btn-text ms-2">Créer un utilisateur</span>
            </button>
        </div>
    </div>

    {{-- ── LOADING STATE (initial fetch) ── --}}
    <div id="auth-loading" class="alert alert-info d-none" role="status">
        <div class="alert-body d-flex align-items-center">
            <span class="spinner spinner-sm spinner-primary me-3"></span>
            <div>
                <h4 class="fs-6 fw-bolder text-dark mb-1">Vérification des accès</h4>
                <p class="fs-7 text-gray-700 mb-0">Chargement de la liste des utilisateurs...</p>
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
                    <p class="fs-7 text-gray-700 mb-0">Vous devez être administrateur pour gérer les utilisateurs.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── LOAD ERROR + RETRY ── --}}
    <div id="load-error" class="alert alert-danger d-none" role="alert">
        <div class="alert-body">
            <div class="d-flex align-items-center">
                <span class="svg-icon svg-icon-2x svg-icon-danger me-3"><i class="ki-outline ki-cloud-off"></i></span>
                <div>
                    <h4 class="fs-6 fw-bolder text-danger mb-1">Impossible de charger les utilisateurs</h4>
                    <p class="fs-7 text-gray-700 mb-0">Vérifiez votre connexion et réessayez.</p>
                </div>
            </div>
            <div class="alert-actions">
                <button type="button" id="btn-retry" class="btn btn-sm btn-light-danger">Réessayer</button>
            </div>
        </div>
    </div>

    {{-- ── TABLE CARD ── --}}
    <div class="card card-flush mb-6 mb-xl-8" id="users-table-card">
        <div class="card-header border-0">
            <div class="card-title">
                <h3 class="fw-bold text-dark fs-4">Liste des utilisateurs</h3>
                <span class="text-muted fw-semibold fs-7" id="users-total">0 compte</span>
            </div>
            <div class="card-toolbar gap-3 d-flex flex-wrap align-items-center">
                {{-- search (Metronic form-control) --}}
                <div class="input-group input-group-sm input-group-solid w-150px w-lg-200px me-2 me-md-0">
                    <input type="text" id="user-search" class="form-control border-0 fs-7" placeholder="Rechercher…"
                        aria-label="Rechercher un utilisateur" />
                </div>

                {{-- status filter --}}
                <select id="user-status-filter" class="form-select form-select-solid form-select-sm w-130px me-2 me-md-0"
                    aria-label="Filtrer par statut">
                    <option value="">Tous statuts</option>
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>

                <button type="button" id="btn-refresh-users" class="btn btn-sm btn-icon btn-light-primary me-0"
                    aria-label="Actualiser la liste">
                    <span class="svg-icon svg-icon-2 text-primary">

                        <i class="ki-outline ki-abstract-18 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>



                </button>
            </div>
        </div>

        <div class="card-body p-0" id="users-card-body">
            <div class="table-responsive">
                <table class="table table-row-hover align-middle" id="users-table" role="grid" aria-label="Utilisateurs">
                    <thead class="text-nowrap fw-bold fs-7">
                        <tr>
                            <th class="text-start ps-4 pe-2">Utilisateur</th>
                            <th class="text-start pe-2">Email / Identifiant</th>
                            <th class="text-start pe-2">Rôle</th>
                            <th class="text-start pe-2">Statut</th>
                            <th class="text-start pe-2 text-center d-none d-lg-table-cell">Web Services</th>
                            <th class="text-start pe-2 text-center d-none d-lg-table-cell">Accès effectif</th>
                            <th class="text-start pe-2 text-center d-none d-xl-table-cell">Dernière activité</th>
                            <th class="text-end pe-4 text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody"></tbody>
                </table>
            </div>

            {{-- Empty state --}}
            <div id="users-empty" class="d-none p-8 text-center">
                <span class="svg-icon svg-icon-3x svg-icon-muted d-block">
<i class="ki-outline ki-user">
 <span class="path1"></span>
 <span class="path2"></span>
</i>

                </span>
                <p id="users-empty-text" class="text-gray-500 fs-6 fw-bold mb-0">Aucun utilisateur trouvé.</p>
            </div>

            {{-- Pagination --}}
            <div id="users-pagination" class="card-footer d-none p-4 p-md-0 pt-0">
                <div id="users-pager" class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <span id="users-pager-info" class="text-muted fs-7">Page 1</span>
                    <ul id="users-pager-controls" class="users-pagination"></ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SUCCESS / ERROR TOAST ── --}}
    <div id="toast" class="alert alert-light-success d-none position-fixed top-0 end-0 m-5 shadow" role="status"
        aria-live="polite">
        <p id="toast-msg" class="fs-7 fw-bold text-success mb-0"></p>
    </div>

    {{-- ── CREATE USER MODAL (Metronic modal markup) ── --}}
    <div id="user-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="user-modal-title">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-radius-something shadow-lg">
                <div class="modal-header border-0 pt-5 pb-2 px-7">
                    <h5 class="fs-5 fw-bolder text-dark" id="user-modal-title">Créer un utilisateur</h5>
                    <button type="button" id="user-modal-close" class="btn-close" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-7 pb-5">
                    <div id="form-error" class="alert alert-danger py-3 d-none" role="alert"></div>

                    <form id="user-create-form" novalidate>
                        {{-- 1. Informations générales --}}
                        <div class="card card-flush mb-5">
                            <div class="card-header py-4">
                                <h4 class="card-title fs-6 fw-bolder text-dark">1. Informations générales</h4>
                            </div>
                            <div class="card-body p-5">
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <label for="nu-name" class="form-label fw-bold fs-7">Nom complet <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="nu-name" name="name" required maxlength="255"
                                            class="form-control form-control-solid fs-6" placeholder="Prénom Nom" />
                                        <p id="err-name" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="nu-email" class="form-label fw-bold fs-7">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" id="nu-email" name="email" required maxlength="255"
                                            class="form-control form-control-solid fs-6"
                                            placeholder="prenom.nom@anapec.ma" autocomplete="off" />
                                        <p id="err-email" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="nu-password" class="form-label fw-bold fs-7">Mot de passe <span id="pwd-req" class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="password" id="nu-password" name="password" required
                                                minlength="8" class="form-control form-control-solid fs-6"
                                                placeholder="8 caractères minimum" autocomplete="new-password" />
                                            <button type="button" id="nu-toggle-password"
                                                class="btn btn-icon btn-sm btn-light-primary"
                                                aria-label="Afficher ou masquer le mot de passe">
                                                <span class="svg-icon svg-icon-2 text-primary">
<i class="ki-outline ki-eye">
 <span class="path1"></span>
 <span class="path2"></span>
 <span class="path3"></span>
</i>

                                                </span>
                                            </button>
</div>
<p id="pwd-help" class="form-text text-muted fs-7 d-none">Laisser vide pour conserver le mot de passe actuel.</p>
<p id="err-password" class="form-message text-danger fw-semibold fs-7 d-none"></p>
                                    </div>
                                    <div class="col-lg-6">
<label for="nu-password-confirm" class="form-label fw-bold fs-7">Confirmation <span id="pwd-confirm-req" class="text-danger">*</span></label>
                                        <input type="password" id="nu-password-confirm" name="password_confirmation"
                                            required minlength="8" class="form-control form-control-solid fs-6"
                                            placeholder="Retapez le mot de passe" autocomplete="new-password" />
                                        <p id="err-password-confirm"
                                            class="form-message text-danger fw-semibold fs-7 d-none"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Statut & Rôle --}}
                        <div class="card card-flush mb-5">
                            <div class="card-header py-4">
                                <h4 class="card-title fs-6 fw-bolder text-dark">2. Statut &amp; Rôle</h4>
                            </div>
                            <div class="card-body p-5">
                                <div class="row g-4 align-items-center">
                                    <div class="col-md-6">
                                        <label for="nu-role" class="form-label fw-bold fs-7">Rôle</label>
                                        <select id="nu-role" name="role" class="form-select form-select-solid fs-6">
                                            <option value="user">Utilisateur</option>
                                            <option value="admin">Administrateur</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="form-label fw-bold fs-7 mb-0 d-block">Compte actif</span>
                                        <div class="mt-3">
                                            <label class="switch">
                                                <input type="checkbox" id="nu-active" name="is_active" checked />
                                                <span class="track"></span>
                                                <span class="handle"></span>
                                            </label>
                                            <span class="ms-2 text-muted fs-7" id="nu-active-label">Actif</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3-5. Web Services + Portée d'accès + Domaines --}}
                        <div class="card card-flush mb-5">
                            <div class="card-header py-4">
                                <h4 class="card-title fs-6 fw-bolder text-dark">3. Web Services — Portée d'accès</h4>
                            </div>
                            <div class="card-body p-5">
                                <div id="scope-radio" class="mb-5">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="list-group-item cursor-pointer d-flex align-items-start gap-3"
                                                style="border-radius: 0.5rem; border: 1px solid var(--bs-border-color, #e5e7eb);">
                                                <input type="radio" name="access_scope" value="all" checked
                                                    class="form-check-input mt-1" data-scope-radio />
                                                <span>
                                                    <span class="d-block fs-6 fw-bold text-dark">Tous les domaines</span>
                                                    <span class="d-block text-muted fs-7">
                                                        Accès automatique à tous les Web Services actifs de tous les
                                                        domaines,
                                                        y compris les domaines créés plus tard.
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="list-group-item cursor-pointer d-flex align-items-start gap-3"
                                                style="border-radius: 0.5rem; border: 1px solid var(--bs-border-color, #e5e7eb);">
                                                <input type="radio" name="access_scope" value="selected"
                                                    class="form-check-input mt-1" data-scope-radio />
                                                <span>
                                                    <span class="d-block fs-6 fw-bold text-dark">Domaines
                                                        sélectionnés</span>
                                                    <span class="d-block text-muted fs-7">
                                                        Accès uniquement aux domaines explicitement choisis ci-dessous.
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- 5. Domaines sélectionnés (only when scope = selected) --}}
                                <div id="domains-section" class="d-none">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                        <h5 class="fs-7 fw-bold text-dark mb-0">Domaines sélectionnés</h5>
                                        <span id="domains-counter" class="badge badge-light-primary fw-semibold fs-7">0
                                            sélectionné</span>
                                    </div>

                                    <div class="input-group input-group-solid mb-3">
                                        <input type="text" id="domain-search" class="form-control border-0 fs-7"
                                            placeholder="Rechercher un domaine…" aria-label="Rechercher un domaine" />
                                    </div>

                                    <div class="d-flex flex-wrap gap-3 mb-3">
                                        <button type="button" id="btn-select-all"
                                            class="btn btn-sm btn-light-primary fw-semibold">Tout sélectionner</button>
                                        <button type="button" id="btn-deselect-all"
                                            class="btn btn-sm btn-light-secondary fw-semibold">Tout désélectionner</button>
                                    </div>

                                    <div id="domains-list"
                                        class="list list-flush d-grid gap-2 max-h-350px overflow-auto scroll-me"></div>

                                    <div id="domains-empty" class="d-none p-4 text-center">
                                        <p id="domains-empty-text" class="text-muted fs-7 mb-0">Aucun domaine disponible.
                                        </p>
                                    </div>
                                </div>

                                {{-- 6. Vérification + 7. Confirmation --}}
                                <div id="summary-box" class="alert alert-light-info d-none mt-4 p-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="svg-icon svg-icon-2x svg-icon-info">

                                            <i class="ki-outline ki-shield">
 <span class="path1"></span>
 <span class="path2"></span>
</i>
                                            </span>
                                        <div>
                                            <p class="fs-6 fw-bolder text-dark mb-1">Récapitulatif</p>
                                            <ul id="summary-list" class="mb-0 ps-4 fs-7 text-gray-700"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="button" id="user-modal-cancel"
                                class="btn btn-light-secondary fw-semibold">Annuler</button>
                            <button type="submit" id="btn-create-user" class="btn btn-primary fw-semibold">
                                <span id="btn-create-user-spinner"
                                    class="spinner spinner-sm spinner-primary d-none me-2"></span>
                                <span id="btn-create-user-text">Créer l'utilisateur</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function() {
            var token = localStorage.getItem('anapec_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            // ── Helpers ──
            function get(url) {
                return fetch(url, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
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
                    }).then(function(res) {
                        return res.json().catch(function() {
                            return {};
                        }).then(function(body) {
                            if (res.ok) return {
                                data: body,
                                status: res.status
                            };
                            var err = new Error('HTTP ' + res.status);
                            err.status = res.status;
                            err.body = body;
                            err.response = {
                                status: res.status,
                                data: body
                            };
                            throw err;
                        });
                    });
                }
                return {
                    get: function(url) {
                        return req('GET', url);
                    },
                    post: function(url, data) {
                        return req('POST', url, data);
                    },
                    put: function(url, data) {
                        return req('PUT', url, data);
                    },
                    patch: function(url, data) {
                        return req('PATCH', url, data);
                    },
                    delete: function(url, data) {
                        return req('DELETE', url, data);
                    }
                };
            }

            function esc(v) {
                if (v === null || v === undefined) return '—';
                var d = document.createElement('div');
                d.textContent = String(v);
                return d.innerHTML;
            }

            function fmtLastActivity(iso) {
                if (!iso) return '<span class="text-muted fs-7">Jamais</span>';
                var d = new Date(iso);
                if (isNaN(d.getTime())) return '<span class="text-muted fs-7">—</span>';
                var diffMs = Date.now() - d.getTime();
                var min = Math.floor(diffMs / 60000);
                if (min < 1) return '<span class="fs-7">À l\'instant</span>';
                if (min < 60) return '<span class="fs-7">' + min + ' min</span>';
                var h = Math.floor(min / 60);
                if (h < 24) return '<span class="fs-7">' + h + ' h</span>';
                var days = Math.floor(h / 24);
                if (days < 7) return '<span class="fs-7">' + days + ' j</span>';
                return '<span class="text-muted fs-7">' + d.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }) + '</span>';
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
                t._timer = setTimeout(function() {
                    t.classList.add('d-none');
                }, 4000);
            }

            // ── Page state ──
            var PER_PAGE = 20;
            var state = {
                page: 1,
                lastPage: 1,
                total: 0,
                search: '',
                status: '',
                loading: false
            };
            var domains = [];
            var webServices = [];
            var selectedDomainData = {};

            function domainActive(code) {
                var m = {};
                domains.forEach(function(d) { m[d.code] = d; });
                return m[code] && m[code].is_active === true;
            }

            function activeWSOf(code) {
                var m = {};
                domains.forEach(function(d) { m[d.code] = d; });
                var d = m[code];
                if (!d) return [];
                var list = d.web_services || [];
                return list.filter(function(ws) {
                    return ws.is_active === true;
                });
            }

            var tbody = document.getElementById('users-tbody');
            var tableEl = document.getElementById('users-table');
            var emptyEl = document.getElementById('users-empty');
            var emptyText = document.getElementById('users-empty-text');
            var paginationEl = document.getElementById('users-pagination');
            var pagerInfo = document.getElementById('users-pager-info');
            var pagerControls = document.getElementById('users-pager-controls');
            var totalEl = document.getElementById('users-total');
            var loadError = document.getElementById('load-error');
            var accessDenied = document.getElementById('access-denied');
            var authLoading = document.getElementById('auth-loading');

            function roleBadge(role) {
                if (role === 'admin') {
                    return '<span class="badge badge-light-primary fw-semibold">Administrateur</span>';
                }
                return '<span class="badge badge-light-secondary fw-semibold">Utilisateur</span>';
            }

            function statusBadge(active) {
                if (active) {
                    return '<span class="badge badge-light-success fw-semibold"><span class="bullet bullet-dot bg-success me-1"></span>Actif</span>';
                }
                return '<span class="badge badge-light-danger fw-semibold"><span class="bullet bullet-dot bg-danger me-1"></span>Désactivé</span>';
            }

            function countBadge(n) {
                if (!n) return '<span class="text-muted fs-7">—</span>';
                return '<span class="badge badge-light-info fw-semibold">' + n + ' service' +
                    (n > 1 ? 's' : '') + '</span>';
            }

            function effectiveBadge(cfg, eff, total) {
                if (!cfg) return '<span class="text-muted fs-7">—</span>';
                var denom = total || cfg;
                var pct = Math.round((eff / denom) * 100);
                var cls = pct === 100 ? 'badge-light-success'
                    : pct === 0 ? 'badge-light-secondary' : 'badge-light-warning';
                return '<span class="badge ' + cls + ' fw-semibold">' + eff + ' / ' + denom +
                    ' actif' + (denom > 1 ? 's' : '') + '</span>';
            }

            function showAuthLoading(show) {
                authLoading.classList.toggle('d-none', !show);
            }

            function hideAuthLoading() {
                showAuthLoading(false);
            }

            function showSkeletonRows() {
                var rows = '';
                for (var i = 0; i < 8; i++) {
                    rows += '<tr>' +
                        '<td class="p-4 ps-4 pe-2"><span class="d-inline-block h-45px w-100px animate-pulse rounded bg-gray-200"></span></td>' +
                        '<td class="p-4 pe-2"><span class="d-inline-block h-45px w-150px animate-pulse rounded bg-gray-200"></span></td>' +
                        '<td class="p-4 pe-2"><span class="d-inline-block h-45px w-80px animate-pulse rounded bg-gray-200"></span></td>' +
                        '<td class="p-4 pe-2"><span class="d-inline-block h-45px w-80px animate-pulse rounded bg-gray-200"></span></td>' +
                        '<td class="p-4 pe-2"><span class="d-inline-block h-45px w-60px animate-pulse rounded bg-gray-200"></span></td>' +
                        '<td class="p-4 pe-2"><span class="d-inline-block h-45px w-60px animate-pulse rounded bg-gray-200"></span></td>' +
                        '<td class="p-4 pe-2"><span class="d-inline-block h-45px w-60px animate-pulse rounded bg-gray-200"></span></td>' +
                        '<td class="p-4 ps-2 pe-4"><span class="d-inline-block h-45px w-90px animate-pulse rounded bg-gray-200"></span></td>' +
                        '</tr>';
                }
                tbody.innerHTML = rows;
            }

            function initialFromName(name) {
                var parts = (name || '').trim().split(/\s+/);
                if (!parts.length) return '?';
                if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
            }

            function pickAvatarClass(name) {
                var classes = [
                    'bg-light-primary bg-opacity-100', 'bg-light-info bg-opacity-100',
                    'bg-light-success bg-opacity-100', 'bg-light-warning bg-opacity-100',
                    'bg-light-danger bg-opacity-100', 'bg-light-purple bg-opacity-100'
                ];
                var hash = 0;
                for (var i = 0; i < (name || '').length; i++) hash = ((hash << 5) - hash) + name.charCodeAt(i);
                return classes[Math.abs(hash) % classes.length];
            }

            function symbolText(name) {
                var colors = ['text-primary', 'text-info', 'text-success', 'text-warning', 'text-danger',
                    'text-purple'
                ];
                var hash = 0;
                for (var i = 0; i < (name || '').length; i++) hash = ((hash << 5) - hash) + name.charCodeAt(i);
                return colors[Math.abs(hash) % colors.length];
            }

            function renderRows(users) {
                if (!users.length) {
                    tableEl.classList.add('d-none');
                    emptyText.textContent = state.search ?
                        'Aucun utilisateur ne correspond à votre recherche.' :
                        'Aucun utilisateur trouvé.';
                    emptyEl.classList.remove('d-none');
                    paginationEl.classList.add('d-none');
                    totalEl.textContent = '0 compte';
                    return;
                }
                tableEl.classList.remove('d-none');
                emptyEl.classList.add('d-none');

                var rows = '';
                for (var i = 0; i < users.length; i++) {
                    var u = users[i];
                    var lastAct = fmtLastActivity(u.last_activity_at);
                    rows += '<tr>' +
                        '<td class="p-4 ps-4 pe-2">' +
                        '<div class="d-flex align-items-center gap-3">' +
                        '<div class="symbol symbol-45px ' + pickAvatarClass(u.name) + ' flex-shrink-0">' +
                        '<span class="symbol-label text-start fs-7 fw-bolder ' + symbolText(u.name) + '">' +
                        initialFromName(u.name) + '</span>' +
                        '</div>' +
                        '<div class="min-w-0">' +
                        '<div class="text-dark fw-bolder fs-7">' + esc(u.name) + '</div>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '<td class="p-4 pe-2"><span class="text-dark fs-7">' + esc(u.email) + '</span></td>' +
                        '<td class="p-4 pe-2">' + roleBadge(u.role) + '</td>' +
                        '<td class="p-4 pe-2">' + statusBadge(u.is_active === true) + '</td>' +
                        '<td class="p-4 pe-2 text-center d-none d-lg-table-cell">' + countBadge(u.web_services_count) +
                        '</td>' +
                        '<td class="p-4 pe-2 text-center d-none d-lg-table-cell">' + effectiveBadge(u
                            .web_services_count, u.effective_services_count) + '</td>' +
                        '<td class="p-4 pe-2 text-center d-none d-xl-table-cell">' + lastAct + '</td>' +
                        '<td class="p-4 ps-2 pe-4 text-end">' +
                        '<div class="dropdown d-inline-block">' +
                        '<button type="button" class="btn btn-sm btn-icon btn-light-primary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions pour ' +
                        esc(u.name) + '">' +
                        '<span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-more-2"></i></span>' +
                        '</button>' +
                        '<div class="dropdown-menu dropdown-menu-end py-2 fw-normal w-200px">' +
                        '<a class="d-flex align-items-center mb-1 px-3 py-2 text-decoration-none text-dark hover-bg-light hover-color-dark rounded" href="/admin/users/' +
                        u.id + '">' +
                        '<span class="svg-icon svg-icon-2 text-gray-500 me-2"><i class="ki-outline ki-element-11"></i></span>' +
                        '<span class="fs-7 fw-semibold">Consulter</span>' +
                        '</a>' +
                        '<a class="d-flex align-items-center mb-1 px-3 py-2 text-decoration-none text-dark hover-bg-light hover-color-dark rounded" data-edit-user="' +
                        u.id + '" href="javascript:void(0);">' +
                        '<span class="svg-icon svg-icon-2 text-gray-500 me-2"><i class="ki-outline ki-setting"></i></span>' +
                        '<span class="fs-7 fw-semibold">Modifier</span>' +
                        '</a>' +
                        '<button type="button" data-toggle-user="' + u.id + '" data-next="' + (u.is_active === true ?
                            'false' : 'true') +
                        '" class="d-flex align-items-center mb-1 px-3 py-2 border-0 bg-transparent text-start hover-bg-light rounded w-100">' +
                        '<span class="svg-icon svg-icon-2 text-' + (u.is_active === true ? 'danger' : 'success') +
                        ' me-2"><i class="ki-outline ki-toggle-' + (u.is_active === true ? 'off' : 'on') +
                        '"></i></span>' +
                        '<span class="fs-7 fw-semibold">' + (u.is_active === true ? 'Désactiver' : 'Activer') +
                        '</span>' +
                        '</button>' +
                        '<a class="d-flex align-items-center px-3 py-2 text-decoration-none text-dark hover-bg-light hover-color-dark rounded" href="/admin/users/' +
                        u.id + '">' +
                        '<i class="svg-icon svg-icon-2 text-gray-500 me-2"><span class="ki-outline ki-shield"></span></i>' +
                        '<span class="fs-7 fw-semibold">Gérer les accès</span>' +
                        '</a>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';
                }
                tbody.innerHTML = rows;
                initEditActionButtons();
                tbody.querySelectorAll('[data-toggle-user]').forEach(function(b) {
                    b.addEventListener('click', function() {
                        toggleUserStatus(b.getAttribute('data-toggle-user'), b.getAttribute(
                            'data-next') === 'true', b);
                    });
                });
            }

            function renderPagination() {
                if (state.lastPage <= 1) {
                    paginationEl.classList.add('d-none');
                    return;
                }
                paginationEl.classList.remove('d-none');
                var from = (state.page - 1) * PER_PAGE + 1;
                var to = Math.min(state.page * PER_PAGE, state.total);
                pagerInfo.textContent = 'Page ' + state.page + ' / ' + state.lastPage + ' — ' + from + ' à ' + to +
                    ' sur ' + state.total;

                var html = '<li><button type="button" ' + (state.page <= 1 ? 'disabled' : '') + ' data-page="' + (state
                    .page - 1) + '" aria-label="Page précédente">‹</button></li>';
                var pages = Math.min(state.lastPage, 7);
                var start = Math.max(1, state.page - 3);
                var end = Math.min(state.lastPage, start + pages - 1);
                start = Math.max(1, end - pages + 1);
                if (start > 1) html += '<li><button type="button" data-page="1">1</button></li>';
                for (var p = start; p <= end; p++) {
                    html += '<li><button type="button" class="' + (p === state.page ? 'active' : '') + '" data-page="' +
                        p + '">' + p + '</button></li>';
                }
                if (end < state.lastPage) html += '<li><button type="button" data-page="' + state.lastPage + '">' +
                    state.lastPage + '</button></li>';
                html += '<li><button type="button" ' + (state.page >= state.lastPage ? 'disabled' : '') +
                    ' data-page="' + (state.page + 1) + '" aria-label="Page suivante">›</button></li>';
                pagerControls.innerHTML = html;

                pagerControls.querySelectorAll('button[data-page]').forEach(function(b) {
                    b.addEventListener('click', function() {
                        var target = parseInt(b.getAttribute('data-page'), 10);
                        if (target && target !== state.page && target >= 1 && target <= state
                            .lastPage) {
                            state.page = target;
                            loadUsers();
                        }
                    });
                });
            }

            function loadUsers(opts) {
                opts = opts || {};
                hideAuthLoading();
                state.loading = true;
                loadError.classList.add('d-none');
                totalEl.textContent = 'Chargement…';
                showSkeletonRows();
                emptyEl.classList.add('d-none');
                tableEl.classList.remove('d-none');
                paginationEl.classList.add('d-none');

                var url = '/api/v1/admin/users?per_page=' + PER_PAGE + '&page=' + state.page;
                if (state.search) url += '&search=' + encodeURIComponent(state.search);
                if (state.status) url += '&status=' + state.status;

                get(url)
                    .then(function(res) {
                        if (res.status === 401) {
                            localStorage.removeItem('anapec_token');
                            localStorage.removeItem('anapec_user');
                            window.location.href = '/login';
                            return false;
                        }
                        if (res.status === 403) {
                            showAccessDenied();
                            return false;
                        }
                        if (!res.ok) {
                            if (!opts.retryDone && res.status >= 500) {
                                return new Promise(function(resolve) {
                                    setTimeout(function() {
                                        loadUsers({ retryDone: true }).then(resolve);
                                    }, 500);
                                });
                            }
                            showLoadError();
                            return false;
                        }
                        return res.json();
                    })
                    .then(function(data) {
                        if (data === false) return;
                        state.loading = false;
                        if (!data || !data.success || !data.data) {
                            showLoadError();
                            return;
                        }
                        loadError.classList.add('d-none');
                        var d = data.data;
                        state.total = d.total;
                        state.lastPage = d.last_page;
                        totalEl.textContent = d.total + ' compte' + (d.total > 1 ? 's' : '');
                        renderRows(d.data);
                        renderPagination();
                    })
                    .catch(function() {
                        state.loading = false;
                        if (!opts.retryDone) {
                            setTimeout(function() {
                                loadUsers({ retryDone: true });
                            }, 500);
                        } else {
                            showLoadError();
                        }
                    });
            }

            function showLoadError() {
                hideAuthLoading();
                tbody.innerHTML = '';
                tableEl.classList.add('d-none');
                emptyEl.classList.add('d-none');
                paginationEl.classList.add('d-none');
                totalEl.textContent = '—';
                loadError.classList.remove('d-none');
            }

            function showAccessDenied() {
                hideAuthLoading();
                accessDenied.classList.remove('d-none');
                tbody.innerHTML = '';
                tableEl.classList.add('d-none');
                emptyEl.classList.add('d-none');
                paginationEl.classList.add('d-none');
            }

            function toggleUserStatus(id, next, btn) {
                var client = api();
                if (!client) {
                    showToast('Client API introuvable.', 'error');
                    return;
                }
                btn.disabled = true;
                client.patch('/admin/users/' + id + '/status', {
                        is_active: next
                    })
                    .then(function() {
                        showToast(next ? 'Utilisateur activé.' : 'Utilisateur désactivé.');
                        loadUsers();
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        var msg = 'Une erreur est survenue. Veuillez réessayer.';
                        if (status === 403) msg = 'Accès non autorisé.';
                        else if (body && body.message) msg = body.message;
                        showToast(msg, 'error');
                    });
            }

            // ── Search + filter wiring ──
            var searchEl = document.getElementById('user-search');
            var statusEl = document.getElementById('user-status-filter');
            var refreshBtn = document.getElementById('btn-refresh-users');
            var searchDebounce;
            searchEl.addEventListener('input', function() {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(function() {
                    state.search = searchEl.value.trim();
                    state.page = 1;
                    loadUsers();
                }, 300);
            });
            statusEl.addEventListener('change', function() {
                state.status = statusEl.value;
                state.page = 1;
                loadUsers();
            });
            refreshBtn.addEventListener('click', loadUsers);
            document.getElementById('btn-retry').addEventListener('click', loadUsers);

            // ── Auth gate ──
            showAuthLoading(true);
            get('/api/v1/auth/me')
                .then(function(res) {
                    if (res.status === 401) {
                        localStorage.removeItem('anapec_token');
                        localStorage.removeItem('anapec_user');
                        window.location.href = '/login';
                        return false;
                    }
                    if (!res.ok) {
                        showLoadError();
                        return false;
                    }
                    return res.json();
                })
                .then(function(data) {
                    if (data === false) return;
                    if (!data || !data.success || !data.data) {
                        showLoadError();
                        return;
                    }
                    var user = data.data;
                    localStorage.setItem('anapec_user', JSON.stringify(user));
                    if (user.role !== 'admin') {
                        showAccessDenied();
                        setTimeout(function() {
                            window.location.replace('/dashboard');
                        }, 1200);
                        return;
                    }
                    loadUsers();
                    ensureCatalogs(function() {});
                })
                .catch(function() {
                    showLoadError();
                });

            // ════════════════════════════════════════════════════
            //  CREATE USER MODAL
            // ════════════════════════════════════════════════════
            var btnNew = document.getElementById('btn-new-user');
            var modal = document.getElementById('user-modal');
            var createModal = bootstrap.Modal.getOrCreateInstance(modal);
            var modalClose = document.getElementById('user-modal-close');
            var modalCancel = document.getElementById('user-modal-cancel');
            var createForm = document.getElementById('user-create-form');
            var formError = document.getElementById('form-error');
            var submitBtn = document.getElementById('btn-create-user');
            var submitText = document.getElementById('btn-create-user-text');
            var submitSpinner = document.getElementById('btn-create-user-spinner');
            var togglePwd = document.getElementById('nu-toggle-password');
            var pwdInput = document.getElementById('nu-password');
            var pwdConfirm = document.getElementById('nu-password-confirm');
            var activeCb = document.getElementById('nu-active');
            var activeLabel = document.getElementById('nu-active-label');
            var domainSearchEl = document.getElementById('domain-search');
            var domainsListEl = document.getElementById('domains-list');
            var domainsEmptyEl = document.getElementById('domains-empty');
            var domainsEmptyText = document.getElementById('domains-empty-text');
            var domainsSection = document.getElementById('domains-section');
            var domainsCounter = document.getElementById('domains-counter');
            var summaryBox = document.getElementById('summary-box');
            var summaryList = document.getElementById('summary-list');
            var selectAllBtn = document.getElementById('btn-select-all');
            var deselectAllBtn = document.getElementById('btn-deselect-all');

            var lastFocused = null;
            var submitting = false;
            var isEditMode = false;
            var editingUserId = null;

            function clearFieldErrors() {
                ['name', 'email', 'password', 'password-confirm'].forEach(function(f) {
                    var el = document.getElementById('err-' + f);
                    if (el) el.classList.add('d-none');
                });
                formError.classList.add('d-none');
            }

            function setFieldError(name, msg) {
                var el = document.getElementById('err-' + name);
                if (!el) return;
                el.textContent = msg;
                el.classList.remove('d-none');
            }

            function setModalMode(mode) {
                isEditMode = (mode === 'edit');
                var title = document.getElementById('user-modal-title');
                var btnText = document.getElementById('btn-create-user-text');
                var pwdReq = document.getElementById('pwd-req');
                var pwdConfirmReq = document.getElementById('pwd-confirm-req');
                var pwdHelp = document.getElementById('pwd-help');
                if (isEditMode) {
                    title.textContent = 'Modifier un utilisateur';
                    btnText.textContent = "Enregistrer les modifications";
                    pwdReq.classList.add('d-none');
                    pwdConfirmReq.classList.add('d-none');
                    pwdHelp.classList.remove('d-none');
                    pwdInput.removeAttribute('required');
                    pwdConfirm.removeAttribute('required');
                } else {
                    title.textContent = 'Créer un utilisateur';
                    btnText.textContent = "Créer l'utilisateur";
                    pwdReq.classList.remove('d-none');
                    pwdConfirmReq.classList.remove('d-none');
                    pwdHelp.classList.add('d-none');
                    pwdInput.setAttribute('required', '');
                    pwdConfirm.setAttribute('required', '');
                }
            }

            function openModal() {
                lastFocused = document.activeElement;
                createForm.reset();
                clearFieldErrors();
                setModalMode('create');
                editingUserId = null;
                pwdInput.type = 'password';
                activeCb.checked = true;
                activeLabel.textContent = 'Actif';
                selectedDomainData = {};
                setScopeRadio('all');
                selectAllScope();
                loadCatalog(function() {
                    renderDomainList('');
                    updateDomainCounter();
                });
                createModal.show();
                document.body.style.overflow = 'hidden';
                document.getElementById('nu-name').focus();
            }

            function closeModal() {
                createModal.hide();
                document.body.style.overflow = '';
                if (lastFocused && lastFocused.focus) lastFocused.focus();
                setModalMode('create');
                editingUserId = null;
            }

            function loadCatalog(cb) {
                get('/api/v1/admin/users/create-scope')
                    .then(function(res) {
                        if (!res.ok) return null;
                        return res.json();
                    })
                    .then(function(data) {
                        if (!data || !data.success || !Array.isArray(data.data)) return;
                        domains = data.data;
                        cb();
                    })
                    .catch(function() {});
            }

            function currentScope() {
                var r = document.querySelector('input[name="access_scope"]:checked');
                return r ? r.value : 'all';
            }

            function setScopeUI(showDomains) {
                if (showDomains) {
                    domainsSection.classList.remove('d-none');
                } else {
                    domainsSection.classList.add('d-none');
                }
            }

            function selectAllScope() {
                selectedDomainData = {};
                setScopeUI(false);
                updateDomainCounter();
                updateSummary();
            }

            function selectSelectedScope() {
                setScopeUI(true);
                renderDomainList(domainSearchEl.value);
                updateDomainCounter();
                updateSummary();
            }

            function setScopeRadio(value) {
                var allR = document.querySelector('[data-scope-radio][value="all"]');
                var selR = document.querySelector('[data-scope-radio][value="selected"]');
                if (allR) allR.checked = value === 'all';
                if (selR) selR.checked = value === 'selected';
            }

            function applyScopeValue(value) {
                if (value === 'all') {
                    selectAllScope();
                } else {
                    selectSelectedScope();
                }
            }

            function updateScopeUI() {
                applyScopeValue(currentScope());
            }
            document.querySelectorAll('[data-scope-radio]').forEach(function(r) {
                r.addEventListener('change', updateScopeUI);
            });

            function renderDomainList(query) {
                var q = (query || '').trim().toLowerCase();
                var visible = domains.filter(function(d) {
                    if (!q) return true;
                    var hay = ((d.code || '') + ' ' + (d.name || '')).toLowerCase();
                    return hay.indexOf(q) !== -1;
                });
                if (!visible.length) {
                    domainsListEl.innerHTML = '';
                    domainsEmptyEl.classList.remove('d-none');
                    domainsEmptyText.textContent = q ?
                        'Aucun domaine ne correspond à « ' + query + ' ».' :
                        'Aucun domaine disponible.';
                    return;
                }
                domainsEmptyEl.classList.add('d-none');
                var html = '';
                visible.forEach(function(d) {
                    var entry = selectedDomainData[d.code];
                    var isDomainSelected = !!entry;
                    var domainWS = activeWSOf(d.code);
                    var wsState = selectedWsState(d.code);
                    var total = domainWS.length;
                    var selectedCount = countSelectedWs(d.code);
                    var allChecked = total > 0 && selectedCount === total;
                    var someChecked = selectedCount > 0 && !allChecked;

                    var activeCount = (d.web_services || []).filter(function(ws) {
                        return ws.is_active === true;
                    }).length;

                    html += '<div class="list-item" data-domain-code="' + esc(d.code) + '">' +
                        '<label class="domain-box">' +
                        '<input type="checkbox" class="domain-checkbox domain-toggle" data-domain-code="' +
                        esc(d.code) + '" ' +
                        (isDomainSelected ? 'checked ' : '') +
                        'aria-label="Sélectionner ' + esc(d.name) + '" />' +
                        '<span class="checkbox-circle"></span>' +
                        '<span class="domain-name">' + esc(d.name) + '</span>' +
                        '<span class="domain-meta">' +
                        '<span class="domain-code me-2">' + esc(d.code) + '</span>' +
                        '<span>' + activeCount + ' / ' + (d.service_count || 0) + ' Web Services actifs</span>' +
                        '</span>' +
                        (d.is_active === true ?
                            '<span class="badge badge-light-success fw-semibold ms-auto">Actif</span>' :
                            '<span class="badge badge-light-secondary fw-semibold ms-auto">Inactif</span>') +
                        '</label>' +
                        '</div>';

                    if (isDomainSelected) {
                        var wsSuffix = total > 1 ? 's' : '';
                        var wsLabel = selectedCount + ' / ' + total + ' sélectionné' + wsSuffix;
                        html += '<div class="ws-group ms-4 mt-1 mb-2 ps-3" style="border-left: 2px solid var(--bs-primary, #1266f1);">';
                        html += '<label class="domain-box mb-2" style="background-color: #f8f9fa;">' +
                            '<input type="checkbox" class="domain-checkbox ws-select-all" data-domain-code="' + esc(d.code) +
                            '" ' + (allChecked ? 'checked ' : '') +
                            (someChecked ? 'data-indeterminate="true" ' : '') +
                            'aria-label="Tous les Web Services de ' + esc(d.name) + '" />' +
                            '<span class="checkbox-circle"></span>' +
                            '<span>' +
                            '<span class="domain-name fw-semibold d-block">Tous les Web Services' +
                            (activeCount < (d.service_count || 0) ? ' actifs' : '') + '</span>' +
                            '<span class="badge badge-light-primary fs-7 mt-1">' + wsLabel + '</span>' +
                            '</span>' +
                            '</label>';
                        if (total === 0) {
                            html += '<div class="text-muted fs-7 ms-3 mb-2">Ce domaine ne contient aucun Web Service actif.</div>';
                        }
                        domainWS.forEach(function(ws) {
                            var wsSel = wsState[ws.code] === true;
                            html += '<label class="domain-box ms-3 mb-2" style="background-color: #fff;">' +
                                '<input type="checkbox" class="domain-checkbox ws-item" data-domain-code="' + esc(d.code) +
                                '" data-ws-code="' + esc(ws.code) +
                                '" ' + (wsSel ? 'checked ' : '') +
                                'aria-label="' + esc(ws.name || ws.code) + '" />' +
                                '<span class="checkbox-circle"></span>' +
                                '<span class="domain-name">' + esc(ws.name || ws.code) + '</span>' +
                                '<span class="domain-meta">' +
                                '<span class="domain-code me-2">' + esc(ws.code) + '</span>' +
                                '</span>' +
                                '<span class="badge badge-light-success fw-semibold ms-auto">Actif</span>' +
                                '</label>';
                        });
                        html += '</div>';
                    }
                });
                domainsListEl.innerHTML = html;

                domainsListEl.querySelectorAll('.domain-toggle').forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        var code = cb.getAttribute('data-domain-code');
                        if (cb.checked) {
                            setSelectedDomainWs(code, {});
                        } else {
                            delete selectedDomainData[code];
                        }
                        renderDomainList(domainSearchEl.value);
                        updateDomainCounter();
                        updateSummary();
                    });
                });

                domainsListEl.querySelectorAll('.ws-select-all').forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        var code = cb.getAttribute('data-domain-code');
                        if (!selectedDomainData[code]) return;
                        activeWSOf(code).forEach(function(ws) {
                            selectedDomainData[code].webServices[ws.code] = cb.checked === true;
                        });
                        renderDomainList(domainSearchEl.value);
                        updateDomainCounter();
                        updateSummary();
                    });
                });

                domainsListEl.querySelectorAll('.ws-item').forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        var code = cb.getAttribute('data-domain-code');
                        var wsCode = cb.getAttribute('data-ws-code');
                        if (!selectedDomainData[code]) return;
                        selectedDomainData[code].webServices[wsCode] = cb.checked === true;
                        renderDomainList(domainSearchEl.value);
                        updateDomainCounter();
                        updateSummary();
                    });
                });

                domainsListEl.querySelectorAll('.ws-select-all[data-indeterminate="true"]').forEach(function(cb) {
                    cb.indeterminate = true;
                });
            }

            // ── Web Service state (single source of truth: `selectedDomainData`) ──
            function selectedWsState(code) {
                var entry = selectedDomainData[code];
                return (entry && entry.webServices) || {};
            }

            // The enabled domain grants, without the internal `__scope` marker.
            function getSelectedDomainCodes() {
                return Object.keys(selectedDomainData).filter(function(code) {
                    return code !== '__scope';
                });
            }

            function countSelectedWs(code) {
                return activeWSOf(code).filter(function(ws) {
                    return selectedWsState(code)[ws.code] === true;
                }).length;
            }

            function setSelectedDomainWs(code, map) {
                selectedDomainData[code] = { selected: true, webServices: map || {} };
            }

            function allActiveWsCount() {
                var n = 0;
                domains.forEach(function(d) { n += activeWSOf(d.code).length; });
                return n;
            }

            // Services the modal writes to the API. Inactive services are never
            // sent: their rows are preserved so a permission becomes effective
            // again the moment the service is reactivated.
            function wsPatchPlan() {
                var plan = [];
                var seen = {};
                getSelectedDomainCodes().forEach(function(code) {
                    activeWSOf(code).forEach(function(ws) {
                        if (seen[ws.code]) return;
                        seen[ws.code] = true;
                        plan.push({
                            code: ws.code,
                            enabled: selectedWsState(code)[ws.code] === true
                        });
                    });
                });
                return plan;
            }

            function clearSelection() {
                selectedDomainData = {};
            }

            function fillAllDomainsAndServices() {
                domains.forEach(function(d) {
                    var state = {};
                    activeWSOf(d.code).forEach(function(w) { state[w.code] = true; });
                    setSelectedDomainWs(d.code, state);
                });
            }

            // The modal's state is rebuilt from the backend only: the enabled
            // domain grants define which domains are in scope, the per-service
            // rows define which Web Services are enabled. The counter, the
            // "Tous les Web Services" control and the checkboxes all read from
            // this single map.
            function buildEditState(scopeData) {
                selectedDomainData = {};
                var scope = scopeData.access_scope === 'all' ? 'all' : 'selected';

                if (scope === 'all') {
                    selectedDomainData.__scope = 'all';
                    return selectedDomainData;
                }

                var row = {};
                (scopeData.web_services || []).forEach(function(w) {
                    row[w.code] = w;
                });

                if (Array.isArray(scopeData.domains)) {
                    scopeData.domains.forEach(function(domain) {
                        if (!domain || domain.is_enabled !== true) return;
                        var dom = domains.find(function(d) { return d.code === domain.code; });
                        if (!dom) return;

                        var state = {};
                        (dom.web_services || []).forEach(function(ws) {
                            state[ws.code] = !!(row[ws.code] && row[ws.code].is_enabled === true);
                        });

                        setSelectedDomainWs(domain.code, state);
                    });
                }

                // If the user has enabled web service rows but no domain grants,
                // build domain entries from the catalog so the services are visible.
                if (!Object.keys(selectedDomainData).filter(function(k) { return k !== '__scope'; }).length) {
                    var enabledCodes = Object.keys(row).filter(function(code) {
                        return row[code] && row[code].is_enabled === true;
                    });
                    if (enabledCodes.length) {
                        domains.forEach(function(d) {
                            var domServices = d.web_services || [];
                            var matched = domServices.filter(function(ws) {
                                return enabledCodes.indexOf(ws.code) !== -1;
                            });
                            if (!matched.length) return;
                            var state = {};
                            domServices.forEach(function(ws) {
                                state[ws.code] = enabledCodes.indexOf(ws.code) !== -1;
                            });
                            setSelectedDomainWs(d.code, state);
                        });
                    }
                }

                selectedDomainData.__scope = 'selected';
                return selectedDomainData;
            }

            function updateDomainCounter() {
                var n = getSelectedDomainCodes().length;
                domainsCounter.textContent = n + ' sélectionné' + (n > 1 ? 's' : '') + ' sur ' + domains.length;
            }

            function updateSummary() {
                buildSummary();
            }

            selectAllBtn.addEventListener('click', function() {
                fillAllDomainsAndServices();
                renderDomainList(domainSearchEl.value);
                updateDomainCounter();
                updateSummary();
            });

            deselectAllBtn.addEventListener('click', function() {
                clearSelection();
                renderDomainList(domainSearchEl.value);
                updateDomainCounter();
                updateSummary();
            });

            domainSearchEl.addEventListener('input', function() {
                renderDomainList(domainSearchEl.value);
            });

            function buildSummary() {
                var items = [];
                var role = document.getElementById('nu-role').value;
                var scope = currentScope();
                var active = allActiveWsCount();
                items.push('Portée d\'accès : ' + (scope === 'all' ?
                    'tous les domaines (y compris futurs) — ' + active + ' Web Service' +
                    (active > 1 ? 's' : '') + ' actif' + (active > 1 ? 's' : '') +
                    ' actuellement' :
                    'domaines sélectionnés ci-dessous'));
                if (scope !== 'all') {
                    getSelectedDomainCodes().forEach(function(code) {
                        var domain = domains.find(function(d) { return d.code === code; });
                        var domainName = domain ? domain.name : code;
                        var wsCount = countSelectedWs(code);
                        var total = activeWSOf(code).length;
                        items.push('  • ' + domainName + ' : ' + wsCount + ' / ' + total +
                            ' Web Service' + (total > 1 ? 's' : '') + ' sélectionné' + (wsCount > 1 ? 's' : ''));
                    });
                }
                var totalSelected = 0;
                getSelectedDomainCodes().forEach(function(code) {
                    totalSelected += countSelectedWs(code);
                });
                items.push('Rôle : ' + (role === 'admin' ? 'Administrateur' : 'Utilisateur'));
                items.push('Statut : ' + (activeCb.checked ? 'Actif' : 'Désactivé'));
                items.push('Accès au total : ' + (scope === 'all' ? active : totalSelected) + ' / ' + active +
                    ' Web Service' + (active > 1 ? 's' : '') + ' actif' + (active > 1 ? 's' : ''));
                summaryBox.classList.toggle('d-none', scope !== 'selected');
                summaryList.innerHTML = items.map(function(s) {
                    return '<li class="mb-1">' + esc(s) + '</li>';
                }).join('');
            }

            function clientSideValidate() {
                clearFieldErrors();
                var ok = true;
                var name = document.getElementById('nu-name').value.trim();
                var email = document.getElementById('nu-email').value.trim();
                var password = pwdInput.value;

                if (!name) {
                    setFieldError('name', 'Le nom est requis.');
                    ok = false;
                }
                if (!email) {
                    setFieldError('email', "L'email est requis.");
                    ok = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    setFieldError('email', 'Format d\'email invalide.');
                    ok = false;
                }
                if (password) {
                    if (password.length < 8) {
                        setFieldError('password', 'Au moins 8 caractères requis.');
                        ok = false;
                    }
                    if (password !== pwdConfirm.value) {
                        setFieldError('password-confirm', 'Les mots de passe ne correspondent pas.');
                        ok = false;
                    }
                } else if (!isEditMode) {
                    setFieldError('password', 'Le mot de passe est requis.');
                    ok = false;
                }

                if (ok && currentScope() === 'selected' && !getSelectedDomainCodes().length) {
                    formError.textContent = 'Sélectionnez au moins un domaine ou passez en « Tous les domaines ».';
                    formError.classList.remove('d-none');
                    ok = false;
                }
                return ok;
            }

            if (btnNew) btnNew.addEventListener('click', openModal);
            if (modalClose) modalClose.addEventListener('click', closeModal);
            if (modalCancel) modalCancel.addEventListener('click', closeModal);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('d-none')) {
                    closeModal();
                    if (btnNew) btnNew.focus();
                }
            });

            activeCb.addEventListener('change', function() {
                activeLabel.textContent = activeCb.checked ? 'Actif' : 'Désactivé';
            });

            if (togglePwd) {
                togglePwd.addEventListener('click', function() {
                    var hidden = pwdInput.type === 'password';
                    pwdInput.type = hidden ? 'text' : 'password';
                    togglePwd.setAttribute('aria-label', hidden ? 'Masquer le mot de passe' :
                        'Afficher le mot de passe');
                });
            }

            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (submitting) return;
                    if (!clientSideValidate()) return;

                    var payload = {
                        name: document.getElementById('nu-name').value.trim(),
                        email: document.getElementById('nu-email').value.trim(),
                        role: document.getElementById('nu-role').value,
                        is_active: activeCb.checked === true,
                        access_scope: currentScope()
                    };
                    if (pwdInput.value) {
                        payload.password = pwdInput.value;
                    } else if (!isEditMode) {
                        payload.password = pwdInput.value;
                    }

                    submitting = true;
                    submitBtn.disabled = true;
                    submitSpinner.classList.remove('d-none');
                    submitText.textContent = isEditMode ? 'Modification...' : 'Création...';

                    var client = api();
                    if (!client) {
                        submitting = false;
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('d-none');
                        submitText.textContent = isEditMode ? "Enregistrer les modifications" : "Créer l'utilisateur";
                        formError.textContent = 'Client API introuvable.';
                        formError.classList.remove('d-none');
                        return;
                    }

                    var apiCall = isEditMode
                        ? client.put('/admin/users/' + editingUserId, payload)
                        : client.post('/admin/users', payload);

                    apiCall.then(function(res) {
                        var body = res && res.data;
                        var created = body && body.data;
                        var targetId = isEditMode ? editingUserId : (created && created.id);
                        var scope = payload.access_scope;

                        if (!targetId || scope === 'all') {
                            return res;
                        }

                        var plan = wsPatchPlan();
                        var selectedCodes = getSelectedDomainCodes();
                        var wsSteps = [];
                        plan.forEach(function(item) {
                            wsSteps.push(client.patch(
                                '/admin/users/' + targetId + '/web-services/' +
                                encodeURIComponent(item.code),
                                { is_enabled: item.enabled === true }
                            ));
                        });

                        var steps = [];
                        if (selectedCodes.length) {
                            steps.push(client.put('/admin/users/' + targetId + '/domains', {
                                domains: selectedCodes
                            }));
                        }
                        if (wsSteps.length) {
                            steps.push(Promise.all(wsSteps));
                        }
                        if (steps.length) {
                            return steps.reduce(function(chain, p) {
                                return chain.then(function() { return p; });
                            }, Promise.resolve()).then(function() {
                                return res;
                            });
                        }
                        return res;
                    })
                    .then(function() {
                        submitting = false;
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('d-none');
                        submitText.textContent = isEditMode ? "Enregistrer les modifications" : "Créer l'utilisateur";
                        closeModal();
                        showToast(isEditMode ? 'Utilisateur modifié avec succès.' : 'Utilisateur créé avec succès.');
                        loadUsers();
                    })
                    .catch(function(err) {
                        submitting = false;
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('d-none');
                        submitText.textContent = isEditMode ? "Enregistrer les modifications" : "Créer l'utilisateur";
                        var status = err.status || (err.response ? err.response.status : null);
                        if (status === 401) {
                            localStorage.removeItem('anapec_token');
                            localStorage.removeItem('anapec_user');
                            window.location.href = '/login';
                            return;
                        }
                        if (status === 403) {
                            formError.textContent = 'Accès non autorisé.';
                            formError.classList.remove('d-none');
                            return;
                        }
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 422 && body && body.errors) {
                            var has = false;
                            var map = {
                                'name': 'name',
                                'email': 'email',
                                'role': 'role',
                                'password': 'password',
                                'is_active': 'is_active'
                            };
                            Object.keys(body.errors).forEach(function(field) {
                                if (map[field]) {
                                    setFieldError(map[field], Array.isArray(body.errors[field]) ? body.errors[field][0] : String(body.errors[field]));
                                    has = true;
                                }
                            });
                            if (has) {
                                formError.classList.add('d-none');
                                return;
                            }
                            formError.textContent = body.message || "L'email est déjà utilisé.";
                            formError.classList.remove('d-none');
                            return;
                        }
                        formError.textContent = (body && body.message) ? body.message :
                            (isEditMode ? "Impossible de modifier l'utilisateur." : "Impossible de créer l'utilisateur.");
                        formError.classList.remove('d-none');
                    });
                });
            }
            // ── EDIT USER MODAL (uses the shared form in EDIT mode) ──
            function openEditModal(id, name, email, role, is_active) {
                lastFocused = document.activeElement;
                createForm.reset();
                clearFieldErrors();
                setModalMode('edit');
                editingUserId = id;

                document.getElementById('nu-name').value = name || '';
                document.getElementById('nu-email').value = email || '';
                document.getElementById('nu-role').value = role || 'user';
                activeCb.checked = is_active === true;
                activeLabel.textContent = is_active === true ? 'Actif' : 'Désactivé';
                pwdInput.value = '';
                pwdConfirm.value = '';
                pwdInput.type = 'password';

                // Reset scope before the async load
                selectedDomainData = {};
                setScopeRadio('all');
                selectAllScope();

                loadCatalog(function() {
                    get('/api/v1/admin/users/' + id + '/domains')
                        .then(function(res) {
                            if (!res.ok) {
                                throw new Error('Impossible de charger les accès utilisateur.');
                            }
                            return res.json();
                        })
                        .then(function(scopeData) {
                            if (!scopeData || !scopeData.success || !scopeData.data) {
                                throw new Error('Scope utilisateur invalide.');
                            }
                            buildEditState(scopeData.data);
                            var scope = selectedDomainData.__scope || 'selected';
                            applyScopeValue(scope);
                            delete selectedDomainData.__scope;
                            renderDomainList('');
                            updateDomainCounter();
                            updateSummary();
                        })
                        .catch(function(err) {
                            console.error('Erreur ouverture édition utilisateur:', err);
                            formError.textContent = err.message || 'Impossible de charger les accès utilisateur.';
                            formError.classList.remove('d-none');
                        });
                });

                createModal.show();
                document.body.style.overflow = 'hidden';
                document.getElementById('nu-name').focus();
            }

            function initEditActionButtons() {
                document.querySelectorAll('[data-edit-user]').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var id = btn.getAttribute('data-edit-user');
                        var client = api();
                        if (!client) {
                            showToast('Client API introuvable.', 'error');
                            return;
                        }
                        client.get('/admin/users/' + id)
                            .then(function(res) {
                                if (!res || res.status < 200 || res.status >= 300) {
                                    throw new Error("Impossible de charger l'utilisateur.");
                                }
                                return res.data;
                            })
                            .then(function(data) {
                                if (!data || !data.success || !data.data) {
                                    throw new Error('Données utilisateur invalides.');
                                }
                                var u = data.data;
                                openEditModal(u.id, u.name, u.email, u.role, u.is_active);
                            })
                            .catch(function(err) {
                                console.error('Erreur Modifier utilisateur:', err);
                                showToast(err.message || 'Erreur lors de la récupération des données.', 'error');
                            });
                    });
                });
            }

            initEditActionButtons();

        })();
    </script>
@endpush
