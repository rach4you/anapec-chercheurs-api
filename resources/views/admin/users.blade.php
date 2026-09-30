@extends('layouts.metronic')

@section('title', 'Utilisateurs')

@section('content')
<div id="admin-users-root" class="hidden">
<div class="flex items-center justify-between gap-4">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Utilisateurs</h2>
        <p class="mt-0.5 text-sm text-gray-500">
            Gérez les utilisateurs du portail et leurs accès.
        </p>
    </div>
    <button id="btn-new-user" type="button"
        class="btn btn-color-mine btn-active btn-primary fw-semibold">
    <span class="svg-icon svg-icon-2">
        <span class="ki ki-user-plus"></span>
    </span>
    <span class="btn-text ms-2">Nouvel utilisateur</span>
</button>
</div>

{{-- "?" "?" AUTHORIZATION LOADING STATE "?" "?" --}}
<div id="auth-loading" class="mt-6 flex items-center justify-center rounded-lg border border-gray-200 bg-white p-10 shadow-sm">
    <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-500">
        <span class="h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-anapec-600"></span>
        Vérification des accès...
    </span>
</div>

{{-- "?" "?" ACCESS DENIED (403 / non-admin) "?" "?" --}}
<div id="access-denied" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
    <p class="text-sm font-medium text-red-800">Accès non autorisé.</p>
    <p class="mt-1 text-sm text-red-600">
        Vous devez être administrateur pour accéder à cette page.
    </p>
</div>

{{-- "?" "?" LOAD ERROR + RETRY "?" "?" --}}
<div id="load-error" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
    <p class="text-sm font-medium text-red-800">Impossible de charger les utilisateurs.</p>
    <button id="btn-retry" type="button"
            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700">
        Réessayer
    </button>
</div>

{{-- "?" "?" SEARCH "?" "?" --}}
<div class="mb-5">
    <div class="input-group">
        <span class="input-group-text">
            <span class="svg-icon svg-icon-2">
                <span class="ki ki-search"></span>
            </span>
        </span>
        <input type="search" id="user-search" class="form-control form-control-solid" placeholder="Rechercher un utilisateur..." aria-label="Rechercher un utilisateur">
    </div>
</div>

{{-- "?" "?" TABLE CARD "?" "?" --}}
<div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
<table class="table table-row-dashed table-row-gray-100 gs-0" id="users-table">
    <thead class="fs-7 fw-bold text-uppercase text-gray-600">
        <tr>
            <th scope="col" class="px-6 py-4">Nom</th>
            <th scope="col" class="px-6 py-4">Email</th>
            <th scope="col" class="px-6 py-4">Rôle</th>
            <th scope="col" class="px-6 py-4">Statut</th>
            <th scope="col" class="px-6 py-4 text-end">Actions</th>
        </tr>
    </thead>
    <tbody id="users-tbody" class="fs-6 fw-bold text-gray-800"></tbody>
</table>

        {{-- Skeleton rows --}}
        <div id="users-skeleton" class="divide-y divide-gray-100 bg-white">
            <tr class="bg-white"></tr>
        </div>

        {{-- Empty state --}}
        <div id="users-empty" class="hidden px-6 py-12 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM4 10a6 6 0 1 1 12 0"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M4 14a1 1 0 0 1 1-1h10a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Zm0 4a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Z"/>
            </svg>
            <p id="users-empty-text" class="mt-3 text-sm text-gray-500">Aucun utilisateur trouvé.</p>
        </div>
    </div>
</div>

