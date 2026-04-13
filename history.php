<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user     = $_SESSION['user'];
$fn       = $user['given_name'] ?? null;
if (!$fn) $fn = explode(' ', (string)($user['name'] ?? 'User'))[0];
$firstName = htmlspecialchars($fn, ENT_QUOTES, 'UTF-8');
$fullName  = htmlspecialchars($user['name']    ?? '', ENT_QUOTES, 'UTF-8');
$email     = htmlspecialchars($user['email']   ?? '', ENT_QUOTES, 'UTF-8');
$picture   = htmlspecialchars($user['picture'] ?? '', ENT_QUOTES, 'UTF-8');
$initials  = strtoupper(substr($firstName, 0, 1));

// ── Load full record from users.json for history ──────────────────────────────
require_once 'auth/signup_common.php';
$userId = (string)($user['id'] ?? '');
$profileHistory = [];
$currentProfile = $user['profile'] ?? null;

$allUsers = loadUsers();
foreach ($allUsers as $u) {
    if (($u['id'] ?? '') === $userId) {
        $profileHistory = array_reverse($u['profile_history'] ?? []);
        if ($currentProfile === null) {
            $currentProfile = $u['profile'] ?? null;
        }
        break;
    }
}

// ── Helper: format a snapshot for display ────────────────────────────────────
function buildEntry(array $p, bool $current = false): array {
    $h  = (float)($p['height'] ?? 0);
    $w  = (float)($p['weight'] ?? 0);
    $hu = $p['height_unit'] ?? 'cm';
    $wu = $p['weight_unit'] ?? 'kg';
    $hm = ($hu === 'ft') ? $h * 0.3048 : $h / 100;
    $wk = ($wu === 'lbs') ? $w * 0.453592 : $w;
    $bmi = ($hm > 0) ? round($wk / ($hm * $hm), 1) : null;
    $bmiLabel = '—'; $bmiColor = '#9ca3af';
    if ($bmi !== null) {
        if      ($bmi < 18.5) { $bmiLabel = 'Underweight'; $bmiColor = '#3b82f6'; }
        elseif  ($bmi < 25.0) { $bmiLabel = 'Normal';      $bmiColor = '#22c55e'; }
        elseif  ($bmi < 30.0) { $bmiLabel = 'Overweight';  $bmiColor = '#f59e0b'; }
        else                  { $bmiLabel = 'Obese';        $bmiColor = '#ef4444'; }
    }
    $ts  = strtotime($p['updated_at'] ?? '') ?: 0;
    $date = $ts ? date('j M Y', $ts) : '—';
    $time = $ts ? date('g:i A', $ts)  : '';
    return compact('p','h','w','hu','wu','bmi','bmiLabel','bmiColor','date','time','current');
}

