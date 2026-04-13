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

            <!-- Tabs -->
            <div class="login-tabs" role="tablist">
                <button class="login-tab active" type="button" data-target="signinPanel" role="tab" aria-selected="true">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Sign In
                </button>
                <button class="login-tab" type="button" data-target="signupPanel" role="tab" aria-selected="false">
                    <i class="fas fa-user-plus" aria-hidden="true"></i> Sign Up
                </button>
            </div>

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

                <p class="login-helper">Use email/password from Sign Up, or continue with Google.</p>
            </div>

            <!-- Sign Up -->
            <div id="signupPanel" class="login-panel" role="tabpanel" aria-hidden="true">
                <form id="signupForm" autocomplete="off" novalidate>

                    <div class="login-field">
                        <label for="signupName">
                            <i class="fas fa-user" aria-hidden="true"></i> Full Name
                        </label>
                        <div class="login-input-wrap">
                            <input id="signupName" type="text" name="name"
                                   placeholder="Enter your full name" required autocomplete="name">
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="signupEmail">
                            <i class="fas fa-envelope" aria-hidden="true"></i> Email Address
                        </label>
                        <div class="login-input-wrap">
                            <input id="signupEmail" type="email" name="email"
                                   placeholder="name@email.com" required autocomplete="email">
                        </div>
                        <div class="otp-controls">
                            <button type="button" id="sendOtpBtn" class="login-btn-secondary">
                                <span class="btn-text"><i class="fas fa-paper-plane" aria-hidden="true"></i> Send OTP</span>
                                <span class="btn-loader" hidden><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                            <span id="otpCountdown" class="otp-countdown" hidden></span>
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="signupOtp">
                            <i class="fas fa-key" aria-hidden="true"></i> OTP Code
                        </label>
                        <div class="login-input-wrap login-otp-wrap">
                            <input id="signupOtp" type="text" name="otp"
                                   maxlength="6" inputmode="numeric"
                                   placeholder="6-digit code" required autocomplete="one-time-code">
                            <span id="otpVerifiedBadge" class="otp-badge" hidden>
                                <i class="fas fa-check-circle"></i> Verified
                            </span>
                        </div>
                        <div class="otp-controls">
                            <button type="button" id="verifyOtpBtn" class="login-btn-secondary">
                                <span class="btn-text"><i class="fas fa-shield" aria-hidden="true"></i> Verify OTP</span>
                                <span class="btn-loader" hidden><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="signupPassword">
                            <i class="fas fa-lock" aria-hidden="true"></i> Password
                        </label>
                        <div class="login-input-wrap login-pw-wrap">
                            <input id="signupPassword" type="password" name="password"
                                   placeholder="Minimum 8 characters" minlength="8" required autocomplete="new-password">
                            <button type="button" class="login-eye" data-input="signupPassword" aria-label="Show/hide password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Password strength</span>
                        </div>
                    </div>

                    <button type="submit" class="login-btn-primary" id="signupSubmit">
                        <span class="btn-text"><i class="fas fa-user-plus" aria-hidden="true"></i> Create Account</span>
                        <span class="btn-loader" hidden><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </form>

                <p class="login-helper"><i class="fas fa-info-circle" aria-hidden="true"></i> A verified OTP is required before creating an account.</p>
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

        // Tabs
        var tabs   = document.querySelectorAll('.login-tab');
        var panels = document.querySelectorAll('.login-panel');
        var statusEl = document.getElementById('authStatus');

        function setStatus(type, message) {
            if (!message) { statusEl.hidden = true; return; }
            statusEl.hidden = false;
            statusEl.className = 'login-status ' + type;
            statusEl.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message;
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('active'); t.setAttribute('aria-selected','false'); });
                panels.forEach(function (p) { p.classList.remove('active'); p.setAttribute('aria-hidden','true'); });
                tab.classList.add('active');
                tab.setAttribute('aria-selected','true');
                var panel = document.getElementById(tab.dataset.target);
                if (panel) { panel.classList.add('active'); panel.setAttribute('aria-hidden','false'); }
                setStatus('','');
            });
        });

        // Password toggle
        document.querySelectorAll('.login-eye').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(this.dataset.input);
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                this.querySelector('i').className = 'fas fa-eye' + (show ? '-slash' : '');
            });
        });

        // Password strength
        var signupPw = document.getElementById('signupPassword');
        if (signupPw) {
            signupPw.addEventListener('input', function () {
                var pw = this.value;
                var s = 0;
                if (pw.length >= 8) s++;
                if (pw.length >= 12) s++;
                if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
                if (/[0-9]/.test(pw)) s++;
                if (/[^a-zA-Z0-9]/.test(pw)) s++;
                var colors = ['#ef4444','#f97316','#eab308','#84cc16','#22c55e'];
                var labels = ['Weak','Fair','Good','Strong','Very Strong'];
                var fill = document.getElementById('strengthFill');
                var txt  = document.getElementById('strengthText');
                fill.style.width = (s / 5 * 100) + '%';
                fill.style.backgroundColor = colors[s-1] || '#ef4444';
                txt.textContent = 'Password strength: ' + (labels[s-1] || 'Weak');
                txt.style.color = colors[s-1] || '#ef4444';
            });
        }

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

        // OTP
        var sendOtpBtn   = document.getElementById('sendOtpBtn');
        var verifyOtpBtn = document.getElementById('verifyOtpBtn');
        var otpBadge     = document.getElementById('otpVerifiedBadge');
        var otpVerified  = false;

        function startCountdown() {
            var countdownEl = document.getElementById('otpCountdown');
            var secs = 60;
            countdownEl.hidden = false;
            countdownEl.textContent = 'Resend in ' + secs + 's';
            sendOtpBtn.disabled = true;
            var iv = setInterval(function () {
                secs--;
                if (secs > 0) { countdownEl.textContent = 'Resend in ' + secs + 's'; }
                else { clearInterval(iv); countdownEl.hidden = true; sendOtpBtn.disabled = false; }
            }, 1000);
        }

        sendOtpBtn.addEventListener('click', async function () {
            var email = document.getElementById('signupEmail').value.trim();
            if (!email) { setStatus('error','Enter your email first.'); return; }
            setBtnLoading(sendOtpBtn, true);
            try {
                var r = await fetch('auth/signup_send_otp.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: formData({email}) });
                var d = await r.json();
                setStatus(d.ok ? 'success' : 'error', d.message || 'Could not send OTP.');
                if (d.ok) { otpVerified = false; otpBadge.hidden = true; startCountdown(); }
            } catch(e) { setStatus('error','Network error while sending OTP.'); }
            finally { setBtnLoading(sendOtpBtn, false); }
        });

        verifyOtpBtn.addEventListener('click', async function () {
            var email = document.getElementById('signupEmail').value.trim();
            var otp   = document.getElementById('signupOtp').value.trim();
            if (!email || !otp) { setStatus('error','Enter email and OTP.'); return; }
            setBtnLoading(verifyOtpBtn, true);
            try {
                var r = await fetch('auth/signup_verify_otp.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: formData({email, otp}) });
                var d = await r.json();
                otpVerified = !!d.ok;
                otpBadge.hidden = !otpVerified;
                setStatus(d.ok ? 'success' : 'error', d.message || 'Could not verify OTP.');
            } catch(e) { setStatus('error','Network error while verifying OTP.'); }
            finally { setBtnLoading(verifyOtpBtn, false); }
        });

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

        // Sign up
        document.getElementById('signupForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!otpVerified) { setStatus('error','Verify OTP before creating your account.'); return; }
            var btn = document.getElementById('signupSubmit');
            setBtnLoading(btn, true);
            try {
                var r = await fetch('auth/signup_register.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: formData({ name: document.getElementById('signupName').value.trim(), email: document.getElementById('signupEmail').value.trim(), password: document.getElementById('signupPassword').value }) });
                var d = await r.json();
                setStatus(d.ok ? 'success' : 'error', d.message || 'Could not create account.');
                if (d.ok && d.redirect) setTimeout(function () { window.location.href = d.redirect; }, 600);
                else setBtnLoading(btn, false);
            } catch(e) { setStatus('error','Network error while creating account.'); setBtnLoading(btn, false); }
        });

    })();
    </script>
</body>
</html>