{{-- "?" "?" SUCCESS NOTIFICATION "?" "?" --}}
<div id="success-toast" class="fixed right-4 top-4 z-[60] hidden max-w-sm rounded-lg border border-green-200 bg-green-50 px-4 py-3 shadow-sm"
     role="status" aria-live="polite">
    <div class="flex items-start gap-2">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M10 1.944a.75.75 0 0 1 .75.75v6.5a.75.75 0 0 1-.44.68l-2.25 1.25.44-.68V2.694a.75.75 0 0 1 .75-.75Zm0 16.112a.75.75 0 0 1-.75.75H2.75a.75.75 0 0 1 0-1.5H9.25a.75.75 0 0 1 .75.75Z"/>
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M10 2a8 8 0 1 0 0 16A8 8 0 0 0 10 2Zm.75 9.25a.75.75 0 0 0-1.5 0V11.5a.75.75 0 0 0 1.5 0Z"/>
        </svg>
        <div>
            <p class="text-sm font-medium text-green-800" id="success-toast-msg">Utilisateur créé avec succès.</p>
        </div>
    </div>
</div>

{{-- "?" "?" DELETE USER MODAL "?" "?" --}}
<div id="user-delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="user-delete-modal-title">
    {{-- Backdrop --}}
    <div id="user-delete-modal-backdrop" class="absolute inset-0 bg-black/40"></div>

    {{-- Panel --}}
    <div class="relative z-10 w-full max-w-md rounded-xl border border-gray-200 bg-white shadow-lg m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="user-delete-modal-title" class="text-base font-semibold text-gray-900">Supprimer l'utilisateur</h3>
            <button type="button" id="user-delete-modal-close"
                    class="p-1 text-gray-400 transition-colors hover:text-gray-600"
                    aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>

        <div class="px-5 py-6 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    <line x1="10" y1="11" x2="10" y2="17"/>
                    <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
            </div>
            <p class="text-sm text-gray-600">
                Êtes-vous sûr de vouloir supprimer l'utilisateur
                <strong id="user-delete-name-display"></strong>
                (<span id="user-delete-email-display"></span>) ?
            </p>
            <p class="mt-2 text-xs text-gray-500">Cette action est irréversible.</p>

            {{-- Error --}}
            <div id="user-delete-modal-error" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700"
                 role="alert"></div>
        </div>

        <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 px-5 py-4">
            <button type="button" id="user-delete-modal-cancel"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                Annuler
            </button>
            <button type="button" id="btn-delete-user-submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                <span id="btn-delete-user-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                <span id="btn-delete-user-text">Supprimer</span>
            </button>
        </div>
    </div>
</div>

