<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user      = $_SESSION['user'];
$fn        = $user['given_name'] ?? null;
if (!$fn) $fn = explode(' ', (string)($user['name'] ?? 'User'))[0];
$firstName = htmlspecialchars($fn, ENT_QUOTES, 'UTF-8');
$fullName  = htmlspecialchars($user['name']  ?? '', ENT_QUOTES, 'UTF-8');
$email     = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');
$picture   = htmlspecialchars($user['picture'] ?? '', ENT_QUOTES, 'UTF-8');
$initials  = strtoupper(substr($firstName, 0, 1));
$isLocal   = ($user['provider'] ?? '') === 'local';

// Load settings from users.json
require_once 'auth/signup_common.php';
$userId = (string)($user['id'] ?? '');
$savedSettings = [];
foreach (loadUsers() as $u) {
    if (($u['id'] ?? '') === $userId) {
        $savedSettings = $u['settings'] ?? [];
        break;
    }
}

$prefHeight = $savedSettings['height_unit'] ?? ($user['profile']['height_unit'] ?? 'cm');
$prefWeight = $savedSettings['weight_unit'] ?? ($user['profile']['weight_unit'] ?? 'kg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings &mdash; <?= APP_NAME ?></title>
    <script>(function(){var s=localStorage.getItem('pregnatrack_theme');var p=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;if(s?s==='dark':p)document.documentElement.setAttribute('data-theme','dark');})()</script>
    <link rel="stylesheet" href="assets/css/style.css?v=4">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
</head>
<body class="home-body">

    <div class="home-blob blob-a" aria-hidden="true"></div>
    <div class="home-blob blob-b" aria-hidden="true"></div>

    <!-- ── Header ──────────────────────────────────────────────────── -->
    <header class="home-header">
        <a href="index.php" class="home-brand" aria-label="<?= APP_NAME ?> Home">
            <span class="brand-heart" aria-hidden="true">&#10084;</span>
            <span><?= APP_NAME ?></span>
        </a>
        <nav class="home-nav" aria-label="Site navigation">
            <button class="theme-toggle" id="themeToggle"
                    aria-label="Toggle dark mode" title="Toggle dark / light mode">
                <i class="fas fa-moon"  id="themeIconDark"></i>
                <i class="fas fa-sun"   id="themeIconLight" hidden></i>
            </button>

            <a href="dashboard.php" class="btn-nav-outline nav-page-link">Dashboard</a>

            <div class="user-menu" id="userMenu">
                <button class="user-menu-trigger" id="userMenuTrigger"
                        aria-haspopup="true" aria-expanded="false"
                        aria-controls="userDropdown">
                    <?php if ($picture): ?>
                        <img src="<?= $picture ?>" alt="Profile photo" class="user-avatar-sm">
                    <?php else: ?>
                        <div class="user-avatar-init" aria-hidden="true"><?= $initials ?></div>
                    <?php endif; ?>
                    <span class="nav-username"><?= $firstName ?></span>
                    <i class="fas fa-chevron-down user-menu-caret" aria-hidden="true"></i>
                </button>

                <div class="user-dropdown" id="userDropdown" role="menu" hidden>
                    <div class="dropdown-header">
                        <?php if ($picture): ?>
                            <img src="<?= $picture ?>" alt="" class="dropdown-avatar">
                        <?php else: ?>
                            <div class="dropdown-avatar-init"><?= $initials ?></div>
                        <?php endif; ?>
                        <div>
                            <div class="dropdown-name"><?= $fullName ?></div>
                            <div class="dropdown-email"><?= $email ?></div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="dashboard.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-gauge-high" aria-hidden="true"></i>
                        Dashboard
                    </a>
                    <a href="diagnose.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-stethoscope" aria-hidden="true"></i>
                        Self-Diagnose
                    </a>
                    <a href="hospitals.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-hospital" aria-hidden="true"></i>
                        Hospital Finder
                    </a>
                    <a href="dashboard.php#history" class="dropdown-item" role="menuitem">
                        <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                        History
                    </a>
                    <a href="settings.php" class="dropdown-item dropdown-item-active" role="menuitem">
                        <i class="fas fa-gear" aria-hidden="true"></i>
                        Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item dropdown-theme-row" id="dropdownThemeToggle"
                            role="menuitem" type="button">
                        <i class="fas fa-circle-half-stroke" aria-hidden="true"></i>
                        <span id="dropdownThemeLabel">Dark Mode</span>
                        <span class="theme-pill" id="themePill">OFF</span>
                    </button>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item dropdown-item-danger" role="menuitem">
                        <i class="fas fa-right-from-bracket" aria-hidden="true"></i>
                        Sign Out
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- ── Main ────────────────────────────────────────────────────── -->
    <main class="settings-page">
        <div class="settings-container">

            <!-- Page heading -->
            <div class="settings-heading">
                <div class="settings-heading-icon" aria-hidden="true">
                    <i class="fas fa-gear"></i>
                </div>
                <div>
                    <h1 class="settings-title">Settings</h1>
                    <p class="settings-sub">Manage your account and preferences.</p>
                </div>
            </div>

            <!-- ── Account Info ──────────────────────────────────────── -->
            <section class="settings-card" aria-labelledby="accountHeading">
                <div class="settings-card-hdr">
                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                    <h2 id="accountHeading">Account</h2>
                </div>
                <div class="settings-card-body">
                    <div class="settings-info-row">
                        <div class="settings-info-item">
                            <span class="settings-info-label">Name</span>
                            <span class="settings-info-val"><?= $fullName ?: '—' ?></span>
                        </div>
                        <div class="settings-info-item">
                            <span class="settings-info-label">Email</span>
                            <span class="settings-info-val"><?= $email ?></span>
                        </div>
                        <div class="settings-info-item">
                            <span class="settings-info-label">Sign-in method</span>
                            <span class="settings-info-val settings-provider-badge <?= $isLocal ? 'badge-local' : 'badge-google' ?>">
                                <?php if ($isLocal): ?>
                                    <i class="fas fa-envelope" aria-hidden="true"></i> Email &amp; Password
                                <?php else: ?>
                                    <i class="fab fa-google" aria-hidden="true"></i> Google
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Preferences ───────────────────────────────────────── -->
            <section class="settings-card" aria-labelledby="prefHeading">
                <div class="settings-card-hdr">
                    <i class="fas fa-sliders" aria-hidden="true"></i>
                    <h2 id="prefHeading">Preferences</h2>
                </div>
                <div class="settings-card-body">
                    <div id="prefStatus" class="settings-status" aria-live="polite"></div>
                    <form id="prefForm" class="settings-form" novalidate>
                        <div class="settings-pref-grid">

                            <div class="settings-field">
                                <label>Default Height Unit</label>
                                <div class="settings-unit-toggle" role="group" aria-label="Height unit">
                                    <label class="settings-unit-option <?= $prefHeight === 'cm' ? 'active' : '' ?>">
                                        <input type="radio" name="height_unit" value="cm" <?= $prefHeight === 'cm' ? 'checked' : '' ?>>
                                        <i class="fas fa-ruler-vertical" aria-hidden="true"></i> cm
                                    </label>
                                    <label class="settings-unit-option <?= $prefHeight === 'ft' ? 'active' : '' ?>">
                                        <input type="radio" name="height_unit" value="ft" <?= $prefHeight === 'ft' ? 'checked' : '' ?>>
                                        <i class="fas fa-ruler" aria-hidden="true"></i> ft
                                    </label>
                                </div>
                            </div>

                            <div class="settings-field">
                                <label>Default Weight Unit</label>
                                <div class="settings-unit-toggle" role="group" aria-label="Weight unit">
                                    <label class="settings-unit-option <?= $prefWeight === 'kg' ? 'active' : '' ?>">
                                        <input type="radio" name="weight_unit" value="kg" <?= $prefWeight === 'kg' ? 'checked' : '' ?>>
                                        <i class="fas fa-weight-scale" aria-hidden="true"></i> kg
                                    </label>
                                    <label class="settings-unit-option <?= $prefWeight === 'lbs' ? 'active' : '' ?>">
                                        <input type="radio" name="weight_unit" value="lbs" <?= $prefWeight === 'lbs' ? 'checked' : '' ?>>
                                        <i class="fas fa-weight-scale" aria-hidden="true"></i> lbs
                                    </label>
                                </div>
                            </div>

                            <div class="settings-field">
                                <label>Theme</label>
                                <div class="settings-unit-toggle" role="group" aria-label="Theme preference">
                                    <label class="settings-unit-option" id="themeOptLight">
                                        <input type="radio" name="theme_ui" value="light">
                                        <i class="fas fa-sun" aria-hidden="true"></i> Light
                                    </label>
                                    <label class="settings-unit-option" id="themeOptDark">
                                        <input type="radio" name="theme_ui" value="dark">
                                        <i class="fas fa-moon" aria-hidden="true"></i> Dark
                                    </label>
                                </div>
                            </div>

                        </div>

                        <button type="submit" class="settings-save-btn" id="prefSaveBtn">
                            <span class="btn-text"><i class="fas fa-floppy-disk"></i> Save Preferences</span>
                            <span class="btn-loader" hidden><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </form>
                </div>
            </section>

        </div>
    </main>

    <script>
    (function () {
        'use strict';
        var STORAGE_KEY = 'pregnatrack_theme';
        var html        = document.documentElement;
        var iconDark    = document.getElementById('themeIconDark');
        var iconLight   = document.getElementById('themeIconLight');
        var pill        = document.getElementById('themePill');
        var label       = document.getElementById('dropdownThemeLabel');

        function applyTheme(dark) {
            dark ? html.setAttribute('data-theme','dark') : html.removeAttribute('data-theme');
            if (iconDark)  iconDark.hidden  =  dark;
            if (iconLight) iconLight.hidden = !dark;
            if (pill)  pill.textContent  = dark ? 'ON' : 'OFF';
            if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
            // sync theme radio on the preferences form
            var isDark = dark;
            var radLight = document.querySelector('input[name="theme_ui"][value="light"]');
            var radDark  = document.querySelector('input[name="theme_ui"][value="dark"]');
            var optLight = document.getElementById('themeOptLight');
            var optDark  = document.getElementById('themeOptDark');
            if (radLight) radLight.checked = !isDark;
            if (radDark)  radDark.checked  =  isDark;
            if (optLight) optLight.classList.toggle('active', !isDark);
            if (optDark)  optDark.classList.toggle('active',   isDark);
        }

        function toggleTheme() {
            var dark = html.getAttribute('data-theme') === 'dark';
            localStorage.setItem(STORAGE_KEY, !dark ? 'dark' : 'light');
            applyTheme(!dark);
        }

        var saved = localStorage.getItem(STORAGE_KEY);
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(saved ? saved === 'dark' : prefersDark);

        document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
        document.getElementById('dropdownThemeToggle')?.addEventListener('click', toggleTheme);

        // Theme radio direct click
        document.querySelectorAll('input[name="theme_ui"]').forEach(function (r) {
            r.addEventListener('change', function () {
                var dark = this.value === 'dark';
                localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
                applyTheme(dark);
            });
        });

        // ── Unit toggle active class ──────────────────────────────────────
        document.querySelectorAll('.settings-unit-toggle').forEach(function (grp) {
            grp.querySelectorAll('.settings-unit-option').forEach(function (opt) {
                opt.querySelector('input')?.addEventListener('change', function () {
                    grp.querySelectorAll('.settings-unit-option').forEach(function (o) {
                        o.classList.remove('active');
                    });
                    opt.classList.add('active');
                });
            });
        });

        // ── User menu dropdown ────────────────────────────────────────────
        var trigger  = document.getElementById('userMenuTrigger');
        var dropdown = document.getElementById('userDropdown');
        var userMenu = document.getElementById('userMenu');
        if (trigger && dropdown) {
            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                var open = !dropdown.hidden;
                dropdown.hidden = open;
                trigger.setAttribute('aria-expanded', String(!open));
                if (!open) dropdown.style.animation = 'dropdownIn 0.2s cubic-bezier(0.4,0,0.2,1)';
            });
            document.addEventListener('click', function (e) {
                if (userMenu && !userMenu.contains(e.target)) {
                    dropdown.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !dropdown.hidden) {
                    dropdown.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                    trigger.focus();
                }
            });
        }

        // ── Show / hide status banner ─────────────────────────────────────
        function showStatus(el, ok, msg) {
            el.className = 'settings-status ' + (ok ? 'success' : 'error');
            el.innerHTML = '<i class="fas ' + (ok ? 'fa-circle-check' : 'fa-circle-exclamation') + '"></i> ' + msg;
            el.hidden = false;
            setTimeout(function () { if (ok) el.hidden = true; }, 4000);
        }

        // ── Preferences form ─────────────────────────────────────────────
        var prefForm = document.getElementById('prefForm');
        if (prefForm) {
            prefForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var btn  = document.getElementById('prefSaveBtn');
                var stat = document.getElementById('prefStatus');
                var fd   = new FormData(prefForm);
                fd.append('action', 'preferences');
                btn.disabled = true;
                btn.querySelector('.btn-text').hidden = true;
                btn.querySelector('.btn-loader').hidden = false;

                fetch('auth/save_settings.php', { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (d) { showStatus(stat, d.ok, d.message); })
                    .catch(function ()  { showStatus(stat, false, 'Network error. Try again.'); })
                    .finally(function () {
                        btn.disabled = false;
                        btn.querySelector('.btn-text').hidden = false;
                        btn.querySelector('.btn-loader').hidden = true;
                    });
            });
        }


    })();
    </script>
</body>
</html>
