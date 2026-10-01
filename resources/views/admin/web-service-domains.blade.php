@push('styles')
    <style>
        /* JS-driven visibility helper used by the inline scripts below. */
        .hidden {
            display: none !important;
        }

        /* Skeleton loading bars */
        @keyframes domain-pulse {
            0%, 100% {
                opacity: 1;
            }

            50% {
                opacity: .4;
            }
        }

        .skeleton-bar {
            display: inline-block;
            height: 15px;
            border-radius: 6px;
            background-color: #e9ecf0;
            animation: domain-pulse 1.6s ease-in-out infinite;
        }

        /* Technical identifiers (domain code, Web Service code) */
        .code-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            letter-spacing: .02em;
        }

        /* Row action items inside the Metronic dropdown */
        .row-action {
            transition: background-color .15s ease;
        }

        .row-action:hover {
            background-color: #f5f7fa;
        }

        /* Web Service picker rows */
        .ws-option {
            transition: border-color .15s ease, background-color .15s ease;
        }

        .ws-option:hover {
            border-color: var(--bs-primary, #1266f1);
        }

        .ws-option:has(.form-check-input:checked) {
            border-color: var(--bs-primary, #1266f1);
            background-color: rgba(18, 102, 241, .05);
        }

        /* Switch control ("Domaine actif") */
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
            transition: background-color .2s ease;
        }

        .switch .handle {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
            transition: transform .2s ease;
        }

        .switch input:checked~.track {
            background-color: var(--bs-primary, #1266f1);
        }

        .switch input:checked~.handle {
            transform: translateX(20px);
        }
    </style>
@endpush

@extends('layouts.metronic')

@section('title', 'Domaines Web Service')

@section('content')
    {{-- ── PAGE HEADING ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mb-6 mb-xl-8">
        <div class="d-flex align-items-center gap-3">
            <div class="symbol symbol-50px bg-light-primary bg-opacity-100 flex-shrink-0">
                <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-folder fs-2"></i></span>
            </div>
            <div>
                <h2 class="fw-bold text-dark fs-3 mb-0">Domaines Web Service</h2>
                <p class="text-muted fs-6 mb-0">Gérez les domaines et leurs Web Services.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <button type="button" id="btn-create-domain" class="btn btn-primary fw-semibold">
                <span class="svg-icon svg-icon-2"><i class="ki-outline ki-plus"></i></span>
                <span class="ms-2">Créer un domaine</span>
            </button>
        </div>
    </div>

    {{-- ── AUTHORIZATION LOADING STATE ── --}}
    <div id="auth-loading" class="alert alert-info" role="status">
        <div class="d-flex align-items-center">
            <span class="spinner-border spinner-border-sm text-primary me-3" role="status" aria-hidden="true"></span>
            <div>
                <h4 class="fs-6 fw-bolder text-dark mb-1">Vérification des accès</h4>
                <p class="fs-7 text-muted mb-0">Chargement des domaines Web Service...</p>
            </div>
        </div>
    </div>

    {{-- ── ACCESS DENIED (403 / non-admin) ── --}}
    <div id="access-denied" class="alert alert-danger d-none" role="alert">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <span class="svg-icon svg-icon-2x svg-icon-danger flex-shrink-0"><i class="ki-outline ki-shield"></i></span>
            <div class="flex-grow-1">
                <h4 class="fs-6 fw-bolder text-white mb-1">Accès non autorisé</h4>
                <p class="fs-7 text-white opacity-75 mb-0">Vous devez être administrateur pour gérer les domaines.</p>
            </div>
            <div>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light fw-semibold">Retour au Dashboard</a>
            </div>
        </div>
    </div>

    {{-- ── LOAD ERROR + RETRY ── --}}
    <div id="load-error" class="alert alert-danger d-none" role="alert">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <span class="svg-icon svg-icon-2x svg-icon-danger flex-shrink-0"><i class="ki-outline ki-cloud"></i></span>
            <div class="flex-grow-1">
                <h4 class="fs-6 fw-bolder text-white mb-1">Impossible de charger les domaines</h4>
                <p class="fs-7 text-white opacity-75 mb-0">Vérifiez votre connexion et réessayez.</p>
            </div>
            <div>
                <button type="button" id="btn-retry" class="btn btn-sm btn-light fw-semibold">Réessayer</button>
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT (shown once the admin session is confirmed) ── --}}
    <div id="admin-domains-root" class="hidden">
        {{-- ── SUMMARY / KPI ── --}}
        <div class="row g-4 g-xl-5 mb-6 mb-xl-8">
            <div class="col-sm-6 col-xl-4">
                <div class="card card-flush">
                    <div class="card-body d-flex align-items-center p-6">
                        <div class="flex-grow-1">
                            <span class="text-muted fs-6 d-block">Total domaines</span>
                            <span class="fs-2x fw-bolder text-dark" id="stat-total-domains">—</span>
                            <span class="text-muted fs-7 d-block mt-1">Catalogue des domaines Web Service</span>
                        </div>
                        <span class="svg-icon svg-icon-2x svg-icon-primary bg-light-primary rounded me-3">
                            <i class="ki-outline ki-folder"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card card-flush">
                    <div class="card-body d-flex align-items-center p-6">
                        <div class="flex-grow-1">
                            <span class="text-muted fs-6 d-block">Domaines actifs</span>
                            <span class="fs-2x fw-bolder text-success" id="stat-active-domains">—</span>
                            <span class="text-muted fs-7 d-block mt-1" id="stat-active-note">—</span>
                        </div>
                        <span class="svg-icon svg-icon-2x svg-icon-success bg-light-success rounded me-3">
                            <i class="ki-outline ki-toggle-on"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card card-flush">
                    <div class="card-body d-flex align-items-center p-6">
                        <div class="flex-grow-1">
                            <span class="text-muted fs-6 d-block">Web Services associés</span>
                            <span class="fs-2x fw-bolder text-info" id="stat-linked-services">—</span>
                            <span class="text-muted fs-7 d-block mt-1" id="stat-services-note">—</span>
                        </div>
                        <span class="svg-icon svg-icon-2x svg-icon-info bg-light-info rounded me-3">
                            <i class="ki-outline ki-element-11"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TABLE CARD ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="domains-table-card">
            <div class="card-header border-0">
                <div class="card-title">
                    <h3 class="fw-bold text-dark fs-4">Liste des domaines</h3>
                    <span class="text-muted fw-semibold fs-7" id="domains-total">0 domaine</span>
                </div>
                <div class="card-toolbar d-flex flex-wrap align-items-center gap-3">
                    <div class="input-group input-group-solid w-150px w-lg-200px">
                        <input type="text" id="domain-search" class="form-control border-0 fs-7"
                            placeholder="Rechercher un domaine…" aria-label="Rechercher un domaine" />
                    </div>
                    <select id="domain-status-filter" class="form-select form-select-solid form-select-sm w-150px"
                        aria-label="Filtrer par statut">
                        <option value="">Tous statuts</option>
                        <option value="active">Actifs</option>
                        <option value="inactive">Désactivés</option>
                    </select>
                    <button type="button" id="btn-refresh-domains" class="btn btn-icon btn-light-primary"
                        aria-label="Actualiser la liste" title="Actualiser la liste">
                        <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-abstract-18 fs-2"></i></span>
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- Skeleton rows --}}
                <div id="domain-skeleton" class="hidden"></div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="domain-table" role="grid"
                        aria-label="Domaines Web Service">
                        <thead>
                            <tr class="text-nowrap fw-bold fs-7 text-muted">
                                <th class="text-start ps-4 pe-2">Domaine</th>
                                <th class="text-start pe-2">Code</th>
                                <th class="text-start pe-2">Description</th>
                                <th class="text-start pe-2 text-center">Web Services</th>
                                <th class="text-start pe-2 text-center">Statut</th>
                                <th class="text-end ps-2 pe-4 text-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="domain-tbody"></tbody>
                    </table>
                </div>

                {{-- Empty state --}}
                <div id="domain-empty" class="hidden p-6 text-center">
                    <span class="svg-icon svg-icon-3x text-muted d-block mb-3"><i class="ki-outline ki-folder"></i></span>
                    <h3 id="domain-empty-title" class="text-dark fs-4 fw-bolder mb-1">Aucun domaine disponible.</h3>
                    <p id="domain-empty-text" class="text-muted fs-6 mb-4">Créez votre premier domaine pour organiser vos Web Services.</p>
                    <button type="button" id="btn-empty-create-domain" class="btn btn-primary fw-semibold">
                        <span class="svg-icon svg-icon-2"><i class="ki-outline ki-plus"></i></span>
                        <span class="ms-2">Créer un domaine</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SUCCESS / ERROR TOAST ── --}}
    <div id="toast" class="alert alert-success position-fixed top-0 end-0 m-5 shadow d-none" role="status"
        aria-live="polite">
        <p id="toast-msg" class="fs-7 fw-bold mb-0"></p>
    </div>

    {{-- ── CREATE DOMAIN MODAL ── --}}
    <div class="modal fade" id="domain-create-modal" tabindex="-1" role="dialog"
        aria-labelledby="domain-create-modal-title">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded shadow-lg">
                <form id="domain-create-form" novalidate>
                    <div class="modal-header border-0 pt-5 pb-2 px-7">
                        <h5 class="fs-5 fw-bolder text-dark" id="domain-create-modal-title">Créer un domaine</h5>
                        <button type="button" id="domain-create-modal-close" class="btn-close" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body px-7 pb-5">
                        <div id="domain-create-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="create-domain-code" class="form-label fw-bold fs-7">Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="create-domain-code" name="code" required maxlength="60"
                                    class="form-control form-control-solid fs-6 code-badge" placeholder="OFFRES"
                                    autocomplete="off" />
                                <span class="form-text fs-7">Identifiant technique en majuscules. Ex. : OFFRES</span>
                                <div id="err-create-domain-code" class="form-text text-danger fw-semibold fs-7 d-none"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-domain-name" class="form-label fw-bold fs-7">Nom <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="create-domain-name" name="name" required maxlength="255"
                                    class="form-control form-control-solid fs-6" placeholder="Offres"
                                    autocomplete="off" />
                                <div id="err-create-domain-name" class="form-text text-danger fw-semibold fs-7 d-none"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="create-domain-description" class="form-label fw-bold fs-7">Description</label>
                            <textarea id="create-domain-description" name="description" rows="3" maxlength="500"
                                class="form-control form-control-solid fs-6"
                                placeholder="Périmètre fonctionnel du domaine…"></textarea>
                            <span class="form-text fs-7">Facultatif — 500 caractères maximum.</span>
                            <div id="err-create-domain-description"
                                class="form-text text-danger fw-semibold fs-7 d-none"></div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-4 rounded border border-gray-200">
                            <div>
                                <span class="fs-6 fw-bold text-dark d-block">Domaine actif</span>
                                <span class="text-muted fs-7">Un domaine désactivé n'accorde aucun accès.</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="switch">
                                    <input type="checkbox" id="create-domain-is-active" name="is_active" checked />
                                    <span class="track"></span>
                                    <span class="handle"></span>
                                </label>
                                <span class="fs-7 fw-semibold text-success" id="create-active-label">Actif</span>
                            </div>
                        </div>
                        <div id="err-create-domain-is_active" class="form-text text-danger fw-semibold fs-7 d-none mt-1"></div>
                    </div>
                    <div class="modal-footer border-0 pt-4 pb-5 px-7">
                        <button type="button" id="domain-create-modal-cancel"
                            class="btn btn-light-secondary fw-semibold">Annuler</button>
                        <button type="submit" id="btn-create-domain-submit" class="btn btn-primary fw-semibold">
                            <span id="btn-create-domain-spinner"
                                class="spinner-border spinner-border-sm text-white d-none me-2" role="status"
                                aria-hidden="true"></span>
                            <span id="btn-create-domain-text">Créer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── EDIT DOMAIN MODAL ── --}}
    <div class="modal fade" id="domain-edit-modal" tabindex="-1" role="dialog"
        aria-labelledby="domain-edit-modal-title">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded shadow-lg">
                <form id="domain-edit-form" novalidate>
                    <div class="modal-header border-0 pt-5 pb-2 px-7">
                        <h5 class="fs-5 fw-bolder text-dark" id="domain-edit-modal-title">Modifier le domaine</h5>
                        <button type="button" id="domain-edit-modal-close" class="btn-close" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body px-7 pb-5">
                        <div id="domain-edit-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>

                        <div class="mb-4">
                            <label for="edit-domain-code" class="form-label fw-bold fs-7">Code</label>
                            <input type="text" id="edit-domain-code" class="form-control form-control-solid fs-6 code-badge bg-gray-100 text-muted"
                                readonly tabindex="-1" />
                            <span class="form-text fs-7">Le code est un identifiant technique et ne peut pas être modifié.</span>
                        </div>

                        <div class="mb-4">
                            <label for="edit-domain-name" class="form-label fw-bold fs-7">Nom <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="edit-domain-name" name="name" required maxlength="255"
                                class="form-control form-control-solid fs-6" />
                            <div id="err-edit-domain-name" class="form-text text-danger fw-semibold fs-7 d-none"></div>
                        </div>

                        <div class="mb-4">
                            <label for="edit-domain-description" class="form-label fw-bold fs-7">Description</label>
                            <textarea id="edit-domain-description" name="description" rows="3" maxlength="500"
                                class="form-control form-control-solid fs-6"></textarea>
                            <span class="form-text fs-7">Facultatif — 500 caractères maximum.</span>
                            <div id="err-edit-domain-description"
                                class="form-text text-danger fw-semibold fs-7 d-none"></div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="edit-domain-sort_order" class="form-label fw-bold fs-7">Ordre d'affichage</label>
                                <input type="number" id="edit-domain-sort_order" name="sort_order" min="0" max="9999"
                                    value="0" class="form-control form-control-solid fs-6" />
                                <span class="form-text fs-7">Les valeurs les plus basses s'affichent en premier.</span>
                                <div id="err-edit-domain-sort_order"
                                    class="form-text text-danger fw-semibold fs-7 d-none"></div>
                            </div>
                            <div class="col-md-6">
                                <span class="form-label fw-bold fs-7">Statut</span>
                                <div class="mt-2" id="edit-domain-status"></div>
                                <span class="form-text fs-7">Modifiez-le via l'action Activer / Désactiver de la liste.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-4 pb-5 px-7">
                        <button type="button" id="domain-edit-modal-cancel"
                            class="btn btn-light-secondary fw-semibold">Annuler</button>
                        <button type="submit" id="btn-edit-domain-submit" class="btn btn-primary fw-semibold">
                            <span id="btn-edit-domain-spinner"
                                class="spinner-border spinner-border-sm text-white d-none me-2" role="status"
                                aria-hidden="true"></span>
                            <span id="btn-edit-domain-text">Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── MANAGE DOMAIN WEB SERVICES MODAL ── --}}
    <div class="modal fade" id="domain-services-modal" tabindex="-1" role="dialog"
        aria-labelledby="domain-services-modal-title">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content rounded shadow-lg">
                <div class="modal-header border-0 pt-5 pb-2 px-7">
                    <div>
                        <h5 class="fs-5 fw-bolder text-dark mb-0" id="domain-services-modal-title">Ajouter des Web Services</h5>
                        <span id="domain-services-modal-subtitle" class="text-muted fs-7 d-block mt-1"></span>
                    </div>
                    <button type="button" id="domain-services-modal-close" class="btn-close" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-7 pb-5">
                    <div id="domain-services-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>

                    <div id="domain-services-skeleton" class="d-none p-2"></div>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div class="input-group input-group-solid w-150px w-lg-250px">
                            <input type="text" id="domain-services-search" class="form-control border-0 fs-7"
                                placeholder="Rechercher un Web Service…" aria-label="Rechercher un Web Service" />
                        </div>
                        <span class="badge badge-light-primary fw-semibold fs-7" id="domain-services-counter">0 sélectionné</span>
                    </div>

                    <div id="domain-services-list" class="scroll-y scroll-me"
                        style="max-height:350px"></div>
                </div>
                <div class="modal-footer border-0 pt-4 pb-5 px-7">
                    <button type="button" id="domain-services-modal-cancel"
                        class="btn btn-light-secondary fw-semibold">Annuler</button>
                    <button type="button" id="btn-save-domain-services" class="btn btn-primary fw-semibold">
                        <span id="btn-save-domain-services-spinner"
                            class="spinner-border spinner-border-sm text-white d-none me-2" role="status"
                            aria-hidden="true"></span>
                        <span id="btn-save-domain-services-text">Enregistrer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── DELETE DOMAIN MODAL ── --}}
    <div class="modal fade" id="domain-delete-modal" tabindex="-1" role="dialog"
        aria-labelledby="domain-delete-modal-title">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded shadow-lg">
                <div class="modal-header border-0 pt-5 pb-2 px-7">
                    <h5 class="fs-5 fw-bolder text-dark" id="domain-delete-modal-title">Supprimer le domaine</h5>
                    <button type="button" id="domain-delete-modal-close" class="btn-close" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-7 pb-5">
                    <div id="domain-delete-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>
                    <div class="alert alert-danger rounded d-flex align-items-start gap-3">
                        <span class="svg-icon svg-icon-2x svg-icon-danger flex-shrink-0"><i class="ki-outline ki-trash"></i></span>
                        <div class="fs-7">
                            <p class="fw-bold mb-1">Êtes-vous sûr de vouloir supprimer le domaine <span
                                    class="code-badge" id="domain-delete-code-display"></span> ?</p>
                            <p class="text-white opacity-75 mb-0">Cette action est irréversible et retire tous les Web Services rattachés à ce domaine.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-4 pb-5 px-7">
                    <button type="button" id="domain-delete-modal-cancel"
                        class="btn btn-light-secondary fw-semibold">Annuler</button>
                    <button type="button" id="btn-delete-domain-submit" class="btn btn-light-danger fw-semibold">
                        <span id="btn-delete-domain-spinner"
                            class="spinner-border spinner-border-sm text-danger d-none me-2" role="status"
                            aria-hidden="true"></span>
                        <span id="btn-delete-domain-text">Supprimer</span>
                    </button>
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
                    }).then(function (res) {
                        return res.json().catch(function () {
                            return {};
                        }).then(function (body) {
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
                    get: function (url) {
                        return req('GET', url);
                    },
                    post: function (url, data) {
                        return req('POST', url, data);
                    },
                    put: function (url, data) {
                        return req('PUT', url, data);
                    },
                    patch: function (url, data) {
                        return req('PATCH', url, data);
                    },
                    delete: function (url, data) {
                        return req('DELETE', url, data);
                    }
                };
            }

            function clearSession() {
                localStorage.removeItem('anapec_token');
                localStorage.removeItem('anapec_user');
                window.location.href = '/login';
            }

            function esc(v) {
                if (v === null || v === undefined) return '';
                var d = document.createElement('div');
                d.textContent = String(v);
                return d.innerHTML;
            }

            function attr(v) {
                return esc(v).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function hashOf(v) {
                var s = v || '';
                var h = 0;
                for (var i = 0; i < s.length; i++) h = ((h << 5) - h) + s.charCodeAt(i);
                return Math.abs(h);
            }

            function showToast(msg, type) {
                var t = document.getElementById('toast');
                var msgEl = document.getElementById('toast-msg');
                msgEl.textContent = msg;
                t.className = 'alert position-fixed top-0 end-0 m-5 shadow ' +
                    (type === 'error' ? 'alert-danger' : 'alert-success');
                clearTimeout(t._timer);
                t._timer = setTimeout(function () {
                    t.classList.add('d-none');
                }, 4000);
            }

            // ── State ──
            var allDomains = [];
            var allServices = [];
            var domainsByCode = {};
            var state = {
                query: '',
                status: ''
            };

            // ── Elements ──
            var authLoading = document.getElementById('auth-loading');
            var accessDenied = document.getElementById('access-denied');
            var loadError = document.getElementById('load-error');
            var tbody = document.getElementById('domain-tbody');
            var skeleton = document.getElementById('domain-skeleton');
            var empty = document.getElementById('domain-empty');
            var tableEl = document.getElementById('domain-table');
            var totalEl = document.getElementById('domains-total');
            var searchEl = document.getElementById('domain-search');
            var statusFilterEl = document.getElementById('domain-status-filter');

            function showAuthLoading(show) {
                authLoading.classList.toggle('d-none', !show);
            }

            function showAdminRoot() {
                showAuthLoading(false);
                accessDenied.classList.add('d-none');
                loadError.classList.add('d-none');
                document.getElementById('admin-domains-root').classList.remove('hidden');
            }

            function showAccessDenied() {
                showAuthLoading(false);
                document.getElementById('admin-domains-root').classList.add('hidden');
                loadError.classList.add('d-none');
                accessDenied.classList.remove('d-none');
            }

            function showLoadError() {
                showAuthLoading(false);
                document.getElementById('admin-domains-root').classList.add('hidden');
                accessDenied.classList.add('d-none');
                loadError.classList.remove('d-none');
            }

            // ── Badges ──
            function statusBadge(active) {
                if (active === true) {
                    return '<span class="badge badge-light-success fw-semibold">' +
                        '<span class="bullet bullet-dot bg-success me-1"></span>Actif</span>';
                }
                return '<span class="badge badge-light-secondary fw-semibold">' +
                    '<span class="bullet bullet-dot bg-secondary me-1"></span>Désactivé</span>';
            }

            function serviceBadge(n) {
                var count = parseInt(n, 10) || 0;
                return '<span class="badge badge-light-primary fw-semibold">' + count + ' Web Service' +
                    (count > 1 ? 's' : '') + '</span>';
            }

            // ── Domain avatars ──
            function pickAvatarClass(name) {
                var classes = [
                    'bg-light-primary bg-opacity-100',
                    'bg-light-info bg-opacity-100',
                    'bg-light-success bg-opacity-100',
                    'bg-light-warning bg-opacity-100',
                    'bg-light-danger bg-opacity-100'
                ];
                return classes[hashOf(name) % classes.length];
            }

            function pickSymbolColor(name) {
                var colors = ['text-primary', 'text-info', 'text-success', 'text-warning', 'text-danger'];
                return colors[hashOf(name) % colors.length];
            }

            function domainInitials(d) {
                var parts = ((d.name || '') + ' ' + (d.code || '')).trim().split(/\s+/).filter(function (p) {
                    return p.length > 0;
                });
                if (!parts.length) return '?';
                if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
            }

            // ── Row actions (Metronic dropdown) ──
            function rowActions(d) {
                var code = d.code || '';
                var active = d.is_active === true;
                var nextActive = !active;
                return '<div class="dropdown d-inline-block">' +
                    '<button type="button" class="btn btn-sm btn-icon btn-light-primary" data-bs-toggle="dropdown" ' +
                    'aria-expanded="false" aria-label="Actions pour le domaine ' + attr(code) + '">' +
                    '<span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-more-2"></i></span>' +
                    '</button>' +
                    '<ul class="dropdown-menu dropdown-menu-end py-2 fw-normal w-200px">' +
                    '<li><button type="button" data-domain-services-code="' + attr(code) + '" ' +
                    'class="row-action d-flex align-items-center w-100 px-3 py-2 border-0 bg-transparent text-start text-dark rounded fs-7 fw-semibold">' +
                    '<span class="svg-icon svg-icon-2 text-primary me-2"><i class="ki-outline ki-element-plus"></i></span>' +
                    '<span>Ajouter des Web Services</span></button></li>' +
                    '<li><button type="button" data-domain-edit-code="' + attr(code) + '" ' +
                    'class="row-action d-flex align-items-center w-100 px-3 py-2 border-0 bg-transparent text-start text-dark rounded fs-7 fw-semibold">' +
                    '<span class="svg-icon svg-icon-2 text-secondary me-2"><i class="ki-outline ki-pencil"></i></span>' +
                    '<span>Modifier</span></button></li>' +
                    '<li><button type="button" data-domain-toggle-code="' + attr(code) + '" data-next="' +
                    (nextActive ? 'true' : 'false') + '" ' +
                    'class="row-action d-flex align-items-center w-100 px-3 py-2 border-0 bg-transparent text-start text-dark rounded fs-7 fw-semibold">' +
                    '<span class="svg-icon svg-icon-2 text-' + (nextActive ? 'success' : 'danger') + ' me-2"><i class="ki-outline ki-toggle-' +
                    (nextActive ? 'on' : 'off') + '"></i></span>' +
                    '<span class="row-action-label">' + (nextActive ? 'Activer' : 'Désactiver') + '</span></button></li>' +
                    '<li><button type="button" data-domain-delete-code="' + attr(code) + '" ' +
                    'class="row-action d-flex align-items-center w-100 px-3 py-2 border-0 bg-transparent text-start text-danger rounded fs-7 fw-semibold">' +
                    '<span class="svg-icon svg-icon-2 text-danger me-2"><i class="ki-outline ki-trash"></i></span>' +
                    '<span>Supprimer</span></button></li>' +
                    '</ul>' +
                    '</div>';
            }

            // ── Skeleton ──
            function showSkeleton() {
                skeleton.classList.remove('hidden');
                tableEl.classList.add('hidden');
                tbody.classList.add('hidden');
                empty.classList.add('hidden');
                var rows = '';
                for (var i = 0; i < 4; i++) {
                    rows += '<tr>' +
                        '<td class="p-4 ps-4 pe-2"><span class="skeleton-bar" style="width:140px"></span></td>' +
                        '<td class="p-4 pe-2"><span class="skeleton-bar" style="width:90px"></span></td>' +
                        '<td class="p-4 pe-2"><span class="skeleton-bar" style="width:180px"></span></td>' +
                        '<td class="p-4 pe-2 text-center"><span class="skeleton-bar" style="width:90px"></span></td>' +
                        '<td class="p-4 pe-2 text-center"><span class="skeleton-bar" style="width:80px"></span></td>' +
                        '<td class="p-4 ps-2 pe-4 text-end"><span class="skeleton-bar" style="width:30px"></span></td>' +
                        '</tr>';
                }
                skeleton.innerHTML = '<div class="table-responsive">' +
                    '<table class="table align-middle mb-0">' + rows + '</table></div>';
            }

            function hideSkeleton() {
                skeleton.classList.add('hidden');
                skeleton.innerHTML = '';
            }

            // ── Filtering (client-side, on the already loaded catalog) ──
            function visibleDomains() {
                var q = state.query.toLowerCase();
                return allDomains.filter(function (d) {
                    if (state.status === 'active' && d.is_active !== true) return false;
                    if (state.status === 'inactive' && d.is_active === true) return false;
                    if (!q) return true;
                    var hay = ((d.code || '') + ' ' + (d.name || '') + ' ' + (d.description || '')).toLowerCase();
                    return hay.indexOf(q) !== -1;
                });
            }

            function renderStats(domains) {
                var total = domains.length;
                var active = 0;
                var services = 0;
                domains.forEach(function (d) {
                    if (d.is_active === true) active++;
                    services += parseInt(d.service_count, 10) || 0;
                });
                document.getElementById('stat-total-domains').textContent = String(total);
                document.getElementById('stat-active-domains').textContent = String(active);
                document.getElementById('stat-linked-services').textContent = String(services);
                document.getElementById('stat-active-note').textContent = total
                    ? active + ' sur ' + total + ' domaine' + (total > 1 ? 's' : '')
                    : 'Aucun domaine';
                document.getElementById('stat-services-note').textContent = total
                    ? 'Répartis sur ' + total + ' domaine' + (total > 1 ? 's' : '')
                    : 'Aucun Web Service';
            }

            function renderRows() {
                domainsByCode = {};
                allDomains.forEach(function (d) {
                    domainsByCode[d.code] = d;
                });

                renderStats(allDomains);

                totalEl.textContent = allDomains.length + ' domaine' + (allDomains.length > 1 ? 's' : '');

                if (!allDomains.length) {
                    tableEl.classList.add('hidden');
                    tbody.classList.add('hidden');
                    tbody.innerHTML = '';
                    empty.classList.remove('hidden');
                    document.getElementById('domain-empty-title').textContent = 'Aucun domaine disponible.';
                    document.getElementById('domain-empty-text').textContent =
                        'Créez votre premier domaine pour organiser vos Web Services.';
                    document.getElementById('btn-empty-create-domain').classList.remove('d-none');
                    return;
                }

                var list = visibleDomains();

                if (!list.length) {
                    tableEl.classList.add('hidden');
                    tbody.classList.add('hidden');
                    tbody.innerHTML = '';
                    empty.classList.remove('hidden');
                    document.getElementById('domain-empty-title').textContent = 'Aucun domaine trouvé.';
                    document.getElementById('domain-empty-text').textContent =
                        'Aucun domaine ne correspond à votre recherche ou à votre filtre.';
                    document.getElementById('btn-empty-create-domain').classList.add('d-none');
                    return;
                }

                empty.classList.add('hidden');
                tableEl.classList.remove('hidden');
                tbody.classList.remove('hidden');

                var rows = '';
                list.forEach(function (d) {
                    var code = d.code || '';
                    var label = (d.name || code) || '?';
                    var desc = d.description
                        ? '<span class="d-block text-dark fs-7" style="max-width:280px" title="' +
                        attr(d.description) + '">' + esc(d.description) + '</span>'
                        : '<span class="text-muted fs-7">—</span>';

                    rows += '<tr>' +
                        '<td class="p-4 ps-4 pe-2">' +
                        '<div class="d-flex align-items-center gap-3">' +
                        '<div class="symbol symbol-45px ' + pickAvatarClass(label) + ' flex-shrink-0">' +
                        '<span class="symbol-label fs-7 fw-bolder ' + pickSymbolColor(label) + '">' +
                        esc(domainInitials(d)) + '</span>' +
                        '</div>' +
                        '<div style="min-width:0">' +
                        '<div class="text-dark fw-bolder fs-7 text-truncate" title="' + attr(d.name) + '">' +
                        esc(d.name) + '</div>' +
                        '<span class="text-muted fs-7 text-truncate d-block">' + esc(code) + '</span>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '<td class="p-4 pe-2 text-nowrap"><span class="badge badge-light-dark fw-semibold code-badge">' +
                        esc(code) + '</span></td>' +
                        '<td class="p-4 pe-2">' + desc + '</td>' +
                        '<td class="p-4 pe-2 text-center">' + serviceBadge(d.service_count) + '</td>' +
                        '<td class="p-4 pe-2 text-center">' + statusBadge(d.is_active === true) + '</td>' +
                        '<td class="p-4 ps-2 pe-4 text-end">' + rowActions(d) + '</td>' +
                        '</tr>';
                });
                tbody.innerHTML = rows;
            }

            // Delegation: the tbody is re-rendered after every mutation.
            tbody.addEventListener('click', function (e) {
                var servicesBtn = e.target.closest('[data-domain-services-code]');
                if (servicesBtn) {
                    openServicesModal(servicesBtn.getAttribute('data-domain-services-code'));
                    return;
                }
                var editBtn = e.target.closest('[data-domain-edit-code]');
                if (editBtn) {
                    openEditModal(editBtn.getAttribute('data-domain-edit-code'));
                    return;
                }
                var toggleBtn = e.target.closest('[data-domain-toggle-code]');
                if (toggleBtn) {
                    toggleDomainStatus(toggleBtn.getAttribute('data-domain-toggle-code'),
                        toggleBtn.getAttribute('data-next') === 'true', toggleBtn);
                    return;
                }
                var deleteBtn = e.target.closest('[data-domain-delete-code]');
                if (deleteBtn) {
                    openDeleteModal(deleteBtn.getAttribute('data-domain-delete-code'));
                }
            });

            // ── Search / filter / refresh ──
            var searchDebounce;
            searchEl.addEventListener('input', function () {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(function () {
                    state.query = searchEl.value.trim();
                    renderRows();
                }, 250);
            });
            statusFilterEl.addEventListener('change', function () {
                state.status = statusFilterEl.value;
                renderRows();
            });
            document.getElementById('btn-refresh-domains').addEventListener('click', loadDomains);
            document.getElementById('btn-retry').addEventListener('click', loadDomains);
            document.getElementById('btn-empty-create-domain').addEventListener('click', openCreateModal);

            // ── Auth gate ──
            get('/api/v1/auth/me')
                .then(function (res) {
                    if (res.status === 401) {
                        clearSession();
                        return;
                    }
                    if (!res.ok) {
                        showLoadError();
                        return res.json().catch(function () {
                            return {};
                        }).then(function () {
                            showLoadError();
                        });
                    }
                    return res.json().then(function (data) {
                        if (!data || !data.success || !data.data) {
                            showLoadError();
                            return;
                        }
                        var user = data.data;
                        localStorage.setItem('anapec_user', JSON.stringify(user));
                        if (user.role !== 'admin') {
                            showAccessDenied();
                            setTimeout(function () {
                                window.location.replace('/dashboard');
                            }, 1200);
                            return;
                        }
                        showAdminRoot();
                        loadDomains();
                    });
                })
                .catch(function () {
                    showLoadError();
                });

            function loadDomains() {
                showSkeleton();
                get('/api/v1/admin/domains')
                    .then(function (res) {
                        if (res.status === 403) {
                            hideSkeleton();
                            showAccessDenied();
                            return;
                        }
                        if (res.status === 401) {
                            hideSkeleton();
                            clearSession();
                            return;
                        }
                        if (!res.ok) {
                            hideSkeleton();
                            showLoadError();
                            return;
                        }
                        return res.json().then(function (data) {
                            hideSkeleton();
                            if (!data || !data.success || !Array.isArray(data.data)) {
                                showLoadError();
                                return;
                            }
                            allDomains = data.data;
                            renderRows();
                        });
                    })
                    .catch(function () {
                        hideSkeleton();
                        showLoadError();
                    });
            }

            function toggleDomainStatus(code, next, btn) {
                var client = api();
                if (!client) {
                    showToast('Client API introuvable.', 'error');
                    return;
                }

                btn.disabled = true;
                var label = btn.querySelector('.row-action-label');
                if (label) label.textContent = '...';

                client.patch('/admin/domains/' + code + '/status', {
                        is_active: next
                    })
                    .then(function (res) {
                        var body = res && res.data;
                        var data = body && body.data;
                        var finalActive = (data && typeof data.is_active === 'boolean') ? data.is_active : next;
                        var d = domainsByCode[code];
                        if (d) d.is_active = finalActive;
                        renderRows();
                        showToast(finalActive ? 'Domaine activé.' : 'Domaine désactivé.');
                    })
                    .catch(function (err) {
                        btn.disabled = false;
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        var msg = 'Une erreur est survenue. Veuillez réessayer.';
                        if (status === 403) msg = 'Accès non autorisé.';
                        else if (body && body.message) msg = body.message;
                        showToast(msg, 'error');
                    });
            }

            // ════════════════════════════════════════════════════
            //  CREATE DOMAIN MODAL
            // ════════════════════════════════════════════════════
            var createModalEl = document.getElementById('domain-create-modal');
            var createModal = bootstrap.Modal.getOrCreateInstance(createModalEl);
            var createForm = document.getElementById('domain-create-form');
            var createBtn = document.getElementById('btn-create-domain-submit');
            var createText = document.getElementById('btn-create-domain-text');
            var createSpinner = document.getElementById('btn-create-domain-spinner');
            var createActiveCb = document.getElementById('create-domain-is-active');
            var createActiveLabel = document.getElementById('create-active-label');
            var creating = false;
            var createLastFocused = null;

            function clearCreateErrors() {
                ['code', 'name', 'description', 'is_active'].forEach(function (f) {
                    var el = document.getElementById('err-create-domain-' + f);
                    if (el) el.classList.add('d-none');
                });
                document.getElementById('domain-create-modal-error').classList.add('d-none');
            }

            function setCreateError(field, msg) {
                var el = document.getElementById('err-create-domain-' + field);
                if (!el) return;
                el.textContent = msg;
                el.classList.remove('d-none');
            }

            function setCreateFormError(msg) {
                var el = document.getElementById('domain-create-modal-error');
                el.textContent = msg;
                el.classList.remove('d-none');
            }

            function openCreateModal() {
                createLastFocused = document.activeElement;
                createForm.reset();
                clearCreateErrors();
                document.getElementById('create-domain-code').value = '';
                document.getElementById('create-domain-name').value = '';
                document.getElementById('create-domain-description').value = '';
                createActiveCb.checked = true;
                updateCreateActiveLabel();
                createModal.show();
            }

            function closeCreateModal() {
                createModal.hide();
                if (createLastFocused && createLastFocused.focus) createLastFocused.focus();
            }

            function updateCreateActiveLabel() {
                var active = createActiveCb.checked === true;
                createActiveLabel.textContent = active ? 'Actif' : 'Désactivé';
                createActiveLabel.className = 'fs-7 fw-semibold ' + (active ? 'text-success' : 'text-muted');
            }

            createModalEl.addEventListener('hide.bs.modal', function (e) {
                if (creating) e.preventDefault();
            });

            document.getElementById('btn-create-domain').addEventListener('click', openCreateModal);
            document.getElementById('domain-create-modal-close').addEventListener('click', closeCreateModal);
            document.getElementById('domain-create-modal-cancel').addEventListener('click', closeCreateModal);
            createActiveCb.addEventListener('change', updateCreateActiveLabel);

            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (creating) return;

                var code = String(document.getElementById('create-domain-code').value || '').trim().toUpperCase();
                var name = String(document.getElementById('create-domain-name').value || '').trim();
                var description = String(document.getElementById('create-domain-description').value || '').trim();
                var isActive = createActiveCb.checked === true;

                clearCreateErrors();
                var ok = true;
                if (!code) {
                    setCreateError('code', 'Le code est requis.');
                    ok = false;
                }
                if (!name) {
                    setCreateError('name', 'Le nom est requis.');
                    ok = false;
                }
                if (!ok) return;

                var client = api();
                if (!client) {
                    setCreateFormError('Client API introuvable.');
                    return;
                }

                creating = true;
                createBtn.disabled = true;
                createSpinner.classList.remove('d-none');
                createText.textContent = 'Création...';

                client.post('/admin/domains', {
                        code: code,
                        name: name,
                        description: description,
                        is_active: isActive
                    })
                    .then(function () {
                        creating = false;
                        createBtn.disabled = false;
                        createSpinner.classList.add('d-none');
                        createText.textContent = 'Créer';
                        closeCreateModal();
                        showToast('Domaine créé avec succès.');
                        loadDomains();
                    })
                    .catch(function (err) {
                        creating = false;
                        createBtn.disabled = false;
                        createSpinner.classList.add('d-none');
                        createText.textContent = 'Créer';
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 401) {
                            clearSession();
                            return;
                        }
                        if (status === 403) {
                            setCreateFormError('Accès non autorisé.');
                            return;
                        }
                        if (status === 422 && body && body.errors) {
                            var shown = false;
                            ['code', 'name', 'description', 'is_active'].forEach(function (f) {
                                if (body.errors[f]) {
                                    setCreateError(f, Array.isArray(body.errors[f]) ? body.errors[f][0] : String(body.errors[f]));
                                    shown = true;
                                }
                            });
                            if (!shown) setCreateFormError(body.message || 'Erreur de validation.');
                            return;
                        }
                        setCreateFormError((body && body.message) ? body.message :
                            'Une erreur est survenue. Veuillez réessayer.');
                    });
            });

            // ════════════════════════════════════════════════════
            //  EDIT DOMAIN MODAL
            // ════════════════════════════════════════════════════
            var editModalEl = document.getElementById('domain-edit-modal');
            var editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            var editForm = document.getElementById('domain-edit-form');
            var editBtn = document.getElementById('btn-edit-domain-submit');
            var editText = document.getElementById('btn-edit-domain-text');
            var editSpinner = document.getElementById('btn-edit-domain-spinner');
            var editing = false;
            var editingCode = null;
            var editLastFocused = null;

            function clearEditErrors() {
                ['name', 'description', 'sort_order'].forEach(function (f) {
                    var el = document.getElementById('err-edit-domain-' + f);
                    if (el) el.classList.add('d-none');
                });
                document.getElementById('domain-edit-modal-error').classList.add('d-none');
            }

            function setEditError(field, msg) {
                var el = document.getElementById('err-edit-domain-' + field);
                if (!el) return;
                el.textContent = msg;
                el.classList.remove('d-none');
            }

            function setEditFormError(msg) {
                var el = document.getElementById('domain-edit-modal-error');
                el.textContent = msg;
                el.classList.remove('d-none');
            }

            function openEditModal(code) {
                var d = domainsByCode[code];
                if (!d) return;
                editingCode = code;
                editLastFocused = document.activeElement;
                editForm.reset();
                clearEditErrors();
                document.getElementById('edit-domain-code').value = d.code || '';
                document.getElementById('edit-domain-name').value = d.name || '';
                document.getElementById('edit-domain-description').value = d.description || '';
                document.getElementById('edit-domain-sort_order').value = d.sort_order != null ? d.sort_order : 0;
                document.getElementById('edit-domain-status').innerHTML = statusBadge(d.is_active === true);
                editModal.show();
            }

            function closeEditModal() {
                editModal.hide();
                editingCode = null;
                if (editLastFocused && editLastFocused.focus) editLastFocused.focus();
            }

            editModalEl.addEventListener('hide.bs.modal', function (e) {
                if (editing) e.preventDefault();
            });

            document.getElementById('domain-edit-modal-close').addEventListener('click', closeEditModal);
            document.getElementById('domain-edit-modal-cancel').addEventListener('click', closeEditModal);

            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (editing || !editingCode) return;

                var name = String(document.getElementById('edit-domain-name').value || '').trim();
                var description = String(document.getElementById('edit-domain-description').value || '').trim();
                var sortRaw = String(document.getElementById('edit-domain-sort_order').value || '').trim();
                var sortOrder = sortRaw === '' ? 0 : parseInt(sortRaw, 10);

                clearEditErrors();
                var ok = true;
                if (!name) {
                    setEditError('name', 'Le nom est requis.');
                    ok = false;
                }
                if (isNaN(sortOrder) || sortOrder < 0) {
                    setEditError('sort_order', 'L\'ordre doit être un entier positif.');
                    ok = false;
                }
                if (!ok) return;

                var client = api();
                if (!client) {
                    setEditFormError('Client API introuvable.');
                    return;
                }

                var code = editingCode;
                editing = true;
                editBtn.disabled = true;
                editSpinner.classList.remove('d-none');
                editText.textContent = 'Enregistrement...';

                client.patch('/admin/domains/' + code, {
                        name: name,
                        description: description,
                        sort_order: sortOrder
                    })
                    .then(function () {
                        editing = false;
                        editBtn.disabled = false;
                        editSpinner.classList.add('d-none');
                        editText.textContent = 'Enregistrer';
                        closeEditModal();
                        showToast('Domaine modifié avec succès.');
                        loadDomains();
                    })
                    .catch(function (err) {
                        editing = false;
                        editBtn.disabled = false;
                        editSpinner.classList.add('d-none');
                        editText.textContent = 'Enregistrer';
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 401) {
                            clearSession();
                            return;
                        }
                        if (status === 403) {
                            setEditFormError('Accès non autorisé.');
                            return;
                        }
                        if (status === 404) {
                            setEditFormError('Domaine introuvable.');
                            return;
                        }
                        if (status === 422 && body && body.errors) {
                            var shown = false;
                            ['name', 'description', 'sort_order'].forEach(function (f) {
                                if (body.errors[f]) {
                                    setEditError(f, Array.isArray(body.errors[f]) ? body.errors[f][0] : String(body.errors[f]));
                                    shown = true;
                                }
                            });
                            if (shown) return;
                            setEditFormError(body.message || 'Erreur de validation.');
                            return;
                        }
                        setEditFormError((body && body.message) ? body.message :
                            'Une erreur est survenue. Veuillez réessayer.');
                    });
            });

            // ════════════════════════════════════════════════════
            //  MANAGE DOMAIN WEB SERVICES MODAL
            // ════════════════════════════════════════════════════
            var servicesModalEl = document.getElementById('domain-services-modal');
            var servicesModal = bootstrap.Modal.getOrCreateInstance(servicesModalEl);
            var servicesList = document.getElementById('domain-services-list');
            var servicesSkeleton = document.getElementById('domain-services-skeleton');
            var servicesSearch = document.getElementById('domain-services-search');
            var servicesCounter = document.getElementById('domain-services-counter');
            var servicesSaveBtn = document.getElementById('btn-save-domain-services');
            var servicesSaveText = document.getElementById('btn-save-domain-services-text');
            var servicesSaveSpinner = document.getElementById('btn-save-domain-services-spinner');
            var servicesMsg = document.getElementById('domain-services-modal-error');
            var servicesSaving = false;
            var servicesCode = null;
            var servicesMemberSet = {};
            var servicesLastFocused = null;

            function setServicesError(msg) {
                servicesMsg.textContent = msg;
                servicesMsg.classList.remove('d-none');
            }

            function clearServicesError() {
                servicesMsg.classList.add('d-none');
            }

            function updateServicesCounter() {
                var n = Object.keys(servicesMemberSet).length;
                servicesCounter.textContent = n + ' sélectionné' + (n > 1 ? 's' : '');
            }

            function renderServicesList() {
                var q = (servicesSearch.value || '').trim().toLowerCase();
                var visible = allServices.filter(function (svc) {
                    if (!q) return true;
                    var hay = ((svc.code || '') + ' ' + (svc.name || '')).toLowerCase();
                    return hay.indexOf(q) !== -1;
                });

                updateServicesCounter();

                if (!visible.length) {
                    servicesList.innerHTML = '<div class="p-5 text-center">' +
                        '<span class="svg-icon svg-icon-2x text-muted d-block mb-2"><i class="ki-outline ki-element-11"></i></span>' +
                        '<p class="text-muted fs-7 mb-0">' +
                        (allServices.length ? 'Aucun Web Service ne correspond à cette recherche.' :
                            'Aucun Web Service enregistré.') + '</p></div>';
                    return;
                }

                var html = '';
                visible.forEach(function (svc) {
                    var checked = servicesMemberSet[svc.code] ? 'checked' : '';
                    var svcActive = svc.is_active === true;
                    html += '<label class="ws-option d-flex align-items-center gap-3 p-3 mb-2 border rounded border-gray-200">' +
                        '<input type="checkbox" data-domain-service-code="' + attr(svc.code) + '" ' + checked +
                        ' class="form-check-input flex-shrink-0" aria-label="' + attr(svc.name || svc.code) + '" />' +
                        '<span class="d-block flex-grow-1" style="min-width:0">' +
                        '<span class="d-block text-dark fw-bold fs-7 code-badge text-truncate">' + esc(svc.code) + '</span>' +
                        '<span class="d-block text-muted fs-7 text-truncate">' + esc(svc.name) + '</span>' +
                        '</span>' +
                        (svcActive
                            ? '<span class="badge badge-light-success fw-semibold">Actif</span>'
                            : '<span class="badge badge-light-secondary fw-semibold">Désactivé</span>') +
                        '</label>';
                });
                servicesList.innerHTML = html;
                updateServicesCounter();
            }

            function openServicesModal(code) {
                var d = domainsByCode[code];
                if (!d) return;
                servicesCode = code;
                servicesLastFocused = document.activeElement;
                clearServicesError();
                servicesSearch.value = '';
                servicesMemberSet = {};
                document.getElementById('domain-services-modal-subtitle').textContent =
                    'Ajouter, retirer ou réorganiser les Web Services rattachés au domaine ' + d.code + '.';
                servicesSkeleton.innerHTML = '<span class="skeleton-bar" style="width:100%;height:40px;margin-bottom:8px"></span>' +
                    '<span class="skeleton-bar" style="width:100%;height:40px;margin-bottom:8px"></span>' +
                    '<span class="skeleton-bar" style="width:100%;height:40px"></span>';
                servicesSkeleton.classList.remove('d-none');
                servicesList.classList.add('d-none');
                servicesModal.show();

                Promise.all([
                    get('/api/v1/admin/web-services'),
                    get('/api/v1/admin/domains/' + code)
                ]).then(function (responses) {
                    servicesSkeleton.classList.add('d-none');
                    if (!responses[0].ok || !responses[1].ok) {
                        servicesList.classList.add('d-none');
                        setServicesError('Impossible de charger les Web Services.');
                        return;
                    }
                    return Promise.all([responses[0].json(), responses[1].json()]).then(function (payloads) {
                        var catalogData = payloads[0];
                        var domainData = payloads[1];
                        if (!catalogData || !catalogData.success || !Array.isArray(catalogData.data)) {
                            servicesList.classList.add('d-none');
                            setServicesError('Réponse invalide.');
                            return;
                        }
                        allServices = catalogData.data;
                        var members = (domainData && domainData.data && Array.isArray(domainData.data.web_services))
                            ? domainData.data.web_services
                            : [];
                        servicesMemberSet = {};
                        members.forEach(function (c) {
                            servicesMemberSet[c] = true;
                        });
                        servicesList.classList.remove('d-none');
                        renderServicesList();
                    });
                }).catch(function () {
                    servicesSkeleton.classList.add('d-none');
                    servicesList.classList.add('d-none');
                    setServicesError('Impossible de charger les Web Services.');
                });
            }

            function closeServicesModal() {
                servicesModal.hide();
                servicesCode = null;
                servicesMemberSet = {};
                if (servicesLastFocused && servicesLastFocused.focus) servicesLastFocused.focus();
            }

            servicesModalEl.addEventListener('hide.bs.modal', function (e) {
                if (servicesSaving) e.preventDefault();
            });

            document.getElementById('domain-services-modal-close').addEventListener('click', closeServicesModal);
            document.getElementById('domain-services-modal-cancel').addEventListener('click', closeServicesModal);
            servicesSearch.addEventListener('input', renderServicesList);
            servicesList.addEventListener('change', function (e) {
                var cb = e.target;
                if (!cb || !cb.getAttribute('data-domain-service-code')) return;
                var code = cb.getAttribute('data-domain-service-code');
                if (cb.checked === true) servicesMemberSet[code] = true;
                else delete servicesMemberSet[code];
                updateServicesCounter();
            });

            servicesSaveBtn.addEventListener('click', function () {
                if (servicesSaving || !servicesCode) return;
                clearServicesError();

                // Read the selection from the member set (single source of
                // truth): rows hidden by the search filter must be kept.
                var selected = Object.keys(servicesMemberSet).sort();

                if (!selected.length) {
                    setServicesError('Sélectionnez au moins un Web Service : un domaine doit en contenir au moins un.');
                    return;
                }

                var client = api();
                if (!client) {
                    setServicesError('Client API introuvable.');
                    return;
                }

                var code = servicesCode;
                servicesSaving = true;
                servicesSaveBtn.disabled = true;
                servicesSaveSpinner.classList.remove('d-none');
                servicesSaveText.textContent = 'Enregistrement...';

                client.put('/admin/domains/' + code + '/services', {
                        web_services: selected
                    })
                    .then(function () {
                        servicesSaving = false;
                        servicesSaveBtn.disabled = false;
                        servicesSaveSpinner.classList.add('d-none');
                        servicesSaveText.textContent = 'Enregistrer';
                        closeServicesModal();
                        showToast('Web Services du domaine mis à jour.');
                        loadDomains();
                    })
                    .catch(function (err) {
                        servicesSaving = false;
                        servicesSaveBtn.disabled = false;
                        servicesSaveSpinner.classList.add('d-none');
                        servicesSaveText.textContent = 'Enregistrer';
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 401) {
                            clearSession();
                            return;
                        }
                        var msg = 'Une erreur est survue. Veuillez réessayer.';
                        if (status === 403) msg = 'Accès non autorisé.';
                        else if (status === 404) msg = 'Domaine introuvable.';
                        else if (status === 422 && body && body.errors && body.errors.web_services) {
                            msg = Array.isArray(body.errors.web_services) ? body.errors.web_services[0] :
                                String(body.errors.web_services);
                        } else if (body && body.message) msg = body.message;
                        setServicesError(msg);
                    });
            });

            // ════════════════════════════════════════════════════
            //  DELETE DOMAIN MODAL
            // ════════════════════════════════════════════════════
            var deleteModalEl = document.getElementById('domain-delete-modal');
            var deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            var deleteBtn = document.getElementById('btn-delete-domain-submit');
            var deleteText = document.getElementById('btn-delete-domain-text');
            var deleteSpinner = document.getElementById('btn-delete-domain-spinner');
            var deleting = false;
            var deleteCode = null;
            var deleteLastFocused = null;

            function setDeleteError(msg) {
                var el = document.getElementById('domain-delete-modal-error');
                el.textContent = msg;
                el.classList.remove('d-none');
            }

            function openDeleteModal(code) {
                var d = domainsByCode[code];
                if (!d) return;
                deleteCode = code;
                deleteLastFocused = document.activeElement;
                document.getElementById('domain-delete-modal-error').classList.add('d-none');
                document.getElementById('domain-delete-code-display').textContent = d.code || '';
                deleteModal.show();
            }

            function closeDeleteModal() {
                deleteModal.hide();
                deleteCode = null;
                if (deleteLastFocused && deleteLastFocused.focus) deleteLastFocused.focus();
            }

            deleteModalEl.addEventListener('hide.bs.modal', function (e) {
                if (deleting) e.preventDefault();
            });

            document.getElementById('domain-delete-modal-close').addEventListener('click', closeDeleteModal);
            document.getElementById('domain-delete-modal-cancel').addEventListener('click', closeDeleteModal);

            deleteBtn.addEventListener('click', function () {
                if (deleting || !deleteCode) return;

                var client = api();
                if (!client) {
                    setDeleteError('Client API introuvable.');
                    return;
                }

                var code = deleteCode;
                deleting = true;
                deleteBtn.disabled = true;
                deleteSpinner.classList.remove('d-none');
                deleteText.textContent = 'Suppression...';

                client['delete']('/admin/domains/' + code)
                    .then(function () {
                        deleting = false;
                        deleteBtn.disabled = false;
                        deleteSpinner.classList.add('d-none');
                        deleteText.textContent = 'Supprimer';
                        closeDeleteModal();
                        showToast('Domaine supprimé avec succès.');
                        loadDomains();
                    })
                    .catch(function (err) {
                        deleting = false;
                        deleteBtn.disabled = false;
                        deleteSpinner.classList.add('d-none');
                        deleteText.textContent = 'Supprimer';
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 401) {
                            clearSession();
                            return;
                        }
                        var msg = 'Une erreur est survenue. Veuillez réessayer.';
                        if (status === 403) msg = 'Accès non autorisé.';
                        else if (status === 404) msg = 'Domaine introuvable.';
                        else if (body && body.message) msg = body.message;
                        setDeleteError(msg);
                    });
            });
        })();
    </script>
@endpush
