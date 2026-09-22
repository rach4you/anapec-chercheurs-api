@extends('layouts.app')

@section('title', 'Web Services')

@section('content')
<div id="admin-web-services-root" class="hidden">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Web Services</h2>
            <p class="mt-0.5 text-sm text-gray-500">
                Gérez les services disponibles et leur accès global.
            </p>
        </div>
    </div>

    {{-- ── TABLE CARD ── --}}
    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left" id="ws-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Code</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Nom</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Description</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Statut global</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="ws-tbody" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>

            {{-- Skeleton rows --}}
            <div id="ws-skeleton" class="divide-y divide-gray-100 bg-white"></div>

            {{-- Empty state --}}
            <div id="ws-empty" class="hidden px-6 py-12 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M5 3.25a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75V5.5a.75.75 0 0 1-.75.75h-8.5A.75.75 0 0 1 5 5.5V3.25Zm0 4.5a.75.75 0 0 1 .75-.75h8.5a.75.75 0 0 1 .75.75v5.5a.75.75 0 0 1-.75.75H5.75a.75.75 0 0 1-.75-.75V7.75Z"/>
                </svg>
                <p id="ws-empty-text" class="mt-3 text-sm text-gray-500">Aucun Web Service disponible.</p>
            </div>
        </div>
    </div>
</div>

{{-- ── AUTHORIZATION LOADING STATE ── --}}
<div id="auth-loading" class="mt-6 flex items-center justify-center rounded-lg border border-gray-200 bg-white p-10 shadow-sm">
    <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-500">
        <span class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-anapec-600"></span>
        Vérification des accès...
    </span>
</div>

{{-- ── ACCESS DENIED (403 / non-admin) ── --}}
<div id="access-denied" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
    <p class="text-sm font-medium text-red-800">Accès non autorisé.</p>
    <p class="mt-1 text-sm text-red-600">
        Vous devez être administrateur pour gérer les Web Services.
    </p>
</div>

{{-- ── LOAD ERROR + RETRY ── --}}
<div id="load-error" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
    <p class="text-sm font-medium text-red-800">Impossible de charger les Web Services.</p>
    <button id="btn-retry" type="button"
            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700">
        Réessayer
    </button>
</div>

{{-- ── SUCCESS / ERROR TOAST ── --}}
<div id="toast" class="fixed right-4 top-4 z-[60] hidden max-w-sm rounded-lg border px-4 py-3 shadow-sm"
     role="status" aria-live="polite">
    <p id="toast-msg" class="text-sm font-medium"></p>
</div>

{{-- ── CONFIRM STATUS MODAL ── --}}
<div id="status-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="status-modal-title">
    <div id="status-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="status-modal-title" class="text-base font-semibold text-gray-900">Modifier le statut global</h3>
            <button type="button" id="status-modal-close"
                    class="p-1 text-gray-400 transition-colors hover:text-gray-600"
                    aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div class="space-y-4 px-5 py-4">
            <p id="status-modal-message" class="text-sm text-gray-700"></p>
            <div id="status-modal-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700"
                 role="alert"></div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" id="status-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="button" id="btn-confirm-status"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-confirm-status-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-confirm-status-text">Confirmer</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── EDIT WEB SERVICE MODAL ── --}}
