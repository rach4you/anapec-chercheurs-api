@extends('layouts.app')

@section('title', 'Détails utilisateur')

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h2 class="text-lg font-semibold text-gray-900">Détails utilisateur</h2>
        <p class="mt-0.5 text-sm text-gray-500">
            Consultez et gérez les informations et les accès de cet utilisateur.
        </p>
    </div>
    <a href="{{ route('admin.users') }}"
       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M12.79 5.23a.75.75 0 0 1-.09 1.04L8.832 9l3.868 2.73a.75.75 0 1 1-.95 1.148l-4.5-3.15a.75.75 0 0 1 0-1.248l4.5-3.15a.75.75 0 0 1 1.04.09Z"/>
        </svg>
        Retour aux utilisateurs
    </a>
</div>

{{-- ── ACCESS DENIED (403 / non-admin) ── --}}
<div id="access-denied" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
    <p class="text-sm font-medium text-red-800">Accès non autorisé.</p>
    <p class="mt-1 text-sm text-red-600">
        Vous devez être administrateur pour accéder à cette page.
    </p>
    <a href="{{ route('dashboard') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700">
        Retour au Dashboard
    </a>
</div>

{{-- ── 404 / NOT FOUND ── --}}
<div id="not-found" class="mt-6 hidden rounded-lg border border-gray-200 bg-white p-6 text-center shadow-sm">
    <p class="text-sm font-medium text-gray-800">Utilisateur introuvable.</p>
    <p class="mt-1 text-sm text-gray-500">Cet utilisateur n'existe pas ou a été supprimé.</p>
</div>

{{-- ── LOAD ERROR + RETRY ── --}}
<div id="load-error" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
    <p class="text-sm font-medium text-red-800">Impossible de charger les détails de l'utilisateur.</p>
    <button id="btn-retry" type="button"
            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700">
        Réessayer
    </button>
</div>

{{-- ── USER INFORMATION CARD ── --}}
<div id="user-info-card" class="mt-6 hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-4">
        <h3 class="text-sm font-semibold text-gray-900">Informations de l'utilisateur</h3>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-edit-user"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M13.586 3.586a2 2 0 1 1 2.828 2.828l-.793.793-2.828-2.828.793-.793ZM11.379 5.793 3 14.172V17h2.828l8.38-8.379-2.83-2.828Z"/>
                </svg>
                Modifier
            </button>
            <button type="button" id="btn-reset-password"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M9.5 2.5a.75.75 0 0 0-.75.75v.5a.75.75 0 0 0 1.5 0v-.5a.75.75 0 0 0-.75-.75Zm.75 5.25a.75.75 0 0 0-1.5 0v.5a.75.75 0 0 0 1.5 0v-.5Z"/>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M8.75 4a.75.75 0 0 0-.5-.682A5.995 5.995 0 0 1 14.6 5.644a.75.75 0 0 0 1.5-.188A7.495 7.495 0 0 0 8.25 2.522a2.25 2.25 0 0 0-.5.682V4.75A2.25 2.25 0 0 0 10 7h2.25a.75.75 0 0 1 0 1.5h-2.25A2.25 2.25 0 0 1 7.75 6.25v1.125H5.5A.75.75 0 0 1 5.5 6.25v-2a.75.75 0 0 1 .75-.75h2.25v-.5Z"/>
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M15.75 10a.75.75 0 0 1 .75.75V11a2.75 2.75 0 0 0 0 5.5h2.75a.75.75 0 0 1 0 1.5H16.5A4.25 4.25 0 0 1 12.25 11v-.25a.75.75 0 0 1 .75-.75h2.75Z"/>
                </svg>
                Réinitialiser le mot de passe
            </button>
        </div>
    </div>

    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <dt class="text-xs font-medium text-gray-500">Nom</dt>
            <dd id="detail-name" class="mt-1 text-sm font-medium text-gray-900">—</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Email</dt>
            <dd id="detail-email" class="mt-1 text-sm font-medium text-gray-900">—</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Rôle</dt>
            <dd id="detail-role" class="mt-1"></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Statut</dt>
            <dd id="detail-status" class="mt-1"></dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Date de création</dt>
            <dd id="detail-created" class="mt-1 text-sm text-gray-700">—</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Dernière modification</dt>
            <dd id="detail-updated" class="mt-1 text-sm text-gray-700">—</dd>
        </div>
    </dl>