$entries = [];
if ($currentProfile) $entries[] = buildEntry($currentProfile, true);
foreach ($profileHistory as $snap) $entries[] = buildEntry($snap, false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History — <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="assets/img/pregna-logo.png">
    <link rel="apple-touch-icon" href="assets/img/pregna-logo.png">
    <script>(function(){var s=localStorage.getItem('pregnatrack_theme');var p=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;if(s?s==='dark':p)document.documentElement.setAttribute('data-theme','dark');})()</script>
    <link rel="stylesheet" href="assets/css/style.css">
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
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
                <i class="fas fa-moon"  id="themeIconDark"></i>
                <i class="fas fa-sun"   id="themeIconLight" hidden></i>
            </button>
            <div class="user-menu" id="userMenu">
                <button class="user-menu-trigger" id="userMenuTrigger"
                        aria-haspopup="true" aria-expanded="false" aria-controls="userDropdown">
                    <?php if ($picture): ?>
                        <img src="<?= $picture ?>" alt="" class="user-avatar-sm">
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
                        <i class="fas fa-gauge-high" aria-hidden="true"></i>Dashboard
                    </a>
                    <a href="history.php" class="dropdown-item dropdown-item-active" role="menuitem">
                        <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>History
                    </a>
                    <a href="settings.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-gear" aria-hidden="true"></i>
                        Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item dropdown-theme-row" id="dropdownThemeToggle" role="menuitem" type="button">
                        <i class="fas fa-circle-half-stroke" aria-hidden="true"></i>
                        <span id="dropdownThemeLabel">Dark Mode</span>
                        <span class="theme-pill" id="themePill">OFF</span>
                    </button>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item dropdown-item-danger" role="menuitem">
                        <i class="fas fa-right-from-bracket" aria-hidden="true"></i>Sign Out
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- ── History page content ────────────────────────────────────── -->
    <main class="dash-page">
        <div class="dash-container">

            <!-- Title row -->
            <div class="dash-welcome">
                <div>
                    <h1 class="dash-greeting" style="font-size:1.75rem;">
                        <i class="fas fa-clock-rotate-left" style="font-size:1.3rem;color:var(--pink-400);margin-right:0.4rem;" aria-hidden="true"></i>
                        Medical History
                    </h1>
                    <p class="dash-sub">A full record of your profile updates.</p>
                </div>
                <?php if (!empty($entries)): ?>
                <a href="auth/download_history.php" class="btn-edit-profile" download>
                    <i class="fas fa-file-arrow-down" aria-hidden="true"></i>
                    Download as Word
                </a>
                <?php endif; ?>
            </div>

            <?php if (empty($entries)): ?>
            <!-- No data yet -->
            <div class="dash-no-profile">
                <div class="no-profile-icon">
                    <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                </div>
                <h2>No History Yet</h2>
                <p>Complete your intake profile and your medical records will appear here.</p>
                <a href="index.php#intake" class="btn-complete-profile">
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    Complete Intake Form
                </a>
            </div>

            <?php else: ?>
            <!-- Timeline -->
            <div class="hist-timeline">
                <?php foreach ($entries as $i => $e): ?>
                <?php $p = $e['p']; ?>
                <div class="hist-entry <?= $e['current'] ? 'hist-entry-current' : '' ?>">
                    <div class="hist-dot-col">
                        <div class="hist-dot <?= $e['current'] ? 'hist-dot-current' : '' ?>">
                            <i class="fas <?= $e['current'] ? 'fa-circle-dot' : 'fa-circle' ?>" aria-hidden="true"></i>
                        </div>
                        <?php if ($i < count($entries) - 1): ?>
                            <div class="hist-line"></div>
                        <?php endif; ?>
                    </div>
                    <div class="hist-card">
                        <div class="hist-card-header">
                            <div>
                                <span class="hist-date"><?= htmlspecialchars($e['date'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($e['time']): ?>
                                    <span class="hist-time"><?= htmlspecialchars($e['time'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($e['current']): ?>
                                <span class="hist-current-badge">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i> Current
                                </span>
                            <?php else: ?>
                                <span class="hist-snapshot-badge">Snapshot</span>
                            <?php endif; ?>
                        </div>
                        <div class="hist-card-body">
                            <!-- Stats row -->
                            <div class="hist-stats-row">
                                <div class="hist-stat">
                                    <i class="fas fa-id-badge" aria-hidden="true"></i>
                                    <div>
                                        <div class="hist-stat-val"><?= htmlspecialchars($p['name'] ?: '—', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="hist-stat-lbl">Name</div>
                                    </div>
                                </div>
                                <div class="hist-stat">
                                    <i class="fas fa-user-clock" aria-hidden="true"></i>
                                    <div>
                                        <div class="hist-stat-val"><?= (int)($p['age'] ?? 0) ?> yrs</div>
                                        <div class="hist-stat-lbl">Age</div>
                                    </div>
                                </div>
                                <div class="hist-stat">
                                    <i class="fas fa-ruler" aria-hidden="true"></i>
                                    <div>
                                        <div class="hist-stat-val"><?= htmlspecialchars($e['h'] . ' ' . $e['hu'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="hist-stat-lbl">Height</div>
                                    </div>
                                </div>
                                <div class="hist-stat">
                                    <i class="fas fa-weight-scale" aria-hidden="true"></i>
                                    <div>
                                        <div class="hist-stat-val"><?= htmlspecialchars($e['w'] . ' ' . $e['wu'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="hist-stat-lbl">Weight</div>
                                    </div>
                                </div>
                                <div class="hist-stat">
                                    <i class="fas fa-scale-balanced" style="color:<?= htmlspecialchars($e['bmiColor'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                                    <div>
                                        <div class="hist-stat-val" style="color:<?= htmlspecialchars($e['bmiColor'], ENT_QUOTES, 'UTF-8') ?>"><?= $e['bmi'] ?? '—' ?></div>
                                        <div class="hist-stat-lbl"><?= htmlspecialchars($e['bmiLabel'], ENT_QUOTES, 'UTF-8') ?> BMI</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pregnancy info -->
                            <?php if (($p['first_pregnancy'] ?? '') === 'yes'): ?>
                                <div class="hist-preg-row">
                                    <span class="preg-first-badge" style="font-size:0.78rem;padding:0.3rem 0.75rem;">
                                        <i class="fas fa-star" aria-hidden="true"></i> First Pregnancy
                                    </span>
                                </div>
                            <?php elseif (isset($p['gtpal_g'])): ?>
                                <div class="hist-gtpal-row">
                                    <?php foreach (['G' => 'gtpal_g', 'T' => 'gtpal_t', 'P' => 'gtpal_p', 'A' => 'gtpal_a', 'L' => 'gtpal_l'] as $letter => $key): ?>
                                    <div class="hist-gtpal-cell">
                                        <div class="hist-gtpal-num"><?= (int)($p[$key] ?? 0) ?></div>
                                        <div class="hist-gtpal-key"><?= $letter ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>

    <script>
    (function () {
        'use strict';
        var STORAGE_KEY = 'pregnatrack_theme';
        var html = document.documentElement;
        var iconDark = document.getElementById('themeIconDark');
        var iconLight = document.getElementById('themeIconLight');
        var pill  = document.getElementById('themePill');
        var label = document.getElementById('dropdownThemeLabel');
        function applyTheme(dark) {
            dark ? html.setAttribute('data-theme','dark') : html.removeAttribute('data-theme');
            if (iconDark)  iconDark.hidden  =  dark;
            if (iconLight) iconLight.hidden = !dark;
            if (pill)  pill.textContent  = dark ? 'ON' : 'OFF';
            if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
        }
        function toggleTheme() {
            var dark = html.getAttribute('data-theme') === 'dark';
            localStorage.setItem(STORAGE_KEY, !dark ? 'dark' : 'light');
            applyTheme(!dark);
        }
        var saved = localStorage.getItem(STORAGE_KEY);
        applyTheme(saved ? saved === 'dark' : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));
        document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
        document.getElementById('dropdownThemeToggle')?.addEventListener('click', toggleTheme);
        var trigger = document.getElementById('userMenuTrigger');
        var dropdown = document.getElementById('userDropdown');
        var userMenu = document.getElementById('userMenu');
        if (trigger && dropdown) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = !dropdown.hidden;
                dropdown.hidden = open;
                trigger.setAttribute('aria-expanded', String(!open));
                if (!open) dropdown.style.animation = 'dropdownIn 0.2s cubic-bezier(0.4,0,0.2,1)';
            });
            document.addEventListener('click', function(e) {
                if (userMenu && !userMenu.contains(e.target)) {
                    dropdown.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !dropdown.hidden) {
                    dropdown.hidden = true;
                    trigger.setAttribute('aria-expanded','false');
                    trigger.focus();
                }
            });
        }
    })();
    </script>
</body>
</html>