<div id="edit-ws-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="edit-ws-modal-title">
    <div id="edit-ws-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="edit-ws-modal-title" class="text-base font-semibold text-gray-900">Modifier le Web Service</h3>
            <button type="button" id="edit-ws-modal-close"
                    class="p-1 text-gray-400 transition-colors hover:text-gray-600"
                    aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <form id="edit-ws-form" class="space-y-4 px-5 py-4" novalidate>
            <div>
                <label for="edit-ws-code" class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" id="edit-ws-code" class="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-sm text-gray-500" readonly tabindex="-1">
                <p class="mt-1 text-xs text-gray-500">Le code est un identifiant technique et ne peut pas être modifié.</p>
            </div>
            <div>
                <label for="edit-ws-name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="edit-ws-name" name="name" required maxlength="255" autocomplete="off"
                       class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-anapec-600 focus:outline-none focus:ring-1 focus:ring-anapec-600">
                <p id="err-edit-ws-name" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="edit-ws-description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="edit-ws-description" name="description" rows="3" maxlength="500"
                          class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-anapec-600 focus:outline-none focus:ring-1 focus:ring-anapec-600"></textarea>
                <p id="err-edit-ws-description" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div id="edit-ws-modal-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700"
                 role="alert"></div>
            <div class="flex items-center justify-end gap-2.5 pt-1">
                <button type="button" id="edit-ws-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="submit" id="btn-edit-web-service"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-edit-web-service-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-edit-web-service-text">Enregistrer</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var token = localStorage.getItem('anapec_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    function get(url) {
        return fetch(url, {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
    }

    function statusBadge(active) {
        if (active) {
            return '<span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Actif</span>';
        }
        return '<span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">Inactif</span>';
    }

    function toggleButton(ws, busy) {
        var activate = ws.is_active === true;
        var label = activate ? 'Désactiver' : 'Activer';
        var classes = activate
            ? 'inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-50'
            : 'inline-flex items-center gap-1 rounded-lg border border-green-200 bg-white px-2.5 py-1.5 text-xs font-medium text-green-700 transition-colors hover:bg-green-50';
        var spin = '<span class="h-3 w-3 animate-spin rounded-full border-2 border-current opacity-60"></span> ';
        return '<button type="button" data-ws-code="' + esc(ws.code) + '" data-next="' + (!activate) + '" ' +
            (busy ? 'disabled ' : '') +
            'class="' + classes + '" ' +
            'aria-label="' + (activate ? 'Désactiver' : 'Activer') + ' ' + esc(ws.code) + '">' +
            (busy ? spin : '') + esc(label) + '</button>';
    }

    function editButton(ws) {
        return '<button type="button" data-edit-code="' + esc(ws.code) + '" ' +
            'class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-anapec-50 hover:text-anapec-700 ' +
            'aria-label="Modifier ' + esc(ws.code) + '">' +
            '<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">' +
            '<path d="M13.586 3.586a2 2 0 1 1 2.828 2.828l-.793.793-2.828-2.828.793-.793ZM11.379 5.793 3 14.172V17h2.828l8.38-8.379-2.83-2.828Z"/>' +
            '</svg>Modifier</button>';
    }

    function detailsButton(ws) {
        return '<a href="/admin/web-services/' + encodeURIComponent(ws.code) + '" ' +
            'class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-anapec-50 hover:text-anapec-700 ' +
            'aria-label="Voir les détails de ' + esc(ws.code) + '">Détails</a>';
    }

    function esc(v) {
        if (v === null || v === undefined) return '';
        var d = document.createElement('div');
        d.textContent = String(v);
        return d.innerHTML;
    }

    var allServices = [];
    var tbody = document.getElementById('ws-tbody');
    var skeleton = document.getElementById('ws-skeleton');
    var empty = document.getElementById('ws-empty');
    var emptyText = document.getElementById('ws-empty-text');
    var tableEl = document.getElementById('ws-table');
    var loadError = document.getElementById('load-error');
    var accessDenied = document.getElementById('access-denied');
    var authLoading = document.getElementById('auth-loading');
    var rootEl = document.getElementById('admin-web-services-root');

    // ── Confirmation modal state ──
    var pendingCode = null;
    var pendingActive = null;
    var modal = document.getElementById('status-modal');
    var modalBackdrop = document.getElementById('status-modal-backdrop');
    var modalClose = document.getElementById('status-modal-close');
    var modalCancel = document.getElementById('status-modal-cancel');
    var modalMessage = document.getElementById('status-modal-message');
    var modalError = document.getElementById('status-modal-error');
    var confirmBtn = document.getElementById('btn-confirm-status');
    var confirmText = document.getElementById('btn-confirm-status-text');
    var confirmSpinner = document.getElementById('btn-confirm-status-spinner');
    var modalLastFocused = null;
    var confirming = false;

    function showAdminRoot() {
        authLoading.classList.add('hidden');
        accessDenied.classList.add('hidden');
        rootEl.classList.remove('hidden');
    }

    function showSkeleton() {
        skeleton.classList.remove('hidden');
        tbody.classList.add('hidden');
        tableEl.classList.add('hidden');
        empty.classList.add('hidden');
        loadError.classList.add('hidden');
        var rows = '';
        for (var i = 0; i < 4; i++) {
            rows += '<tr class="bg-white">' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-24 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-32 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-40 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-16 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-20 animate-pulse rounded bg-gray-200"></span></td></tr>';
        }
        skeleton.innerHTML = rows;
    }

    function hideSkeleton() {
        skeleton.classList.add('hidden');
        skeleton.innerHTML = '';
    }

    function showEmpty(message) {
        emptyText.textContent = message;
        empty.classList.remove('hidden');
        tableEl.classList.add('hidden');
        tbody.classList.add('hidden');
    }

    function showTable() {
        tableEl.classList.remove('hidden');
        tbody.classList.remove('hidden');
        empty.classList.add('hidden');
        loadError.classList.add('hidden');
    }

    function renderRows() {
        if (!allServices.length) {
            showTable();
            tbody.innerHTML = '';
            showEmpty('Aucun Web Service disponible.');
            return;
        }
        var rows = '';
        allServices.forEach(function (ws) {
            var desc = ws.description ? esc(ws.description) : '<span class="text-gray-400">—</span>';
            var action = '<div class="inline-flex flex-wrap items-center gap-1.5">' +
                detailsButton(ws) +
                editButton(ws) +
                toggleButton(ws, false) +
                '</div>';
            rows += '<tr class="transition-colors hover:bg-anapec-50/40">' +
                '<td class="px-4 py-3 text-sm font-medium text-gray-900">' + esc(ws.code) + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-600">' + esc(ws.name) + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-500">' + desc + '</td>' +
                '<td class="px-4 py-3">' + statusBadge(ws.is_active === true) + '</td>' +
                '<td class="px-4 py-3 text-right">' + action + '</td></tr>';
        });
        showTable();
        tbody.innerHTML = rows;

        tbody.querySelectorAll('[data-ws-code]').forEach(function (b) {
            b.addEventListener('click', function () {
                openStatusConfirm(b.getAttribute('data-ws-code'), b.getAttribute('data-next') === 'true');
            });
        });
        tbody.querySelectorAll('[data-edit-code]').forEach(function (b) {
            b.addEventListener('click', function () {
                openEditModal(b.getAttribute('data-edit-code'));
            });
        });
    }

    function showLoadError() {
        loadError.classList.remove('hidden');
        showTable();
        tbody.innerHTML = '';
    }

    function showAccessDenied() {
        accessDenied.classList.remove('hidden');
        showTable();
        tbody.innerHTML = '';
    }

    // ── Auth gate ──
    get('/api/v1/auth/me')
        .then(function (res) {
            if (res.status === 401) {
                localStorage.removeItem('anapec_token');
                localStorage.removeItem('anapec_user');
                window.location.href = '/login';
                return;
            }
            if (!res.ok) {
                authLoading.classList.add('hidden');
                return res.json().then(function (d) { showLoadError(); });
            }
            return res.json().then(function (data) {
                if (!data.success || !data.data) {
                    authLoading.classList.add('hidden');
                    showLoadError();
                    return;
                }
                var user = data.data;
                localStorage.setItem('anapec_user', JSON.stringify(user));

                if (user.role !== 'admin') {
                    // Non-admin: do NOT initialize the admin UI, do NOT call the
                    // admin web service endpoints, and do NOT log the user out.
                    authLoading.classList.add('hidden');
                    showAccessDenied();
                    setTimeout(function () { window.location.replace('/dashboard'); }, 1200);
                    return;
                }
                showAdminRoot();
                loadServices();
            });
        })
        .catch(function () {
            authLoading.classList.add('hidden');
            showLoadError();
        });

    function loadServices() {
        showSkeleton();
        get('/api/v1/admin/web-services')
            .then(function (res) {
                if (res.status === 403) { showAccessDenied(); return; }
                if (res.status === 401) {
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                    window.location.href = '/login';
                    return;
                }
                if (!res.ok) { showLoadError(); return; }
                return res.json().then(function (data) {
                    hideSkeleton();
                    if (!data.success || !Array.isArray(data.data)) {
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

    // ── Retry ──
    var retry = document.getElementById('btn-retry');
    if (retry) {
        retry.addEventListener('click', function () {
            loadServices();
        });
    }

    // ── Toast ──
    function showToast(msg, type) {
        var t = document.getElementById('toast');
        var msgEl = document.getElementById('toast-msg');
        msgEl.textContent = msg;
        t.className = 'fixed right-4 top-4 z-[60] max-w-sm rounded-lg border px-4 py-3 shadow-sm ' +
            (type === 'error'
                ? 'border-red-200 bg-red-50'
                : 'border-green-200 bg-green-50');
        msgEl.className = type === 'error' ? 'text-sm font-medium text-red-800' : 'text-sm font-medium text-green-800';
        t.classList.remove('hidden');
        clearTimeout(t._timer);
        t._timer = setTimeout(function () {
            t.classList.add('hidden');
        }, 4000);
    }

    // ════════════════════════════════════════════════════
    //  CONFIRM STATUS MODAL
    // ════════════════════════════════════════════════════
    function openStatusConfirm(code, nextActive) {
        var svc = null;
        for (var i = 0; i < allServices.length; i++) {
            if (allServices[i].code === code) { svc = allServices[i]; break; }
        }
        if (!svc) return;
        pendingCode = code;
        pendingActive = nextActive === true;
        modalMessage.textContent = pendingActive
            ? 'Voulez-vous activer le Web Service ' + svc.name + ' ?'
            : 'Voulez-vous désactiver le Web Service ' + svc.name + ' ?';
        modalError.classList.add('hidden');
        modalLastFocused = document.activeElement;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        confirmBtn.focus();
    }

    function closeStatusModal(force) {
        if (confirming && !force) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        pendingCode = null;
        pendingActive = null;
        if (modalLastFocused && modalLastFocused.focus) modalLastFocused.focus();
    }

    if (modalClose) modalClose.addEventListener('click', closeStatusModal);
    if (modalCancel) modalCancel.addEventListener('click', closeStatusModal);
    if (modalBackdrop) modalBackdrop.addEventListener('click', closeStatusModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeStatusModal();
        }
    });

    function setBusy(busy) {
        confirming = busy;
        confirmBtn.disabled = busy;
        if (busy) {
            confirmSpinner.classList.remove('hidden');
            confirmText.textContent = 'Mise à jour...';
        } else {
            confirmSpinner.classList.add('hidden');
            confirmText.textContent = 'Confirmer';
        }
    }

    confirmBtn.addEventListener('click', function () {
        if (confirming || !pendingCode) return;

        var api = window.apiClient;
        if (!api) {
            modalError.textContent = 'Client API introuvable.';
            modalError.classList.remove('hidden');
            return;
        }

        setBusy(true);
        // Disable the row's toggle button while the request is in flight.
        var rowBtn = tbody.querySelector('[data-ws-code="' + pendingCode + '"]');
        if (rowBtn) {
            rowBtn.disabled = true;
            rowBtn.setAttribute('aria-busy', 'true');
        }

        var code = pendingCode;
        var nextActive = pendingActive;
        pendingCode = null;
        pendingActive = null;

        api.patch('/admin/web-services/' + code + '/status', { is_active: nextActive })
            .then(function (data) {
                var updated = data && data.data;
                if (updated && updated.code === code && typeof updated.is_active === 'boolean') {
                    nextActive = updated.is_active;
                }
                for (var i = 0; i < allServices.length; i++) {
                    if (allServices[i].code === code) {
                        allServices[i].is_active = nextActive;
                        break;
                    }
                }
                renderRows();
                // Close AFTER the API succeeded, then release the busy guard.
                closeStatusModal(true);
                setBusy(false);
                showToast(nextActive ? 'Web Service activé.' : 'Web Service désactivé.', 'success');
            })
            .catch(function (err) {
                setBusy(false);
                // The table still holds the pre-request row; re-enable its
                // toggle button so the user can retry. (On success renderRows()
                // rebuilds the buttons, so no stale reference is left.)
                if (rowBtn) {
                    rowBtn.disabled = false;
                    rowBtn.removeAttribute('aria-busy');
                }
                var status = err.status || (err.response ? err.response.status : null);
                if (status === 401) {
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                    window.location.href = '/login';
                    return;
                }
                if (status === 403) {
                    modalError.textContent = 'Accès non autorisé.';
                    modalError.classList.remove('hidden');
                    closeStatusModal(true);
                    return;
                }
                var body = err.body || (err.response ? err.response.data : null);
                if (status === 422 && body && body.errors) {
                    modalError.textContent = Array.isArray(body.errors.is_active) ? body.errors.is_active[0] : 'Erreur de validation.';
                    modalError.classList.remove('hidden');
                    return;
                }
                modalError.textContent = (body && body.message) ? body.message : 'Une erreur est survenue. Veuillez réessayer.';
                modalError.classList.remove('hidden');
            });
    });

    // ════════════════════════════════════════════════════
    //  EDIT (METADATA) WEB SERVICE MODAL
    // ════════════════════════════════════════════════════
    var editModal = document.getElementById('edit-ws-modal');
    var editBackdrop = document.getElementById('edit-ws-modal-backdrop');
    var editClose = document.getElementById('edit-ws-modal-close');
    var editCancel = document.getElementById('edit-ws-modal-cancel');
    var editForm = document.getElementById('edit-ws-form');
    var editGlobalError = document.getElementById('edit-ws-modal-error');
    var editSubmitBtn = document.getElementById('btn-edit-web-service');
    var editSubmitText = document.getElementById('btn-edit-web-service-text');
    var editSubmitSpinner = document.getElementById('btn-edit-web-service-spinner');
    var editCodeInput = document.getElementById('edit-ws-code');
    var editNameInput = document.getElementById('edit-ws-name');
    var editDescInput = document.getElementById('edit-ws-description');
    var editLastFocused = null;
    var editing = false;
    var editingCode = null;

    function clearEditErrors() {
        ['name', 'description'].forEach(function (f) {
            var el = document.getElementById('err-edit-ws-' + f);
            if (el) el.classList.add('hidden');
        });
        editGlobalError.classList.add('hidden');
    }

    function setEditFieldError(field, message) {
        var el = document.getElementById('err-edit-ws-' + field);
        if (el) {
            el.textContent = message;
            el.classList.remove('hidden');
        }
    }

    function openEditModal(code) {
        var svc = null;
        for (var i = 0; i < allServices.length; i++) {
            if (allServices[i].code === code) { svc = allServices[i]; break; }
        }
        if (!svc) return;
        editingCode = code;
        editLastFocused = document.activeElement;
        editCodeInput.value = svc.code;
        editNameInput.value = svc.name || '';
        editDescInput.value = svc.description || '';
        clearEditErrors();
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        editNameInput.focus();
    }

    function closeEditModal(force) {
        if (editing && !force) return;
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
        document.body.style.overflow = '';
        editingCode = null;
        clearEditErrors();
        if (editLastFocused && editLastFocused.focus) editLastFocused.focus();
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

    if (editClose) editClose.addEventListener('click', closeEditModal);
    if (editCancel) editCancel.addEventListener('click', closeEditModal);
    if (editBackdrop) editBackdrop.addEventListener('click', closeEditModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !editModal.classList.contains('hidden')) {
            closeEditModal();
        }
    });

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (editing || !editingCode) return;

            if (!validateEditForm()) return;

            var api = window.apiClient;
            if (!api) {
                editGlobalError.textContent = 'Client API introuvable.';
                editGlobalError.classList.remove('hidden');
                return;
            }

            var code = editingCode;
            var payload = {
                name: String(editNameInput.value || '').trim(),
                description: String(editDescInput.value || '').trim()
            };

            editing = true;
            editSubmitBtn.disabled = true;
            editSubmitSpinner.classList.remove('hidden');
            editSubmitText.textContent = 'Enregistrement...';

            function done() {
                editing = false;
                editSubmitBtn.disabled = false;
                editSubmitSpinner.classList.add('hidden');
                editSubmitText.textContent = 'Enregistrer';
            }

            api.patch('/admin/web-services/' + code, payload)
                .then(function (data) {
                    var updated = data && data.data;
                    for (var i = 0; i < allServices.length; i++) {
                        if (allServices[i].code === code) {
                            allServices[i].name = updated ? updated.name : payload.name;
                            allServices[i].description = updated ? updated.description : payload.description;
                            break;
                        }
                    }
                    done();
                    closeEditModal(true);
                    showToast('Web Service modifié avec succès.', 'success');
                    loadServices();
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
                        editGlobalError.textContent = 'Accès non autorisé.';
                        editGlobalError.classList.remove('hidden');
                        closeEditModal(true);
                        return;
                    }
                    if (status === 404) {
                        editGlobalError.textContent = 'Web Service introuvable.';
                        editGlobalError.classList.remove('hidden');
                        closeEditModal(true);
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
                        editGlobalError.classList.remove('hidden');
                        return;
                    }
                    editGlobalError.textContent = (body && body.message) ? body.message : 'Une erreur est survenue. Veuillez réessayer.';
                    editGlobalError.classList.remove('hidden');
                });
        });
    }

    renderRows();

    // Deep-link: opening the list with ?edit=WS_XXX opens the edit modal for
    // that service once the admin is authorized and the data is loaded
    // (used by the "Modifier" action on the Web Service details page).
    var editParam = new URLSearchParams(window.location.search).get('edit');
    if (editParam) {
        var editTimer;
        var wait = function () {
            if (rootEl && !rootEl.classList.contains('hidden') && allServices.length) {
                clearInterval(editTimer);
                var svc = null;
                for (var i = 0; i < allServices.length; i++) {
                    if (allServices[i].code === editParam) { svc = allServices[i]; break; }
                }
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
@endsection