</div>

{{-- ── WEB SERVICE PERMISSIONS ── --}}
<div id="permissions-card" class="mt-6 hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-900">Permissions Web Services</h3>
        <button type="button" id="btn-edit-permissions"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
            Modifier les permissions
        </button>
    </div>
    <div id="permissions-skeleton" class="px-6 py-4"></div>
    <div id="permissions-body" class="hidden px-6 py-4"></div>
</div>

{{-- ── SUCCESS / ERROR TOAST ── --}}
<div id="toast" class="fixed right-4 top-4 z-[60] hidden max-w-sm rounded-lg border px-4 py-3 shadow-sm"
     role="status" aria-live="polite">
    <p id="toast-msg" class="text-sm font-medium"></p>
</div>

{{-- ── EDIT USER MODAL ── --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
    <div id="edit-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="edit-modal-title" class="text-base font-semibold text-gray-900">Modifier l'utilisateur</h3>
            <button type="button" id="edit-modal-close" class="p-1 text-gray-400 transition-colors hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div id="edit-form-error" class="mx-5 mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700" role="alert"></div>
        <form id="edit-user-form" class="space-y-4 px-5 py-4" novalidate>
            <div>
                <label for="ed-name" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="ed-name" name="name" required maxlength="255"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500" />
                <p id="err-ed-name" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="ed-email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="ed-email" name="email" required maxlength="255"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500" autocomplete="email" />
                <p id="err-ed-email" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="ed-role" class="block text-sm font-medium text-gray-700">Rôle</label>
                <select id="ed-role" name="role"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
                <p id="err-ed-role" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="ed-active" class="flex items-center gap-2.5 text-sm font-medium text-gray-700">
                    <input type="checkbox" id="ed-active" name="is_active"
                           class="h-4 w-4 rounded border-gray-300 text-anapec-600 focus:ring-anapec-500" />
                    Compte actif
                </label>
                <p id="err-ed-is_active" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" id="edit-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="submit" id="btn-save-user"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-save-user-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-save-user-text">Enregistrer</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── PASSWORD RESET MODAL ── --}}
<div id="password-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
    <div id="password-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="password-modal-title" class="text-base font-semibold text-gray-900">Réinitialiser le mot de passe</h3>
            <button type="button" id="password-modal-close" class="p-1 text-gray-400 transition-colors hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div id="password-form-error" class="mx-5 mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700" role="alert"></div>
        <form id="password-reset-form" class="space-y-4 px-5 py-4" novalidate>
            <div>
                <label for="psw-new" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                <div class="relative mt-1">
                    <input type="password" id="psw-new" name="password" required minlength="8"
                           class="block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 pr-10 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                           placeholder="8 caractères minimum" autocomplete="new-password" />
                    <button type="button" id="psw-toggle"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
                            aria-label="Afficher ou masquer le mot de passe">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M10 1.944a.75.75 0 0 1 .75.75v.554a.75.75 0 0 1-1.5 0V2.7a.75.75 0 0 1 .75-.756ZM2.25 9.75a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Zm14.5-.75h-2.5a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5ZM2.25 13.75a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Zm14.5-.75h-2.5a.75.75 0 0 0 0 1.5h2.5a.75.75 0 0 0 0-1.5ZM10 10.75a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0V11.5a.75.75 0 0 1 .75-.75Z"/>
                        </svg>
                    </button>
                </div>
                <p id="err-psw-new" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label for="psw-confirm" class="block text-sm font-medium text-gray-700">Confirmation du mot de passe</label>
                <input type="password" id="psw-confirm" name="password_confirmation" required minlength="8"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm placeholder-gray-400 focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                       placeholder="Retapez le mot de passe" autocomplete="new-password" />
                <p id="err-psw-confirm" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" id="password-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="submit" id="btn-reset-password"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-reset-password-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-reset-password-text">Réinitialiser</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── PERMISSIONS EDIT MODAL ── --}}
<div id="perm-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="perm-modal-title">
    <div id="perm-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="perm-modal-title" class="text-base font-semibold text-gray-900">Modifier les permissions</h3>
            <button type="button" id="perm-modal-close" class="p-1 text-gray-400 transition-colors hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div id="perm-modal-list" class="max-h-80 overflow-y-auto px-5 py-4"></div>
        <div class="flex items-center justify-end gap-2.5 border-t border-gray-100 px-5 py-4">
            <button type="button" id="perm-modal-cancel"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                Annuler
            </button>
            <button type="button" id="btn-save-perms"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                <span id="btn-save-perms-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                <span id="btn-save-perms-text">Enregistrer</span>
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

    // ── Parse user id from /admin/users/{id} ──
    var m = window.location.pathname.match(/\/admin\/users\/(\d+)$/);
    var userId = m ? m[1] : null;
    if (!userId) {
        window.location.href = '/admin/users';
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

    var currentUser = null;

    // ── Auth gate ──
    get('/api/v1/auth/me')
        .then(function (res) {
            if (res.status === 401) {
                localStorage.removeItem('anapec_token');
                localStorage.removeItem('anapec_user');
                window.location.href = '/login';
                return;
            }
            if (!res.ok) return res.json().then(function () { showLoadError(); });
            return res.json().then(function (data) {
                if (!data.success || !data.data) { showLoadError(); return; }
                var me = data.data;
                if (me.role !== 'admin') {
                    showAccessDenied();
                    return;
                }
                loadUser();
            });
        })
        .catch(function () { showLoadError(); });

    function showAccessDenied() {
        document.getElementById('access-denied').classList.remove('hidden');
    }

    function showNotFound() {
        document.getElementById('not-found').classList.remove('hidden');
        document.getElementById('permissions-card').classList.add('hidden');
    }

    function showLoadError() {
        document.getElementById('load-error').classList.remove('hidden');
    }

    function hideAllStates() {
        ['access-denied', 'not-found', 'load-error'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
    }

    function loadUser() {
        hideAllStates();
        document.getElementById('user-info-card').classList.add('hidden');
        document.getElementById('permissions-card').classList.add('hidden');
        document.getElementById('permissions-skeleton').innerHTML =
            '<div class="space-y-3">' +
            '<div class="h-4 w-32 animate-pulse rounded bg-gray-200"></div>' +
            '<div class="h-4 w-48 animate-pulse rounded bg-gray-200"></div>' +
            '<div class="h-4 w-40 animate-pulse rounded bg-gray-200"></div>' +
            '</div>';
        document.getElementById('permissions-skeleton').classList.remove('hidden');
        document.getElementById('permissions-body').classList.add('hidden');

        get('/api/v1/admin/users/' + userId)
            .then(function (res) {
                if (res.status === 403) { showAccessDenied(); return; }
                if (res.status === 404) { showNotFound(); return; }
                if (res.status === 401) {
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                    window.location.href = '/login';
                    return;
                }
                if (!res.ok) { showLoadError(); return; }
                return res.json().then(function (data) {
                    document.getElementById('permissions-skeleton').classList.add('hidden');
                    if (!data.success || !data.data) { showLoadError(); return; }
                    currentUser = data.data;
                    renderUser();
                    loadPermissions();
                });
            })
            .catch(function () {
                document.getElementById('permissions-skeleton').classList.add('hidden');
                showLoadError();
            });
    }

    function renderUser() {
        document.getElementById('user-info-card').classList.remove('hidden');
        document.getElementById('permissions-card').classList.remove('hidden');
        document.getElementById('detail-name').textContent = currentUser.name || '—';
        document.getElementById('detail-email').textContent = currentUser.email || '—';
        document.getElementById('detail-role').innerHTML = roleBadge(currentUser.role);
        document.getElementById('detail-status').innerHTML = statusBadge(currentUser.is_active === true);
        document.getElementById('detail-created').textContent = fmtDate(currentUser.created_at);
        document.getElementById('detail-updated').textContent = fmtDate(currentUser.updated_at);
    }

    // ── PERMISSIONS ──
    var allPermissions = [];
    function loadPermissions() {
        document.getElementById('permissions-skeleton').innerHTML =
            '<div class="space-y-3"><div class="h-4 w-32 animate-pulse rounded bg-gray-200"></div>' +
            '<div class="h-4 w-48 animate-pulse rounded bg-gray-200"></div></div>';
        document.getElementById('permissions-skeleton').classList.remove('hidden');
        document.getElementById('permissions-body').classList.add('hidden');

        get('/api/v1/admin/users/' + userId + '/web-services')
            .then(function (res) {
                document.getElementById('permissions-skeleton').classList.add('hidden');
                if (res.status === 403) {
                    renderPermissionsEmpty();
                    return;
                }
                if (!res.ok) { renderPermissionsError(); return; }
                return res.json().then(function (data) {
                    if (!data.success || !Array.isArray(data.data)) { renderPermissionsError(); return; }
                    allPermissions = data.data;
                    renderPermissions();
                });
            })
            .catch(function () {
                document.getElementById('permissions-skeleton').classList.add('hidden');
                renderPermissionsError();
            });
    }

    function renderPermissions() {
        var body = document.getElementById('permissions-body');
        body.classList.remove('hidden');
        if (!allPermissions.length) {
            body.innerHTML = '<p class="text-sm text-gray-500">Aucune permission Web Service pour cet utilisateur.</p>';
            return;
        }
        var html = '';
        allPermissions.forEach(function (p) {
            var globalOn = p.global_is_active === true;
            var enabled = p.is_enabled === true;
            var effective = p.effective_access === true;
            var globalBadge = globalOn
                ? '<span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Actif</span>'
                : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Inactif</span>';
            var permBadge = enabled
                ? '<span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Activée</span>'
                : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Désactivée</span>';
            var effBadge = effective
                ? '<span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Autorisé</span>'
                : '<span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">Refusé</span>';

            html += '<div class="mb-3 last:mb-0 rounded-lg border border-gray-200 p-4">' +
                '<div class="flex items-center justify-between gap-3">' +
                '<div>' +
                '<p class="text-sm font-semibold text-gray-900">' + esc(p.code) + '</p>' +
                '<p class="text-xs text-gray-500">' + esc(p.name || '') + '</p>' +
                '</div>' +
                effBadge +
                '</div>' +
                '<dl class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">' +
                '<div><dt class="text-xs font-medium text-gray-500">Statut global</dt><dd class="mt-0.5">' + globalBadge + '</dd></div>' +
                '<div><dt class="text-xs font-medium text-gray-500">Permission</dt><dd class="mt-0.5">' + permBadge + '</dd></div>' +
                '<div><dt class="text-xs font-medium text-gray-500">Accès effectif</dt><dd class="mt-0.5 text-xs font-medium text-gray-700">' +
                (effective ? 'Autorisé' : 'Refusé') + '</dd></div>' +
                '</dl>' +
                '</div>';
        });
        body.innerHTML = html;
    }

    function renderPermissionsEmpty() {
        var body = document.getElementById('permissions-body');
        body.classList.remove('hidden');
        body.innerHTML = '<p class="text-sm text-gray-500">Permission introuvable ou non autorisé.</p>';
    }

    function renderPermissionsError() {
        var body = document.getElementById('permissions-body');
        body.classList.remove('hidden');
        body.innerHTML = '<p class="text-sm text-red-600">Impossible de charger les permissions.</p>';
    }

    // ── RETRY ──
    var retry = document.getElementById('btn-retry');
    if (retry) retry.addEventListener('click', loadUser);

    // ════════════════════════════════════════════
    //  EDIT USER MODAL
    // ════════════════════════════════════════════
    var editModal = document.getElementById('edit-modal');
    var editBackdrop = document.getElementById('edit-modal-backdrop');
    var editForm = document.getElementById('edit-user-form');
    var editFormError = document.getElementById('edit-form-error');
    var btnSave = document.getElementById('btn-save-user');
    var btnSaveText = document.getElementById('btn-save-user-text');
    var btnSaveSpinner = document.getElementById('btn-save-user-spinner');
    var editLastFocused = null;
    var editSubmitting = false;

    var editFields = ['name', 'email', 'role', 'is_active'];
    function clearEditErrors() {
        editFields.forEach(function (f) {
            var el = document.getElementById('err-ed-' + f);
            if (el) el.classList.add('hidden');
        });
        editFormError.classList.add('hidden');
    }
    function setEditError(name, msg) {
        var el = document.getElementById('err-ed-' + name);
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function openEdit() {
        if (!currentUser) return;
        editLastFocused = document.activeElement;
        document.getElementById('ed-name').value = currentUser.name || '';
        document.getElementById('ed-email').value = currentUser.email || '';
        document.getElementById('ed-role').value = currentUser.role || 'user';
        document.getElementById('ed-active').checked = currentUser.is_active === true;
        clearEditErrors();
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        document.getElementById('ed-name').focus();
    }
    function closeEdit() {
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
        document.body.style.overflow = '';
        if (editLastFocused && editLastFocused.focus) editLastFocused.focus();
    }

    document.getElementById('btn-edit-user').addEventListener('click', openEdit);
    document.getElementById('edit-modal-close').addEventListener('click', closeEdit);
    document.getElementById('edit-modal-cancel').addEventListener('click', closeEdit);
    editBackdrop.addEventListener('click', closeEdit);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !editModal.classList.contains('hidden')) {
            closeEdit();
        }
    });

    function editValidate() {
        clearEditErrors();
        var ok = true;
        var name = document.getElementById('ed-name').value.trim();
        var email = document.getElementById('ed-email').value.trim();
        if (!name) { setEditError('name', 'Le nom est requis.'); ok = false; }
        if (!email) { setEditError('email', "L'email est requis."); ok = false; }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setEditError('email', 'Format d\'email invalide.'); ok = false; }
        return ok;
    }

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (editSubmitting) return;
        if (!editValidate()) return;

        var payload = {
            name: document.getElementById('ed-name').value.trim(),
            email: document.getElementById('ed-email').value.trim(),
            role: document.getElementById('ed-role').value,
            is_active: document.getElementById('ed-active').checked === true
        };

        editSubmitting = true;
        btnSave.disabled = true;
        btnSaveSpinner.classList.remove('hidden');
        btnSaveText.textContent = 'Enregistrement...';

        function done() {
            editSubmitting = false;
            btnSave.disabled = false;
            btnSaveSpinner.classList.add('hidden');
            btnSaveText.textContent = 'Enregistrer';
        }

        var api = window.apiClient;
        if (!api) {
            done();
            showToast('Client API introuvable.', 'error');
            return;
        }

        // PUT update profile (name/email/role) + PATCH status (is_active)
        api.put('/admin/users/' + userId, {
            name: payload.name,
            email: payload.email,
            role: payload.role
        }).then(function () {
            return api.patch('/admin/users/' + userId + '/status', { is_active: payload.is_active });
        }).then(function () {
            done();
            closeEdit();
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
                editFormError.classList.remove('hidden');
                return;
            }
            var body = err.body || (err.response ? err.response.data : null);
            if (status === 422 && body && body.errors) {
                var mapped = { 'name': 'name', 'email': 'email', 'role': 'role', 'is_active': 'is_active' };
                var has = false;
                Object.keys(body.errors).forEach(function (f) {
                    if (mapped[f]) {
                        setEditError(mapped[f], Array.isArray(body.errors[f]) ? body.errors[f][0] : String(body.errors[f]));
                        has = true;
                    }
                });
                if (has) editFormError.classList.add('hidden');
                else {
                    editFormError.textContent = body.message || 'Erreur de validation.';
                    editFormError.classList.remove('hidden');
                }
                return;
            }
            editFormError.textContent = 'Une erreur est survenue. Veuillez réessayer.';
            editFormError.classList.remove('hidden');
        });
    });

    // ════════════════════════════════════════════
    //  PASSWORD RESET MODAL
    // ════════════════════════════════════════════
    var pswModal = document.getElementById('password-modal');
    var pswBackdrop = document.getElementById('password-modal-backdrop');
    var pswForm = document.getElementById('password-reset-form');
    var pswFormError = document.getElementById('password-form-error');
    var pswBtn = document.getElementById('btn-reset-password');
    var pswBtnText = document.getElementById('btn-reset-password-text');
    var pswBtnSpinner = document.getElementById('btn-reset-password-spinner');
    var pswLastFocused = null;
    var pswSubmitting = false;

    function openPassword() {
        pswLastFocused = document.activeElement;
        document.getElementById('psw-new').value = '';
        document.getElementById('psw-confirm').value = '';
        pswFormError.classList.add('hidden');
        document.getElementById('err-psw-new').classList.add('hidden');
        document.getElementById('err-psw-confirm').classList.add('hidden');
        pswModal.classList.remove('hidden');
        pswModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        document.getElementById('psw-new').focus();
    }
    function closePassword() {
        pswModal.classList.add('hidden');
        pswModal.classList.remove('flex');
        document.body.style.overflow = '';
        if (pswLastFocused && pswLastFocused.focus) pswLastFocused.focus();
    }

    document.getElementById('btn-reset-password').addEventListener('click', openPassword);
    document.getElementById('password-modal-close').addEventListener('click', closePassword);
    document.getElementById('password-modal-cancel').addEventListener('click', closePassword);
    pswBackdrop.addEventListener('click', closePassword);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !pswModal.classList.contains('hidden')) closePassword();
    });

    // Show/hide password
    document.getElementById('psw-toggle').addEventListener('click', function () {
        var el = document.getElementById('psw-new');
        var hidden = el.type === 'password';
        el.type = hidden ? 'text' : 'password';
    });

    pswForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (pswSubmitting) return;

        var psw = document.getElementById('psw-new').value;
        var confirm = document.getElementById('psw-confirm').value;
        var ok = true;
        document.getElementById('err-psw-new').classList.add('hidden');
        document.getElementById('err-psw-confirm').classList.add('hidden');
        pswFormError.classList.add('hidden');

        if (!psw) { document.getElementById('err-psw-new').textContent = 'Le mot de passe est requis.'; document.getElementById('err-psw-new').classList.remove('hidden'); ok = false; }
        else if (psw.length < 8) { document.getElementById('err-psw-new').textContent = 'Au moins 8 caractères requis.'; document.getElementById('err-psw-new').classList.remove('hidden'); ok = false; }
        if (psw !== confirm) { document.getElementById('err-psw-confirm').textContent = 'Les mots de passe ne correspondent pas.'; document.getElementById('err-psw-confirm').classList.remove('hidden'); ok = false; }
        if (!ok) return;

        pswSubmitting = true;
        pswBtn.disabled = true;
        pswBtnSpinner.classList.remove('hidden');
        pswBtnText.textContent = 'Réinitialisation en cours...';

        function done() {
            pswSubmitting = false;
            pswBtn.disabled = false;
            pswBtnSpinner.classList.add('hidden');
            pswBtnText.textContent = 'Réinitialiser';
        }

        var api = window.apiClient;
        if (!api) {
            done();
            pswFormError.textContent = 'Client API introuvable.';
            pswFormError.classList.remove('hidden');
            return;
        }

        // Backend expects only "password" (no confirmation)
        api.post('/admin/users/' + userId + '/password', { password: psw })
            .then(function () {
                done();
                closePassword();
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
                    pswFormError.classList.remove('hidden');
                    return;
                }
                var body = err.body || (err.response ? err.response.data : null);
                if (status === 422 && body && body.errors) {
                    var f = body.errors.password || body.errors;
                    pswFormError.textContent = Array.isArray(f) ? f[0] : (body.message || 'Erreur de validation.');
                    pswFormError.classList.remove('hidden');
                    return;
                }
                pswFormError.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                pswFormError.classList.remove('hidden');
            });
    });

    // ════════════════════════════════════════════
    //  PERMISSIONS EDIT MODAL
    // ════════════════════════════════════════════
    var permModal = document.getElementById('perm-modal');
    var permBackdrop = document.getElementById('perm-modal-backdrop');
    var permList = document.getElementById('perm-modal-list');
    var permBtn = document.getElementById('btn-save-perms');
    var permBtnText = document.getElementById('btn-save-perms-text');
    var permBtnSpinner = document.getElementById('btn-save-perms-spinner');
    var permLastFocused = null;
    var permSubmitting = false;
    var permChecks = {};

    function openPerms() {
        if (!allPermissions.length) return;
        permLastFocused = document.activeElement;
        permChecks = {};
        var html = '';
        allPermissions.forEach(function (p) {
            var enabled = p.is_enabled === true;
            html += '<label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 mb-2 last:mb-0 cursor-pointer">' +
                '<div>' +
                '<p class="text-sm font-medium text-gray-900">' + esc(p.code) + '</p>' +
                '<p class="text-xs text-gray-500">' + esc(p.name || '') + '</p>' +
                '</div>' +
                '<input type="checkbox" data-code="' + esc(p.code) + '" class="perm-check h-4 w-4 rounded border-gray-300 text-anapec-600 focus:ring-anapec-500" ' + (enabled ? 'checked' : '') + ' />' +
                '</label>';
        });
        permList.innerHTML = html;
        permModal.classList.remove('hidden');
        permModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closePerms() {
        permModal.classList.add('hidden');
        permModal.classList.remove('flex');
        document.body.style.overflow = '';
        if (permLastFocused && permLastFocused.focus) permLastFocused.focus();
    }

    document.getElementById('btn-edit-permissions').addEventListener('click', openPerms);
    document.getElementById('perm-modal-close').addEventListener('click', closePerms);
    document.getElementById('perm-modal-cancel').addEventListener('click', closePerms);
    permBackdrop.addEventListener('click', closePerms);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !permModal.classList.contains('hidden')) closePerms();
    });

    permBtn.addEventListener('click', function () {
        if (permSubmitting || !allPermissions.length) return;
        permSubmitting = true;
        permBtn.disabled = true;
        permBtnSpinner.classList.remove('hidden');
        permBtnText.textContent = 'Enregistrement...';

        var payload = allPermissions.map(function (p) {
            var cb = permList.querySelector('.perm-check[data-code="' + p.code + '"]');
            return { code: p.code, enabled: cb ? cb.checked : (p.is_enabled === true) };
        });

        var api = window.apiClient;
        function done() {
            permSubmitting = false;
            permBtn.disabled = false;
            permBtnSpinner.classList.add('hidden');
            permBtnText.textContent = 'Enregistrer';
        }

        if (!api) {
            done();
            showToast('Client API introuvable.', 'error');
            return;
        }

        api.put('/admin/users/' + userId + '/web-services', { web_services: payload })
            .then(function () {
                done();
                closePerms();
                showToast('Permissions mises à jour avec succès.');
                loadPermissions();
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
                    showToast('Accès non autorisé.', 'error');
                    return;
                }
                var body = err.body || (err.response ? err.response.data : null);
                if (status === 422 && body) {
                    showToast('Erreur de validation. Veuillez réessayer.', 'error');
                    return;
                }
                showToast('Une erreur est survenue. Veuillez réessayer.', 'error');
            });
    });
})();
</script>
@endsection