{{-- "?" "?" CREATE USER MODAL "?" "?" --}}
<div id="user-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
    {{-- Backdrop --}}
    <div id="user-modal-backdrop" class="absolute inset-0 bg-black/40"></div>

    {{-- Panel --}}
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="user-modal-title" class="text-base font-semibold text-gray-900">Nouvel utilisateur</h3>
            <button type="button" id="user-modal-close"
                    class="p-1 text-gray-400 transition-colors hover:text-gray-600"
                    aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>

        <!-- Global form error -->
        <div id="form-error" class="mx-5 mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700"
             role="alert"></div>

        <form id="user-create-form" class="space-y-4 px-5 py-4" novalidate>
            <!-- Nom -->
            <div>
                <label for="nu-name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="nu-name" name="name" required maxlength="255"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                       placeholder="Nom complet" />
                <p id="err-name" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Email -->
            <div>
                <label for="nu-email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="nu-email" name="email" required maxlength="255"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                       placeholder="prenom.nom@anapec.com" autocomplete="email" />
                <p id="err-email" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Rôle -->
            <div>
                <label for="nu-role" class="block text-sm font-medium text-gray-700">Rôle</label>
                <select id="nu-role" name="role"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
                <p id="err-role" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Mot de passe -->
            <div>
                <label for="nu-password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <div class="relative mt-1">
                    <input type="password" id="nu-password" name="password" required minlength="8"
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 pr-10 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                           placeholder="8 caractères minimum" autocomplete="new-password" />
                    <button type="button" id="nu-toggle-password"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
                            aria-label="Afficher ou masquer le mot de passe">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M10 1.944a.75.75 0 0 1 .75.75v.554a.75.75 0 0 1-1.5 0V2.7a.75.75 0 0 1 .75-.756ZM2.25 9.75a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Zm14.5-.75h-2.5a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5ZM2.25 13.75a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Zm14.5-.75h-2.5a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5ZM10 10.75a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V11.5a.75.75 0 0 1 .75-.75Z"/>
                        </svg>
                    </button>
                </div>
                <p id="err-password" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Confirmation du mot de passe -->
            <div>
                <label for="nu-password-confirm" class="block text-sm font-medium text-gray-700">Confirmation du mot de passe</label>
                <input type="password" id="nu-password-confirm" name="password_confirmation" required minlength="8"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                       placeholder="Retapez le mot de passe" autocomplete="new-password" />
                <p id="err-password-confirm" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Statut -->
            <div>
                <label for="nu-active" class="flex items-center gap-2.5 text-sm font-medium text-gray-700">
                    <input type="checkbox" id="nu-active" name="is_active" checked
                           class="h-4 w-4 rounded border-gray-300 text-anapec-600 focus:ring-anapec-500" />
                    Compte actif
                </label>
                <p id="err-is_active" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" id="user-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="submit" id="btn-create-user"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-create-user-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-create-user-text">Créer l'utilisateur</span>
                </button>
            </div>
        </form>
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

    function esc(v) {
        if (v === null || v === undefined) return '—';
        var d = document.createElement('div');
        d.textContent = String(v);
        return d.innerHTML;
    }

    var allUsers = [];
    var tbody = document.getElementById('users-tbody');
    var skeleton = document.getElementById('users-skeleton');
    var empty = document.getElementById('users-empty');
    var emptyText = document.getElementById('users-empty-text');
    var tableEl = document.getElementById('users-table');
    var loadError = document.getElementById('load-error');
    var accessDenied = document.getElementById('access-denied');
    var authLoading = document.getElementById('auth-loading');
    var rootEl = document.getElementById('admin-users-root');

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
        accessDenied.classList.add('hidden');
        var rows = '';
        for (var i = 0; i < 5; i++) {
            rows += '<tr class="bg-white"><td class="px-6 py-4"><span class="inline-block h-4 w-24 animate-pulse rounded bg-gray-200"></span></td>' +
                    '<td class="px-6 py-4"><span class="inline-block h-4 w-40 animate-pulse rounded bg-gray-200"></span></td>' +
                    '<td class="px-6 py-4"><span class="inline-block h-4 w-16 animate-pulse rounded bg-gray-200"></span></td>' +
                    '<td class="px-6 py-4"><span class="inline-block h-4 w-16 animate-pulse rounded bg-gray-200"></span></td>' +
                    '<td class="px-6 py-4"><span class="inline-block h-4 w-10 animate-pulse rounded bg-gray-200"></span></td></tr>';
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

    function renderRows(filter) {
        var q = (filter || '').trim().toLowerCase();
        var rows = '';
        var count = 0;

        for (var i = 0; i < allUsers.length; i++) {
            var u = allUsers[i];
            if (!u || !u.id) continue;
            count++;
            var hay = ((u.name || '') + ' ' + (u.email || '') + ' ' + (u.role || '')).toLowerCase();
            if (q && hay.indexOf(q) === -1) continue;

            var viewBtn = '<a href="/admin/users/' + esc(u.id) + '" ' +
                'class="btn btn-icon btn-active-light-primary w-30px h-30px me-1" ' +
                'title="Voir les détails" aria-label="Voir les détails de ' + esc(u.name) + '">' +
                '<span class="svg-icon svg-icon-2">' +
                '<span class="ki ki-eye"></span>' +
                '</span>' +
                '</a>';

            var deleteBtn = '<button type="button" ' +
                'class="btn btn-icon btn-active-light-danger w-30px h-30px" ' +
                'data-user-id="' + esc(u.id) + '" ' +
                'data-user-name="' + esc(u.name) + '" ' +
                'data-user-email="' + esc(u.email) + '" ' +
                'title="Supprimer" aria-label="Supprimer ' + esc(u.name) + '">' +
                '<span class="svg-icon svg-icon-2">' +
                '<span class="ki ki-trash"></span>' +
                '</span>' +
                '</button>';

            var actionsHtml = viewBtn + deleteBtn;

            rows += '<tr>' +
                '<td class="px-6 py-4 text-gray-800">' + esc(u.name) + '</td>' +
                '<td class="px-6 py-4 text-gray-600">' + esc(u.email) + '</td>' +
                '<td class="px-6 py-4">' + roleBadge(u.role) + '</td>' +
                '<td class="px-6 py-4">' + statusBadge(u.is_active === true) + '</td>' +
                '<td class="px-6 py-4 text-end">' + actionsHtml + '</td></tr>';
        }

        if (count === 0) {
            showTable();
            tbody.innerHTML = '';
            showEmpty('Aucun utilisateur trouvé.');
            return;
        }

        if (q && rows === '') {
            showTable();
            tbody.innerHTML = '';
            showEmpty('Aucun utilisateur ne correspond à votre recherche.');
            return;
        }

        showTable();
        tbody.innerHTML = rows;
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

    // "?" "?" Auth gate "?" "?"
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
                    // Non-admin: do NOT initialize the admin UI, do NOT call
                    // the admin users endpoint, and do NOT log the user out.
                    authLoading.classList.add('hidden');
                    showAccessDenied();
                    setTimeout(function () { window.location.replace('/dashboard'); }, 1200);
                    return;
                }
                showAdminRoot();
                loadUsers();
            });
        })
        .catch(function () {
            authLoading.classList.add('hidden');
            showLoadError();
        });

    function loadUsers() {
        showSkeleton();
        get('/api/v1/admin/users')
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
                    allUsers = data.data;
                    renderRows('');
                });
            })
            .catch(function () {
                hideSkeleton();
                showLoadError();
            });
    }

    // "?" "?" Search "?" "?"
    var search = document.getElementById('user-search');
    if (search) {
        search.addEventListener('input', function () {
            renderRows(search.value);
        });
    }

    // "?" "?" Retry "?" "?"
    var retry = document.getElementById('btn-retry');
    if (retry) {
        retry.addEventListener('click', function () {
            loadUsers();
        });
    }

    // "?" "?" CREATE USER MODAL "?" "?"
    var btnNew = document.getElementById('btn-new-user');
    var modal = document.getElementById('user-modal');
    var backdrop = document.getElementById('user-modal-backdrop');
    var modalClose = document.getElementById('user-modal-close');
    var modalCancel = document.getElementById('user-modal-cancel');
    var createForm = document.getElementById('user-create-form');
    var formError = document.getElementById('form-error');
    var successToast = document.getElementById('success-toast');
    var successToastMsg = document.getElementById('success-toast-msg');
    var submitBtn = document.getElementById('btn-create-user');
    var submitText = document.getElementById('btn-create-user-text');
    var submitSpinner = document.getElementById('btn-create-user-spinner');
    var togglePwd = document.getElementById('nu-toggle-password');
    var pwdInput = document.getElementById('nu-password');
    var pwdConfirm = document.getElementById('nu-password-confirm');
    var activeCb = document.getElementById('nu-active');

    var lastFocused = null;
    var submitting = false;

    var fieldNames = ['name', 'email', 'role', 'password', 'password-confirm', 'is_active'];
    function clearFieldErrors() {
        fieldNames.forEach(function (f) {
            var el = document.getElementById('err-' + f);
            if (el) el.classList.add('hidden');
        });
        formError.classList.add('hidden');
    }

    function setFieldError(name, msg) {
        var el = document.getElementById('err-' + name);
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function openModal() {
        lastFocused = document.activeElement;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        clearFieldErrors();
        // Focus first field
        var first = document.getElementById('nu-name');
        if (first) first.focus();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
        if (lastFocused && lastFocused.focus) lastFocused.focus();
        resetForm();
    }

    function resetForm() {
        createForm.reset();
        clearFieldErrors();
        if (pwdInput) pwdInput.type = 'password';
        activeCb.checked = true;
    }

    function clientSideValidate() {
        clearFieldErrors();
        var ok = true;
        var name = document.getElementById('nu-name').value.trim();
        var email = document.getElementById('nu-email').value.trim();
        var password = pwdInput.value;
        var confirm = pwdConfirm.value;

        if (!name) { setFieldError('name', 'Le nom est requis.'); ok = false; }
        if (!email) { setFieldError('email', "L'email est requis."); ok = false; }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setFieldError('email', 'Format d\'email invalide.'); ok = false; }
        if (!password) { setFieldError('password', 'Le mot de passe est requis.'); ok = false; }
        else if (password.length < 8) { setFieldError('password', 'Au moins 8 caractères requis.'); ok = false; }
        if (password !== confirm) { setFieldError('password-confirm', 'Les mots de passe ne correspondent pas.'); ok = false; }
        return ok;
    }

    if (btnNew) btnNew.addEventListener('click', openModal);
    if (modalClose) modalClose.addEventListener('click', closeModal);
    if (modalCancel) modalCancel.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
            if (btnNew) btnNew.focus();
        }
    });

    // Show/hide password
    if (togglePwd) {
        togglePwd.addEventListener('click', function () {
            if (!pwdInput) return;
            var hidden = pwdInput.type === 'password';
            pwdInput.type = hidden ? 'text' : 'password';
            togglePwd.setAttribute('aria-label', hidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    }

    function showSuccess(msg) {
        successToastMsg.textContent = msg || 'Utilisateur créé avec succès.';
        successToast.classList.remove('hidden');
        setTimeout(function () {
            successToast.classList.add('hidden');
        }, 4000);
    }

    // Submit
    if (createForm) {
        createForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (submitting) return; // prevent double submit

            if (!clientSideValidate()) return;

            // Build payload — send only backend-supported fields
            var payload = {
                name: document.getElementById('nu-name').value.trim(),
                email: document.getElementById('nu-email').value.trim(),
                password: pwdInput.value,
                role: document.getElementById('nu-role').value,
                is_active: activeCb.checked === true
            };

            // Loading state
            submitting = true;
            submitBtn.disabled = true;
            submitSpinner.classList.remove('hidden');
            submitText.textContent = 'Création...';

            function done() {
                submitting = false;
                submitBtn.disabled = false;
                submitSpinner.classList.add('hidden');
                submitText.textContent = "Créer l'utilisateur";
            }

            var api = window.apiClient;
            if (!api) {
                // Fallback to fetch if the shared client is unavailable
                api = {
                    post: function (url, data) {
                        return fetch('/api/v1' + url, {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(data)
                        }).then(function (res) {
                            var p = res.json().catch(function () { return {}; });
                            return p.then(function (body) {
                                if (res.ok) return body;
                                var err = new Error('HTTP ' + res.status);
                                err.status = res.status;
                                err.body = body;
                                throw err;
                            });
                        });
                    }
                };
            }

            api.post('/admin/users', payload)
                .then(function (data) {
                    done();
                    // Success: close modal, notify, refresh list
                    closeModal();
                    showSuccess('Utilisateur créé avec succès.');
                    loadUsers();
                })
                .catch(function (err) {
                    done();
                    var status = err.status || (err.response ? err.response.status : null);
                    if (status === 401) {
                        // The Axios interceptor already redirects; be defensive.
                        localStorage.removeItem('anapec_token');
                        localStorage.removeItem('anapec_user');
                        window.location.href = '/login';
                        return;
                    }
                    if (status === 403) {
                        formError.textContent = 'Accès non autorisé.';
                        formError.classList.remove('hidden');
                        return;
                    }
                    var body = err.body || (err.response ? err.response.data : null);
                    if (status === 422 && body && body.errors) {
                        // Map real field-level errors
                        var hasFieldErr = false;
                        var map = {
                            'name': 'name',
                            'email': 'email',
                            'role': 'role',
                            'password': 'password',
                            'is_active': 'is_active'
                        };
                        Object.keys(body.errors).forEach(function (field) {
                            if (map[field]) {
                                setFieldError(map[field], Array.isArray(body.errors[field]) ? body.errors[field][0] : String(body.errors[field]));
                                hasFieldErr = true;
                            }
                        });
                        if (hasFieldErr) formError.classList.add('hidden');
                        else {
                            formError.textContent = body.message || "L'email est déjà utilisé.";
                            formError.classList.remove('hidden');
                        }
                        return;
                    }
                    // Duplicate email (422 with email key) or any other 4xx/5xx
                    if (status >= 500 || !status) {
                        formError.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                        formError.classList.remove('hidden');
                        return;
                    }
                    // Fallback 409 / unknown
                    formError.textContent = (body && body.message) ? body.message : "Impossible de créer l'utilisateur.";
                    formError.classList.remove('hidden');
                });
        });
    }

    // "?" "?" DELETE USER MODAL "?" "?"
    var deleteModal = document.getElementById('user-delete-modal');
    var deleteBtn = document.getElementById('btn-delete-user-submit');
    var deleteText = document.getElementById('btn-delete-user-text');
    var deleteSpinner = document.getElementById('btn-delete-user-spinner');
    var deleting = false;
    var deleteUserId = null;
    var deleteLastFocused = null;

    function openDeleteModal(id, name, email) {
        deleteUserId = id;
        deleteLastFocused = document.activeElement;
        document.getElementById('user-delete-name-display').textContent = name;
        document.getElementById('user-delete-email-display').textContent = email;
        document.getElementById('user-delete-modal-error').classList.add('hidden');
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        document.getElementById('btn-delete-user-submit').focus();
    }

    function closeDeleteModal(force) {
        if (deleting && !force) return;
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        document.body.style.overflow = '';
        deleteUserId = null;
        if (deleteLastFocused && deleteLastFocused.focus) deleteLastFocused.focus();
    }

    document.getElementById('user-delete-modal-close').addEventListener('click', closeDeleteModal);
    document.getElementById('user-delete-modal-cancel').addEventListener('click', closeDeleteModal);
    document.getElementById('user-delete-modal-backdrop').addEventListener('click', closeDeleteModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) closeDeleteModal();
    });

    // Delegate delete button clicks from table rows
    if (tbody) {
        tbody.addEventListener('click', function (e) {
            var target = e.target.closest('[data-user-id]');
            if (!target) return;
            var id = target.getAttribute('data-user-id');
            var name = target.getAttribute('data-user-name');
            var email = target.getAttribute('data-user-email');
            openDeleteModal(id, name, email);
        });
    }

    deleteBtn.addEventListener('click', function () {
        if (deleting || !deleteUserId) return;

        var api = window.apiClient;
        if (!api) {
            document.getElementById('user-delete-modal-error').textContent = 'Client API introuvable.';
            document.getElementById('user-delete-modal-error').classList.remove('hidden');
            return;
        }

        var id = deleteUserId;
        deleting = true;
        deleteBtn.disabled = true;
        deleteSpinner.classList.remove('hidden');
        deleteText.textContent = 'Suppression...';

        api['delete']('/admin/users/' + id)
            .then(function () {
                deleting = false;
                deleteBtn.disabled = false;
                deleteSpinner.classList.add('hidden');
                deleteText.textContent = 'Supprimer';
                closeDeleteModal(true);
                showSuccess('Utilisateur supprimé avec succès.');
                loadUsers();
            })
            .catch(function (err) {
                deleting = false;
                deleteBtn.disabled = false;
                deleteSpinner.classList.add('hidden');
                deleteText.textContent = 'Supprimer';
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
                else if (status === 404) msg = 'Utilisateur introuvable.';
                else if (body && body.message) msg = body.message;
                document.getElementById('user-delete-modal-error').textContent = msg;
                document.getElementById('user-delete-modal-error').classList.remove('hidden');
            });
    });
})();
</script>
@endsection