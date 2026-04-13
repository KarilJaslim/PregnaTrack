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

require_once 'auth/signup_common.php';
$userId = (string)($user['id'] ?? '');
$profileHistory = [];
$assessmentHistory = [];
$currentProfile = $user['profile'] ?? null;
foreach (loadUsers() as $u) {
    if (($u['id'] ?? '') === $userId) {
        $profileHistory = array_reverse($u['profile_history'] ?? []);
        $assessmentHistory = array_reverse($u['assessment_history'] ?? []);
        if ($currentProfile === null) $currentProfile = $u['profile'] ?? null;
        break;
    }
}

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
    $ts   = strtotime($p['updated_at'] ?? '') ?: 0;
    $date = $ts ? date('j M Y', $ts) : '—';
    $time = $ts ? date('g:i A', $ts) : '';
    return compact('p','h','w','hu','wu','bmi','bmiLabel','bmiColor','date','time','current');
}

$entries = [];
if ($currentProfile) $entries[] = buildEntry($currentProfile, true);
foreach ($profileHistory as $snap) $entries[] = buildEntry($snap, false);

// Stat cards from current profile
$bmi = null; $bmiLabel = '—'; $bmiColor = '#9ca3af';
$heightDisplay = '—'; $weightDisplay = '—';
if (!empty($entries)) {
    $e0 = $entries[0];
    $bmi = $e0['bmi']; $bmiLabel = $e0['bmiLabel']; $bmiColor = $e0['bmiColor'];
    $heightDisplay = $e0['h'] . ' ' . $e0['hu'];
    $weightDisplay = $e0['w'] . ' ' . $e0['wu'];
}

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= APP_NAME ?></title>
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

            <button class="theme-toggle" id="themeToggle"
                    aria-label="Toggle dark mode" title="Toggle dark / light mode">
                <i class="fas fa-moon"  id="themeIconDark"></i>
                <i class="fas fa-sun"   id="themeIconLight" hidden></i>
            </button>

            <div class="user-menu" id="userMenu">
                <button class="user-menu-trigger" id="userMenuTrigger"
                        aria-haspopup="true" aria-expanded="false"
                        aria-controls="userDropdown">
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
                    <a href="dashboard.php" class="dropdown-item dropdown-item-active" role="menuitem">
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
                    <a href="#history" class="dropdown-item" role="menuitem">
                        <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
                        History
                    </a>
                    <a href="#assessment-history" class="dropdown-item" role="menuitem">
                        <i class="fas fa-notes-medical" aria-hidden="true"></i>
                        Assessment History
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
        </nav>
    </header>

    <!-- ── Dashboard + History (merged) ─────────────────────────────── -->
    <main class="dash-page">
        <div class="dash-container">

            <!-- Greeting -->
            <div class="dash-welcome">
                <div>
                    <h1 class="dash-greeting"><?= $greeting ?>, <?= $firstName ?>! 🌸</h1>
                    <p class="dash-sub">Here's your pregnancy health overview.</p>
                </div>
                <div style="display:flex;gap:0.6rem;flex-wrap:wrap;align-items:center">
                    <a href="diagnose.php" class="btn-edit-profile" style="background:linear-gradient(135deg,#7c3aed,#0284c7)">
                        <i class="fas fa-stethoscope" aria-hidden="true"></i>
                        Self-Diagnose
                    </a>
                    <a href="index.php#intake" class="btn-edit-profile">
                        <i class="fas fa-pen-to-square" aria-hidden="true"></i>
                        <?= $currentProfile ? 'Update Profile' : 'Complete Profile' ?>
                    </a>
                </div>
            </div>

            <?php if ($currentProfile): ?>
            <!-- Stat cards -->
            <div class="dash-stats-grid">
                <div class="dash-stat-card">
                    <div class="stat-icon stat-icon-pink"><i class="fas fa-user-clock" aria-hidden="true"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= (int)$currentProfile['age'] ?></div>
                        <div class="stat-label">Age</div>
                        <div class="stat-sub">years old</div>
                    </div>
                </div>
                <div class="dash-stat-card">
                    <div class="stat-icon stat-icon-blue"><i class="fas fa-ruler" aria-hidden="true"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= htmlspecialchars($heightDisplay, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="stat-label">Height</div>
                    </div>
                </div>
                <div class="dash-stat-card">
                    <div class="stat-icon stat-icon-purple"><i class="fas fa-weight-scale" aria-hidden="true"></i></div>
                    <div class="stat-body">
                        <div class="stat-value"><?= htmlspecialchars($weightDisplay, ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="stat-label">Weight</div>
                    </div>
                </div>
                <div class="dash-stat-card">
                    <div class="stat-icon" style="background:<?= htmlspecialchars($bmiColor, ENT_QUOTES, 'UTF-8') ?>22;color:<?= htmlspecialchars($bmiColor, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-scale-balanced" aria-hidden="true"></i>
                    </div>
                    <div class="stat-body">
                        <div class="stat-value" style="color:<?= htmlspecialchars($bmiColor, ENT_QUOTES, 'UTF-8') ?>"><?= $bmi ?? '—' ?></div>
                        <div class="stat-label">BMI</div>
                        <div class="stat-sub"><?= htmlspecialchars($bmiLabel, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ── Profile History timeline ────────────────────────── -->
            <section class="hist-section" id="history">
                <div class="hist-section-hdr">
                    <div>
                        <h2 class="hist-section-title">
                            <i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Profile History
                        </h2>
                        <p class="hist-section-sub">Click any entry to expand full details.</p>
                    </div>
                    <?php if (!empty($entries)): ?>
                    <a href="auth/download_history.php" class="btn-dl-all" download>
                        <i class="fas fa-file-arrow-down" aria-hidden="true"></i> Download All
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($entries)): ?>
                <div class="dash-no-profile">
                    <div class="no-profile-icon"><i class="fas fa-file-medical" aria-hidden="true"></i></div>
                    <h2>No Data Yet</h2>
                    <p>Complete your intake profile and your records will appear here.</p>
                    <a href="index.php#intake" class="btn-complete-profile">
                        <i class="fas fa-arrow-right" aria-hidden="true"></i> Complete Intake Form
                    </a>
                </div>
                <?php else: ?>
                <div class="hist-timeline">
                    <?php foreach ($entries as $i => $e): ?>
                    <?php $p = $e['p']; ?>
                    <div class="hist-entry <?= $i === 0 ? 'hist-entry-current' : '' ?>">
                        <div class="hist-dot-col">
                            <div class="hist-dot <?= $i === 0 ? 'hist-dot-current' : '' ?>">
                                <i class="fas <?= $i === 0 ? 'fa-circle-dot' : 'fa-circle' ?>" aria-hidden="true"></i>
                            </div>
                            <?php if ($i < count($entries) - 1): ?>
                                <div class="hist-line"></div>
                            <?php endif; ?>
                        </div>
                        <article class="hist-card <?= $i === 0 ? 'is-open' : '' ?>">
                            <button class="hist-card-toggle" type="button"
                                    aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                                <div class="hist-toggle-left">
                                    <span class="hist-date"><?= htmlspecialchars($e['date'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if ($e['time']): ?>
                                        <span class="hist-time"><?= htmlspecialchars($e['time'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="hist-toggle-center">
                                    <?php if (!empty($p['name'])): ?>
                                        <span class="hist-peek-name"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="hist-peek-sep" aria-hidden="true">·</span>
                                    <?php endif; ?>
                                    <?php if ($e['bmi'] !== null): ?>
                                        <span class="hist-peek-bmi" style="color:<?= htmlspecialchars($e['bmiColor'], ENT_QUOTES, 'UTF-8') ?>">
                                            BMI <?= $e['bmi'] ?>
                                            <span class="hist-peek-bmi-label"><?= htmlspecialchars($e['bmiLabel'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="hist-toggle-right">
                                    <?php if ($i === 0): ?>
                                        <span class="hist-current-badge">
                                            <i class="fas fa-check-circle" aria-hidden="true"></i> Current
                                        </span>
                                    <?php else: ?>
                                        <span class="hist-snapshot-badge">Snapshot</span>
                                    <?php endif; ?>
                                    <i class="fas fa-chevron-down hist-chevron" aria-hidden="true"></i>
                                </div>
                            </button>
                            <div class="hist-card-body">
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

                                <?php if (($p['first_pregnancy'] ?? '') === 'yes'): ?>
                                    <div class="hist-preg-row">
                                        <span class="preg-first-badge" style="font-size:.78rem;padding:.3rem .75rem;">
                                            <i class="fas fa-star" aria-hidden="true"></i> First Pregnancy
                                        </span>
                                    </div>
                                <?php elseif (isset($p['gtpal_g'])): ?>
                                    <div class="hist-gtpal-row">
                                        <?php foreach (['G'=>'gtpal_g','T'=>'gtpal_t','P'=>'gtpal_p','A'=>'gtpal_a','L'=>'gtpal_l'] as $letter => $key): ?>
                                        <div class="hist-gtpal-cell">
                                            <div class="hist-gtpal-num"><?= (int)($p[$key] ?? 0) ?></div>
                                            <div class="hist-gtpal-key"><?= $letter ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="hist-actions">
                                    <a href="auth/download_history.php?entry=<?= $i ?>" class="btn-dl-entry" download>
                                        <i class="fas fa-file-arrow-down" aria-hidden="true"></i>
                                        Download this record
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <section class="hist-section" id="assessment-history">
                <div class="hist-section-hdr">
                    <div>
                        <h2 class="hist-section-title">
                            <i class="fas fa-notes-medical" aria-hidden="true"></i> Assessment History
                        </h2>
                        <p class="hist-section-sub">Saved self-assessment results from your symptom checks.</p>
                    </div>
                    <a href="diagnose.php" class="btn-dl-all" style="background:linear-gradient(135deg,#ec4899,#a855f7)">
                        <i class="fas fa-plus" aria-hidden="true"></i> New Assessment
                    </a>
                </div>

                <?php if (empty($assessmentHistory)): ?>
                <div class="assessment-empty">
                    <div class="no-profile-icon"><i class="fas fa-notes-medical" aria-hidden="true"></i></div>
                    <h2>No Assessment History Yet</h2>
                    <p>Save your first assessment result to see it here anytime.</p>
                    <a href="diagnose.php" class="btn-complete-profile">
                        <i class="fas fa-stethoscope" aria-hidden="true"></i> Start Assessment
                    </a>
                </div>
                <?php else: ?>
                <div class="assessment-history-list">
                    <?php foreach ($assessmentHistory as $assessment): ?>
                    <?php
                        $savedTs = strtotime((string)($assessment['saved_at'] ?? '')) ?: 0;
                        $savedDate = $savedTs ? date('j M Y', $savedTs) : '—';
                        $savedTime = $savedTs ? date('g:i A', $savedTs) : '';

                        $week = (int)($assessment['week'] ?? 0);
                        $trimesterLabel = trim((string)($assessment['trimester_label'] ?? ''));
                        if ($trimesterLabel === '') {
                            $trimester = (int)($assessment['trimester'] ?? 0);
                            $trimesterLabel = $trimester === 1 ? '1st Trimester' : ($trimester === 2 ? '2nd Trimester' : '3rd Trimester');
                        }

                        $risk = strtolower((string)($assessment['overall_level'] ?? 'normal'));
                        $riskClass = 'risk-normal';
                        $riskLabel = 'Normal';
                        if ($risk === 'emergency') {
                            $riskClass = 'risk-emergency';
                            $riskLabel = 'Emergency';
                        } elseif ($risk === 'warning') {
                            $riskClass = 'risk-warning';
                            $riskLabel = 'See Doctor';
                        } elseif ($risk === 'watch') {
                            $riskClass = 'risk-watch';
                            $riskLabel = 'Monitor';
                        }

                        $counts = is_array($assessment['counts'] ?? null) ? $assessment['counts'] : [];
                        $symptoms = is_array($assessment['symptoms'] ?? null) ? $assessment['symptoms'] : [];
                        $total = (int)($counts['total'] ?? count($symptoms));
                        $previewSymptoms = array_slice($symptoms, 0, 3);
                        $extraSymptoms = count($symptoms) - count($previewSymptoms);
                    ?>
                    <article class="assessment-history-card">
                        <div class="assessment-history-top">
                            <div>
                                <div class="assessment-history-date">
                                    <?= htmlspecialchars($savedDate, ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($savedTime): ?>
                                        <span class="assessment-history-time"><?= htmlspecialchars($savedTime, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="assessment-history-meta">
                                    Week <?= $week > 0 ? $week : '—' ?> &middot; <?= htmlspecialchars($trimesterLabel, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            <span class="assessment-risk-badge <?= $riskClass ?>"><?= htmlspecialchars($riskLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>

                        <div class="assessment-history-body">
                            <div class="assessment-count-pills">
                                <span class="assessment-count-pill"><strong><?= $total ?></strong> symptoms</span>
                                <span class="assessment-count-pill"><strong><?= (int)($counts['warning'] ?? 0) ?></strong> warning</span>
                                <span class="assessment-count-pill"><strong><?= (int)($counts['emergency'] ?? 0) ?></strong> emergency</span>
                            </div>

                            <?php if (!empty($previewSymptoms)): ?>
                            <div class="assessment-symptom-preview">
                                <?php foreach ($previewSymptoms as $symptom): ?>
                                    <span class="assessment-symptom-chip"><?= htmlspecialchars((string)($symptom['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endforeach; ?>
                                <?php if ($extraSymptoms > 0): ?>
                                    <span class="assessment-symptom-chip">+<?= $extraSymptoms ?> more</span>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <p class="assessment-no-symptoms">No symptoms were selected in this assessment.</p>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- Quick actions -->
            <?php if ($currentProfile): ?>
            <div class="quick-actions-row">
                <a href="index.php#intake" class="quick-action-card">
                    <i class="fas fa-file-pen" aria-hidden="true"></i>
                    <div class="qa-label">Update Profile</div>
                </a>
                <div class="quick-action-card qa-soon">
                    <i class="fas fa-thermometer" aria-hidden="true"></i>
                    <div class="qa-label">Symptom Tracker</div>
                    <span class="qa-badge">Soon</span>
                </div>
                <div class="quick-action-card qa-soon">
                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                    <div class="qa-label">Appointments</div>
                    <span class="qa-badge">Soon</span>
                </div>
                <div class="quick-action-card qa-soon">
                    <i class="fas fa-book-medical" aria-hidden="true"></i>
                    <div class="qa-label">Resources</div>
                    <span class="qa-badge">Soon</span>
                </div>
            </div>
            <?php endif; ?>

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

        // ── History card expand / collapse ──────────────────────────────────
        document.querySelectorAll('.hist-card-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var card = this.closest('.hist-card');
                var open = card.classList.toggle('is-open');
                this.setAttribute('aria-expanded', String(open));
            });
        });

        // Smooth-scroll for in-page anchors in dropdown
        document.querySelectorAll('a[href="#history"], a[href="#assessment-history"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var targetId = this.getAttribute('href').replace('#', '');
                var target = document.getElementById(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    if (dropdown) dropdown.hidden = true;
                    if (trigger) trigger.setAttribute('aria-expanded', 'false');
                }
            });
        });
    })();
    </script>
</body>
</html>
