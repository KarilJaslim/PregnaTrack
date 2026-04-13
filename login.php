<?php
require_once 'config.php';
session_start();
if (isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="assets/img/pregna-logo.png">
    <link rel="apple-touch-icon" href="assets/img/pregna-logo.png">
    <script>(function(){var s=localStorage.getItem('pregnatrack_theme');var p=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;if(s?s==='dark':p)document.documentElement.setAttribute('data-theme','dark');})()</script>
    <link rel="stylesheet" href="assets/css/style.css?v=5">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
</head>
<body class="home-body login-body">

    <div class="home-blob blob-a" aria-hidden="true"></div>
    <div class="home-blob blob-b" aria-hidden="true"></div>
    <div class="home-blob blob-c" aria-hidden="true"></div>

    <!-- Header -->
    <header class="home-header">
        <a href="index.php" class="home-brand" aria-label="<?= APP_NAME ?> Home">
            <span class="brand-heart" aria-hidden="true">&#10084;</span>
            <span><?= APP_NAME ?></span>
        </a>
        <nav class="home-nav">
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
                <i class="fas fa-moon"  id="themeIconDark"></i>
                <i class="fas fa-sun"   id="themeIconLight" hidden></i>
            </button>
        </nav>
    </header>

    <!-- Auth Card -->
    <main class="login-main">
        <div class="login-card">

            <div class="login-brand-badge" aria-hidden="true">
                <span class="login-brand-heart">&#10084;</span>
            </div>

            <h1 class="login-heading">Welcome to <?= APP_NAME ?></h1>
            <p class="login-subhead">Your pregnancy care companion</p>

            <div id="authStatus" class="login-status" aria-live="polite" hidden></div>

            <!-- Sign In -->
            <div id="signinPanel" class="login-panel active" role="tabpanel">
                <form id="signinForm" autocomplete="on" novalidate>

                    <div class="login-field">
                        <label for="signinEmail">
                            <i class="fas fa-envelope" aria-hidden="true"></i> Email Address
                        </label>
                        <div class="login-input-wrap">
                            <input id="signinEmail" type="email" name="email"
                                   placeholder="name@email.com" required autocomplete="email">
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="signinPassword">
                            <i class="fas fa-lock" aria-hidden="true"></i> Password
                        </label>
                        <div class="login-input-wrap login-pw-wrap">
                            <input id="signinPassword" type="password" name="password"
                                   placeholder="Enter your password" required autocomplete="current-password">
                            <button type="button" class="login-eye" data-input="signinPassword" aria-label="Show/hide password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-btn-primary" id="signinSubmit">
                        <span class="btn-text"><i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign In</span>
                        <span class="btn-loader" hidden><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </form>

                <div class="login-divider"><span>or continue with</span></div>

                <a href="auth/google.php" class="login-btn-google" role="button">
                    <svg class="google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </a>

                <p class="login-helper">Use your account password, or continue with Google.</p>
            </div>

        </div>
    </main>

    <script>
    (function () {
        'use strict';

        // Theme
        var STORAGE_KEY = 'pregnatrack_theme';
        var html = document.documentElement;
        var iconDark  = document.getElementById('themeIconDark');
        var iconLight = document.getElementById('themeIconLight');

        function applyTheme(dark) {
            dark ? html.setAttribute('data-theme','dark') : html.removeAttribute('data-theme');
            if (iconDark)  iconDark.hidden  =  dark;
            if (iconLight) iconLight.hidden = !dark;
        }
        function toggleTheme() {
            var dark = html.getAttribute('data-theme') === 'dark';
            localStorage.setItem(STORAGE_KEY, !dark ? 'dark' : 'light');
            applyTheme(!dark);
        }
        var saved = localStorage.getItem(STORAGE_KEY);
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(saved ? saved === 'dark' : prefersDark);
        document.getElementById('themeToggle').addEventListener('click', toggleTheme);

        var statusEl = document.getElementById('authStatus');

        function setStatus(type, message) {
            if (!message) { statusEl.hidden = true; return; }
            statusEl.hidden = false;
            statusEl.className = 'login-status ' + type;
            statusEl.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
        }

        // Password toggle
        document.querySelectorAll('.login-eye').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(this.dataset.input);
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                this.querySelector('i').className = 'fas fa-eye' + (show ? '-slash' : '');
            });
        });

        // Helpers
        function formData(obj) {
            var d = new URLSearchParams();
            Object.keys(obj).forEach(function (k) { d.append(k, obj[k]); });
            return d;
        }
        function setBtnLoading(btn, on) {
            btn.disabled = on;
            btn.querySelector('.btn-text').hidden = on;
            btn.querySelector('.btn-loader').hidden = !on;
        }


        // Sign in
        document.getElementById('signinForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            var btn = document.getElementById('signinSubmit');
            setBtnLoading(btn, true);
            try {
                var r = await fetch('auth/signin_local.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: formData({ email: document.getElementById('signinEmail').value.trim(), password: document.getElementById('signinPassword').value }) });
                var d = await r.json();
                setStatus(d.ok ? 'success' : 'error', d.message || 'Could not sign in.');
                if (d.ok && d.redirect) setTimeout(function () { window.location.href = d.redirect; }, 600);
                else setBtnLoading(btn, false);
            } catch(e) { setStatus('error','Network error while signing in.'); setBtnLoading(btn, false); }
        });

    })();
    </script>
</body>
</html>
