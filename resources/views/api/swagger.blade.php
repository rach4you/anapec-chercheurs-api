<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANAPEC Chercheurs API — Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.18.0/swagger-ui.css">
    <style>
        body { margin: 0; padding: 0; font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; }
        #swagger-ui { max-width: 100%; }
        .page-state {
            display: none;
            box-sizing: border-box;
            min-height: 100vh;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 2rem;
            text-align: center;
            color: #1f2937;
        }
        .page-state .icon { width: 2.5rem; height: 2.5rem; color: #6b7280; }
        .page-state h1 { font-size: 1.25rem; font-weight: 600; margin: 0; }
        .page-state p { font-size: 0.875rem; color: #6b7280; margin: 0; }
        .page-state a {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: #2563eb;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
        }
        .page-state a:hover { background: #1d4ed8; }
        .spinner {
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div id="page-state" class="page-state">
    <div id="state-loading">
        <span class="spinner" aria-hidden="true"></span>
        <p>Chargement de la documentation…</p>
    </div>
    <div id="state-empty" style="display:none;">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v8.5M5 7.5h14M5 19h14M5 7.5v11.5M5 16h14"/>
        </svg>
        <h1>Aucun Web Service accessible.</h1>
        <p>Votre compte ne dispose actuellement d'aucun accès effectif à un Web Service.</p>
    </div>
    <div id="state-error" style="display:none;">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M12 2.25a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5Z"/>
        </svg>
        <h1 id="state-error-title">Impossible de charger la documentation.</h1>
        <p id="state-error-msg"></p>
        <a href="/login" id="state-error-action" style="display:none;">Se connecter</a>
    </div>
</div>
<div id="swagger-ui" style="display:none;"></div>
<script src="https://unpkg.com/swagger-ui-dist@5.18.0/swagger-ui-bundle.js"></script>
<script>
    (function () {
        var SPEC_URL = '{{ url('api/documentation/openapi') }}';
        var PERMS_URL = '/api/v1/user/web-services';
        var ME_URL = '/api/v1/auth/me';

        var stateRoot = document.getElementById('page-state');
        var elLoading = document.getElementById('state-loading');
        var elEmpty = document.getElementById('state-empty');
        var elError = document.getElementById('state-error');
        var elErrorTitle = document.getElementById('state-error-title');
        var elErrorMsg = document.getElementById('state-error-msg');
        var elErrorAction = document.getElementById('state-error-action');
        var uiEl = document.getElementById('swagger-ui');

        function showState(el) {
            stateRoot.style.display = 'flex';
            [elLoading, elEmpty, elError].forEach(function (n) { n.style.display = 'none'; });
            el.style.display = '';
        }

        function showError(title, msg, actionUrl) {
            elErrorTitle.textContent = title;
            elErrorMsg.textContent = msg || '';
            elErrorMsg.style.display = msg ? '' : 'none';
            if (actionUrl) {
                elErrorAction.href = actionUrl;
                elErrorAction.style.display = '';
            } else {
                elErrorAction.style.display = 'none';
            }
            showState(elError);
        }

        function redirectToLogin() {
            window.location.replace('/login');
        }

        function hideStateAndShowUi() {
            stateRoot.style.display = 'none';
            uiEl.style.display = '';
        }

        var token = localStorage.getItem('anapec_token');
        if (!token) {
            redirectToLogin();
            return;
        }

        function authorizedGet(url) {
            return fetch(url, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });
        }

        // Data-driven mapping: Web Service code -> the set of documented
        // "Services" operations (paths) that document that code.
        //
        // This mirrors the `ws:{CODE}` middleware bindings in
        // routes/api.php and the official Web Service catalogue. A user's
        // documentation is the union of the operations for every code they
        // have effective_access on; operations for codes they do NOT have
        // access to are removed. Codes with no documented operation yet
        // (WS_PROFILE, WS_CV, WS_BILAN) contribute nothing today, which is
        // correct — there is simply no documented endpoint to hide or show.
        //
        // Authentication paths are generic platform docs and are always kept.
        // For non-admins /admin/* and /user/* paths are stripped (see the
        // filter below).
        var SERVICE_OPERATIONS = {
            'WS_CHECK_CIN': ['/services/check-cin']
        };

        function collectServicePaths(allowed) {
            var seen = {};
            allowed.forEach(function (code) {
                var paths = SERVICE_OPERATIONS[code];
                if (!paths) return;
                paths.forEach(function (p) { seen[p] = true; });
            });
            return Object.keys(seen);
        }

        var pendingError = null;

        Promise.all([
            fetch(SPEC_URL, { headers: { 'Accept': 'application/json' } }),
            authorizedGet(PERMS_URL),
            authorizedGet(ME_URL)
        ])
            .then(function (responses) {
                var specRes = responses[0];
                var permsRes = responses[1];
                var meRes = responses[2];

                if (specRes.status === 401 || specRes.status === 403) {
                    redirectToLogin();
                    return;
                }
                if (!specRes.ok) {
                    throw new Error('Impossible de charger la spécification OpenAPI.');
                }

                if (permsRes.status === 401 || permsRes.status === 403) {
                    pendingError = { code: 'auth', status: permsRes.status };
                    redirectToLogin();
                    return;
                }
                if (!permsRes.ok) {
                    throw new Error('Impossible de charger vos permissions Web Service.');
                }

                // Role source for the /admin/* filter. /auth/me is authoritative
                // because it is served by the same Sanctum token that guards the
                // spec and perms calls; anapec_user is the login-side cache and
                // only serves as a fallback if the profile payload lacks role.
                if (meRes.status === 401 || meRes.status === 403) {
                    pendingError = { code: 'auth', status: meRes.status };
                    redirectToLogin();
                    return;
                }
                if (!meRes.ok) {
                    throw new Error('Impossible de charger votre profil utilisateur.');
                }

                return Promise.all([specRes.json(), permsRes.json(), meRes.json()]);
            })
            .then(function (payloads) {
                if (!payloads) return;
                var spec = payloads[0];
                var perms = payloads[1];
                var me = payloads[2];
                if (!spec || !spec.paths) {
                    throw new Error('Spécification OpenAPI invalide.');
                }
                if (!perms || !perms.success || !Array.isArray(perms.data)) {
                    throw new Error('Réponse de permissions invalide.');
                }

                var meData = me && me.data;
                var storedRole = null;
                try { storedRole = JSON.parse(localStorage.getItem('anapec_user')).role; } catch (e) {}
                var role = (meData && typeof meData.role === 'string' && meData.role) || storedRole || '';
                var isAdmin = role === 'admin';

                var allowed = perms.data
                    .filter(function (p) { return p.effective_access === true; })
                    .map(function (p) { return p.code; });

                var keepPaths = collectServicePaths(allowed);

                // Zero effective Web Services -> empty state, no UI mount.
                if (allowed.length === 0) {
                    showState(elEmpty);
                    return;
                }

                // Keep only the documented /services/* operations that belong
                // to Web Services the user has effective access to; drop every
                // documented /services/* path the user does not have.
                // Non-admins additionally lose every /admin/* and /user/* path;
                // Authentication paths always stay.
                var filtered = JSON.parse(JSON.stringify(spec));
                Object.keys(filtered.paths).forEach(function (key) {
                    if (key.indexOf('/services/') !== 0) return;
                    if (keepPaths.indexOf(key) === -1) {
                        delete filtered.paths[key];
                    }
                });

                if (!isAdmin) {
                    Object.keys(filtered.paths).forEach(function (key) {
                        if (key.indexOf('/admin/') === 0 || key.indexOf('/user/') === 0) {
                            delete filtered.paths[key];
                        }
                    });

                    // Drop the now-empty Admin/User tag metadata so Swagger UI
                    // does not render orphan section headers.
                    if (Array.isArray(filtered.tags)) {
                        filtered.tags = filtered.tags.filter(function (tag) {
                            var name = tag && typeof tag.name === 'string' ? tag.name : '';
                            return name.indexOf('Admin') !== 0 && name !== 'User';
                        });
                    }
                }

                // Remove schemas that the surviving operations no longer
                // reference. Reachability follows the real $ref graph
                // (paths -> responses -> schemas, transitively), so no
                // hardcoded schema list is needed.
                if (!isAdmin && filtered.components
                        && typeof filtered.components.schemas === 'object'
                        && filtered.components.schemas !== null) {
                    var schemas = filtered.components.schemas;
                    var responses = filtered.components.responses || {};
                    var keepSchemas = {};
                    var seenResponses = {};
                    var schemaQueue = [];

                    var walkNode = function (node) {
                        if (!node || typeof node !== 'object') return;
                        for (var key in node) {
                            if (!Object.prototype.hasOwnProperty.call(node, key)) continue;
                            var value = node[key];
                            if (key !== '$ref' || typeof value !== 'string'
                                    || value.charAt(0) !== '#') {
                                walkNode(value);
                                continue;
                            }
                            var parts = value.split('/');
                            var name = parts[parts.length - 1];
                            try { name = decodeURIComponent(name); } catch (e) {}
                            if (parts[1] !== 'components') continue;
                            if (parts[2] === 'schemas') {
                                if (!keepSchemas[name]) {
                                    keepSchemas[name] = true;
                                    schemaQueue.push(name);
                                }
                            } else if (parts[2] === 'responses' && !seenResponses[name]) {
                                seenResponses[name] = true;
                                walkNode(responses[name]);
                            }
                        }
                    };

                    walkNode(filtered.paths);
                    while (schemaQueue.length > 0) {
                        walkNode(schemas[schemaQueue.shift()]);
                    }

                    Object.keys(schemas).forEach(function (name) {
                        if (!keepSchemas[name]) delete schemas[name];
                    });
                }

                var uiConfig = {
                    spec: filtered,
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    tagsSorter: 'alpha',
                    operationsSorter: 'method',
                };

                if (!isAdmin) {
                    // Presentation only: non-admins must not see the
                    // Schemas/Models section. The User schema legitimately
                    // stays in the spec because POST /auth/login and
                    // GET /auth/me reference it. In swagger-ui this value
                    // below zero makes the Models component render null, so
                    // the whole section — heading included — is not shown.
                    uiConfig.defaultModelsExpandDepth = -1;
                }

                hideStateAndShowUi();
                SwaggerUIBundle(uiConfig);
            })
            .catch(function (err) {
                if (pendingError && pendingError.code === 'auth') {
                    return;
                }
                if (err && (err.status === 401 || err.status === 403)) {
                    redirectToLogin();
                    return;
                }
                if (err && err.name === 'AbortError') {
                    return;
                }
                showError(
                    'Erreur lors du chargement de la documentation.',
                    (err && err.message && err.message.indexOf('permissions') !== -1)
                        ? err.message
                        : 'Veuillez réessayer dans quelques instants.',
                    null
                );
            });
    })();
</script>
</body>
</html>
