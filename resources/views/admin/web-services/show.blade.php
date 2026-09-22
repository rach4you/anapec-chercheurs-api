@extends('layouts.app')

@section('title', 'Web Service')

@section('content')
<div id="admin-ws-show-root" class="hidden">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Web Service</h2>
            <p class="mt-0.5 text-sm text-gray-500">
                Détails du service et de son accès global.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.web-services') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M12.79 5.23a.75.75 0 0 1-.09 1.04L8.832 9l3.868 2.73a.75.75 0 1 1-.95 1.148l-4.5-3.15a.75.75 0 0 1 0-1.248l4.5-3.15a.75.75 0 0 1 1.04.09Z"/>
                </svg>
                Retour aux Web Services
            </a>
            <button type="button" id="btn-edit-ws"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M13.586 3.586a2 2 0 1 1 2.828 2.828l-.793.793-2.828-2.828.793-.793ZM11.379 5.793 3 14.172V17h2.828l8.38-8.379-2.83-2.828Z"/>
                </svg>
                Modifier
            </button>
            <button type="button" id="btn-toggle-ws"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2">
                <span id="btn-toggle-ws-text">Désactiver</span>
            </button>
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
    <p class="mt-1 text-sm text-gray-600">
        Vous devez être administrateur pour accéder à cette page.
    </p>
</div>

{{-- ── 404 / NOT FOUND ── --}}
<div id="not-found" class="mt-6 hidden rounded-lg border border-gray-200 bg-white p-6 text-center shadow-sm">
    <p class="text-sm font-medium text-gray-800">Web Service introuvable.</p>
    <p class="mt-1 text-sm text-gray-500">Ce service n'existe pas ou a été retiré.</p>
    <a href="{{ route('admin.web-services') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700">
        Retour aux Web Services
    </a>
</div>

{{-- ── LOAD ERROR + RETRY ── --}}
<div id="load-error" class="mt-6 hidden rounded-lg border border-red-200 bg-red-50 p-6 text-center" role="alert">
    <p class="text-sm font-medium text-red-800">Impossible de charger ce Web Service.</p>
    <button id="btn-retry" type="button"
            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-anapec-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-anapec-700">
        Réessayer
    </button>
</div>

{{-- ── DETAILS CARD ── --}}
<div id="details-card" class="mt-6 hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-gray-900">Informations du service</h3>
    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
        <div>
            <dt class="text-xs font-medium text-gray-500">Code</dt>
            <dd id="detail-code" class="mt-1 font-mono text-sm font-medium text-gray-900">—</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Nom</dt>
            <dd id="detail-name" class="mt-1 text-sm font-medium text-gray-900">—</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-xs font-medium text-gray-500">Description</dt>
            <dd id="detail-description" class="mt-1 text-sm text-gray-700">—</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Statut global</dt>
            <dd id="detail-status" class="mt-1">—</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Créé le</dt>
            <dd id="detail-created" class="mt-1 text-sm text-gray-700">—</dd>
        </div>
        <div>
            <dt class="text-xs font-medium text-gray-500">Modifié le</dt>
            <dd id="detail-updated" class="mt-1 text-sm text-gray-700">—</dd>
        </div>
    </dl>
</div>

{{-- ── TOAST ── --}}
<div id="toast" class="fixed right-4 top-4 z-[60] hidden max-w-sm rounded-lg border px-4 py-3 shadow-sm"
     role="status" aria-live="polite">
    <p id="toast-msg" class="text-sm font-medium"></p>
</div>

<script>
(function () {
    var token = localStorage.getItem('anapec_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    // ── Parse the code from /admin/web-services/{code} ──
    var m = window.location.pathname.match(/\/admin\/web-services\/([A-Za-z0-9_]+)$/);
    var code = m ? m[1] : null;

    function get(url) {
        return fetch(url, {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '—';
        return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function statusBadge(active) {
        if (active) {
            return '<span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">Actif</span>';
        }
        return '<span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">Inactif</span>';
    }

    function showToast(msg, type) {
        var t = document.getElementById('toast');
        var msgEl = document.getElementById('toast-msg');
        msgEl.textContent = msg;
        t.className = 'fixed right-4 top-4 z-[60] max-w-sm rounded-lg border px-4 py-3 shadow-sm ' +
            (type === 'error' ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50');
        msgEl.className = type === 'error' ? 'text-sm font-medium text-red-800' : 'text-sm font-medium text-green-800';
        t.classList.remove('hidden');
        clearTimeout(t._timer);
        t._timer = setTimeout(function () { t.classList.add('hidden'); }, 4000);
    }

    var root = document.getElementById('admin-ws-show-root');
    var detailsCard = document.getElementById('details-card');
    var authLoading = document.getElementById('auth-loading');
    var accessDenied = document.getElementById('access-denied');
    var notFound = document.getElementById('not-found');
    var loadError = document.getElementById('load-error');
    var toggleBtn = document.getElementById('btn-toggle-ws');
    var toggleBtnText = document.getElementById('btn-toggle-ws-text');

    var currentService = null;

    function showOnly(id) {
        [authLoading, accessDenied, notFound, loadError, detailsCard].forEach(function (el) {
            el.classList.add('hidden');
        });
        if (id === 'auth-loading') authLoading.classList.remove('hidden');
        else if (id === 'access-denied') accessDenied.classList.remove('hidden');
        else if (id === 'not-found') notFound.classList.remove('hidden');
        else if (id === 'load-error') loadError.classList.remove('hidden');
        else if (id === 'details') detailsCard.classList.remove('hidden');
    }

    function updateToggleButton() {
        var active = currentService.is_active === true;
        toggleBtnText.textContent = active ? 'Désactiver' : 'Activer';
        toggleBtn.classList.toggle('border-red-200', active);
        toggleBtn.classList.toggle('text-red-700', active);
        toggleBtn.classList.toggle('border-green-200', !active);
        toggleBtn.classList.toggle('text-green-700', !active);
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
                    // Non-admin: do NOT call the admin endpoint, do NOT log out.
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

    function renderDetails() {
        document.getElementById('detail-code').textContent = currentService.code || '—';
        document.getElementById('detail-name').textContent = currentService.name || '—';
        document.getElementById('detail-description').textContent = currentService.description || '—';
        document.getElementById('detail-status').innerHTML = statusBadge(currentService.is_active === true);
        document.getElementById('detail-created').textContent = fmtDate(currentService.created_at);
        document.getElementById('detail-updated').textContent = fmtDate(currentService.updated_at);
        root.classList.remove('hidden');
        updateToggleButton();
        showOnly('details');
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
            if (!currentService) return;
            var nextActive = currentService.is_active !== true;
            var api = window.apiClient;
            if (!api) {
                showToast('Client API introuvable.', 'error');
                return;
            }
            toggleBtn.disabled = true;
            api.patch('/admin/web-services/' + encodeURIComponent(currentService.code) + '/status', { is_active: nextActive })
                .then(function () {
                    currentService.is_active = nextActive;
                    updateToggleButton();
                    document.getElementById('detail-status').innerHTML = statusBadge(nextActive);
                    document.getElementById('detail-updated').textContent = '—';
                    showToast(nextActive ? 'Web Service activé.' : 'Web Service désactivé.', 'success');
                })
                .catch(function (err) {
                    toggleBtn.disabled = false;
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
@endsection
