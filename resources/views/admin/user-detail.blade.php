@extends('layouts.metronic')

@section('title', 'Détails utilisateur')

@section('content')
<div id="admin-user-detail-root" class="hidden">
    <div class="d-flex align-items-center justify-content-between gap-4 mb-7">
        <div>
            <h2 class="fw-bold text-dark fs-3 mb-1">Détails utilisateur</h2>
            <p class="text-gray-600 fw-semibold fs-6 mb-0">
                Voir les informations et accès de l'utilisateur.
            </p>
        </div>
        <a href="/admin/users" 
           class="btn btn-light-primary fw-semibold">
            <span class="svg-icon svg-icon-2">
                <span class="ki-outline ki-arrow-back"></span>
            </span>
            <span class="btn-text ms-2">Retour</span>
        </a>
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
        <p class="text-sm font-medium text-red-800">Impossible de charger les données utilisateur.</p>
        <button id="btn-retry" type="button"
                class="mt-3 btn btn-primary">
            Réessayer
        </button>
    </div>

    {{-- "?" "?" USER DETAILS LOADING STATE -- --}}
    <div id="user-loading" class="mt-6 hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="d-flex align-items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-gray-200 animate-pulse"></div>
            <div class="flex-1">
                <div class="h-4 w-32 bg-gray-200 animate-pulse rounded mb-2"></div>
                <div class="h-3 w-16 bg-gray-200 animate-pulse rounded"></div>
            </div>
        </div>
        
        <div class="mt-6 row g-4">
            <div class="col-6"><div class="h-4 w-100 bg-gray-200 animate-pulse rounded mb-4"></div></div>
            <div class="col-6"><div class="h-4 w-100 bg-gray-200 animate-pulse rounded mb-4"></div></div>
        </div>

        <div class="mt-6">
            <div class="h-8 w-16 bg-gray-200 animate-pulse rounded mb-4"></div>
            <div class="space-y-3">
                <div class="h-3 w-100 bg-gray-200 animate-pulse rounded"></div>
                <div class="h-3 w-80 bg-gray-200 animate-pulse rounded"></div>
                <div class="h-3 w-60 bg-gray-200 animate-pulse rounded"></div>
            </div>
        </div>
    </div>

    {{-- "?" "?" USER DETAILS -- --}}
    <div id="user-detail" class="mt-6 hidden">
        <div class="card card-flush">
            <div class="card-body pt-5 pb-0">
                <div class="d-flex align-items-center gap-5 mb-7">
                    <div class="flex-shrink-0">
                        <span id="user-initials" class="symbol symbol-80px bg-light-primary">
                            <span class="symbol-label fs-2 fw-bolder text-primary"></span>
                        </span>
                    </div>
                    <div>
                        <h3 id="user-name" class="fw-bolder text-dark fs-3 mb-1">Nom complet</h3>
                        <p id="user-email" class="text-gray-500 fw-semibold fs-6 mb-0">Email</p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card-body pt-0">
                <ul class="nav nav-line nav-line-tabs nav-line-tabs-2x border-gray-300 mb-5" id="user-detail-tabs" role="tablist">
                    <li class="nav-item me-5" role="presentation">
                        <button class="nav-link active" id="tab-overview" data-bs-toggle="tab" data-bs-target="#tab-overview-content" type="button" role="tab" aria-selected="true">
                            <span class="nav-link-title fs-6 fw-bold">Vue d'ensemble</span>
                        </button>
                    </li>
                    <li class="nav-item me-5" role="presentation">
                        <button class="nav-link" id="tab-access" data-bs-toggle="tab" data-bs-target="#tab-access-content" type="button" role="tab" aria-selected="false">
                            <span class="nav-link-title fs-6 fw-bold">Accès</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-activity" data-bs-toggle="tab" data-bs-target="#tab-activity-content" type="button" role="tab" aria-selected="false">
                            <span class="nav-link-title fs-6 fw-bold">Activité</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="user-detail-tab-content">
                    <!-- Overview tab content -->
                    <div class="tab-pane fade show active" id="tab-overview-content" role="tabpanel" aria-labelledby="tab-overview">
                        <div class="row g-4 mb-7">
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-900 fs-7 text-uppercase">Nom complet</div>
                                <div id="detail-name" class="fw-bold text-dark fs-6"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-900 fs-7 text-uppercase">Email</div>
                                <div id="detail-email" class="fw-bold text-dark fs-6"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-900 fs-7 text-uppercase">Rôle</div>
                                <div id="detail-role" class="fw-bold text-dark fs-6"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-bold text-gray-900 fs-7 text-uppercase">Statut</div>
                                <div id="detail-status" class="fw-bold text-dark fs-6"></div>
                            </div>
                        </div>

                        <div class="border-top border-gray-200 pt-5 mb-5">
                            <h4 class="fw-bold text-dark fs-6 mb-4">Détails du compte</h4>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="fw-bold text-gray-500 fs-7 text-uppercase">Date de création</div>
                                    <div id="detail-created-at" class="fw-bold text-dark fs-6"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-bold text-gray-500 fs-7 text-uppercase">Dernière modification</div>
                                    <div id="detail-updated-at" class="fw-bold text-dark fs-6"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-5 bg-light">
                            <h4 class="fw-bold text-dark fs-6 mb-3">Actions rapides</h4>
                            <p class="text-gray-500 fw-semibold fs-7 mb-4">Vous pouvez modifier le statut ou réinitialiser le mot de passe.</p>
                            <div class="d-flex flex-wrap gap-3">
                                <button id="toggle-status-btn" 
                                        class="btn btn-warning fw-semibold">
                                    <span class="svg-icon svg-icon-2 me-2">
                                        <span class="ki-outline ki-rotate-cw"></span>
                                    </span>
                                    <span id="toggle-status-text">Désactiver le compte</span>
                                </button>
                                <button id="reset-password-btn" 
                                        class="btn btn-info fw-semibold">
                                    <span class="svg-icon svg-icon-2 me-2">
                                        <span class="ki-outline ki-key"></span>
                                    </span>
                                    Réinitialiser le mot de passe
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Access tab content -->
                    <div class="tab-pane fade" id="tab-access-content" role="tabpanel" aria-labelledby="tab-access">
                        <h4 class="fw-bold text-dark fs-6 mb-4">Accès aux services web</h4>
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-100 gs-0">
                                <thead class="fs-7 fw-bold text-uppercase text-gray-600">
                                    <tr>
                                        <th scope="col" class="px-6 py-4">Service</th>
                                        <th scope="col" class="px-6 py-4">Statut</th>
                                        <th scope="col" class="px-6 py-4">Accès effectif</th>
                                    </tr>
                                </thead>
                                <tbody id="web-services-tbody" class="fs-6 fw-bold text-gray-800">
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">Aucun service disponible</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Activity tab content -->
                    <div class="tab-pane fade" id="tab-activity-content" role="tabpanel" aria-labelledby="tab-activity">
                        <h4 class="fw-bold text-dark fs-6 mb-4">Dernière activité</h4>
                        <div class="text-center py-10">
                            <span class="symbol symbol-60px bg-light-secondary mb-4">
                                <span class="svg-icon svg-icon-3x text-gray-400">
                                    <span class="ki-outline ki-clock"></span>
                                </span>
                            </span>
                            <p class="text-gray-600 fw-semibold fs-6 mb-1">Aucune activité disponible.</p>
                            <p class="text-gray-500 fs-7 mb-0">Le journal d'audit sera bientôt disponible.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- "?" "?" SUCCESS NOTIFICATION -- --}}
    <div id="success-toast" class="fixed top-5 end-5 z-[60] hidden max-w-sm rounded-lg border border-green-200 bg-green-50 p-4 shadow-sm"
         role="status" aria-live="polite">
        <div class="flex items-start gap-2">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M10 1.944a.75.75 0 0 1 .75.75v6.5a.75.75 0 0 1-.44.68l-2.25 1.25.44-.68V2.694a.75.75 0 0 1 .75-.75Zm0 16.112a.75.75 0 0 1-.75.75H2.75a.75.75 0 0 1 0-1.5H9.25a.75.75 0 0 1 .75.75Z"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M10 2a8 8 0 1 0 0 16A8 8 0 0 0 10 2Zm.75 9.25a.75.75 0 0 0-1.5 0V11.5a.75.75 0 0 0 1.5 0Z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-green-800" id="success-toast-msg">Opération effectuée avec succès.</p>
            </div>
        </div>
    </div>

    {{-- "?" "?" CONFIRMATION MODAL -- --}}
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
        <div id="confirm-backdrop" class="absolute inset-0 bg-black/40"></div>
        <div class="relative z-10 w-full max-w-md rounded-xl border border-gray-200 bg-white shadow-lg m-4">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h3 id="confirm-modal-title" class="fw-bold text-dark fs-5">Confirmer l'action</h3>
                <button type="button" id="confirm-close" class="btn btn-icon btn-active-light-primary" aria-label="Fermer">
                    <span class="svg-icon svg-icon-2">
                        <span class="ki-outline ki-close"></span>
                    </span>
                </button>
            </div>
            <div class="p-5">
                <p id="confirm-message" class="text-gray-700 fw-semibold fs-6"></p>
                <div class="mt-6 d-flex justify-content-end gap-3">
                    <button type="button" id="confirm-cancel" class="btn btn-light-secondary fw-semibold">Annuler</button>
                    <button type="button" id="confirm-ok" class="btn btn-primary fw-semibold">Confirmer</button>
                </div>
            </div>
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

    const userId = window.location.pathname.split('/').pop();

    function get(url) {
        return fetch(url, {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
    }

    function patch(url, data) {
        return fetch(url, {
            method: 'PATCH',
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
    }

    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
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

    // Elements
    var rootEl = document.getElementById('admin-user-detail-root');
    var authLoading = document.getElementById('auth-loading');
    var accessDenied = document.getElementById('access-denied');
    var loadError = document.getElementById('load-error');
    var userLoading = document.getElementById('user-loading');
    var userDetails = document.getElementById('user-detail');

    // Navigation tabs elements
    var tabOverview = document.getElementById('tab-overview');
    var tabAccess = document.getElementById('tab-access');
    var tabActivity = document.getElementById('tab-activity');
    
    var tabOverviewContent = document.getElementById('tab-overview-content');
    var tabAccessContent = document.getElementById('tab-access-content');
    var tabActivityContent = document.getElementById('tab-activity-content');

    // Action buttons
    var toggleStatusBtn = document.getElementById('toggle-status-btn');
    var resetPasswordBtn = document.getElementById('reset-password-btn');
    var toggleStatusText = document.getElementById('toggle-status-text');
    
    // Modal elements  
    var confirmModal = document.getElementById('confirm-modal');
    var confirmBackdrop = document.getElementById('confirm-backdrop');
    var confirmClose = document.getElementById('confirm-close');
    var confirmCancel = document.getElementById('confirm-cancel');
    var confirmOk = document.getElementById('confirm-ok');
    var confirmMessage = document.getElementById('confirm-message');

    // Success toast
    var successToast = document.getElementById('success-toast');
    var successToastMsg = document.getElementById('success-toast-msg');

    function showAdminRoot() {
        authLoading.classList.add('hidden');
        accessDenied.classList.add('hidden');
        rootEl.classList.remove('hidden');
    }

    function showAccessDenied() {
        accessDenied.classList.remove('hidden');
        authLoading.classList.add('hidden');
        rootEl.classList.add('hidden');
    }

    function showLoadError() {
        loadError.classList.remove('hidden');
        authLoading.classList.add('hidden');
        rootEl.classList.add('hidden');
    }

    function showUserLoading() {
        userLoading.classList.remove('hidden');
        userDetails.classList.add('hidden');
    }

    function hideUserLoading() {
        userLoading.classList.add('hidden');
        userDetails.classList.remove('hidden');
    }

    function loadUserData() {
        showUserLoading();
        
        get('/api/v1/admin/users/' + userId)
            .then(function (res) {
                if (res.status === 403) { 
                    showAccessDenied(); 
                    return; 
                }
                if (res.status === 401) {
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                    window.location.href = '/login';
                    return;
                }
                if (!res.ok) { 
                    showLoadError(); 
                    return; 
                }
                return res.json().then(function (data) {
                    hideUserLoading();
                    if (!data.success || !data.data) {
                        showLoadError();
                        return;
                    }
                    
                    var user = data.data;
                    displayUserData(user);
                    loadWebServices();
                });
            })
            .catch(function () {
                hideUserLoading();
                showLoadError();
            });
    }

    function loadWebServices() {
        get('/api/v1/admin/users/' + userId + '/web-services')
            .then(function (res) {
                if (!res.ok) return;
                return res.json().then(function (data) {
                    if (!data.success || !Array.isArray(data.data)) return;
                    
                    var tbody = document.getElementById('web-services-tbody');
                    var rows = '';
                    
                    if (data.data.length === 0) {
                        rows = '<tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">Aucun service assigné</td></tr>';
                    } else {
                        data.data.forEach(function(service) {
                            var status = service.effective_access === false ? 
                                '<span class="badge badge-light-danger fw-semibold">Refusé</span>' : 
                                '<span class="badge badge-light-success fw-semibold">Accordé</span>';
                            
                            var effective = service.effective_access ? 
                                '<span class="badge badge-light-info fw-semibold">Oui</span>' : 
                                '<span class="badge badge-light-secondary fw-semibold">Non</span>';
                                
                            rows += '<tr>' +
                                '<td class="px-6 py-4 text-gray-800">' + esc(service.name) + '</td>' +
                                '<td class="px-6 py-4 text-gray-600">' + status + '</td>' +
                                '<td class="px-6 py-4 text-gray-600">' + effective + '</td>' +
                                '</tr>';
                        });
                    }
                    
                    tbody.innerHTML = rows;
                });
            })
            .catch(function () {
                // Handle error
            });
    }

    function displayUserData(user) {
        // Populate basic user info
        document.getElementById('user-name').textContent = user.name || '';
        document.getElementById('user-email').textContent = user.email || '';
        document.getElementById('detail-name').textContent = user.name || '';
        document.getElementById('detail-email').textContent = user.email || '';
        
        // Display role with styling
        const roleEl = document.getElementById('detail-role');
        roleEl.innerHTML = roleBadge(user.role || '');
        
        // Display status with styling
        const statusEl = document.getElementById('detail-status');
        statusEl.innerHTML = statusBadge(user.is_active);
        
        // Display dates
        document.getElementById('detail-created-at').textContent = user.created_at ? new Date(user.created_at).toLocaleDateString('fr-FR') : '';
        document.getElementById('detail-updated-at').textContent = user.updated_at ? new Date(user.updated_at).toLocaleDateString('fr-FR') : '';
        
        
        // Display initials
        const initials = (user.name || '').split(' ').map(n => n[0]).join('').substring(0, 2);
        document.getElementById('user-initials').textContent = initials.toUpperCase();
        
        // Set the action button text based on user status
        if (toggleStatusText) {
            toggleStatusText.textContent = user.is_active ? 'Désactiver le compte' : 'Activer le compte';
        }
        
        hideUserLoading();
        userDetails.classList.remove('hidden');
    }

    function showSuccess(msg) {
        successToastMsg.textContent = msg;
        successToast.classList.remove('hidden');
        setTimeout(function () {
            successToast.classList.add('hidden');
        }, 4000);
    }

    function showConfirm(message, callback, okBtnText = 'Confirmer') {
        confirmMessage.textContent = message;
        confirmModal.classList.remove('hidden');
        confirmModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        
        confirmOk.textContent = okBtnText;
        
        function handleOk() {
            callback();
            closeModal();
        }
        
        function handleClose() {
            closeModal();
        }
        
        confirmOk.onclick = handleOk;
        confirmCancel.onclick = handleClose;
        confirmClose.onclick = handleClose;
        confirmBackdrop.onclick = handleClose;
        
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !confirmModal.classList.contains('hidden')) {
                handleClose();
            }
        });
    }

    function closeModal() {
        confirmModal.classList.add('hidden');
        confirmModal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function toggleUserStatus() {
        const newStatus = !window.currentUser.is_active;
        showConfirm('Êtes-vous sûr de vouloir ' + (newStatus ? 'activer' : 'désactiver') + ' ce compte ?', 
            function() {
                patch('/api/v1/admin/users/' + userId + '/status', { is_active: newStatus })
                    .then(function(res) {
                        if (res.ok) {
                            window.currentUser.is_active = newStatus;
                            loadUserData();
                            showSuccess('Statut du compte mis à jour.');
                        } else {
                            return res.json().then(function(data) {
                                throw new Error(data.message || 'Erreur lors de la mise à jour');
                            });
                        }
                    })
                    .catch(function(err) {
                        console.error('Error updating status:', err);
                        showLoadError();
                    });
            },
            newStatus ? 'Activer le compte' : 'Désactiver le compte'
        );
    }

    function resetUserPassword() {
        showConfirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?', 
            function() {
                post('/api/v1/admin/users/' + userId + '/password')
                    .then(function(res) {
                        if (res.ok) {
                            showSuccess('Mot de passe réinitialisé. Un email va être envoyé à l\'utilisateur.');
                        } else {
                            return res.json().then(function(data) {
                                throw new Error(data.message || 'Erreur lors de la réinitialisation');
                            });
                        }
                    })
                    .catch(function(err) {
                        console.error('Error resetting password:', err);
                        showLoadError();
                    });
            },
            'Réinitialiser le mot de passe'
        );
    }

    // Initialize tabs
    function initTabs() {
        // Use Bootstrap tab events if available, otherwise manual switching
        if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            var tabs = document.querySelectorAll('#user-detail-tabs .nav-link');
            tabs.forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(e) {
                    // Tab shown
                });
            });
        } else {
            // Manual tab switching fallback
            tabOverview.addEventListener('click', function(e) {
                e.preventDefault();
                switchTabManually(tabOverview, tabOverviewContent);
            });
            tabAccess.addEventListener('click', function(e) {
                e.preventDefault();
                switchTabManually(tabAccess, tabAccessContent);
            });
            tabActivity.addEventListener('click', function(e) {
                e.preventDefault();
                switchTabManually(tabActivity, tabActivityContent);
            });
        }
    }

    function switchTabManually(activeTab, targetContent) {
        // Hide all tab content
        tabOverviewContent.classList.remove('show', 'active');
        tabAccessContent.classList.remove('show', 'active');
        tabActivityContent.classList.remove('show', 'active');
        
        // Remove active state from all tabs
        tabOverview.classList.remove('active');
        tabAccess.classList.remove('active');
        tabActivity.classList.remove('active');
        
        // Show target content and set active tab
        targetContent.classList.add('show', 'active');
        activeTab.classList.add('active');
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
                showLoadError();
                return res.json().then(function (d) { 
                    console.error('Auth error:', d);
                });
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
                    authLoading.classList.add('hidden');
                    showAccessDenied();
                    setTimeout(function () { window.location.replace('/dashboard'); }, 1200);
                    return;
                }
                
                window.currentUser = user;
                showAdminRoot();
                loadUserData();
                initTabs();
            });
        })
        .catch(function () {
            authLoading.classList.add('hidden');
            showLoadError();
        });

    // Buttons handling
    if (toggleStatusBtn) toggleStatusBtn.addEventListener('click', toggleUserStatus);
    if (resetPasswordBtn) resetPasswordBtn.addEventListener('click', resetUserPassword);

    // Retry
    var retry = document.getElementById('btn-retry');
    if (retry) {
        retry.addEventListener('click', function () {
            loadUserData();
        });
    }
})();
</script>
@endsection