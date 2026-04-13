<?php
require_once 'config.php';
session_start();

$user       = $_SESSION['user'] ?? null;
$isLoggedIn = $user !== null;
$profile    = (array) ($user['profile'] ?? []);

$storedName = trim((string) ($profile['name'] ?? ($user['name'] ?? '')));
$firstNamePrefill = trim((string) ($profile['first_name'] ?? ''));
$lastNamePrefill = trim((string) ($profile['last_name'] ?? ''));
$middleInitialPrefill = strtoupper(substr(trim((string) ($profile['middle_initial'] ?? '')), 0, 1));

if (($firstNamePrefill === '' || $lastNamePrefill === '') && $storedName !== '') {
    $parts = preg_split('/\s+/', $storedName) ?: [];
    $parts = array_values(array_filter($parts, static function ($p) {
        return $p !== '';
    }));
    if ($firstNamePrefill === '' && isset($parts[0])) {
        $firstNamePrefill = $parts[0];
    }
    if ($lastNamePrefill === '' && count($parts) >= 2) {
        $lastNamePrefill = $parts[count($parts) - 1];
    }
    if ($middleInitialPrefill === '' && count($parts) >= 3) {
        $middleInitialPrefill = strtoupper(substr($parts[1], 0, 1));
    }
}

$displayName = '';
if ($isLoggedIn) {
    $displayName = htmlspecialchars(
        $user['given_name'] ?: (explode(' ', (string) ($user['name'] ?? 'User'))[0] ?? 'User'),
        ENT_QUOTES, 'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> &ndash; Pregnancy Care Companion</title>
    <link rel="icon" type="image/png" href="assets/img/pregna-logo.png">
    <link rel="apple-touch-icon" href="assets/img/pregna-logo.png">
    <!-- Apply saved theme BEFORE paint to avoid flash -->
    <script>(function(){var s=localStorage.getItem('pregnatrack_theme');var p=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;if(s?s==='dark':p)document.documentElement.setAttribute('data-theme','dark');})()</script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
</head>
<body class="home-body">

    <!-- â”€â”€ Decorative background blobs â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div class="home-blob blob-a" aria-hidden="true"></div>
    <div class="home-blob blob-b" aria-hidden="true"></div>
    <div class="home-blob blob-c" aria-hidden="true"></div>

    <!-- â”€â”€ Header â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <header class="home-header">
        <a href="index.php" class="home-brand" aria-label="<?= APP_NAME ?> Home">
            <span class="brand-heart" aria-hidden="true">&#10084;</span>
            <span><?= APP_NAME ?></span>
        </a>
        <nav class="home-nav" aria-label="Site navigation">

            <!-- â”€â”€ Dark / Light mode toggle â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
            <button class="theme-toggle" id="themeToggle"
                    aria-label="Toggle dark mode" title="Toggle dark / light mode">
                <i class="fas fa-moon"  id="themeIconDark"></i>
                <i class="fas fa-sun"   id="themeIconLight" hidden></i>
            </button>

            <?php if ($isLoggedIn): ?>
                <?php $pic = htmlspecialchars($user['picture'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                <a href="diagnose.php" class="btn-nav-outline nav-page-link">Self-Diagnose</a>
                <a href="hospitals.php" class="btn-nav-outline nav-page-link">Hospitals</a>
                <a href="dashboard.php" class="btn-nav-outline nav-page-link">Dashboard</a>

                <!-- â”€â”€ User dropdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="user-menu" id="userMenu">
                    <button class="user-menu-trigger" id="userMenuTrigger"
                            aria-haspopup="true" aria-expanded="false"
                            aria-controls="userDropdown">
                        <?php if ($pic): ?>
                            <img src="<?= $pic ?>" alt="Profile photo" class="user-avatar-sm">
                        <?php else: ?>
                            <div class="user-avatar-init" aria-hidden="true">
                                <?= strtoupper(substr($displayName, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <span class="nav-username"><?= $displayName ?></span>
                        <i class="fas fa-chevron-down user-menu-caret" aria-hidden="true"></i>
                    </button>

                    <div class="user-dropdown" id="userDropdown" role="menu" hidden>
                        <div class="dropdown-header">
                            <?php if ($pic): ?>
                                <img src="<?= $pic ?>" alt="" class="dropdown-avatar">
                            <?php else: ?>
                                <div class="dropdown-avatar-init">
                                    <?= strtoupper(substr($displayName, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="dropdown-name"><?= $displayName ?></div>
                                <div class="dropdown-email"><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
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
                        <a href="settings.php" class="dropdown-item" role="menuitem">
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

            <?php else: ?>
                <a href="login.php" class="btn-nav-outline">Sign In</a>
                <a href="login.php" class="btn-nav-primary">Get Started</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- â”€â”€ Hero Section â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <section class="hero-section" aria-label="Welcome to <?= APP_NAME ?>">
        <div class="hero-content">
            <span class="hero-eyebrow">
                <i class="fas fa-heart-pulse" aria-hidden="true"></i>
                Maternal Health Companion
            </span>
            <h1 class="hero-title">
                Your pregnancy journey,<br>supported every step.
            </h1>
            <p class="hero-desc">
                <?= APP_NAME ?> is designed to support pregnant women throughout their
                <strong>1st</strong>, <strong>2nd</strong>, and <strong>3rd trimesters</strong>
                by providing reliable information about both the benefits and possible risks during
                each stage of pregnancy. It helps mothers understand what is normal, what to expect,
                and what warning signs to watch out for &mdash; empowering them to make informed decisions
                about their health and their baby.
            </p>

            <div class="trimester-row" role="list" aria-label="Pregnancy trimesters">
                <div class="trimester-badge t1" role="listitem">
                    <span class="trimester-num">1st</span>
                    <span class="trimester-lbl">Trimester<br><small>Weeks 1&ndash;13</small></span>
                </div>
                <div class="trimester-sep" aria-hidden="true">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="trimester-badge t2" role="listitem">
                    <span class="trimester-num">2nd</span>
                    <span class="trimester-lbl">Trimester<br><small>Weeks 14&ndash;26</small></span>
                </div>
                <div class="trimester-sep" aria-hidden="true">
                    <i class="fas fa-arrow-right"></i>
                </div>
                <div class="trimester-badge t3" role="listitem">
                    <span class="trimester-num">3rd</span>
                    <span class="trimester-lbl">Trimester<br><small>Weeks 27&ndash;40</small></span>
                </div>
            </div>

            <?php if ($isLoggedIn): ?>
                <a href="diagnose.php" class="hero-cta-btn">
                    <i class="fas fa-stethoscope" aria-hidden="true"></i>
                    Start My Assessment
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="hero-cta-btn">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                    Get Started &mdash; It's Free
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="hero-img-ring">
                <div class="hero-img-inner"></div>
                <div class="hero-ring-dec dec-1"></div>
                <div class="hero-ring-dec dec-2"></div>
            </div>
            <div class="hero-stat stat-a">
                <i class="fas fa-person-pregnant"></i>
                <div>
                    <strong>3 Trimesters</strong>
                    <span>Fully Covered</span>
                </div>
            </div>
            <div class="hero-stat stat-b">
                <i class="fas fa-shield-heart"></i>
                <div>
                    <strong>Safe &amp; Reliable</strong>
                    <span>Evidence-based info</span>
                </div>
            </div>
        </div>
    </section>

    <!-- â”€â”€ Medical Disclaimer Ribbon â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <div class="disclaimer-ribbon" role="note" aria-label="Medical disclaimer">
        <i class="fas fa-stethoscope" aria-hidden="true"></i>
        <span>
            <strong>Medical Disclaimer:</strong> This platform provides educational information
            only and <em>cannot replace professional medical advice</em>. Always consult your
            healthcare provider for proper diagnosis and treatment.
        </span>
    </div>

    <!-- â”€â”€ Patient Intake Form â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <!-- ── Combined Assessment Gateway ──────────────────────────────── -->
    <section class="intake-section" id="intake" aria-label="Begin combined assessment">
        <div class="intake-gateway">
            <div class="igw-icon" aria-hidden="true">
                <i class="fas fa-stethoscope"></i>
            </div>
            <h2 class="igw-title">Pregnancy Intake &amp; Self-Assessment</h2>
            <p class="igw-desc">
                Fill in your details, select your pregnancy week, check your symptoms
                by trimester, and download your personalised report &mdash; all in one place.
            </p>
            <ul class="igw-features" aria-label="What the assessment includes">
                <li><i class="fas fa-check-circle" aria-hidden="true"></i> Patient info &mdash; name, age, height &amp; weight</li>
                <li><i class="fas fa-check-circle" aria-hidden="true"></i> Pregnancy week &amp; trimester detection</li>
                <li><i class="fas fa-check-circle" aria-hidden="true"></i> Trimester-based symptom checker &amp; diagnosis</li>
                <li><i class="fas fa-check-circle" aria-hidden="true"></i> Downloadable Word report of your assessment</li>
            </ul>
            <?php if ($isLoggedIn): ?>
                <a href="diagnose.php" class="igw-start-btn">
                    <i class="fas fa-play-circle" aria-hidden="true"></i>
                    Start Combined Assessment
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="igw-start-btn">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                    Sign In to Start
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- â”€â”€ Footer â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
    <footer class="home-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="brand-heart" aria-hidden="true">&#10084;</span>
                <span><?= APP_NAME ?></span>
            </div>
            <p class="footer-disclaimer">
                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                While <?= APP_NAME ?> offers easy access to helpful guidance and promotes
                awareness, it <strong>cannot replace professional medical advice</strong>.
                Users are strongly encouraged to consult their healthcare providers for
                proper diagnosis and treatment.
            </p>
            <p class="footer-copy">
                &copy; <?= date('Y') ?> <?= APP_NAME ?>. Built for modern maternal care.
            </p>
        </div>
    </footer>


    <script>
    // â”€â”€ Dark / Light mode â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    (function () {
        'use strict';

        var STORAGE_KEY = 'pregnatrack_theme';
        var html        = document.documentElement;

        var iconDark    = document.getElementById('themeIconDark');
        var iconLight   = document.getElementById('themeIconLight');
        var pill        = document.getElementById('themePill');
        var label       = document.getElementById('dropdownThemeLabel');

        function applyTheme(dark) {
            if (dark) {
                html.setAttribute('data-theme', 'dark');
            } else {
                html.removeAttribute('data-theme');
            }
            if (iconDark)  iconDark.hidden  =  dark;
            if (iconLight) iconLight.hidden = !dark;
            if (pill)  pill.textContent  = dark ? 'ON'  : 'OFF';
            if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
        }

        function toggleTheme() {
            var isDark = html.getAttribute('data-theme') === 'dark';
            var next   = !isDark;
            localStorage.setItem(STORAGE_KEY, next ? 'dark' : 'light');
            applyTheme(next);
        }

        // Restore saved preference
        var saved = localStorage.getItem(STORAGE_KEY);
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(saved ? saved === 'dark' : prefersDark);

        var headerToggle   = document.getElementById('themeToggle');
        var dropdownToggle = document.getElementById('dropdownThemeToggle');
        if (headerToggle)   headerToggle.addEventListener('click', toggleTheme);
        if (dropdownToggle) dropdownToggle.addEventListener('click', toggleTheme);

        // â”€â”€ User dropdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        var trigger    = document.getElementById('userMenuTrigger');
        var dropdown   = document.getElementById('userDropdown');
        var userMenu   = document.getElementById('userMenu');

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
    })();
    </script>

</body>
</html>
