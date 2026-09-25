@extends('layouts.app')

@section('title', 'Domaines Web Service')

@section('content')
<div id="admin-domains-root" class="hidden">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Domaines Web Service</h2>
            <p class="mt-0.5 text-sm text-gray-500">Gérez les domaines et leurs Web Services.</p>
        </div>
        <button type="button" id="btn-create-domain"
                class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/>
            </svg>
            Créer un domaine
        </button>
    </div>

    {{-- ── ACCESS DENIED (403 / non-admin) ── --}}
    <div id="access-denied" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
        <p class="text-sm font-medium text-red-800">Accès non autorisé.</p>
        <p class="mt-1 text-sm text-red-600">Vous devez être administrateur pour gérer les domaines.</p>
    </div>

    {{-- ── LOAD ERROR + RETRY ── --}}
    <div id="load-error" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
        <p class="text-sm font-medium text-red-800">Impossible de charger les domaines.</p>
        <button id="btn-retry" type="button"
                class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700">
            Réessayer
        </button>
    </div>

    {{-- ── TABLE CARD ── --}}
    <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left" id="domain-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Code</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Nom</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Description</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Services</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Statut</th>
                        <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="domain-tbody" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>

            {{-- Skeleton rows --}}
            <div id="domain-skeleton" class="divide-y divide-gray-100 bg-white"></div>

            {{-- Empty state --}}
            <div id="domain-empty" class="hidden px-6 py-12 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.5 3.25a.75.75 0 0 1 .75-.75h13.5a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H3.25a.75.75 0 0 1-.75-.75V3.25Zm1.5.75v12h12v-12h-12Z"/>
                </svg>
                <p class="mt-3 text-sm text-gray-500">Aucun domaine disponible.</p>
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

{{-- ── SUCCESS / ERROR TOAST ── --}}
<div id="toast" class="fixed right-4 top-4 z-[60] hidden max-w-sm rounded-lg border px-4 py-3 shadow-sm"
     role="status" aria-live="polite">
    <p id="toast-msg" class="text-sm font-medium"></p>
</div>

{{-- ── CREATE DOMAIN MODAL ── --}}
<div id="domain-create-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="domain-create-modal-title">
    <div id="domain-create-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="domain-create-modal-title" class="text-base font-semibold text-gray-900">Créer un domaine</h3>
            <button type="button" id="domain-create-modal-close" class="p-1 text-gray-400 transition-colors hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div id="domain-create-modal-error" class="mx-5 mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700" role="alert"></div>
        <form id="domain-create-form" class="space-y-4 px-5 py-4" novalidate>
            <div>
                <label for="create-domain-code" class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" id="create-domain-code" name="code" required maxlength="60"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-sm text-gray-900 uppercase shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500" />
                <p class="mt-1 text-xs text-gray-500">Identifiant technique, majuscules. Ex. : OFFRES</p>
                <p id="err-create-domain-code" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="create-domain-name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="create-domain-name" name="name" required maxlength="255"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500" />
                <p id="err-create-domain-name" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="create-domain-description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="create-domain-description" name="description" rows="3" maxlength="500"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"></textarea>
                <p id="err-create-domain-description" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="create-domain-is-active" class="flex items-center gap-2.5 text-sm font-medium text-gray-700">
                    <input type="checkbox" id="create-domain-is-active" name="is_active" checked
                           class="h-4 w-4 rounded border-gray-300 text-anapec-600 focus:ring-anapec-500" />
                    Domaine actif
                </label>
                <p id="err-create-domain-is_active" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" id="domain-create-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="submit" id="btn-create-domain-submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-create-domain-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-create-domain-text">Créer</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── EDIT DOMAIN MODAL ── --}}
