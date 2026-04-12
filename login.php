<?php
require_once 'config.php';
session_start();

// Already logged in — go to dashboard
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
    <title>Login — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-bg-shape shape-a" aria-hidden="true"></div>
    <div class="auth-bg-shape shape-b" aria-hidden="true"></div>

    <main class="auth-shell">
        <section class="auth-left" aria-label="Welcome">
            <div class="auth-left-brand">
                <span class="auth-left-logo">&#10084;</span>
                <span><?= APP_NAME ?></span>
            </div>
            <div class="auth-left-media" aria-hidden="true"></div>
            <h1>Your care journey starts with one secure sign in.</h1>
            <p>Track your pregnancy progress, wellness reminders, and upcoming appointments in one private place.</p>
            <div class="auth-left-foot">Built for modern maternal care</div>
        </section>

        <section class="auth-right">
            <div class="auth-form-card">
                <div id="authStatus" class="auth-status" aria-live="polite"></div>

                <div class="auth-tabs" role="tablist" aria-label="Authentication tabs">
                    <button class="tab active" type="button" data-target="signinPanel" role="tab" aria-selected="true">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                    <button class="tab" type="button" data-target="signupPanel" role="tab" aria-selected="false">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </button>
                </div>

                <div id="signinPanel" class="auth-panel active" role="tabpanel">
                    <form id="signinForm" class="ghost-form" autocomplete="on">
                        <div class="form-group">
                            <label for="signinEmail"><i class="fas fa-envelope"></i> Email Address</label>
                            <div class="input-wrapper">
                                <input id="signinEmail" type="email" name="email" placeholder="name@email.com" required>
                                <span class="input-focus-border"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="signinPassword"><i class="fas fa-lock"></i> Password</label>
                            <div class="input-wrapper password-wrapper">
                                <input id="signinPassword" type="password" name="password" placeholder="Enter your password" required>
                                <button type="button" class="toggle-password" data-input="signinPassword" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <span class="input-focus-border"></span>
                            </div>
                        </div>

                        <button type="submit" class="btn-google hero-btn">
                            <span class="btn-text">Sign In</span>
                            <span class="btn-loader" hidden>
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </form>

                    <div class="or-divider"><span>or continue with</span></div>

                    <a href="auth/google.php" class="btn-google hero-btn btn-google-outline" role="button">
                        <svg class="google-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Google
                    </a>

                    <p class="helper-note">Use email/password from Sign Up or continue with Google.</p>
                </div>

                <div id="signupPanel" class="auth-panel" role="tabpanel" aria-hidden="true">
                    <form id="signupForm" class="ghost-form" autocomplete="off">
                        <div class="form-group">
                            <label for="signupName"><i class="fas fa-user"></i> Full Name</label>
                            <div class="input-wrapper">
                                <input id="signupName" type="text" name="name" placeholder="Enter your full name" required>
                                <span class="input-focus-border"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="signupEmail"><i class="fas fa-envelope"></i> Email Address</label>
                            <div class="input-wrapper">
                                <input id="signupEmail" type="email" name="email" placeholder="name@email.com" required>
                                <span class="input-focus-border"></span>
                            </div>
                            <div class="otp-controls">
                                <button type="button" id="sendOtpBtn" class="btn-secondary">
                                    <span class="btn-text">Send OTP</span>
                                    <span class="btn-loader" hidden><i class="fas fa-spinner fa-spin"></i></span>
                                </button>
                                <span id="otpCountdown" class="otp-countdown" hidden></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="signupOtp"><i class="fas fa-key"></i> OTP Code</label>
                            <div class="input-wrapper otp-input-wrapper">
                                <input id="signupOtp" type="text" name="otp" maxlength="6" inputmode="numeric" placeholder="000000" required>
                                <span id="otpVerifiedBadge" class="otp-badge verify-badge" hidden>
                                    <i class="fas fa-check-circle"></i> Verified
                                </span>
                                <span class="input-focus-border"></span>
                            </div>
                            <div class="otp-controls">
                                <button type="button" id="verifyOtpBtn" class="btn-secondary">
                                    <span class="btn-text">Verify OTP</span>
                                    <span class="btn-loader" hidden><i class="fas fa-spinner fa-spin"></i></span>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="signupPassword"><i class="fas fa-lock"></i> Password</label>
                            <div class="input-wrapper password-wrapper">
                                <input id="signupPassword" type="password" name="password" placeholder="Minimum 8 characters" minlength="8" required>
                                <button type="button" class="toggle-password" data-input="signupPassword" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <span class="input-focus-border"></span>
                            </div>
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <span class="strength-text" id="strengthText">Password strength: weak</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-google hero-btn">
                            <span class="btn-text">Create Account</span>
                            <span class="btn-loader" hidden>
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </form>
                    <p class="helper-note"><i class="fas fa-info-circle"></i> A verified OTP is required before account creation.</p>
                </div>
            </div>

        </section>
    </main>

    <script>
        (function () {
            const tabs = document.querySelectorAll('.tab');
            const panels = document.querySelectorAll('.auth-panel');
            const statusEl = document.getElementById('authStatus');

            function setStatus(type, message) {
                statusEl.className = 'auth-status ' + type;
                statusEl.innerHTML = message ? '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + message : '';
            }

            // Tab switching with smooth transitions
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) {
                        t.classList.remove('active');
                        t.setAttribute('aria-selected', 'false');
                    });
                    panels.forEach(function (p) {
                        p.classList.remove('active');
                        p.setAttribute('aria-hidden', 'true');
                    });

                    tab.classList.add('active');
                    tab.setAttribute('aria-selected', 'true');
                    const panel = document.getElementById(tab.dataset.target);
                    if (panel) {
                        panel.classList.add('active');
                        panel.setAttribute('aria-hidden', 'false');
                    }
                    setStatus('', '');
                });
            });

            // Password visibility toggle
            document.querySelectorAll('.toggle-password').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const inputId = this.dataset.input;
                    const input = document.getElementById(inputId);
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Password strength indicator
            const signupPassword = document.getElementById('signupPassword');
            if (signupPassword) {
                signupPassword.addEventListener('input', function () {
                    const strength = calculatePasswordStrength(this.value);
                    updatePasswordStrength(strength);
                });
            }

            function calculatePasswordStrength(password) {
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                return strength;
            }

            function updatePasswordStrength(strength) {
                const fill = document.getElementById('strengthFill');
                const text = document.getElementById('strengthText');
                const colors = ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'];
                const labels = ['Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
                
                fill.style.width = ((strength / 5) * 100) + '%';
                fill.style.backgroundColor = colors[strength - 1] || '#ef4444';
                text.textContent = 'Password strength: ' + labels[strength - 1];
                text.style.color = colors[strength - 1] || '#ef4444';
            }

            // Form utilities
            const signupForm = document.getElementById('signupForm');
            const sendOtpBtn = document.getElementById('sendOtpBtn');
            const verifyOtpBtn = document.getElementById('verifyOtpBtn');
            const otpBadge = document.getElementById('otpVerifiedBadge');
            let otpVerified = false;
            const signinForm = document.getElementById('signinForm');

            function formData(obj) {
                const data = new URLSearchParams();
                Object.keys(obj).forEach(function (key) {
                    data.append(key, obj[key]);
                });
                return data;
            }

            function setButtonLoading(btn, isLoading) {
                const text = btn.querySelector('.btn-text');
                const loader = btn.querySelector('.btn-loader');
                btn.disabled = isLoading;
                if (isLoading) {
                    text.hidden = true;
                    loader.hidden = false;
                } else {
                    text.hidden = false;
                    loader.hidden = true;
                }
            }

            // OTP countdown timer
            function startOtpCountdown() {
                const countdown = document.getElementById('otpCountdown');
                let seconds = 60;
                countdown.hidden = false;
                countdown.textContent = 'Resend in ' + seconds + 's';
                sendOtpBtn.disabled = true;
                
                const interval = setInterval(function () {
                    seconds--;
                    if (seconds > 0) {
                        countdown.textContent = 'Resend in ' + seconds + 's';
                    } else {
                        clearInterval(interval);
                        countdown.hidden = true;
                        sendOtpBtn.disabled = false;
                    }
                }, 1000);
            }

            // Send OTP
            sendOtpBtn.addEventListener('click', async function () {
                const email = document.getElementById('signupEmail').value.trim();
                if (!email) {
                    setStatus('error', 'Enter your email first.');
                    return;
                }

                setButtonLoading(sendOtpBtn, true);
                try {
                    const response = await fetch('auth/signup_send_otp.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData({ email: email }),
                    });
                    const result = await response.json();
                    setStatus(result.ok ? 'success' : 'error', result.message || 'Could not send OTP.');
                    if (result.ok) {
                        otpVerified = false;
                        otpBadge.hidden = true;
                        startOtpCountdown();
                    }
                } catch (e) {
                    setStatus('error', 'Network error while sending OTP.');
                } finally {
                    setButtonLoading(sendOtpBtn, false);
                }
            });

            // Verify OTP
            verifyOtpBtn.addEventListener('click', async function () {
                const email = document.getElementById('signupEmail').value.trim();
                const otp = document.getElementById('signupOtp').value.trim();
                if (!email || !otp) {
                    setStatus('error', 'Enter both email and OTP.');
                    return;
                }

                setButtonLoading(verifyOtpBtn, true);
                try {
                    const response = await fetch('auth/signup_verify_otp.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData({ email: email, otp: otp }),
                    });
                    const result = await response.json();
                    otpVerified = !!result.ok;
                    otpBadge.hidden = !otpVerified;
                    setStatus(result.ok ? 'success' : 'error', result.message || 'Could not verify OTP.');
                } catch (e) {
                    setStatus('error', 'Network error while verifying OTP.');
                } finally {
                    setButtonLoading(verifyOtpBtn, false);
                }
            });

            // Sign in
            signinForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const btn = this.querySelector('.hero-btn');
                const payload = {
                    email: document.getElementById('signinEmail').value.trim(),
                    password: document.getElementById('signinPassword').value,
                };

                setButtonLoading(btn, true);
                try {
                    const response = await fetch('auth/signin_local.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData(payload),
                    });
                    const result = await response.json();
                    setStatus(result.ok ? 'success' : 'error', result.message || 'Could not sign in.');
                    if (result.ok && result.redirect) {
                        setTimeout(function () {
                            window.location.href = result.redirect;
                        }, 600);
                    }
                } catch (err) {
                    setStatus('error', 'Network error while signing in.');
                    setButtonLoading(btn, false);
                }
            });

            // Sign up
            signupForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (!otpVerified) {
                    setStatus('error', 'Verify OTP before creating your account.');
                    return;
                }

                const btn = this.querySelector('.hero-btn');
                const payload = {
                    name: document.getElementById('signupName').value.trim(),
                    email: document.getElementById('signupEmail').value.trim(),
                    password: document.getElementById('signupPassword').value,
                };

                setButtonLoading(btn, true);
                try {
                    const response = await fetch('auth/signup_register.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData(payload),
                    });
                    const result = await response.json();
                    setStatus(result.ok ? 'success' : 'error', result.message || 'Could not create account.');
                    if (result.ok && result.redirect) {
                        setTimeout(function () {
                            window.location.href = result.redirect;
                        }, 600);
                    }
                } catch (err) {
                    setStatus('error', 'Network error while creating account.');
                    setButtonLoading(btn, false);
                }
            });

            // Add ripple effect on buttons
            document.querySelectorAll('button, a.btn-google').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    if (this.tagName !== 'BUTTON' || this.type === 'submit') return;
                    const rect = this.getBoundingClientRect();
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple';
                    ripple.style.left = (e.clientX - rect.left) + 'px';
                    ripple.style.top = (e.clientY - rect.top) + 'px';
                    this.appendChild(ripple);
                });
            });
        })();
    </script>
</body>
</html>
