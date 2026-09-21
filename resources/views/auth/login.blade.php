<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — ANAPEC API Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="h-full bg-white font-sans text-gray-900 antialiased">

<div class="flex min-h-screen">

    {{-- ── LEFT: Image panel (hidden on mobile) ── --}}
    <div class="relative hidden lg:flex w-[50%]">
        <img src="{{ asset('images/espace-anapec.jpg') }}"
             alt="ANAPEC Espace"
             class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        <div class="absolute bottom-0 left-0 p-10">
            <p class="text-2xl font-semibold text-white leading-tight">ANAPEC</p>
            <p class="mt-1 text-base text-white/80">Espace emploi</p>
        </div>
    </div>

    {{-- ── RIGHT: Login form ── --}}
    <div class="flex w-full lg:w-[50%] flex-col items-center justify-center p-6 sm:p-10 lg:p-16">

        {{-- Logo --}}
        <a href="{{ route('login') }}" class="mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="ANAPEC" style="height:48px" class="w-auto">
        </a>

        <div class="w-full max-w-sm">

            <h1 class="text-2xl font-bold text-gray-900">Connexion</h1>
            <p class="mt-1 text-sm text-gray-500">
                Accédez à votre espace de gestion des API ANAPEC
            </p>

            {{-- Error banner --}}
            <div id="login-error" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                 role="alert"></div>

            <form id="login-form" class="mt-6 space-y-5" novalidate>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
                    <input type="email" id="email" name="email"
                           class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm
                                  placeholder-gray-400 shadow-sm focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                           placeholder="vous@anapec.com" required autocomplete="email">
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <div class="relative mt-1.5">
                        <input type="password" id="password" name="password"
                               class="block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm
                                      placeholder-gray-400 shadow-sm pr-10 focus:border-anapec-500 focus:outline-none focus:ring-1 focus:ring-anapec-500"
                               placeholder="••••••••" required autocomplete="current-password" minlength="8">
                        <button type="button" id="toggle-password"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                aria-label="Afficher ou masquer le mot de passe">
                            <svg class="h-5 w-5" id="eye-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm0 8.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7Z"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10 1a9 9 0 0 0-9 9 9 9 0 0 0 18 0 9 9 0 0 0-9-9Zm0 15a6 6 0 1 1 0-12 6 6 0 0 1 0 12Z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" id="login-btn"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-anapec-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm
                               transition-colors hover:bg-anapec-700 focus:outline-none focus:ring-2 focus:ring-anapec-500 focus:ring-offset-2
                               disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="login-spinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                    <span id="login-btn-text">Se connecter</span>
                </button>

            </form>

            {{-- API error --}}
            <div id="login-api-error" class="mt-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                 role="alert"></div>

        </div>

        {{-- Footer --}}
        <p class="mt-10 text-xs text-gray-400">
            &copy; {{ date('Y') }} ANAPEC — Agence Nationale Pour l'Emploi
        </p>
    </div>

</div>

<script>
(function () {
    // Show/hide password
    var toggleBtn = document.getElementById('toggle-password');
    var pwdInput = document.getElementById('password');
    var eyeIcon = document.getElementById('eye-icon');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function () {
            var isHidden = pwdInput.type === 'password';
            pwdInput.type = isHidden ? 'text' : 'password';
            toggleBtn.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        });
    }

    // Login form
    var form = document.getElementById('login-form');
    var emailInput = document.getElementById('email');
    var loginBtn = document.getElementById('login-btn');
    var spinner = document.getElementById('login-spinner');
    var btnText = document.getElementById('login-btn-text');
    var errorBanner = document.getElementById('login-error');
    var apiErrorBanner = document.getElementById('login-api-error');

    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        var email = emailInput.value.trim();
        var password = pwdInput.value;

        // Client-side validation
        errorBanner.classList.add('hidden');
        apiErrorBanner.classList.add('hidden');

        if (!email || !password) {
            errorBanner.textContent = 'Veuillez remplir tous les champs.';
            errorBanner.classList.remove('hidden');
            return;
        }
        if (password.length < 8) {
            errorBanner.textContent = 'Le mot de passe doit contenir au moins 8 caractères.';
            errorBanner.classList.remove('hidden');
            return;
        }

        // Disable button + show spinner
        loginBtn.disabled = true;
        spinner.classList.remove('hidden');
        btnText.textContent = 'Connexion…';

        try {
            var res = await fetch('/api/v1/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email: email, password: password }),
            });

            var data = await res.json();

            if (res.ok && data.success) {
                // Store token + user
                localStorage.setItem('anapec_token', data.data.token);
                localStorage.setItem('anapec_user', JSON.stringify(data.data.user));

                // Call /auth/me to refresh user state
                try {
                    var meRes = await fetch('/api/v1/auth/me', {
                        method: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + data.data.token,
                            'Accept': 'application/json',
                        },
                    });
                    if (meRes.ok) {
                        var meData = await meRes.json();
                        if (meData.success) {
                            localStorage.setItem('anapec_user', JSON.stringify(meData.data));
                        }
                    }
                } catch (meErr) { /* non-critical */ }

                window.location.href = '/dashboard';
                return;
            }

            // API error
            apiErrorBanner.textContent = data.message || 'Erreur lors de la connexion.';
            apiErrorBanner.classList.remove('hidden');

        } catch (err) {
            apiErrorBanner.textContent = 'Une erreur réseau est survenue. Veuillez réessayer.';
            apiErrorBanner.classList.remove('hidden');
        } finally {
            loginBtn.disabled = false;
            spinner.classList.add('hidden');
            btnText.textContent = 'Se connecter';
        }
    });

    // Auto-redirect if already authenticated
    var token = localStorage.getItem('anapec_token');
    if (token) {
        fetch('/api/v1/auth/me', {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
        })
            .then(function (res) {
                if (res.ok) {
                    window.location.href = '/dashboard';
                } else {
                    localStorage.removeItem('anapec_token');
                    localStorage.removeItem('anapec_user');
                }
            })
            .catch(function () {
                localStorage.removeItem('anapec_token');
                localStorage.removeItem('anapec_user');
            });
    }
})();
</script>

</body>
</html>