<div id="domain-edit-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="domain-edit-modal-title">
    <div id="domain-edit-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="domain-edit-modal-title" class="text-base font-semibold text-gray-900">Modifier le domaine</h3>
            <button type="button" id="domain-edit-modal-close" class="p-1 text-gray-400 transition-colors hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div id="domain-edit-modal-error" class="mx-5 mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700" role="alert"></div>
        <form id="domain-edit-form" class="space-y-4 px-5 py-4" novalidate>
            <div>
                <label for="edit-domain-code" class="block text-sm font-medium text-gray-700">Code</label>
                <input type="text" id="edit-domain-code" class="mt-1 block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-sm text-gray-500" readonly tabindex="-1">
                <p class="mt-1 text-xs text-gray-500">Le code est un identifiant technique et ne peut pas être modifié.</p>
            </div>
            <div>
                <label for="edit-domain-name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="edit-domain-name" name="name" required maxlength="255"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500" />
                <p id="err-edit-domain-name" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="edit-domain-description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="edit-domain-description" name="description" rows="3" maxlength="500"
                          class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"></textarea>
                <p id="err-edit-domain-description" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" id="domain-edit-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="submit" id="btn-edit-domain-submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-edit-domain-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-edit-domain-text">Enregistrer</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MANAGE DOMAIN SERVICES MODAL ── --}}
