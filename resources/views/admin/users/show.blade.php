@extends('layouts.app')

@section('title', 'Détails utilisateur')

@section('content')
<div id="admin-user-show-root" class="hidden">
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

{{-- ── DOMAIN GRANTS ── --}}
<div id="domains-card" class="mt-6 hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-900">Domaines</h3>
        <p class="text-xs text-gray-500">Accès par domaine (mécanisme par défaut)</p>
    </div>
    <div id="domains-skeleton" class="px-6 py-4"></div>
    <div id="domains-body" class="hidden px-6 py-4"></div>
</div>

{{-- ── WEB SERVICE PERMISSIONS ── --}}
<div id="permissions-card" class="mt-6 hidden rounded-lg border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
        <h3 class="text-sm font-semibold text-gray-900">Exceptions Web Services</h3>
        <button type="button" id="btn-revoke-perms"
                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
            Révoquer toutes les permissions
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

{{-- ── CONFIRM REVOKE-ALL MODAL ── --}}
<div id="revoke-modal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center"
     role="dialog" aria-modal="true" aria-labelledby="revoke-modal-title">
    <div id="revoke-modal-backdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative z-10 w-full max-w-md rounded-t-xl sm:rounded-xl border border-gray-200 bg-white shadow-lg sm:m-4">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h3 id="revoke-modal-title" class="text-base font-semibold text-gray-900">Révoquer toutes les permissions</h3>
            <button type="button" id="revoke-modal-close"
                    class="p-1 text-gray-400 transition-colors hover:text-gray-600"
                    aria-label="Fermer">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
                </svg>
            </button>
        </div>
        <div class="space-y-4 px-5 py-4">
            <p id="revoke-modal-message" class="text-sm text-gray-700"></p>
            <div id="revoke-modal-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-sm text-red-700"
                 role="alert"></div>
            <div class="flex items-center justify-end gap-2.5 pt-2">
                <button type="button" id="revoke-modal-cancel"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                    Annuler
                </button>
                <button type="button" id="btn-confirm-revoke"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="btn-confirm-revoke-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="btn-confirm-revoke-text">Révoquer</span>
                </button>
            </div>
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
    var rootEl = document.getElementById('admin-user-show-root');
    var authLoading = document.getElementById('auth-loading');

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
                return res.json().then(function () { showLoadError(); });
            }
            return res.json().then(function (data) {
                if (!data.success || !data.data) {
                    authLoading.classList.add('hidden');
                    showLoadError();
                    return;
                }
                var me = data.data;
                localStorage.setItem('anapec_user', JSON.stringify(me));
                if (me.role !== 'admin') {
                    // Non-admin: do NOT load admin data, do NOT log out.
                    authLoading.classList.add('hidden');
                    showAccessDenied();
                    setTimeout(function () { window.location.replace('/dashboard'); }, 1200);
                    return;
                }
                authLoading.classList.add('hidden');
                rootEl.classList.remove('hidden');
                loadUser();
            });
        })
        .catch(function () {
            authLoading.classList.add('hidden');
            showLoadError();
        });

    function showAccessDenied() {
        document.getElementById('access-denied').classList.remove('hidden');
    }

    function showNotFound() {
        document.getElementById('not-found').classList.remove('hidden');
        document.getElementById('permissions-card').classList.add('hidden');
        document.getElementById('domains-card').classList.add('hidden');
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
        document.getElementById('domains-card').classList.add('hidden');
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
        document.getElementById('domains-card').classList.remove('hidden');
        document.getElementById('permissions-card').classList.remove('hidden');
        document.getElementById('detail-name').textContent = currentUser.name || '—';
        document.getElementById('detail-email').textContent = currentUser.email || '—';
        document.getElementById('detail-role').innerHTML = roleBadge(currentUser.role);
        document.getElementById('detail-status').innerHTML = statusBadge(currentUser.is_active === true);
        document.getElementById('detail-created').textContent = fmtDate(currentUser.created_at);
        document.getElementById('detail-updated').textContent = fmtDate(currentUser.updated_at);
    }

    // ── PERMISSIONS ──
    // allPermissions merges the full official Web Service catalogue with this
    // user's assigned permissions, so an admin can also grant a first
    // permission to a user who currently has none.
    var allPermissions = [];
    var allDomains = [];
    var allDomainCatalog = [];
    var permissionToggleBusy = {};
    var domainToggleBusy = {};

    function updateRevokeButton() {
        var btn = document.getElementById('btn-revoke-perms');
        if (!btn) return;
        var anyAssigned = allPermissions.some(function (p) {
            return p.is_enabled === true;
        });
        btn.disabled = !anyAssigned;
        btn.title = anyAssigned ? '' : 'Cet utilisateur n\'a aucune permission activée.';
    }

    function loadPermissions() {
        document.getElementById('permissions-skeleton').innerHTML =
            '<div class="space-y-3"><div class="h-4 w-32 animate-pulse rounded bg-gray-200"></div>' +
            '<div class="h-4 w-48 animate-pulse rounded bg-gray-200"></div>' +
            '<div class="h-4 w-40 animate-pulse rounded bg-gray-200"></div></div>';
        document.getElementById('domains-skeleton').innerHTML =
            '<div class="space-y-3"><div class="h-4 w-32 animate-pulse rounded bg-gray-200"></div>' +
            '<div class="h-4 w-48 animate-pulse rounded bg-gray-200"></div>' +
            '<div class="h-4 w-40 animate-pulse rounded bg-gray-200"></div></div>';
        document.getElementById('permissions-skeleton').classList.remove('hidden');
        document.getElementById('domains-skeleton').classList.remove('hidden');
        document.getElementById('permissions-body').classList.add('hidden');
        document.getElementById('domains-body').classList.add('hidden');

        var assignedReq = get('/api/v1/admin/users/' + userId + '/web-services');
        var catalogReq = get('/api/v1/admin/web-services');
        var domainsReq = get('/api/v1/admin/users/' + userId + '/domains');
        var domainCatalogReq = get('/api/v1/admin/domains');

        Promise.all([assignedReq, catalogReq, domainsReq, domainCatalogReq])
            .then(function (responses) {
                document.getElementById('permissions-skeleton').classList.add('hidden');
                document.getElementById('domains-skeleton').classList.add('hidden');

                var assignedRes = responses[0];
                var catalogRes = responses[1];
                var domainsRes = responses[2];
                var domainCatalogRes = responses[3];

                if (assignedRes.status === 403) {
                    renderPermissionsEmpty();
                    return;
                }
                if (!assignedRes.ok || !catalogRes.ok) {
                    renderPermissionsError();
                    return;
                }

                return Promise.all([
                    assignedRes.json(), catalogRes.json(),
                    domainsRes.ok ? domainsRes.json() : { success: true, data: { domains: [], web_services: [] } },
                    domainCatalogRes.ok ? domainCatalogRes.json() : { success: true, data: [] }
                ]).then(function (payloads) {
                    var assignedData = payloads[0];
                    var catalogData = payloads[1];
                    var domainsData = payloads[2];
                    var domainCatalogData = payloads[3];

                    if (!assignedData.success || !Array.isArray(assignedData.data)) {
                        renderPermissionsError();
                        return;
                    }
                    if (!catalogData.success || !Array.isArray(catalogData.data)) {
                        renderPermissionsError();
                        return;
                    }

                    var byCode = {};
                    assignedData.data.forEach(function (p) {
                        byCode[p.code] = p;
                    });

                    allPermissions = catalogData.data.map(function (svc) {
                        var assigned = byCode[svc.code];
                        var isEnabled = assigned !== undefined && assigned.is_enabled === true;
                        var grantSource = assigned !== undefined ? assigned.grant_source : 'none';
                        var domainGranted = assigned !== undefined ? assigned.domain_granted === true : false;
                        var overrideDisabled = assigned !== undefined ? assigned.override_disabled === true : false;
                        return {
                            code: svc.code,
                            name: svc.name || '',
                            description: svc.description || '',
                            global_is_active: svc.is_active === true,
                            is_enabled: isEnabled,
                            grant_source: grantSource,
                            domain_granted: domainGranted,
                            override_disabled: overrideDisabled,
                            effective_access: assigned !== undefined
                                ? assigned.effective_access === true
                                : (currentUser && currentUser.is_active === true && svc.is_active === true && isEnabled)
                        };
                    });

                    if (domainsData.success && domainsData.data) {
                        allDomains = Array.isArray(domainsData.data.domains) ? domainsData.data.domains : [];
                    } else {
                        allDomains = [];
                    }

                    if (domainCatalogData.success && Array.isArray(domainCatalogData.data)) {
                        allDomainCatalog = domainCatalogData.data;
                    } else {
                        allDomainCatalog = [];
                    }

                    renderPermissions();
                    renderDomains();
                });
            })
            .catch(function () {
                document.getElementById('permissions-skeleton').classList.add('hidden');
                document.getElementById('domains-skeleton').classList.add('hidden');
                renderPermissionsError();
            });
    }

    function renderDomains() {
        var body = document.getElementById('domains-body');
        body.classList.remove('hidden');

        var grantedSet = {};
        allDomains.forEach(function (d) { grantedSet[d.code] = d; });

        // Build the full checkbox list from the domain catalog, merging the
        // user's current grants (allDomains) onto it.
        var rows = allDomainCatalog.map(function (d) {
            var grant = grantedSet[d.code];
            return {
                code: d.code,
                name: d.name || '',
                is_active: d.is_active === true,
                service_count: grant ? (grant.service_count || 0) : 0,
                is_enabled: grant ? grant.is_enabled === true : false
            };
        });

        if (!rows.length) {
            body.innerHTML = '<p class="text-sm text-gray-500">Aucun domaine disponible.</p>';
            return;
        }

        var anyGranted = rows.some(function (r) { return r.is_enabled; });
        var html =
            '<div class="mb-4 flex items-center justify-between gap-3 rounded-lg border border-anapec-200 bg-anapec-50 px-4 py-3">' +
            '<p class="text-xs font-medium text-anapec-700">' +
            'Accordez un domaine pour ouvrir tous ses Web Services actifs d\'un coup.' +
            '</p>' +
            '<button type="button" id="btn-assign-domains" ' +
            (anyGranted ? '' : 'disabled ') +
            'class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">' +
            'Assigner les domaines cochés' +
            '</button>' +
            '</div>';

        rows.forEach(function (r) {
            var active = r.is_active === true;
            var granted = r.is_enabled === true;
            html += '<div class="mb-3 last:mb-0 flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-4">' +
                '<label class="flex min-w-0 items-start gap-3">' +
                '<input type="checkbox" data-domain-check="' + esc(r.code) + '" ' + (granted ? 'checked' : '') +
                ' class="mt-0.5 h-4 w-4 rounded border-gray-300 text-anapec-600 focus:ring-anapec-500" aria-label="Accorder ' + esc(r.code) + '" />' +
                '<span class="min-w-0">' +
                '<span class="flex items-center gap-2">' +
                '<span class="font-mono text-sm font-semibold text-gray-900">' + esc(r.code) + '</span>' +
                (active
                    ? '<span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700">Actif</span>'
                    : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">Inactif</span>') +
                '</span>' +
                '<span class="mt-0.5 block text-xs text-gray-500">' + esc(r.name) + '</span>' +
                '<span class="mt-0.5 block text-xs text-gray-400">' + (r.service_count || 0) + ' Web Services</span>' +
                '</span>' +
                '</label>' +
                '</div>';
        });

        body.innerHTML = html;

        var assignBtn = document.getElementById('btn-assign-domains');
        if (assignBtn) assignBtn.addEventListener('click', function () {
            var selected = [];
            body.querySelectorAll('[data-domain-check]:checked').forEach(function (cb) {
                selected.push(cb.getAttribute('data-domain-check'));
            });
            bulkAssignDomains(selected);
        });
    }

    function bulkAssignDomains(selectedCodes) {
        var api = window.apiClient;
        if (!api) {
            showToast('Client API introuvable.', 'error');
            return;
        }

        var btn = document.getElementById('btn-assign-domains');
        if (btn) { btn.disabled = true; btn.textContent = 'Assignation...'; }

        api.put('/admin/users/' + userId + '/domains', { domains: selectedCodes })
            .then(function () {
                if (btn) { btn.disabled = false; btn.textContent = 'Assigner les domaines cochés'; }
                showToast('Domaines assignés.');
                loadPermissions();
            })
            .catch(function (err) {
                if (btn) { btn.disabled = false; btn.textContent = 'Assigner les domaines cochés'; }
                var status = err.status || (err.response ? err.response.status : null);
                var body = err.body || (err.response ? err.response.data : null);
                var msg = 'Une erreur est survenue. Veuillez réessayer.';
                if (status === 403) msg = 'Accès non autorisé.';
                else if (body && body.message) msg = body.message;
                showToast(msg, 'error');
            });
    }

    function toggleDomain(code, next, btn) {
        // Legacy single-domain toggle (kept for PATCH-based toggles).
        domainToggleBusy[code] = true;
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');

        var api = window.apiClient;

        function done() {
            domainToggleBusy[code] = false;
            loadPermissions();
        }

        if (!api) {
            delete domainToggleBusy[code];
            showToast('Client API introuvable.', 'error');
            btn.disabled = false;
            btn.removeAttribute('aria-busy');
            return;
        }

        api.patch('/admin/users/' + userId + '/domains/' + encodeURIComponent(code), { is_enabled: next })
            .then(function (res) {
                var body = res && res.data;
                var data = body && body.data;
                if (data) {
                    var idx = -1;
                    for (var i = 0; i < allDomains.length; i++) {
                        if (allDomains[i].code === code) { idx = i; break; }
                    }
                    if (idx !== -1) {
                        allDomains[idx].is_enabled = data.is_enabled === true;
                    }
                }
                done();
                showToast(next ? 'Domaine activé.' : 'Domaine désactivé.');
            })
            .catch(function (err) {
                delete domainToggleBusy[code];
                var status = err.status || (err.response ? err.response.status : null);
                var body = err.body || (err.response ? err.response.data : null);
                var msg = 'Une erreur est survenue. Veuillez réessayer.';
                if (status === 403) msg = 'Accès non autorisé.';
                else if (status === 404) msg = 'Domaine introuvable.';
                else if (body && body.message) msg = body.message;
                showToast(msg, 'error');
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
            });
    }

    function renderPermissions() {
        var body = document.getElementById('permissions-body');
        body.classList.remove('hidden');
        if (!allPermissions.length) {
            body.innerHTML = '<p class="text-sm text-gray-500">Aucun Web Service officiel disponible.</p>';
            updateRevokeButton();
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
                : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">Non appliquée</span>';
            var effBadge = effective
                ? '<span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Autorisé</span>'
                : '<span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">Refusé</span>';
            var sourceLabel = (function (src) {
                switch (src) {
                    case 'domain':
                        return 'Via domaine';
                    case 'direct':
                        return 'Permission directe';
                    case 'override_disabled':
                        return 'Refus explicite';
                    case 'none':
                        return 'Aucun';
                    default:
                        return '—';
                }
            })(p.grant_source);

            html += '<div class="mb-3 last:mb-0 rounded-lg border border-gray-200 p-4">' +
                '<div class="flex items-center justify-between gap-3">' +
                '<div class="min-w-0">' +
                '<p class="text-sm font-semibold text-gray-900">' + esc(p.code) + '</p>' +
                '<p class="text-xs text-gray-500">' + esc(p.name) + '</p>' +
                (p.description ? '<p class="mt-0.5 text-xs text-gray-400">' + esc(p.description) + '</p>' : '') +
                '</div>' +
                '<div class="flex items-center gap-2.5">' +
                effBadge +
                permToggle(p, enabled) +
                '</div>' +
                '</div>' +
                '<dl class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">' +
                '<div><dt class="text-xs font-medium text-gray-500">Statut global</dt><dd class="mt-0.5">' + globalBadge + '</dd></div>' +
                '<div><dt class="text-xs font-medium text-gray-500">Permission</dt><dd class="mt-0.5">' + permBadge + '</dd></div>' +
                '<div><dt class="text-xs font-medium text-gray-500">Source d\'accès</dt><dd class="mt-0.5 text-xs font-medium text-gray-700">' +
                esc(sourceLabel) + '</dd></div>' +
                '</dl>' +
                '</div>';
        });
        body.innerHTML = html;
        updateRevokeButton();

        body.querySelectorAll('[data-perm-code]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                togglePermission(btn.getAttribute('data-perm-code'), btn.getAttribute('data-next') === 'true', btn);
            });
        });
    }

    function permToggle(p, enabled) {
        var code = esc(p.code);
        var next = !enabled;
        var isGlobalActive = p.global_is_active === true;
        // Enabling requires a globally active service; disabling is always allowed.
        var nextAllowed = !next || isGlobalActive;
        var busy = permissionToggleBusy[p.code] === true;
        var spin = '<span class="h-3 w-3 animate-spin rounded-full border-2 border-current opacity-60"></span> ';
        var classes = nextAllowed
            ? (next
                ? 'border-green-200 text-green-700 hover:bg-green-50'
                : 'border-red-200 text-red-700 hover:bg-red-50')
            : 'border-gray-200 text-gray-400 cursor-not-allowed';
        var title = nextAllowed ? '' : 'Ce Web Service est inactif : impossible de l\'activer.';
        var label = nextAllowed ? (next ? 'Activer' : 'Désactiver') : 'Verrouillé';

        return '<button type="button" data-perm-code="' + code + '" data-next="' + next + '" ' +
            (busy || !nextAllowed ? 'disabled ' : '') +
            'aria-busy="' + busy + '" aria-label="' + esc(label + ' ' + p.code) + '" title="' + esc(title) + '" ' +
            'class="inline-flex items-center gap-1 rounded-lg border bg-white px-2.5 py-1.5 text-xs font-medium transition-colors ' + classes + '">' +
            (busy ? spin : '') + esc(label) + '</button>';
    }

    function togglePermission(code, next, btn) {
        var idx = -1;
        for (var i = 0; i < allPermissions.length; i++) {
            if (allPermissions[i].code === code) { idx = i; break; }
        }
        if (idx === -1) return;

        var prev = {};
        Object.keys(allPermissions[idx]).forEach(function (k) {
            prev[k] = allPermissions[idx][k];
        });
        permissionToggleBusy[code] = true;
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');

        var api = window.apiClient;

        function done() {
            permissionToggleBusy[code] = false;
            renderPermissions();
        }

        if (!api) {
            delete permissionToggleBusy[code];
            showToast('Client API introuvable.', 'error');
            btn.disabled = false;
            btn.removeAttribute('aria-busy');
            return;
        }

        api.patch('/admin/users/' + userId + '/web-services/' + encodeURIComponent(code), { is_enabled: next })
            .then(function (res) {
                // The API wraps the payload: res.data = {success, message, data: {...}}.
                var body = res && res.data;
                var data = body && body.data;
                if (data && typeof data.is_enabled === 'boolean') {
                    // API response is the source of truth for this card.
                    allPermissions[idx].is_enabled = data.is_enabled === true;
                    allPermissions[idx].effective_access = data.effective_access === true;
                    if (typeof data.global_is_active === 'boolean') {
                        allPermissions[idx].global_is_active = data.global_is_active === true;
                    }
                } else {
                    // Malformed/empty payload: fall back to the requested state
                    // and keep effective access consistent so no field goes stale.
                    allPermissions[idx].is_enabled = next;
                    allPermissions[idx].effective_access = !!(next
                        && allPermissions[idx].global_is_active === true
                        && currentUser
                        && currentUser.is_active === true);
                }
                done();
                showToast(next ? 'Permission activée.' : 'Permission désactivée.');
            })
            .catch(function (err) {
                // Restore the previous switch state on any failure.
                allPermissions[idx] = prev;
                var status = err.status || (err.response ? err.response.status : null);
                var body = err.body || (err.response ? err.response.data : null);
                var msg = 'Une erreur est survenue. Veuillez réessayer.';
                if (status === 403) {
                    msg = 'Accès non autorisé.';
                } else if (status === 422 && body && body.message) {
                    msg = body.message;
                } else if (body && body.message) {
                    msg = body.message;
                }
                done();
                showToast(msg, 'error');
            });
    }

    function renderPermissionsEmpty() {
        var body = document.getElementById('permissions-body');
        body.classList.remove('hidden');
        body.innerHTML = '<p class="text-sm text-gray-500">Permission introuvable ou non autorisé.</p>';
        updateRevokeButton();
    }

    function renderPermissionsError() {
        var body = document.getElementById('permissions-body');
        body.classList.remove('hidden');
        body.innerHTML = '<p class="text-sm text-red-600">Impossible de charger les permissions.</p>';
        updateRevokeButton();
    }

    // ════════════════════════════════════════════
    //  REVOKE-ALL CONFIRMATION
    // ════════════════════════════════════════════
    var revokeModal = document.getElementById('revoke-modal');
    var revokeBackdrop = document.getElementById('revoke-modal-backdrop');
    var revokeMessage = document.getElementById('revoke-modal-message');
    var revokeError = document.getElementById('revoke-modal-error');
    var revokeBtn = document.getElementById('btn-confirm-revoke');
    var revokeBtnText = document.getElementById('btn-confirm-revoke-text');
    var revokeBtnSpinner = document.getElementById('btn-confirm-revoke-spinner');
    var revokeLastFocused = null;
    var revoking = false;

    function openRevoke() {
        var count = allPermissions.filter(function (p) {
            return p.is_enabled === true;
        }).length;
        if (!count) return;
        revokeLastFocused = document.activeElement;
        revokeMessage.textContent = 'Voulez-vous révoquer les ' + count +
            ' permission(s) Web Service accordée(s) à ' + (currentUser ? currentUser.name : 'cet utilisateur') + ' ?';
        revokeError.classList.add('hidden');
        revokeModal.classList.remove('hidden');
        revokeModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        revokeBtn.focus();
    }
    function closeRevoke(force) {
        if (revoking && !force) return;
        revokeModal.classList.add('hidden');
        revokeModal.classList.remove('flex');
        document.body.style.overflow = '';
        if (revokeLastFocused && revokeLastFocused.focus) revokeLastFocused.focus();
    }

    var revokeTrigger = document.getElementById('btn-revoke-perms');
    if (revokeTrigger) revokeTrigger.addEventListener('click', openRevoke);
    document.getElementById('revoke-modal-close').addEventListener('click', closeRevoke);
    document.getElementById('revoke-modal-cancel').addEventListener('click', closeRevoke);
    revokeBackdrop.addEventListener('click', closeRevoke);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !revokeModal.classList.contains('hidden')) closeRevoke();
    });

    revokeBtn.addEventListener('click', function () {
        if (revoking) return;
        var api = window.apiClient;
        if (!api) {
            revokeError.textContent = 'Client API introuvable.';
            revokeError.classList.remove('hidden');
            return;
        }

        revoking = true;
        revokeBtn.disabled = true;
        revokeBtnSpinner.classList.remove('hidden');
        revokeBtnText.textContent = 'Révocation en cours...';

        api.delete('/admin/users/' + userId + '/web-services')
            .then(function () {
                allPermissions = allPermissions.map(function (p) {
                    return {
                        code: p.code,
                        name: p.name,
                        description: p.description,
                        global_is_active: p.global_is_active,
                        is_enabled: false,
                        effective_access: false
                    };
                });
                revoking = false;
                revokeBtn.disabled = false;
                revokeBtnSpinner.classList.add('hidden');
                revokeBtnText.textContent = 'Révoquer';
                closeRevoke(true);
                renderPermissions();
                showToast('Toutes les permissions ont été révoquées.');
            })
            .catch(function (err) {
                revoking = false;
                revokeBtn.disabled = false;
                revokeBtnSpinner.classList.add('hidden');
                revokeBtnText.textContent = 'Révoquer';
                var status = err.status || (err.response ? err.response.status : null);
                var body = err.body || (err.response ? err.response.data : null);
                var msg = 'Une erreur est survenue. Veuillez réessayer.';
                if (status === 403) {
                    msg = 'Accès non autorisé.';
                } else if (body && body.message) {
                    msg = body.message;
                }
                revokeError.textContent = msg;
                revokeError.classList.remove('hidden');
            });
    });

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

})();
</script>
@endsection
