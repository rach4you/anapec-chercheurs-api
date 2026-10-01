@push('styles')
    <style>
        /* JS-driven visibility helper used by the inline scripts below. */
        .hidden {
            display: none !important;
        }

        /* Skeleton loading bars */
        @keyframes ws-pulse {
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
            animation: ws-pulse 1.6s ease-in-out infinite;
        }

        /* Technical identifiers (web service code, domain code) */
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

        /* Domain picker rows inside the create Web Service modal */
        .ws-domain-option {
            transition: border-color .15s ease, background-color .15s ease;
        }

        .ws-domain-option:hover {
            border-color: var(--bs-primary, #1266f1);
        }

        .ws-domain-option:has(.form-check-input:checked) {
            border-color: var(--bs-primary, #1266f1);
            background-color: rgba(18, 102, 241, .05);
        }

        /* Switch control ("Service actif") */
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

@section('title', 'Web Services')

@section('content')
    {{-- ── PAGE HEADING ── --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mb-6 mb-xl-8">
        <div class="d-flex align-items-center gap-3">
            <div class="symbol symbol-50px bg-light-primary bg-opacity-100 flex-shrink-0">
                <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-element-11 fs-2"></i></span>
            </div>
            <div>
                <h2 class="fw-bold text-dark fs-3 mb-0">Web Services</h2>
                <p class="text-muted fs-6 mb-0">Gérez les services disponibles et leur accès global.</p>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <button type="button" id="btn-create-ws" class="btn btn-primary fw-semibold">
                <span class="svg-icon svg-icon-2"><i class="ki-outline ki-plus"></i></span>
                <span class="ms-2">Créer un Web Service</span>
            </button>
        </div>
    </div>

    {{-- ── AUTHORIZATION LOADING STATE ── --}}
    <div id="auth-loading" class="alert alert-info" role="status">
        <div class="alert-body d-flex align-items-center">
            <span class="spinner-border spinner-border-sm text-primary me-3" role="status" aria-hidden="true"></span>
            <div>
                <h4 class="fs-6 fw-bolder text-dark mb-1">Vérification des accès</h4>
                <p class="fs-7 text-muted mb-0">Chargement des Web Services...</p>
            </div>
        </div>
    </div>

    {{-- ── ACCESS DENIED (403 / non-admin) ── --}}
    <div id="access-denied" class="alert alert-danger d-none" role="alert">
        <div class="alert-body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="svg-icon svg-icon-2x svg-icon-danger flex-shrink-0"><i class="ki-outline ki-shield"></i></span>
                <div class="flex-grow-1">
                    <h4 class="fs-6 fw-bolder text-white mb-1">Accès non autorisé</h4>
                    <p class="fs-7 text-white opacity-75 mb-0">Vous devez être administrateur pour gérer les Web Services.</p>
                </div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light fw-semibold">Retour au Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── LOAD ERROR + RETRY ── --}}
    <div id="load-error" class="alert alert-danger d-none" role="alert">
        <div class="alert-body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="svg-icon svg-icon-2x svg-icon-danger flex-shrink-0"><i class="ki-outline ki-cloud-off"></i></span>
                <div class="flex-grow-1">
                    <h4 class="fs-6 fw-bolder text-white mb-1">Impossible de charger les Web Services</h4>
                    <p class="fs-7 text-white opacity-75 mb-0">Vérifiez votre connexion et réessayez.</p>
                </div>
                <div>
                    <button type="button" id="btn-retry" class="btn btn-sm btn-light fw-semibold">Réessayer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MAIN CONTENT (shown once the admin session is confirmed) ── --}}
    <div id="admin-web-services-root" class="hidden">
        {{-- ── SUMMARY / KPI ── --}}
        <div class="row g-4 g-xl-5 mb-6 mb-xl-8">
            <div class="col-sm-6 col-xl-4">
                <div class="card card-flush">
                    <div class="card-body d-flex align-items-center p-6">
                        <div class="flex-grow-1">
                            <span class="text-muted fs-6 d-block">Total Web Services</span>
                            <span class="fs-2x fw-bolder text-dark" id="stat-total-services">—</span>
                            <span class="text-muted fs-7 d-block mt-1">Catalogue des services disponibles</span>
                        </div>
                        <span class="svg-icon svg-icon-2x svg-icon-primary bg-light-primary rounded me-3">
                            <i class="ki-outline ki-element-11"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card card-flush">
                    <div class="card-body d-flex align-items-center p-6">
                        <div class="flex-grow-1">
                            <span class="text-muted fs-6 d-block">Services actifs</span>
                            <span class="fs-2x fw-bolder text-success" id="stat-active-services">—</span>
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
                            <span class="text-muted fs-6 d-block">Services désactivés</span>
                            <span class="fs-2x fw-bolder text-danger" id="stat-inactive-services">—</span>
                            <span class="text-muted fs-7 d-block mt-1">Statut global coupé</span>
                        </div>
                        <span class="svg-icon svg-icon-2x svg-icon-danger bg-light-danger rounded me-3">
                            <i class="ki-outline ki-toggle-off"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TABLE CARD ── --}}
        <div class="card card-flush mb-6 mb-xl-8" id="ws-table-card">
            <div class="card-header border-0">
                <div class="card-title">
                    <h3 class="fw-bold text-dark fs-4">Liste des Web Services</h3>
                    <span class="text-muted fw-semibold fs-7" id="ws-total">0 service</span>
                </div>
                <div class="card-toolbar d-flex flex-wrap align-items-center gap-3">
                    <div class="input-group input-group-solid w-150px w-lg-200px">
                        <input type="text" id="ws-search" class="form-control border-0 fs-7"
                            placeholder="Rechercher un Web Service…" aria-label="Rechercher un Web Service" />
                    </div>
                    <select id="ws-status-filter" class="form-select form-select-solid form-select-sm w-150px"
                        aria-label="Filtrer par statut">
                        <option value="">Tous statuts</option>
                        <option value="active">Actifs</option>
                        <option value="inactive">Désactivés</option>
                    </select>
                    <button type="button" id="btn-refresh-ws" class="btn btn-icon btn-light-primary"
                        aria-label="Actualiser la liste" title="Actualiser la liste">
                        <span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-abstract-18 fs-2"></i></span>
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- Skeleton rows --}}
                <div id="ws-skeleton" class="hidden"></div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="ws-table" role="grid"
                        aria-label="Web Services">
                        <thead>
                            <tr class="text-nowrap fw-bold fs-7 text-muted">
                                <th class="text-start ps-4 pe-2">Web Service</th>
                                <th class="text-start pe-2">Code</th>
                                <th class="text-start pe-2">Description</th>
                                <th class="text-start pe-2 text-center">Statut global</th>
                                <th class="text-start pe-2 text-center">Domaines</th>
                                <th class="text-end ps-2 pe-4 text-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ws-tbody"></tbody>
                    </table>
                </div>

                {{-- Empty state --}}
                <div id="ws-empty" class="hidden p-6 text-center">
                    <span class="svg-icon svg-icon-3x text-muted d-block mb-3"><i class="ki-outline ki-element-11"></i></span>
                    <h3 id="ws-empty-title" class="text-dark fs-4 fw-bolder mb-1">Aucun Web Service disponible.</h3>
                    <p id="ws-empty-text" class="text-muted fs-6 mb-0">Le catalogue de services est vide.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SUCCESS / ERROR TOAST ── --}}
    <div id="toast" class="alert alert-success position-fixed top-0 end-0 m-5 shadow d-none" role="status"
        aria-live="polite">
        <p id="toast-msg" class="fs-7 fw-bold mb-0"></p>
    </div>

    {{-- ── CREATE WEB SERVICE MODAL ── --}}
    <div class="modal fade" id="ws-create-modal" tabindex="-1" role="dialog"
        aria-labelledby="ws-create-modal-title">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content rounded shadow-lg">
                <form id="ws-create-form" novalidate>
                    <div class="modal-header border-0 pt-5 pb-2 px-7">
                        <h5 class="fs-5 fw-bolder text-dark" id="ws-create-modal-title">Créer un Web Service</h5>
                        <button type="button" id="ws-create-modal-close" class="btn-close" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body px-7 pb-5">
                        <div id="ws-create-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="create-ws-code" class="form-label fw-bold fs-7">Code <span class="text-danger">*</span></label>
                                <input type="text" id="create-ws-code" name="code" required maxlength="60"
                                    class="form-control form-control-solid fs-6 code-badge" placeholder="WS_INSCRIPTION"
                                    autocomplete="off" />
                                <span class="form-text fs-7">Identifiant technique unique du Web Service (majuscules, ex. : WS_INSCRIPTION).</span>
                                <div id="err-create-ws-code" class="form-text text-danger fw-semibold fs-7 d-none"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="create-ws-name" class="form-label fw-bold fs-7">Nom <span class="text-danger">*</span></label>
                                <input type="text" id="create-ws-name" name="name" required maxlength="255"
                                    class="form-control form-control-solid fs-6" placeholder="Inscription chercheur"
                                    autocomplete="off" />
                                <div id="err-create-ws-name" class="form-text text-danger fw-semibold fs-7 d-none"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="create-ws-description" class="form-label fw-bold fs-7">Description</label>
                            <textarea id="create-ws-description" name="description" rows="3" maxlength="500"
                                class="form-control form-control-solid fs-6"
                                placeholder="Décrivez l'objectif fonctionnel du Web Service…"></textarea>
                            <span class="form-text fs-7">Facultatif — 500 caractères maximum.</span>
                            <div id="err-create-ws-description"
                                class="form-text text-danger fw-semibold fs-7 d-none"></div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                <label class="form-label fw-bold fs-7 mb-0">Domaines</label>
                                <span class="badge badge-light-primary fw-semibold fs-7" id="create-ws-domains-counter">0 sélectionné</span>
                            </div>
                            <div id="create-ws-domains-loading" class="d-none p-3">
                                <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                <span class="fs-7 text-muted">Chargement des domaines…</span>
                            </div>
                            <div id="create-ws-domains-list" class="scroll-y scroll-me"
                                style="max-height:220px"></div>
                            <div id="create-ws-domains-empty" class="hidden p-3 text-center border rounded border-gray-200">
                                <span class="svg-icon svg-icon-2x text-muted d-block mb-2"><i class="ki-outline ki-folder"></i></span>
                                <p class="text-muted fs-7 mb-0">Aucun domaine disponible. Créez d'abord un domaine.</p>
                            </div>
                            <div id="create-ws-domains-error" class="d-none p-3 text-center">
                                <span class="svg-icon svg-icon-2x text-danger d-block mb-2"><i class="ki-outline ki-cloud-off"></i></span>
                                <p class="text-danger fs-7 mb-0">Impossible de charger la liste des domaines.</p>
                            </div>
                            <span class="form-text fs-7">Facultatif — un Web Service peut être rattaché à plusieurs domaines existants.</span>
                            <div id="err-create-ws-domain_codes" class="form-text text-danger fw-semibold fs-7 d-none"></div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-4 rounded border border-gray-200">
                            <div>
                                <span class="fs-6 fw-bold text-dark d-block">Service actif</span>
                                <span class="text-muted fs-7">Un service désactivé n'est accessible à aucun utilisateur.</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="switch">
                                    <input type="checkbox" id="create-ws-is-active" name="is_active" checked />
                                    <span class="track"></span>
                                    <span class="handle"></span>
                                </label>
                                <span class="fs-7 fw-semibold text-success" id="create-ws-active-label">Actif</span>
                            </div>
                        </div>
                        <div id="err-create-ws-is_active" class="form-text text-danger fw-semibold fs-7 d-none mt-1"></div>
                    </div>
                    <div class="modal-footer border-0 pt-4 pb-5 px-7">
                        <button type="button" id="ws-create-modal-cancel"
                            class="btn btn-light-secondary fw-semibold">Annuler</button>
                        <button type="submit" id="btn-create-ws-submit" class="btn btn-primary fw-semibold">
                            <span id="btn-create-ws-spinner"
                                class="spinner-border spinner-border-sm text-white d-none me-2" role="status"
                                aria-hidden="true"></span>
                            <span id="btn-create-ws-text">Créer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── EDIT WEB SERVICE MODAL ── --}}
    <div class="modal fade" id="edit-ws-modal" tabindex="-1" role="dialog"
        aria-labelledby="edit-ws-modal-title">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded shadow-lg">
                <form id="edit-ws-form" novalidate>
                    <div class="modal-header border-0 pt-5 pb-2 px-7">
                        <h5 class="fs-5 fw-bolder text-dark" id="edit-ws-modal-title">Modifier le Web Service</h5>
                        <button type="button" id="edit-ws-modal-close" class="btn-close" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body px-7 pb-5">
                        <div id="edit-ws-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>

                        <div class="mb-4">
                            <label for="edit-ws-code" class="form-label fw-bold fs-7">Code</label>
                            <input type="text" id="edit-ws-code"
                                class="form-control form-control-solid fs-6 code-badge bg-gray-100 text-muted"
                                readonly tabindex="-1" />
                            <span class="form-text fs-7">Le code est un identifiant technique et ne peut pas être modifié.</span>
                        </div>

                        <div class="mb-4">
                            <label for="edit-ws-name" class="form-label fw-bold fs-7">Nom <span class="text-danger">*</span></label>
                            <input type="text" id="edit-ws-name" name="name" required maxlength="255" autocomplete="off"
                                class="form-control form-control-solid fs-6" />
                            <div id="err-edit-ws-name" class="form-text text-danger fw-semibold fs-7 d-none"></div>
                        </div>

                        <div class="mb-4">
                            <label for="edit-ws-description" class="form-label fw-bold fs-7">Description</label>
                            <textarea id="edit-ws-description" name="description" rows="3" maxlength="500"
                                class="form-control form-control-solid fs-6"></textarea>
                            <span class="form-text fs-7">Facultatif — 500 caractères maximum.</span>
                            <div id="err-edit-ws-description"
                                class="form-text text-danger fw-semibold fs-7 d-none"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-4 pb-5 px-7">
                        <button type="button" id="edit-ws-modal-cancel"
                            class="btn btn-light-secondary fw-semibold">Annuler</button>
                        <button type="submit" id="btn-edit-web-service" class="btn btn-primary fw-semibold">
                            <span id="btn-edit-web-service-spinner"
                                class="spinner-border spinner-border-sm text-white d-none me-2" role="status"
                                aria-hidden="true"></span>
                            <span id="btn-edit-web-service-text">Enregistrer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── CONFIRM STATUS MODAL ── --}}
    <div class="modal fade" id="status-modal" tabindex="-1" role="dialog"
        aria-labelledby="status-modal-title">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded shadow-lg">
                <div class="modal-header border-0 pt-5 pb-2 px-7">
                    <h5 class="fs-5 fw-bolder text-dark" id="status-modal-title">
                        <span id="status-modal-icon" class="svg-icon svg-icon-2 text-primary me-2">
                            <i class="ki-outline ki-toggle-off"></i>
                        </span>
                        <span id="status-modal-title-text">Modifier le statut global</span>
                    </h5>
                    <button type="button" id="status-modal-close" class="btn-close" aria-label="Fermer"></button>
                </div>
                <div class="modal-body px-7 pb-5">
                    <div id="status-modal-error" class="alert alert-danger py-3 d-none" role="alert"></div>
                    <div class="d-flex align-items-start gap-3">
                        <span id="status-modal-bullet" class="svg-icon svg-icon-2x svg-icon-warning flex-shrink-0 mt-1">
                            <i class="ki-outline ki-info"></i>
                        </span>
                        <p id="status-modal-message" class="fs-6 text-gray-700 mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-4 pb-5 px-7">
                    <button type="button" id="status-modal-cancel"
                        class="btn btn-light-secondary fw-semibold">Annuler</button>
                    <button type="button" id="btn-confirm-status" class="btn btn-primary fw-semibold">
                        <span id="btn-confirm-status-spinner"
                            class="spinner-border spinner-border-sm text-white d-none me-2" role="status"
                            aria-hidden="true"></span>
                        <span id="btn-confirm-status-text">Confirmer</span>
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
                msgEl.className = 'fs-7 fw-bold mb-0 ' + (type === 'error' ? 'text-danger' : 'text-success');
                t.classList.remove('d-none');
                clearTimeout(t._timer);
                t._timer = setTimeout(function () {
                    t.classList.add('d-none');
                }, 4000);
            }

            // ── State ──
            var allServices = [];
            var servicesByCode = {};
            var state = {
                query: '',
                status: ''
            };

            // ── Elements ──
            var authLoading = document.getElementById('auth-loading');
            var accessDenied = document.getElementById('access-denied');
            var loadError = document.getElementById('load-error');
            var tbody = document.getElementById('ws-tbody');
            var skeleton = document.getElementById('ws-skeleton');
            var empty = document.getElementById('ws-empty');
            var tableEl = document.getElementById('ws-table');
            var totalEl = document.getElementById('ws-total');
            var searchEl = document.getElementById('ws-search');
            var statusFilterEl = document.getElementById('ws-status-filter');

            function showAuthLoading(show) {
                authLoading.classList.toggle('d-none', !show);
            }

            function showAdminRoot() {
                showAuthLoading(false);
                accessDenied.classList.add('d-none');
                loadError.classList.add('d-none');
                document.getElementById('admin-web-services-root').classList.remove('hidden');
            }

            function showAccessDenied() {
                showAuthLoading(false);
                document.getElementById('admin-web-services-root').classList.add('hidden');
                loadError.classList.add('d-none');
                accessDenied.classList.remove('d-none');
            }

            function showLoadError() {
                showAuthLoading(false);
                document.getElementById('admin-web-services-root').classList.add('hidden');
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

            function domainBadges(ws) {
                var domains = Array.isArray(ws.domains) ? ws.domains : [];
                if (!domains.length) {
                    return '<span class="text-muted fs-7">—</span>';
                }
                var html = '<div class="d-flex flex-wrap gap-1 justify-content-center">';
                domains.forEach(function (d) {
                    html += '<span class="badge badge-light-info fw-semibold code-badge">' +
                        esc(d.code) + '</span>';
                });
                html += '</div>';
                return html;
            }

            // ── Service avatars ──
            function pickAvatarClass(name) {
                var classes = [
                    'bg-light-primary bg-opacity-100',
                    'bg-light-info bg-opacity-100',
                    'bg-light-success bg-opacity-100',
                    'bg-light-warning bg-opacity-100',
                    'bg-light-danger bg-opacity-100',
                    'bg-light-purple bg-opacity-100'
                ];
                return classes[hashOf(name) % classes.length];
            }

            function pickSymbolColor(name) {
                var colors = ['text-primary', 'text-info', 'text-success', 'text-warning', 'text-danger', 'text-purple'];
                return colors[hashOf(name) % colors.length];
            }

            function serviceInitials(s) {
                var parts = ((s.name || '') + ' ' + (s.code || '')).trim().split(/\s+/).filter(function (p) {
                    return p.length > 0;
                });
                if (!parts.length) return '?';
                if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
                return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
            }

            // ── Row actions (Metronic dropdown) ──
            function rowActions(s) {
                var code = s.code || '';
                var active = s.is_active === true;
                var nextActive = !active;
                return '<div class="dropdown d-inline-block">' +
                    '<button type="button" class="btn btn-sm btn-icon btn-light-primary" data-bs-toggle="dropdown" ' +
                    'aria-expanded="false" aria-label="Actions pour le Web Service ' + attr(code) + '">' +
                    '<span class="svg-icon svg-icon-2 text-primary"><i class="ki-outline ki-more-2"></i></span>' +
                    '</button>' +
                    '<ul class="dropdown-menu dropdown-menu-end py-2 fw-normal w-220px">' +
                    '<li><a href="/admin/web-services/' + encodeURIComponent(code) + '" ' +
                    'class="row-action d-flex align-items-center mb-1 px-3 py-2 text-decoration-none text-dark hover-bg-light hover-color-dark rounded">' +
                    '<span class="svg-icon svg-icon-2 text-gray-500 me-2"><i class="ki-outline ki-element-11"></i></span>' +
                    '<span class="fs-7 fw-semibold">Détails</span></a></li>' +
                    '<li><button type="button" data-ws-edit-code="' + attr(code) + '" ' +
                    'class="row-action d-flex align-items-center mb-1 px-3 py-2 border-0 bg-transparent text-start text-dark hover-bg-light rounded w-100">' +
                    '<span class="svg-icon svg-icon-2 text-secondary me-2"><i class="ki-outline ki-pencil"></i></span>' +
                    '<span class="fs-7 fw-semibold">Modifier</span></button></li>' +
                    '<li><button type="button" data-ws-code="' + attr(code) + '" data-next="' +
                    (nextActive ? 'true' : 'false') + '" ' +
                    'class="row-action d-flex align-items-center px-3 py-2 border-0 bg-transparent text-start text-dark hover-bg-light rounded w-100">' +
                    '<span class="svg-icon svg-icon-2 text-' + (nextActive ? 'success' : 'danger') + ' me-2"><i class="ki-outline ki-toggle-' +
                    (nextActive ? 'on' : 'off') + '"></i></span>' +
                    '<span class="fs-7 fw-semibold row-action-label">' + (nextActive ? 'Activer' : 'Désactiver') + '</span></button></li>' +
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
                        '<td class="p-4 ps-4 pe-2"><span class="skeleton-bar" style="width:160px"></span></td>' +
                        '<td class="p-4 pe-2"><span class="skeleton-bar" style="width:90px"></span></td>' +
                        '<td class="p-4 pe-2"><span class="skeleton-bar" style="width:200px"></span></td>' +
                        '<td class="p-4 pe-2 text-center"><span class="skeleton-bar" style="width:80px"></span></td>' +
                        '<td class="p-4 pe-2 text-center"><span class="skeleton-bar" style="width:90px"></span></td>' +
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
            function visibleServices() {
                var q = state.query.toLowerCase();
                return allServices.filter(function (s) {
                    if (state.status === 'active' && s.is_active !== true) return false;
                    if (state.status === 'inactive' && s.is_active === true) return false;
                    if (!q) return true;
                    var hay = ((s.code || '') + ' ' + (s.name || '') + ' ' + (s.description || '')).toLowerCase();
                    return hay.indexOf(q) !== -1;
                });
            }

            function renderStats(services) {
                var total = services.length;
                var active = 0;
                var inactive = 0;
                services.forEach(function (s) {
                    if (s.is_active === true) active++;
                    else inactive++;
                });
                document.getElementById('stat-total-services').textContent = String(total);
                document.getElementById('stat-active-services').textContent = String(active);
                document.getElementById('stat-inactive-services').textContent = String(inactive);
                document.getElementById('stat-active-note').textContent = total
                    ? active + ' sur ' + total + ' service' + (total > 1 ? 's' : '')
                    : 'Aucun service';
            }

            function renderRows() {
                servicesByCode = {};
                allServices.forEach(function (s) {
                    servicesByCode[s.code] = s;
                });

                renderStats(allServices);

                totalEl.textContent = allServices.length + ' service' + (allServices.length > 1 ? 's' : '');

                if (!allServices.length) {
                    tableEl.classList.add('hidden');
                    tbody.classList.add('hidden');
                    tbody.innerHTML = '';
                    empty.classList.remove('hidden');
                    document.getElementById('ws-empty-title').textContent = 'Aucun Web Service disponible.';
                    document.getElementById('ws-empty-text').textContent =
                        'Le catalogue de services est vide.';
                    return;
                }

                var list = visibleServices();

                if (!list.length) {
                    tableEl.classList.add('hidden');
                    tbody.classList.add('hidden');
                    tbody.innerHTML = '';
                    empty.classList.remove('hidden');
                    document.getElementById('ws-empty-title').textContent = 'Aucun Web Service trouvé.';
                    document.getElementById('ws-empty-text').textContent =
                        'Aucun Web Service ne correspond à votre recherche ou à votre filtre.';
                    return;
                }

                empty.classList.add('hidden');
                tableEl.classList.remove('hidden');
                tbody.classList.remove('hidden');

                var rows = '';
                list.forEach(function (s) {
                    var code = s.code || '';
                    var label = (s.name || code) || '?';
                    var desc = s.description
                        ? '<span class="d-block text-dark fs-7" style="max-width:320px" title="' +
                        attr(s.description) + '">' + esc(s.description) + '</span>'
                        : '<span class="text-muted fs-7">—</span>';

                    rows += '<tr>' +
                        '<td class="p-4 ps-4 pe-2">' +
                        '<div class="d-flex align-items-center gap-3">' +
                        '<div class="symbol symbol-45px ' + pickAvatarClass(label) + ' flex-shrink-0">' +
                        '<span class="symbol-label fs-7 fw-bolder ' + pickSymbolColor(label) + '">' +
                        esc(serviceInitials(s)) + '</span>' +
                        '</div>' +
                        '<div style="min-width:0">' +
                        '<div class="text-dark fw-bolder fs-7 text-truncate" title="' + attr(s.name) + '">' +
                        esc(s.name) + '</div>' +
                        '<span class="text-muted fs-7 text-truncate d-block">' + esc(code) + '</span>' +
                        '</div>' +
                        '</div>' +
                        '</td>' +
                        '<td class="p-4 pe-2 text-nowrap"><span class="badge badge-light-dark fw-semibold code-badge">' +
                        esc(code) + '</span></td>' +
                        '<td class="p-4 pe-2">' + desc + '</td>' +
                        '<td class="p-4 pe-2 text-center">' + statusBadge(s.is_active === true) + '</td>' +
                        '<td class="p-4 pe-2 text-center">' + domainBadges(s) + '</td>' +
                        '<td class="p-4 ps-2 pe-4 text-end">' + rowActions(s) + '</td>' +
                        '</tr>';
                });
                tbody.innerHTML = rows;
            }

            // Delegation: the tbody is re-rendered after every mutation.
            tbody.addEventListener('click', function (e) {
                var toggleBtn = e.target.closest('[data-ws-code]');
                if (toggleBtn) {
                    openStatusConfirm(toggleBtn.getAttribute('data-ws-code'),
                        toggleBtn.getAttribute('data-next') === 'true', toggleBtn);
                    return;
                }
                var editBtn = e.target.closest('[data-ws-edit-code]');
                if (editBtn) {
                    openEditModal(editBtn.getAttribute('data-ws-edit-code'));
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
            document.getElementById('btn-refresh-ws').addEventListener('click', loadServices);
            document.getElementById('btn-retry').addEventListener('click', loadServices);

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
                        loadServices();
                    });
                })
                .catch(function () {
                    showLoadError();
                });

            function loadServices() {
                showSkeleton();
                get('/api/v1/admin/web-services')
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
                            allServices = data.data;
                            renderRows();
                        });
                    })
                    .catch(function () {
                        hideSkeleton();
                        showLoadError();
                    });
            }

            // ════════════════════════════════════════════════════
            //  CREATE WEB SERVICE MODAL
            // ════════════════════════════════════════════════════
            var createModalEl = document.getElementById('ws-create-modal');
            var createModal = bootstrap.Modal.getOrCreateInstance(createModalEl);
            var createForm = document.getElementById('ws-create-form');
            var createBtn = document.getElementById('btn-create-ws-submit');
            var createText = document.getElementById('btn-create-ws-text');
            var createSpinner = document.getElementById('btn-create-ws-spinner');
            var createActiveCb = document.getElementById('create-ws-is-active');
            var createActiveLabel = document.getElementById('create-ws-active-label');
            var createCodeInput = document.getElementById('create-ws-code');
            var createNameInput = document.getElementById('create-ws-name');
            var createDescInput = document.getElementById('create-ws-description');
            var createGlobalError = document.getElementById('ws-create-modal-error');
            var createDomainsList = document.getElementById('create-ws-domains-list');
            var createDomainsLoading = document.getElementById('create-ws-domains-loading');
            var createDomainsEmpty = document.getElementById('create-ws-domains-empty');
            var createDomainsError = document.getElementById('create-ws-domains-error');
            var createDomainsCounter = document.getElementById('create-ws-domains-counter');
            var allDomains = [];
            var creating = false;
            var domainsLoaded = false;

            function clearCreateErrors() {
                ['code', 'name', 'description', 'domain_codes', 'is_active'].forEach(function (f) {
                    var el = document.getElementById('err-create-ws-' + f);
                    if (el) el.classList.add('d-none');
                });
                createGlobalError.classList.add('d-none');
            }

            function setCreateError(field, msg) {
                var el = document.getElementById('err-create-ws-' + field);
                if (el) {
                    el.textContent = msg;
                    el.classList.remove('d-none');
                }
            }

            function setCreateFormError(msg) {
                createGlobalError.textContent = msg;
                createGlobalError.classList.remove('d-none');
            }

            function updateCreateActiveLabel() {
                var active = createActiveCb.checked === true;
                createActiveLabel.textContent = active ? 'Actif' : 'Désactivé';
                createActiveLabel.className = 'fs-7 fw-semibold ' + (active ? 'text-success' : 'text-muted');
            }

            function updateCreateDomainsCounter() {
                var n = createDomainsList.querySelectorAll('input[type="checkbox"]:checked').length;
                createDomainsCounter.textContent = n + ' sélectionné' + (n > 1 ? 's' : '');
            }

            function renderDomainsList() {
                if (!allDomains.length) {
                    createDomainsEmpty.classList.remove('hidden');
                    createDomainsList.classList.add('d-none');
                    createDomainsError.classList.add('d-none');
                    updateCreateDomainsCounter();
                    return;
                }
                createDomainsEmpty.classList.add('hidden');
                createDomainsError.classList.add('d-none');
                var html = '';
                allDomains.forEach(function (d) {
                    html += '<label class="ws-domain-option d-flex align-items-center gap-3 p-3 mb-2 border rounded border-gray-200">' +
                        '<input type="checkbox" data-domain-code="' + attr(d.code) + '" ' +
                        'class="form-check-input flex-shrink-0" aria-label="' + attr(d.name || d.code) + '" />' +
                        '<span class="d-block flex-grow-1" style="min-width:0">' +
                        '<span class="d-block text-dark fw-bold fs-7 code-badge text-truncate">' + esc(d.code) + '</span>' +
                        '<span class="d-block text-muted fs-7 text-truncate">' + esc(d.name) + '</span>' +
                        '</span>' +
                        (d.is_active === true
                            ? '<span class="badge badge-light-success fw-semibold">Actif</span>'
                            : '<span class="badge badge-light-secondary fw-semibold">Désactivé</span>') +
                        '</label>';
                });
                createDomainsList.innerHTML = html;
                createDomainsList.classList.remove('d-none');
                createDomainsList.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
                    cb.addEventListener('change', updateCreateDomainsCounter);
                });
                updateCreateDomainsCounter();
            }

            function loadDomainsForCreate() {
                if (domainsLoaded) return;
                createDomainsLoading.classList.remove('d-none');
                createDomainsList.classList.add('d-none');
                createDomainsEmpty.classList.add('hidden');
                createDomainsError.classList.add('d-none');
                get('/api/v1/admin/domains')
                    .then(function (res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(function (data) {
                        createDomainsLoading.classList.add('d-none');
                        if (!data || !data.success || !Array.isArray(data.data)) {
                            createDomainsError.classList.remove('d-none');
                            return;
                        }
                        allDomains = data.data;
                        domainsLoaded = true;
                        renderDomainsList();
                    })
                    .catch(function () {
                        createDomainsLoading.classList.add('d-none');
                        createDomainsError.classList.remove('d-none');
                    });
            }

            function openCreateModal() {
                createForm.reset();
                clearCreateErrors();
                createCodeInput.value = '';
                createNameInput.value = '';
                createDescInput.value = '';
                createActiveCb.checked = true;
                updateCreateActiveLabel();
                if (!domainsLoaded) loadDomainsForCreate();
                createModal.show();
            }

            function closeCreateModal() {
                createModal.hide();
            }

            createModalEl.addEventListener('hide.bs.modal', function (e) {
                if (creating) e.preventDefault();
            });

            document.getElementById('btn-create-ws').addEventListener('click', openCreateModal);
            document.getElementById('ws-create-modal-close').addEventListener('click', closeCreateModal);
            document.getElementById('ws-create-modal-cancel').addEventListener('click', closeCreateModal);
            createActiveCb.addEventListener('change', updateCreateActiveLabel);

            function validateCreateForm() {
                clearCreateErrors();
                var ok = true;
                var code = String(createCodeInput.value || '').trim();
                var name = String(createNameInput.value || '').trim();
                var description = String(createDescInput.value || '').trim();
                if (!code) {
                    setCreateError('code', 'Le code est requis.');
                    ok = false;
                } else if (!/^[A-Za-z0-9_\-]+$/.test(code)) {
                    setCreateError('code', 'Le code doit contenir uniquement des lettres, chiffres, tirets ou underscores.');
                    ok = false;
                } else if (code.length > 60) {
                    setCreateError('code', 'Le code doit comporter 60 caractères ou moins.');
                    ok = false;
                }
                if (!name) {
                    setCreateError('name', 'Le nom est requis.');
                    ok = false;
                } else if (name.length > 255) {
                    setCreateError('name', 'Le nom doit comporter 255 caractères ou moins.');
                    ok = false;
                }
                if (description.length > 500) {
                    setCreateError('description', 'La description doit comporter 500 caractères ou moins.');
                    ok = false;
                }
                return ok;
            }

            createForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (creating) return;

                if (!validateCreateForm()) return;

                var client = api();
                if (!client) {
                    setCreateFormError('Client API introuvable.');
                    return;
                }

                var code = String(createCodeInput.value || '').trim();
                var name = String(createNameInput.value || '').trim();
                var description = String(createDescInput.value || '').trim();
                var isActive = createActiveCb.checked === true;

                var selectedDomains = [];
                createDomainsList.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
                    selectedDomains.push(cb.getAttribute('data-domain-code'));
                });

                var payload = {
                    code: code,
                    name: name,
                    is_active: isActive
                };
                if (description) payload.description = description;
                if (selectedDomains.length) payload.domain_codes = selectedDomains;

                creating = true;
                createBtn.disabled = true;
                createSpinner.classList.remove('d-none');
                createText.textContent = 'Création...';

                client.post('/admin/web-services', payload)
                    .then(function () {
                        creating = false;
                        createBtn.disabled = false;
                        createSpinner.classList.add('d-none');
                        createText.textContent = 'Créer';
                        closeCreateModal();
                        showToast('Web Service créé avec succès.');
                        loadServices();
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
                            ['code', 'name', 'description', 'domain_codes', 'is_active'].forEach(function (f) {
                                if (body.errors[f]) {
                                    var msg = Array.isArray(body.errors[f]) ? body.errors[f][0] : String(body.errors[f]);
                                    setCreateError(f, msg);
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
            //  CONFIRM STATUS MODAL
            // ════════════════════════════════════════════════════
            var pendingCode = null;
            var pendingActive = null;
            var statusModalEl = document.getElementById('status-modal');
            var statusModal = bootstrap.Modal.getOrCreateInstance(statusModalEl);
            var statusModalMsg = document.getElementById('status-modal-message');
            var statusModalError = document.getElementById('status-modal-error');
            var statusModalTitleText = document.getElementById('status-modal-title-text');
            var statusModalIcon = document.getElementById('status-modal-icon');
            var statusModalBullet = document.getElementById('status-modal-bullet');
            var confirmBtn = document.getElementById('btn-confirm-status');
            var confirmText = document.getElementById('btn-confirm-status-text');
            var confirmSpinner = document.getElementById('btn-confirm-status-spinner');
            var confirming = false;

            function openStatusConfirm(code, nextActive, btn) {
                var svc = servicesByCode[code];
                if (!svc) return;
                pendingCode = code;
                pendingActive = nextActive === true;

                statusModalTitleText.textContent = pendingActive
                    ? 'Activer le Web Service'
                    : 'Désactiver le Web Service';

                var iconColor = pendingActive ? 'text-success' : 'text-danger';
                var iconGlyph = pendingActive ? 'ki-toggle-on' : 'ki-toggle-off';
                statusModalIcon.className = 'svg-icon svg-icon-2 ' + iconColor + ' me-2';
                statusModalIcon.innerHTML = '<i class="ki-outline ' + iconGlyph + '"></i>';

                statusModalBullet.className = 'svg-icon svg-icon-2x svg-icon-warning flex-shrink-0 mt-1';
                statusModalBullet.innerHTML = '<i class="ki-outline ki-info"></i>';

                statusModalMsg.innerHTML = 'Voulez-vous ' + (pendingActive ? 'activer' : 'désactiver') +
                    ' le Web Service <span class="fw-bold">' + esc(svc.name) + '</span> ' +
                    '(<span class="code-badge">' + esc(svc.code) + '</span>) ?';

                statusModalError.classList.add('d-none');
                confirmText.textContent = pendingActive ? 'Activer' : 'Désactiver';
                confirmBtn.className = 'btn fw-semibold ' + (pendingActive ? 'btn-success' : 'btn-danger');
                statusModal.show();
            }

            function closeStatusModal() {
                statusModal.hide();
            }

            statusModalEl.addEventListener('hide.bs.modal', function (e) {
                if (confirming) e.preventDefault();
            });

            document.getElementById('status-modal-close').addEventListener('click', closeStatusModal);
            document.getElementById('status-modal-cancel').addEventListener('click', closeStatusModal);

            function setBusy(busy) {
                confirming = busy;
                confirmBtn.disabled = busy;
                if (busy) {
                    confirmSpinner.classList.remove('d-none');
                    confirmText.textContent = 'Mise à jour...';
                } else {
                    confirmSpinner.classList.add('d-none');
                }
            }

            confirmBtn.addEventListener('click', function () {
                if (confirming || !pendingCode) return;

                var client = api();
                if (!client) {
                    statusModalError.textContent = 'Client API introuvable.';
                    statusModalError.classList.remove('d-none');
                    return;
                }

                setBusy(true);
                var rowBtn = tbody.querySelector('[data-ws-code="' + pendingCode + '"]');
                if (rowBtn) {
                    rowBtn.disabled = true;
                    rowBtn.setAttribute('aria-busy', 'true');
                }

                var code = pendingCode;
                var nextActive = pendingActive;
                pendingCode = null;
                pendingActive = null;

                client.patch('/admin/web-services/' + encodeURIComponent(code) + '/status', {
                        is_active: nextActive
                    })
                    .then(function (res) {
                        var body = res && res.data;
                        var data = body && body.data;
                        var finalActive = (data && typeof data.is_active === 'boolean') ? data.is_active : nextActive;
                        var s = servicesByCode[code];
                        if (s) s.is_active = finalActive;
                        renderRows();
                        closeStatusModal();
                        setBusy(false);
                        confirmText.textContent = 'Confirmer';
                        confirmBtn.className = 'btn btn-primary fw-semibold';
                        showToast(finalActive ? 'Web Service activé.' : 'Web Service désactivé.');
                    })
                    .catch(function (err) {
                        setBusy(false);
                        if (rowBtn) {
                            rowBtn.disabled = false;
                            rowBtn.removeAttribute('aria-busy');
                        }
                        var status = err.status || (err.response ? err.response.status : null);
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 401) {
                            clearSession();
                            return;
                        }
                        if (status === 403) {
                            statusModalError.textContent = 'Accès non autorisé.';
                            statusModalError.classList.remove('d-none');
                            return;
                        }
                        if (status === 422 && body && body.errors) {
                            statusModalError.textContent = Array.isArray(body.errors.is_active) ? body.errors.is_active[0] : 'Erreur de validation.';
                            statusModalError.classList.remove('d-none');
                            return;
                        }
                        statusModalError.textContent = (body && body.message) ? body.message : 'Une erreur est survenue. Veuillez réessayer.';
                        statusModalError.classList.remove('d-none');
                    });
            });

            // ════════════════════════════════════════════════════
            //  EDIT (METADATA) WEB SERVICE MODAL
            // ════════════════════════════════════════
            var editModalEl = document.getElementById('edit-ws-modal');
            var editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            var editForm = document.getElementById('edit-ws-form');
            var editGlobalError = document.getElementById('edit-ws-modal-error');
            var editSubmitBtn = document.getElementById('btn-edit-web-service');
            var editSubmitText = document.getElementById('btn-edit-web-service-text');
            var editSubmitSpinner = document.getElementById('btn-edit-web-service-spinner');
            var editCodeInput = document.getElementById('edit-ws-code');
            var editNameInput = document.getElementById('edit-ws-name');
            var editDescInput = document.getElementById('edit-ws-description');
            var editing = false;
            var editingCode = null;

            function clearEditErrors() {
                ['name', 'description'].forEach(function (f) {
                    var el = document.getElementById('err-edit-ws-' + f);
                    if (el) el.classList.add('d-none');
                });
                editGlobalError.classList.add('d-none');
            }

            function setEditFieldError(field, message) {
                var el = document.getElementById('err-edit-ws-' + field);
                if (el) {
                    el.textContent = message;
                    el.classList.remove('d-none');
                }
            }

            function openEditModal(code) {
                var svc = servicesByCode[code];
                if (!svc) return;
                editingCode = code;
                editCodeInput.value = svc.code;
                editNameInput.value = svc.name || '';
                editDescInput.value = svc.description || '';
                clearEditErrors();
                editModal.show();
            }

            function closeEditModal() {
                editModal.hide();
            }

            function validateEditForm() {
                clearEditErrors();
                var ok = true;
                var name = String(editNameInput.value || '').trim();
                var description = String(editDescInput.value || '').trim();
                if (!name) { setEditFieldError('name', 'Le nom est requis.'); ok = false; }
                else if (name.length > 255) { setEditFieldError('name', 'Le nom doit comporter 255 caractères ou moins.'); ok = false; }
                if (description.length > 500) { setEditFieldError('description', 'La description doit comporter 500 caractères ou moins.'); ok = false; }
                return ok;
            }

            editModalEl.addEventListener('hide.bs.modal', function (e) {
                if (editing) e.preventDefault();
            });

            document.getElementById('edit-ws-modal-close').addEventListener('click', closeEditModal);
            document.getElementById('edit-ws-modal-cancel').addEventListener('click', closeEditModal);

            editForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (editing || !editingCode) return;

                if (!validateEditForm()) return;

                var client = api();
                if (!client) {
                    editGlobalError.textContent = 'Client API introuvable.';
                    editGlobalError.classList.remove('d-none');
                    return;
                }

                var code = editingCode;
                var payload = {
                    name: String(editNameInput.value || '').trim(),
                    description: String(editDescInput.value || '').trim()
                };

                editing = true;
                editSubmitBtn.disabled = true;
                editSubmitSpinner.classList.remove('d-none');
                editSubmitText.textContent = 'Enregistrement...';

                client.patch('/admin/web-services/' + encodeURIComponent(code), payload)
                    .then(function (res) {
                        var body = res && res.data;
                        var updated = body && body.data;
                        for (var i = 0; i < allServices.length; i++) {
                            if (allServices[i].code === code) {
                                allServices[i].name = updated ? updated.name : payload.name;
                                allServices[i].description = updated ? updated.description : payload.description;
                                break;
                            }
                        }
                        editing = false;
                        editSubmitBtn.disabled = false;
                        editSubmitSpinner.classList.add('d-none');
                        editSubmitText.textContent = 'Enregistrer';
                        closeEditModal();
                        showToast('Web Service modifié avec succès.');
                        renderRows();
                    })
                    .catch(function (err) {
                        editing = false;
                        editSubmitBtn.disabled = false;
                        editSubmitSpinner.classList.add('d-none');
                        editSubmitText.textContent = 'Enregistrer';
                        var status = err.status || (err.response ? err.response.status : null);
                        if (status === 401) {
                            clearSession();
                            return;
                        }
                        if (status === 403) {
                            editGlobalError.textContent = 'Accès non autorisé.';
                            editGlobalError.classList.remove('d-none');
                            return;
                        }
                        if (status === 404) {
                            editGlobalError.textContent = 'Web Service introuvable.';
                            editGlobalError.classList.remove('d-none');
                            return;
                        }
                        var body = err.body || (err.response ? err.response.data : null);
                        if (status === 422 && body && body.errors) {
                            var shown = false;
                            ['name', 'description'].forEach(function (f) {
                                if (body.errors[f]) {
                                    setEditFieldError(f, Array.isArray(body.errors[f]) ? body.errors[f][0] : body.errors[f]);
                                    shown = true;
                                }
                            });
                            if (shown) return;
                            editGlobalError.textContent = body.message || 'Erreur de validation.';
                            editGlobalError.classList.remove('d-none');
                            return;
                        }
                        editGlobalError.textContent = (body && body.message) ? body.message : 'Une erreur est survenue. Veuillez réessayer.';
                        editGlobalError.classList.remove('d-none');
                    });
            });

            renderRows();

            // Deep-link: opening the list with ?edit=WS_XXX opens the edit modal for
            // that service once the admin is authorized and the data is loaded
            // (used by the "Modifier" action on the Web Service details page).
            var editParam = new URLSearchParams(window.location.search).get('edit');
            if (editParam) {
                var editTimer;
                var wait = function () {
                    if (!document.getElementById('admin-web-services-root').classList.contains('hidden') && allServices.length) {
                        clearInterval(editTimer);
                        var svc = servicesByCode[editParam];
                        if (svc) openEditModal(svc.code);
                    }
                };
                editTimer = setInterval(wait, 250);
                setTimeout(function () {
                    clearInterval(editTimer);
                    wait();
                }, 1500);
            }
        })();
    </script>
@endpush