<div id="domain-services-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="domain-services-modal-title">
    <div id="domain-services-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-lg rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <div>
                <h3 id="domain-services-modal-title" class="text-base font-semibold text-gray-900">Gérer les Web Services du domaine</h3>
                <p id="domain-services-modal-subtitle" class="mt-0.5 text-xs text-gray-500"></p>
            </div>
            <button type="button" id="domain-services-modal-close" class="p-1 text-gray-400 transition-colors hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div id="domain-services-modal-error" class="mx-5 mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700" role="alert"></div>
        <div id="domain-services-skeleton" class="hidden px-5 py-4"></div>
        <div id="domain-services-list" class="max-h-80 overflow-y-auto px-5 py-4"></div>
        <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 px-5 py-4">
            <button type="button" id="domain-services-modal-cancel"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                Annuler
            </button>
            <button type="button" id="btn-save-domain-services"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                <span id="btn-save-domain-services-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                <span id="btn-save-domain-services-text">Enregistrer</span>
            </button>
        </div>
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

    function esc(v) {
        if (v === null || v === undefined) return '';
        var d = document.createElement('div');
        d.textContent = String(v);
        return d.innerHTML;
    }

    function statusBadge(active) {
        if (active) {
            return '<span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Actif</span>';
        }
        return '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Inactif</span>';
    }

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

    var allDomains = [];
    var allServices = [];
    var domainsByCode = {};

    var tbody = document.getElementById('domain-tbody');
    var skeleton = document.getElementById('domain-skeleton');
    var empty = document.getElementById('domain-empty');
    var tableEl = document.getElementById('domain-table');

    function showAdminRoot() {
        document.getElementById('auth-loading').classList.add('hidden');
        document.getElementById('access-denied').classList.add('hidden');
        document.getElementById('admin-domains-root').classList.remove('hidden');
    }

    function showSkeleton() {
        skeleton.classList.remove('hidden');
        tbody.classList.add('hidden');
        tableEl.classList.add('hidden');
        empty.classList.add('hidden');
        document.getElementById('load-error').classList.add('hidden');
        var rows = '';
        for (var i = 0; i < 4; i++) {
            rows += '<tr class="bg-white">' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-20 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-28 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-36 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-12 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-16 animate-pulse rounded bg-gray-200"></span></td>' +
                '<td class="px-4 py-4"><span class="inline-block h-4 w-24 animate-pulse rounded bg-gray-200"></span></td></tr>';
        }
        skeleton.innerHTML = rows;
    }

    function hideSkeleton() {
        skeleton.classList.add('hidden');
        skeleton.innerHTML = '';
    }

    function renderRows() {
        domainsByCode = {};
        allDomains.forEach(function (d) { domainsByCode[d.code] = d; });

        if (!allDomains.length) {
            tableEl.classList.remove('hidden');
            tbody.classList.remove('hidden');
            tbody.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        tableEl.classList.remove('hidden');
        tbody.classList.remove('hidden');
        var rows = '';
        allDomains.forEach(function (d) {
            var desc = d.description ? esc(d.description) : '<span class="text-gray-400">—</span>';
            var code = esc(d.code);
            rows += '<tr class="transition-colors hover:bg-anapec-50/40">' +
                '<td class="px-4 py-3 font-mono text-sm font-medium text-gray-900">' + code + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-600">' + esc(d.name) + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-500">' + desc + '</td>' +
                '<td class="px-4 py-3 text-sm text-gray-500">' +
                    '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">' + (d.service_count || 0) + '</span>' +
                '</td>' +
                '<td class="px-4 py-3">' + statusBadge(d.is_active === true) + '</td>' +
                '<td class="px-4 py-3 text-right">' +
                '<div class="inline-flex flex-wrap items-center justify-end gap-1.5">' +
                '<button type="button" data-domain-services-code="' + code + '" ' +
                    'class="inline-flex items-center gap-1 rounded-lg border border-anapec-200 bg-anapec-50 px-2.5 py-1.5 text-xs font-medium text-anapec-700 transition-colors hover:bg-anapec-100 ' +
                    'aria-label="Ajouter ou retirer des Web Services de ' + code + '">Ajouter des Web Services</button>' +
                '<button type="button" data-domain-edit-code="' + code + '" ' +
                    'class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-anapec-50 hover:text-anapec-700 ' +
                    'aria-label="Modifier ' + code + '">Modifier</button>' +
                '<button type="button" data-domain-toggle-code="' + code + '" data-next="' + (d.is_active !== true) + '" ' +
                    'class="inline-flex items-center gap-1 rounded-lg border ' +
                    (d.is_active === true ? 'border-red-200 text-red-700 hover:bg-red-50' : 'border-green-200 text-green-700 hover:bg-green-50') +
                    ' bg-white px-2.5 py-1.5 text-xs font-medium transition-colors ' +
                    'aria-label="' + (d.is_active === true ? 'Désactiver' : 'Activer') + ' ' + code + '">' +
                    (d.is_active === true ? 'Désactiver' : 'Activer') + '</button>' +
                '</div></td></tr>';
        });
        tbody.innerHTML = rows;

        tbody.querySelectorAll('[data-domain-toggle-code]').forEach(function (b) {
            b.addEventListener('click', function () {
                toggleDomainStatus(b.getAttribute('data-domain-toggle-code'), b.getAttribute('data-next') === 'true', b);
            });
        });
        tbody.querySelectorAll('[data-domain-edit-code]').forEach(function (b) {
            b.addEventListener('click', function () {
                openEditModal(b.getAttribute('data-domain-edit-code'));
            });
        });
        tbody.querySelectorAll('[data-domain-services-code]').forEach(function (b) {
            b.addEventListener('click', function () {
                openServicesModal(b.getAttribute('data-domain-services-code'));
            });
        });
    }

    function showLoadError() {
        document.getElementById('load-error').classList.remove('hidden');
    }

    function showAccessDenied() {
        document.getElementById('access-denied').classList.remove('hidden');
        document.getElementById('admin-domains-root').classList.add('hidden');
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
                document.getElementById('auth-loading').classList.add('hidden');
                return res.json().then(function () { showLoadError(); });
            }
            return res.json().then(function (data) {
                if (!data.success || !data.data) {
                    document.getElementById('auth-loading').classList.add('hidden');
                    showLoadError();
                    return;
                }
                var user = data.data;
                localStorage.setItem('anapec_user', JSON.stringify(user));

                if (user.role !== 'admin') {
                    document.getElementById('auth-loading').classList.add('hidden');
                    showAccessDenied();
                    setTimeout(function () { window.location.replace('/dashboard'); }, 1200);
                    return;
                }
                showAdminRoot();
                loadDomains();
            });
        })
        .catch(function () {
            document.getElementById('auth-loading').classList.add('hidden');
            showLoadError();
        });

    function loadDomains() {
        showSkeleton();
        get('/api/v1/admin/domains')
            .then(function (res) {
                if (res.status === 403) { showAccessDenied(); return; }
                if (res.status === 401) {
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                    window.location.href = '/login';
                    return;
                }
                if (!res.ok) { hideSkeleton(); showLoadError(); return; }
                return res.json().then(function (data) {
                    hideSkeleton();
                    if (!data.success || !Array.isArray(data.data)) {
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
        var api = window.apiClient;
        if (!api) { showToast('Client API introuvable.', 'error'); return; }

        btn.disabled = true;
        btn.textContent = '...';

        api.patch('/admin/domains/' + code + '/status', { is_active: next })
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
                var d = domainsByCode[code];
                btn.textContent = d && d.is_active === true ? 'Désactiver' : 'Activer';
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
    var createModal = document.getElementById('domain-create-modal');
    var createForm = document.getElementById('domain-create-form');
    var createBtn = document.getElementById('btn-create-domain-submit');
    var createText = document.getElementById('btn-create-domain-text');
    var createSpinner = document.getElementById('btn-create-domain-spinner');
    var creating = false;
    var createLastFocused = null;

    function openCreateModal() {
        createLastFocused = document.activeElement;
        document.getElementById('create-domain-code').value = '';
        document.getElementById('create-domain-name').value = '';
        document.getElementById('create-domain-description').value = '';
        document.getElementById('create-domain-is-active').checked = true;
        document.getElementById('domain-create-modal-error').classList.add('hidden');
        ['code', 'name', 'description', 'is_active'].forEach(function (f) {
            var el = document.getElementById('err-create-domain-' + f);
            if (el) el.classList.add('hidden');
        });
        createModal.classList.remove('hidden');
        createModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        document.getElementById('create-domain-code').focus();
    }

    function closeCreateModal(force) {
        if (creating && !force) return;
        createModal.classList.add('hidden');
        createModal.classList.remove('flex');
        document.body.style.overflow = '';
        if (createLastFocused && createLastFocused.focus) createLastFocused.focus();
    }

    document.getElementById('btn-create-domain').addEventListener('click', openCreateModal);
    document.getElementById('domain-create-modal-close').addEventListener('click', closeCreateModal);
    document.getElementById('domain-create-modal-cancel').addEventListener('click', closeCreateModal);
    document.getElementById('domain-create-modal-backdrop').addEventListener('click', closeCreateModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !createModal.classList.contains('hidden')) closeCreateModal();
    });

    createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (creating) return;

        var code = String(document.getElementById('create-domain-code').value || '').trim().toUpperCase();
        var name = String(document.getElementById('create-domain-name').value || '').trim();
        var description = String(document.getElementById('create-domain-description').value || '').trim();
        var isActive = document.getElementById('create-domain-is-active').checked === true;

        var ok = true;
        document.getElementById('domain-create-modal-error').classList.add('hidden');
        ['code', 'name', 'description', 'is_active'].forEach(function (f) {
            var el = document.getElementById('err-create-domain-' + f);
            if (el) el.classList.add('hidden');
        });

        if (!code) {
            document.getElementById('err-create-domain-code').textContent = 'Le code est requis.';
            document.getElementById('err-create-domain-code').classList.remove('hidden');
            ok = false;
        }
        if (!name) {
            document.getElementById('err-create-domain-name').textContent = 'Le nom est requis.';
            document.getElementById('err-create-domain-name').classList.remove('hidden');
            ok = false;
        }
        if (!ok) return;

        var api = window.apiClient;
        if (!api) {
            document.getElementById('domain-create-modal-error').textContent = 'Client API introuvable.';
            document.getElementById('domain-create-modal-error').classList.remove('hidden');
            return;
        }

        creating = true;
        createBtn.disabled = true;
        createSpinner.classList.remove('hidden');
        createText.textContent = 'Création...';

        api.post('/admin/domains', {
            code: code,
            name: name,
            description: description,
            is_active: isActive
        }).then(function () {
            creating = false;
            createBtn.disabled = false;
            createSpinner.classList.add('hidden');
            createText.textContent = 'Créer';
            closeCreateModal(true);
            showToast('Domaine créé avec succès.');
            loadDomains();
        }).catch(function (err) {
            creating = false;
            createBtn.disabled = false;
            createSpinner.classList.add('hidden');
            createText.textContent = 'Créer';
            var status = err.status || (err.response ? err.response.status : null);
            var body = err.body || (err.response ? err.response.data : null);
            if (status === 401) {
                localStorage.removeItem('anapec_token');
                localStorage.removeItem('anapec_user');
                window.location.href = '/login';
                return;
            }
            if (status === 403) {
                document.getElementById('domain-create-modal-error').textContent = 'Accès non autorisé.';
                document.getElementById('domain-create-modal-error').classList.remove('hidden');
                return;
            }
            if (status === 422 && body && body.errors) {
                var shown = false;
                ['code', 'name', 'description', 'is_active'].forEach(function (f) {
                    if (body.errors[f]) {
                        var el = document.getElementById('err-create-domain-' + f);
                        if (el) {
                            el.textContent = Array.isArray(body.errors[f]) ? body.errors[f][0] : body.errors[f];
                            el.classList.remove('hidden');
                        }
                        shown = true;
                    }
                });
                if (!shown) {
                    document.getElementById('domain-create-modal-error').textContent = body.message || 'Erreur de validation.';
                    document.getElementById('domain-create-modal-error').classList.remove('hidden');
                }
                return;
            }
            document.getElementById('domain-create-modal-error').textContent = (body && body.message) ? body.message : 'Une erreur est survenue. Veuillez réessayer.';
            document.getElementById('domain-create-modal-error').classList.remove('hidden');
        });
    });

    // ════════════════════════════════════════════════════
    //  EDIT DOMAIN MODAL
    // ════════════════════════════════════════════════════
    var editModal = document.getElementById('domain-edit-modal');
    var editForm = document.getElementById('domain-edit-form');
    var editBtn = document.getElementById('btn-edit-domain-submit');
    var editText = document.getElementById('btn-edit-domain-text');
    var editSpinner = document.getElementById('btn-edit-domain-spinner');
    var editing = false;
    var editingCode = null;
    var editLastFocused = null;

    function openEditModal(code) {
        var d = domainsByCode[code];
        if (!d) return;
        editingCode = code;
        editLastFocused = document.activeElement;
        document.getElementById('edit-domain-code').value = d.code;
        document.getElementById('edit-domain-name').value = d.name || '';
        document.getElementById('edit-domain-description').value = d.description || '';
        document.getElementById('domain-edit-modal-error').classList.add('hidden');
        ['name', 'description'].forEach(function (f) {
            var el = document.getElementById('err-edit-domain-' + f);
            if (el) el.classList.add('hidden');
        });
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        document.getElementById('edit-domain-name').focus();
    }

    function closeEditModal(force) {
        if (editing && !force) return;
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
        document.body.style.overflow = '';
        editingCode = null;
        if (editLastFocused && editLastFocused.focus) editLastFocused.focus();
    }

    document.getElementById('domain-edit-modal-close').addEventListener('click', closeEditModal);
    document.getElementById('domain-edit-modal-cancel').addEventListener('click', closeEditModal);
    document.getElementById('domain-edit-modal-backdrop').addEventListener('click', closeEditModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !editModal.classList.contains('hidden')) closeEditModal();
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (editing || !editingCode) return;

        var name = String(document.getElementById('edit-domain-name').value || '').trim();
        var description = String(document.getElementById('edit-domain-description').value || '').trim();
        if (!name) {
            document.getElementById('err-edit-domain-name').textContent = 'Le nom est requis.';
            document.getElementById('err-edit-domain-name').classList.remove('hidden');
            return;
        }

        var api = window.apiClient;
        if (!api) {
            document.getElementById('domain-edit-modal-error').textContent = 'Client API introuvable.';
            document.getElementById('domain-edit-modal-error').classList.remove('hidden');
            return;
        }

        var code = editingCode;
        editing = true;
        editBtn.disabled = true;
        editSpinner.classList.remove('hidden');
        editText.textContent = 'Enregistrement...';

        api.patch('/admin/domains/' + code, {
            name: name,
            description: description
        }).then(function () {
            editing = false;
            editBtn.disabled = false;
            editSpinner.classList.add('hidden');
            editText.textContent = 'Enregistrer';
            closeEditModal(true);
            showToast('Domaine modifié avec succès.');
            loadDomains();
        }).catch(function (err) {
            editing = false;
            editBtn.disabled = false;
            editSpinner.classList.add('hidden');
            editText.textContent = 'Enregistrer';
            var status = err.status || (err.response ? err.response.status : null);
            var body = err.body || (err.response ? err.response.data : null);
            if (status === 401) {
                localStorage.removeItem('anapec_token');
                localStorage.removeItem('anapec_user');
                window.location.href = '/login';
                return;
            }
            if (status === 403) {
                document.getElementById('domain-edit-modal-error').textContent = 'Accès non autorisé.';
                document.getElementById('domain-edit-modal-error').classList.remove('hidden');
                closeEditModal(true);
                return;
            }
            if (status === 404) {
                document.getElementById('domain-edit-modal-error').textContent = 'Domaine introuvable.';
                document.getElementById('domain-edit-modal-error').classList.remove('hidden');
                closeEditModal(true);
                return;
            }
            if (status === 422 && body && body.errors) {
                var shown = false;
                ['name', 'description'].forEach(function (f) {
                    if (body.errors[f]) {
                        var el = document.getElementById('err-edit-domain-' + f);
                        if (el) {
                            el.textContent = Array.isArray(body.errors[f]) ? body.errors[f][0] : body.errors[f];
                            el.classList.remove('hidden');
                        }
                        shown = true;
                    }
                });
                if (shown) return;
                document.getElementById('domain-edit-modal-error').textContent = body.message || 'Erreur de validation.';
                document.getElementById('domain-edit-modal-error').classList.remove('hidden');
                return;
            }
            document.getElementById('domain-edit-modal-error').textContent = (body && body.message) ? body.message : 'Une erreur est survenue. Veuillez réessayer.';
            document.getElementById('domain-edit-modal-error').classList.remove('hidden');
        });
    });

    // ════════════════════════════════════════════════════
    //  MANAGE DOMAIN SERVICES MODAL
    // ════════════════════════════════════════════════════
    var servicesModal = document.getElementById('domain-services-modal');
    var servicesList = document.getElementById('domain-services-list');
    var servicesSkeleton = document.getElementById('domain-services-skeleton');
    var servicesSaveBtn = document.getElementById('btn-save-domain-services');
    var servicesSaveText = document.getElementById('btn-save-domain-services-text');
    var servicesSaveSpinner = document.getElementById('btn-save-domain-services-spinner');
    var servicesSaving = false;
    var servicesCode = null;
    var servicesLastFocused = null;

    function openServicesModal(code) {
        var d = domainsByCode[code];
        if (!d) return;
        servicesCode = code;
        servicesLastFocused = document.activeElement;
        document.getElementById('domain-services-modal-subtitle').textContent =
            'Ajouter, retirer ou modifier les Web Services existants rattachés à ' + d.code + '.';
        document.getElementById('domain-services-modal-error').classList.add('hidden');
        servicesSkeleton.classList.remove('hidden');
        servicesList.classList.add('hidden');
        servicesModal.classList.remove('hidden');
        servicesModal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        Promise.all([
            get('/api/v1/admin/web-services'),
            get('/api/v1/admin/domains/' + code)
        ]).then(function (responses) {
            servicesSkeleton.classList.add('hidden');
            if (!responses[0].ok || !responses[1].ok) {
                document.getElementById('domain-services-modal-error').textContent = 'Impossible de charger les Web Services.';
                document.getElementById('domain-services-modal-error').classList.remove('hidden');
                return;
            }
            return Promise.all([responses[0].json(), responses[1].json()]).then(function (payloads) {
                var catalogData = payloads[0];
                var domainData = payloads[1];
                if (!catalogData.success || !Array.isArray(catalogData.data)) {
                    document.getElementById('domain-services-modal-error').textContent = 'Réponse invalide.';
                    document.getElementById('domain-services-modal-error').classList.remove('hidden');
                    return;
                }
                allServices = catalogData.data;
                var members = (domainData.data && Array.isArray(domainData.data.web_services))
                    ? domainData.data.web_services
                    : [];
                var memberSet = {};
                members.forEach(function (c) { memberSet[c] = true; });

                if (!allServices.length) {
                    servicesList.innerHTML = '<p class="text-sm text-gray-500">Aucun Web Service enregistré.</p>';
                    servicesList.classList.remove('hidden');
                    return;
                }

                var html = '';
                allServices.forEach(function (svc) {
                    var checked = memberSet[svc.code] ? 'checked' : '';
                    html += '<label class="flex items-start gap-3 rounded-lg border border-gray-100 p-3 transition-colors hover:bg-anapec-50/40">' +
                        '<input type="checkbox" data-domain-service-code="' + esc(svc.code) + '" ' + checked +
                        ' class="mt-0.5 h-4 w-4 rounded border-gray-300 text-anapec-600 focus:ring-anapec-500" />' +
                        '<span class="min-w-0">' +
                        '<span class="block font-mono text-sm font-medium text-gray-900">' + esc(svc.code) + '</span>' +
                        '<span class="block text-xs text-gray-500">' + esc(svc.name) + '</span>' +
                        (svc.global_is_active === true
                            ? '<span class="mt-1 inline-block rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700">Actif</span>'
                            : '<span class="mt-1 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">Inactif</span>') +
                        '</span>' +
                        '</label>';
                });
                servicesList.innerHTML = html;
                servicesList.classList.remove('hidden');
            });
        }).catch(function () {
            servicesSkeleton.classList.add('hidden');
            document.getElementById('domain-services-modal-error').textContent = 'Impossible de charger les Web Services.';
            document.getElementById('domain-services-modal-error').classList.remove('hidden');
        });
    }

    function closeServicesModal(force) {
        if (servicesSaving && !force) return;
        servicesModal.classList.add('hidden');
        servicesModal.classList.remove('flex');
        document.body.style.overflow = '';
        servicesCode = null;
        if (servicesLastFocused && servicesLastFocused.focus) servicesLastFocused.focus();
    }

    document.getElementById('domain-services-modal-close').addEventListener('click', closeServicesModal);
    document.getElementById('domain-services-modal-cancel').addEventListener('click', closeServicesModal);
    document.getElementById('domain-services-modal-backdrop').addEventListener('click', closeServicesModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !servicesModal.classList.contains('hidden')) closeServicesModal();
    });

    servicesSaveBtn.addEventListener('click', function () {
        if (servicesSaving || !servicesCode) return;

        var selected = [];
        servicesList.querySelectorAll('[data-domain-service-code]:checked').forEach(function (cb) {
            selected.push(cb.getAttribute('data-domain-service-code'));
        });

        var api = window.apiClient;
        if (!api) {
            document.getElementById('domain-services-modal-error').textContent = 'Client API introuvable.';
            document.getElementById('domain-services-modal-error').classList.remove('hidden');
            return;
        }

        var code = servicesCode;
        servicesSaving = true;
        servicesSaveBtn.disabled = true;
        servicesSaveSpinner.classList.remove('hidden');
        servicesSaveText.textContent = 'Enregistrement...';

        api.put('/admin/domains/' + code + '/services', { web_services: selected })
            .then(function () {
                servicesSaving = false;
                servicesSaveBtn.disabled = false;
                servicesSaveSpinner.classList.add('hidden');
                servicesSaveText.textContent = 'Enregistrer';
                closeServicesModal(true);
                showToast('Services du domaine mis à jour.');
                loadDomains();
            })
            .catch(function (err) {
                servicesSaving = false;
                servicesSaveBtn.disabled = false;
                servicesSaveSpinner.classList.add('hidden');
                servicesSaveText.textContent = 'Enregistrer';
                var status = err.status || (err.response ? err.response.status : null);
                var body = err.body || (err.response ? err.response.data : null);
                if (status === 401) {
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                    window.location.href = '/login';
                    return;
                }
                var msg = 'Une erreur est survenue. Veuillez réessayer.';
                if (status === 403) msg = 'Accès non autorisé.';
                else if (status === 404) msg = 'Domaine introuvable.';
                else if (body && body.message) msg = body.message;
                document.getElementById('domain-services-modal-error').textContent = msg;
                document.getElementById('domain-services-modal-error').classList.remove('hidden');
            });
    });

    // ── Retry ──
    var retry = document.getElementById('btn-retry');
    if (retry) retry.addEventListener('click', loadDomains);
})();
</script>
@endsection
