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
$fullName  = htmlspecialchars($user['name']    ?? '', ENT_QUOTES, 'UTF-8');
$email     = htmlspecialchars($user['email']   ?? '', ENT_QUOTES, 'UTF-8');
$picture   = htmlspecialchars($user['picture'] ?? '', ENT_QUOTES, 'UTF-8');
$initials  = strtoupper(substr($firstName, 0, 1));
$profile   = (array)($user['profile'] ?? []);
$storedName           = trim((string)($profile['name'] ?? ($user['name'] ?? '')));
$firstNamePrefill     = trim((string)($profile['first_name'] ?? ''));
$lastNamePrefill      = trim((string)($profile['last_name']  ?? ''));
$middleInitialPrefill = strtoupper(substr(trim((string)($profile['middle_initial'] ?? '')), 0, 1));
if (($firstNamePrefill === '' || $lastNamePrefill === '') && $storedName !== '') {
    $parts = preg_split('/\s+/', $storedName) ?: [];
    $parts = array_values(array_filter($parts, static function ($p) { return $p !== ''; }));
    if ($firstNamePrefill === '' && isset($parts[0]))       $firstNamePrefill     = $parts[0];
    if ($lastNamePrefill  === '' && count($parts) >= 2)     $lastNamePrefill      = $parts[count($parts) - 1];
    if ($middleInitialPrefill === '' && count($parts) >= 3) $middleInitialPrefill = strtoupper(substr($parts[1], 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pregnancy Assessment — <?= APP_NAME ?></title>
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
                    <a href="index.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-house" aria-hidden="true"></i>
                        Home
                    </a>
                    <a href="dashboard.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-gauge-high" aria-hidden="true"></i>
                        Dashboard
                    </a>
                    <a href="diagnose.php" class="dropdown-item dropdown-item-active" role="menuitem">
                        <i class="fas fa-stethoscope" aria-hidden="true"></i>
                        Self-Diagnose
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
    <main class="diag-page">
        <div class="diag-container">

            <!-- Page heading -->
            <div class="diag-heading">
                <div class="diag-icon-wrap" aria-hidden="true">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div>
                    <h1 class="diag-title">Pregnancy Self-Assessment</h1>
                    <p class="diag-desc">
                        Select your current pregnancy week, then check off any symptoms you're experiencing.
                        We'll categorise what's normal, what to monitor, and what needs urgent attention.
                    </p>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="disclaimer-ribbon" role="note" aria-label="Disclaimer">
                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                <span>
                    <strong>This is not a medical diagnosis.</strong>
                    Always consult your healthcare provider. For emergencies, call <strong>911</strong> immediately.
                </span>
            </div>

            <!-- Step progress indicator -->
            <div class="diag-progress" role="list" aria-label="Assessment steps">
                <div class="diag-step active" id="progStep1" role="listitem">
                    <span class="diag-step-num" aria-hidden="true">1</span>
                    <span class="diag-step-lbl">Patient Info</span>
                </div>
                <div class="diag-step-line" aria-hidden="true"></div>
                <div class="diag-step" id="progStep2" role="listitem">
                    <span class="diag-step-num" aria-hidden="true">2</span>
                    <span class="diag-step-lbl">Preg. Week</span>
                </div>
                <div class="diag-step-line" aria-hidden="true"></div>
                <div class="diag-step" id="progStep3" role="listitem">
                    <span class="diag-step-num" aria-hidden="true">3</span>
                    <span class="diag-step-lbl">Symptoms</span>
                </div>
                <div class="diag-step-line" aria-hidden="true"></div>
                <div class="diag-step" id="progStep4" role="listitem">
                    <span class="diag-step-num" aria-hidden="true">4</span>
                    <span class="diag-step-lbl">Assessment</span>
                </div>
            </div>

            <!-- ── Step 1: Patient Info ──────────────────────── -->
            <section class="diag-card" id="stepInfo" aria-labelledby="stepInfoTitle">
                <div class="diag-card-header">
                    <i class="fas fa-id-badge" aria-hidden="true"></i>
                    <div>
                        <h2 id="stepInfoTitle">Your Patient Information</h2>
                        <p class="diag-card-sub">Pre-fills your assessment report &mdash; not stored unless you are signed in.</p>
                    </div>
                </div>

                <div class="intake-form" style="padding: 1.5rem 1.8rem 1.2rem">
                    <div class="intake-section-sep">
                        <span class="intake-section-sep-label">
                            <i class="fas fa-id-badge" aria-hidden="true"></i> Name
                        </span>
                    </div>
                    <div class="intake-name-grid" role="group" aria-label="Name fields">
                        <div class="intake-subfield">
                            <label class="intake-sub-label" for="dFirstName">First Name <span class="field-required">*</span></label>
                            <div class="intake-input-wrap">
                                <input id="dFirstName" type="text" class="intake-input" placeholder="First name"
                                       autocomplete="given-name"
                                       value="<?= htmlspecialchars($firstNamePrefill, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                        <div class="intake-subfield">
                            <label class="intake-sub-label" for="dLastName">Last Name <span class="field-required">*</span></label>
                            <div class="intake-input-wrap">
                                <input id="dLastName" type="text" class="intake-input" placeholder="Last name"
                                       autocomplete="family-name"
                                       value="<?= htmlspecialchars($lastNamePrefill, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                        <div class="intake-subfield">
                            <label class="intake-sub-label" for="dMiddleInitial">M.I. <span class="field-optional">(opt.)</span></label>
                            <div class="intake-input-wrap">
                                <input id="dMiddleInitial" type="text" class="intake-input" placeholder="M"
                                       maxlength="1" autocomplete="additional-name"
                                       value="<?= htmlspecialchars($middleInitialPrefill, ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="intake-section-sep" style="margin-top: 0.6rem">
                        <span class="intake-section-sep-label">
                            <i class="fas fa-ruler" aria-hidden="true"></i> Measurements
                        </span>
                    </div>
                    <div class="intake-row three-cols">
                        <div class="intake-field">
                            <label class="intake-label" for="dAge">
                                <i class="fas fa-hashtag" aria-hidden="true"></i> Age <span class="field-required">*</span>
                            </label>
                            <div class="intake-input-wrap">
                                <input id="dAge" type="number" class="intake-input" placeholder="e.g. 28"
                                       min="12" max="60"
                                       value="<?= htmlspecialchars((string)($profile['age'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <span class="intake-unit">yrs</span>
                            </div>
                        </div>
                        <div class="intake-field">
                            <label class="intake-label" for="dHeight">
                                <i class="fas fa-ruler" aria-hidden="true"></i> Height <span class="field-required">*</span>
                            </label>
                            <div class="intake-input-wrap">
                                <input id="dHeight" type="number" class="intake-input" placeholder="e.g. 160"
                                       min="60" max="250" step="0.1"
                                       value="<?= htmlspecialchars((string)($profile['height'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <select id="dHeightUnit" class="intake-unit-sel" aria-label="Height unit">
                                    <option value="cm" <?= ($profile['height_unit'] ?? 'cm') === 'cm' ? 'selected' : '' ?>>cm</option>
                                    <option value="ft" <?= ($profile['height_unit'] ?? '') === 'ft'  ? 'selected' : '' ?>>ft</option>
                                </select>
                            </div>
                        </div>
                        <div class="intake-field">
                            <label class="intake-label" for="dWeight">
                                <i class="fas fa-weight-scale" aria-hidden="true"></i> Weight <span class="field-required">*</span>
                            </label>
                            <div class="intake-input-wrap">
                                <input id="dWeight" type="number" class="intake-input" placeholder="e.g. 65"
                                       min="20" max="350" step="0.1"
                                       value="<?= htmlspecialchars((string)($profile['weight'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <select id="dWeightUnit" class="intake-unit-sel" aria-label="Weight unit">
                                    <option value="kg"  <?= ($profile['weight_unit'] ?? 'kg') === 'kg'  ? 'selected' : '' ?>>kg</option>
                                    <option value="lbs" <?= ($profile['weight_unit'] ?? '') === 'lbs'   ? 'selected' : '' ?>>lbs</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="dInfoStatus" class="intake-status" aria-live="polite" style="margin: 0.5rem 0 0"></div>
                </div>

                <div class="diag-card-foot">
                    <button class="diag-btn-primary" id="toStep2">
                        <i class="fas fa-calendar-days" aria-hidden="true"></i>
                        Next: Pregnancy Week
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </section>

            <!-- ── Step 2: Week Selector ────────────────────── -->
            <section class="diag-card" id="stepWeek" hidden aria-labelledby="step2Title">
                <div class="diag-card-header">
                    <i class="fas fa-calendar-days" aria-hidden="true"></i>
                    <div>
                        <h2 id="step2Title">What week of pregnancy are you in?</h2>
                        <p class="diag-card-sub">Move the slider or type your week number.</p>
                    </div>
                </div>

                <div class="week-selector">
                    <div class="week-display" aria-live="polite">
                        <span class="week-num" id="weekLabel">—</span>
                        <span class="week-unit">weeks</span>
                    </div>

                    <input type="range" id="weekSlider" min="1" max="40" step="1"
                           class="week-slider" aria-label="Pregnancy week (1–40)"
                           aria-valuemin="1" aria-valuemax="40">

                    <div class="week-ticks" aria-hidden="true">
                        <span>Wk 1</span>
                        <span>Wk 10</span>
                        <span>Wk 20</span>
                        <span>Wk 30</span>
                        <span>Wk 40</span>
                    </div>

                    <p class="week-hint" id="weekHint" aria-live="polite">
                        Drag the slider or enter your week below.
                    </p>

                    <div class="week-manual">
                        <label for="weekInput" class="sr-only">Enter week manually</label>
                        <input type="number" id="weekInput" min="1" max="40"
                               placeholder="e.g. 12" class="week-number-input"
                               aria-describedby="weekHint">
                        <span class="week-input-unit">/ 40 weeks</span>
                    </div>
                </div>

                <div class="diag-card-foot">
                    <button class="diag-btn-primary" id="toStep3" disabled
                            aria-label="Proceed to symptom selection">
                        <i class="fas fa-list-check" aria-hidden="true"></i>
                        Next: Check Symptoms
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </section>

            <!-- ── Step 2: Symptom Checklist ────────────────────────── -->
            <section class="diag-card" id="stepSymptoms" hidden aria-labelledby="step3Title">
                <div class="diag-card-header">
                    <i class="fas fa-list-check" aria-hidden="true"></i>
                    <div>
                        <h2 id="step3Title">Which symptoms are you experiencing?</h2>
                        <p class="diag-card-sub">Select all that apply. Severity colour-coding is shown for your trimester.</p>
                    </div>
                </div>

                <!-- Legend -->
                <div class="symptom-legend" role="note" aria-label="Colour severity legend">
                    <span class="sym-badge sym-badge-normal">
                        <span class="sym-dot sym-dot-normal" aria-hidden="true"></span>
                        Normal
                    </span>
                    <span class="sym-badge sym-badge-watch">
                        <span class="sym-dot sym-dot-watch" aria-hidden="true"></span>
                        Monitor
                    </span>
                    <span class="sym-badge sym-badge-warning">
                        <span class="sym-dot sym-dot-warning" aria-hidden="true"></span>
                        See Doctor
                    </span>
                    <span class="sym-badge sym-badge-emergency">
                        <span class="sym-dot sym-dot-emergency" aria-hidden="true"></span>
                        Emergency
                    </span>
                </div>

                <!-- Symptom groups — populated by JS -->
                <div class="symptom-groups" id="symptomGroups"></div>

                <div class="diag-card-foot diag-card-foot-split">
                    <button class="diag-btn-secondary" id="backToStep2">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
                    </button>
                    <button class="diag-btn-primary" id="toStep4">
                        <i class="fas fa-stethoscope" aria-hidden="true"></i>
                        Assess My Symptoms
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </section>

            <!-- ── Step 4: Assessment Result ─────────────────── -->
            <section class="diag-card" id="stepResult" hidden aria-labelledby="step4Title">
                <div class="diag-card-header">
                    <i class="fas fa-notes-medical" aria-hidden="true"></i>
                    <div>
                        <h2 id="step4Title">Your Assessment</h2>
                        <p class="diag-card-sub">Based on your patient info, pregnancy week, and reported symptoms.</p>
                    </div>
                </div>

                <div id="assessmentResult" role="region" aria-label="Assessment results" aria-live="polite"></div>

                <div class="diag-card-foot diag-card-foot-split">
                    <button class="diag-btn-secondary" id="backToStep3">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
                    </button>
                    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;justify-content:flex-end">
                        <button class="diag-btn-download" id="downloadReportBtn">
                            <i class="fas fa-file-arrow-down" aria-hidden="true"></i>
                            Download Report
                        </button>
                        <button class="diag-btn-primary" id="retakeBtn">
                            <i class="fas fa-rotate-right" aria-hidden="true"></i>
                            New Assessment
                        </button>
                    </div>
                </div>
            </section>

            <!-- Hidden form for Word document download -->
            <form id="reportDownloadForm" action="auth/generate_report.php" method="POST" style="display:none">
                <input type="hidden" name="intake_json">
                <input type="hidden" name="week">
                <input type="hidden" name="trimester">
                <input type="hidden" name="symptoms_json">
            </form>

        </div>
    </main>

    <!-- ── Footer ──────────────────────────────────────────────────── -->
    <footer class="home-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="brand-heart" aria-hidden="true">&#10084;</span>
                <span><?= APP_NAME ?></span>
            </div>
            <p class="footer-disclaimer">
                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                While <?= APP_NAME ?> offers easy access to helpful guidance and promotes awareness, it
                <strong>cannot replace professional medical advice</strong>. Always consult your healthcare provider.
            </p>
            <p class="footer-copy">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Built for modern maternal care.</p>
        </div>
    </footer>

    <script>
    (function () {
        'use strict';

        // ── Symptom data ──────────────────────────────────────────────
        // level:    'normal' | 'watch' | 'warning' | 'emergency'
        // trimester: [1,2,3] — trimesters where this symptom is expected/normal
        var SYMPTOMS = [
            // ── General & Hormonal ────────────────────────────────────
            { id: 'nausea',            group: 'General',              label: 'Nausea / Morning Sickness',                            level: 'normal',    trimester: [1, 2] },
            { id: 'vomiting_mild',     group: 'General',              label: 'Mild Vomiting',                                        level: 'normal',    trimester: [1] },
            { id: 'vomiting_severe',   group: 'General',              label: 'Severe Vomiting (can\'t keep fluids down)',             level: 'warning',   trimester: [] },
            { id: 'fatigue',           group: 'General',              label: 'Extreme Fatigue / Tiredness',                          level: 'normal',    trimester: [1, 2, 3] },
            { id: 'breast_tender',     group: 'General',              label: 'Breast Tenderness or Fullness',                        level: 'normal',    trimester: [1, 2] },
            { id: 'food_cravings',     group: 'General',              label: 'Food Cravings or Aversions',                           level: 'normal',    trimester: [1, 2, 3] },
            { id: 'mood_swings',       group: 'General',              label: 'Mood Swings / Emotional Changes',                      level: 'normal',    trimester: [1, 2, 3] },
            { id: 'insomnia',          group: 'General',              label: 'Insomnia / Trouble Sleeping',                          level: 'normal',    trimester: [3] },
            { id: 'fever',             group: 'General',              label: 'Fever above 38°C / 100.4°F',                           level: 'warning',   trimester: [] },
            { id: 'smell_sensitivity', group: 'General',              label: 'Heightened Sense of Smell',                            level: 'normal',    trimester: [1] },

            // ── Abdominal & Pelvic ────────────────────────────────────
            { id: 'cramping_mild',     group: 'Abdominal & Pelvic',   label: 'Mild Cramping',                                        level: 'normal',    trimester: [1] },
            { id: 'bloating',          group: 'Abdominal & Pelvic',   label: 'Bloating / Gas',                                       level: 'normal',    trimester: [1, 2] },
            { id: 'constipation',      group: 'Abdominal & Pelvic',   label: 'Constipation',                                         level: 'normal',    trimester: [1, 2, 3] },
            { id: 'heartburn',         group: 'Abdominal & Pelvic',   label: 'Heartburn / Indigestion',                              level: 'normal',    trimester: [2, 3] },
            { id: 'round_lig',         group: 'Abdominal & Pelvic',   label: 'Round Ligament Pain (sharp flank or groin pain)',      level: 'normal',    trimester: [2] },
            { id: 'braxton_mild',      group: 'Abdominal & Pelvic',   label: 'Mild Braxton-Hicks Contractions',                      level: 'normal',    trimester: [2, 3] },
            { id: 'pelvic_pressure',   group: 'Abdominal & Pelvic',   label: 'Pelvic Pressure or Heaviness',                         level: 'normal',    trimester: [3] },
            { id: 'abdo_severe',       group: 'Abdominal & Pelvic',   label: 'Severe or Persistent Abdominal Pain',                  level: 'warning',   trimester: [] },
            { id: 'preterm_contract',  group: 'Abdominal & Pelvic',   label: 'Regular Contractions Before 37 Weeks',                 level: 'warning',   trimester: [] },

            // ── Head, Eyes & Balance ──────────────────────────────────
            { id: 'headache_mild',     group: 'Head, Eyes & Balance', label: 'Mild Headaches',                                       level: 'normal',    trimester: [1, 2, 3] },
            { id: 'headache_severe',   group: 'Head, Eyes & Balance', label: 'Severe or Persistent Headache',                        level: 'warning',   trimester: [] },
            { id: 'dizziness_mild',    group: 'Head, Eyes & Balance', label: 'Mild Dizziness / Light-headedness',                    level: 'normal',    trimester: [1, 2] },
            { id: 'vision_spots',      group: 'Head, Eyes & Balance', label: 'Blurred Vision or Seeing Spots / Flashes',             level: 'warning',   trimester: [] },
            { id: 'vision_loss',       group: 'Head, Eyes & Balance', label: 'Sudden Vision Loss',                                   level: 'emergency', trimester: [] },
            { id: 'stroke_signs',      group: 'Head, Eyes & Balance', label: 'Face Drooping, Arm Weakness, or Slurred Speech',       level: 'emergency', trimester: [] },
            { id: 'seizure',           group: 'Head, Eyes & Balance', label: 'Seizure or Loss of Consciousness',                     level: 'emergency', trimester: [] },

            // ── Chest & Breathing ─────────────────────────────────────
            { id: 'breathless_mild',   group: 'Chest & Breathing',    label: 'Mild Shortness of Breath',                             level: 'normal',    trimester: [2, 3] },
            { id: 'chest_pain',        group: 'Chest & Breathing',    label: 'Chest Pain or Pressure',                               level: 'warning',   trimester: [] },
            { id: 'breathless_severe', group: 'Chest & Breathing',    label: 'Sudden Severe Difficulty Breathing',                   level: 'emergency', trimester: [] },

            // ── Skin & Swelling ───────────────────────────────────────
            { id: 'nasal_congestion',  group: 'Skin & Swelling',      label: 'Nasal Congestion / Nosebleeds',                        level: 'normal',    trimester: [1, 2, 3] },
            { id: 'skin_changes',      group: 'Skin & Swelling',      label: 'Skin Darkening (linea nigra / melasma)',                level: 'normal',    trimester: [2, 3] },
            { id: 'swelling_ankles',   group: 'Skin & Swelling',      label: 'Mild Swelling — Ankles or Feet',                       level: 'normal',    trimester: [3] },
            { id: 'face_swelling',     group: 'Skin & Swelling',      label: 'Sudden Swelling of Face or Hands',                     level: 'warning',   trimester: [] },
            { id: 'severe_itching',    group: 'Skin & Swelling',      label: 'Severe Itching All Over (especially palms and soles)', level: 'warning',   trimester: [] },
            { id: 'back_pain',         group: 'Skin & Swelling',      label: 'Back Pain',                                            level: 'normal',    trimester: [2, 3] },
            { id: 'leg_cramps',        group: 'Skin & Swelling',      label: 'Leg Cramps',                                           level: 'normal',    trimester: [2, 3] },

            // ── Bleeding & Discharge ──────────────────────────────────
            { id: 'spotting_light',    group: 'Bleeding & Discharge', label: 'Light Spotting (implantation or early pregnancy)',      level: 'normal',    trimester: [1] },
            { id: 'discharge_normal',  group: 'Bleeding & Discharge', label: 'Increased Vaginal Discharge (milky white, no odour)',   level: 'normal',    trimester: [1, 2, 3] },
            { id: 'bleeding_heavy',    group: 'Bleeding & Discharge', label: 'Heavy Vaginal Bleeding',                               level: 'warning',   trimester: [] },
            { id: 'fluid_leaking',     group: 'Bleeding & Discharge', label: 'Fluid Leaking from Vagina (possible water breaking)',   level: 'warning',   trimester: [] },
            { id: 'uncontrolled_bleed',group: 'Bleeding & Discharge', label: 'Uncontrolled or Gushing Vaginal Bleeding',             level: 'emergency', trimester: [] },

            // ── Urinary ───────────────────────────────────────────────
            { id: 'freq_urine',        group: 'Urinary',              label: 'Frequent Urination',                                   level: 'normal',    trimester: [1, 3] },
            { id: 'painful_urine',     group: 'Urinary',              label: 'Painful or Burning Urination (UTI signs)',              level: 'warning',   trimester: [] },

            // ── Fetal Movement ────────────────────────────────────────
            { id: 'quickening',        group: 'Fetal Movement',       label: 'Fetal Movement / Quickening',                          level: 'normal',    trimester: [2, 3] },
            { id: 'no_movement',       group: 'Fetal Movement',       label: 'Reduced or No Fetal Movement (after 20 weeks)',         level: 'warning',   trimester: [] },
            { id: 'colostrum',         group: 'Fetal Movement',       label: 'Colostrum Leaking from Nipples',                       level: 'normal',    trimester: [3] },
        ];

        var currentWeek      = 0;
        var currentTrimester = 0;
        var intakeData       = {};

        var trimesterInfo = [
            null,
            { num: 1, label: '1st Trimester', weeks: 'Weeks 1–13',  color: '#db2777', bg: '#fff0f6' },
            { num: 2, label: '2nd Trimester', weeks: 'Weeks 14–26', color: '#7c3aed', bg: '#f5f3ff' },
            { num: 3, label: '3rd Trimester', weeks: 'Weeks 27–40', color: '#0284c7', bg: '#f0f9ff' },
        ];

        function getTrimester(week) {
            if (week >= 1  && week <= 13) return 1;
            if (week >= 14 && week <= 26) return 2;
            if (week >= 27 && week <= 40) return 3;
            return 0;
        }

        // ── Step 1: Patient Info ────────────────────────────────────
        var infoStatusEl = document.getElementById('dInfoStatus');
        function setInfoStatus(type, msg) {
            if (!infoStatusEl) return;
            infoStatusEl.className = 'intake-status ' + type;
            infoStatusEl.innerHTML = '<i class="fas fa-circle-exclamation" aria-hidden="true"></i> ' + msg;
            infoStatusEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        document.getElementById('toStep2').addEventListener('click', function () {
            var fn  = document.getElementById('dFirstName').value.trim();
            var ln  = document.getElementById('dLastName').value.trim();
            var age = document.getElementById('dAge').value.trim();
            var h   = document.getElementById('dHeight').value.trim();
            var w   = document.getElementById('dWeight').value.trim();
            if (!fn || !ln) { setInfoStatus('error', 'Please enter your first and last name.'); return; }
            if (!age)        { setInfoStatus('error', 'Please enter your age.'); return; }
            if (!h || !w)    { setInfoStatus('error', 'Please enter your height and weight.'); return; }
            if (infoStatusEl) infoStatusEl.className = 'intake-status';
            intakeData = {
                firstName: fn, lastName: ln,
                middleInitial: document.getElementById('dMiddleInitial').value.trim().toUpperCase(),
                age: age,
                height: h,     heightUnit: document.getElementById('dHeightUnit').value,
                weight: w,     weightUnit: document.getElementById('dWeightUnit').value,
            };
            showStep(2);
        });

        // ── Week selector ─────────────────────────────────────────────────
        var slider    = document.getElementById('weekSlider');
        var numInput  = document.getElementById('weekInput');
        var weekLabel = document.getElementById('weekLabel');
        var weekHint  = document.getElementById('weekHint');
        var toStep3Btn = document.getElementById('toStep3');

        function updateWeek(week) {
            currentWeek      = week;
            currentTrimester = getTrimester(week);
            weekLabel.textContent = week;
            slider.value          = week;
            numInput.value        = week;
            toStep3Btn.disabled   = false;

            var t = trimesterInfo[currentTrimester];
            if (t) {
                weekHint.innerHTML =
                    '<span class="week-trimester-pill" style="background:' + t.bg +
                    ';color:' + t.color + ';border-color:' + t.color + '44">' +
                    '<i class="fas fa-person-pregnant" aria-hidden="true"></i> ' +
                    t.label + ' &mdash; ' + t.weeks + '</span>';
            }
        }

        slider.addEventListener('input', function () {
            updateWeek(parseInt(this.value, 10));
        });

        numInput.addEventListener('input', function () {
            var v = parseInt(this.value, 10);
            if (!isNaN(v) && v >= 1 && v <= 40) updateWeek(v);
        });

        // ── Build symptom list ────────────────────────────────────────
        function getDisplayLevel(symptom) {
            // For "normal" symptoms, check if they're expected this trimester
            if (symptom.level === 'normal' && currentTrimester > 0) {
                return symptom.trimester.includes(currentTrimester) ? 'normal' : 'watch';
            }
            return symptom.level;
        }

        function buildSymptomList() {
            var container = document.getElementById('symptomGroups');
            container.innerHTML = '';

            // Group symptoms preserving order
            var groupOrder = [];
            var groups     = {};
            SYMPTOMS.forEach(function (s) {
                if (!groups[s.group]) {
                    groups[s.group] = [];
                    groupOrder.push(s.group);
                }
                groups[s.group].push(s);
            });

            groupOrder.forEach(function (groupName) {
                var groupEl  = document.createElement('div');
                groupEl.className = 'symptom-group';

                var header = document.createElement('h3');
                header.className = 'symptom-group-title';
                var icons = {
                    'General':              'fa-heart',
                    'Abdominal & Pelvic':   'fa-circle-dot',
                    'Head, Eyes & Balance': 'fa-eye',
                    'Chest & Breathing':    'fa-lungs',
                    'Skin & Swelling':      'fa-hand-dots',
                    'Bleeding & Discharge': 'fa-droplet',
                    'Urinary':              'fa-flask',
                    'Fetal Movement':       'fa-person-pregnant',
                };
                header.innerHTML = '<i class="fas ' + (icons[groupName] || 'fa-list') + '" aria-hidden="true"></i> ' + groupName;
                groupEl.appendChild(header);

                var grid = document.createElement('div');
                grid.className = 'symptom-grid';

                groups[groupName].forEach(function (s) {
                    var displayLevel = getDisplayLevel(s);

                    var item      = document.createElement('label');
                    item.className = 'symptom-item sym-lvl-' + displayLevel;
                    item.htmlFor  = 'sym_' + s.id;

                    var cb        = document.createElement('input');
                    cb.type       = 'checkbox';
                    cb.id         = 'sym_' + s.id;
                    cb.value      = s.id;
                    cb.name       = 'symptoms';
                    cb.className  = 'symptom-checkbox';
                    cb.setAttribute('data-level', s.level);

                    var dot       = document.createElement('span');
                    dot.className = 'sym-dot sym-dot-' + displayLevel;
                    dot.setAttribute('aria-hidden', 'true');

                    var text      = document.createElement('span');
                    text.className = 'sym-label';
                    text.textContent = s.label;

                    item.appendChild(cb);
                    item.appendChild(dot);
                    item.appendChild(text);
                    grid.appendChild(item);
                });

                groupEl.appendChild(grid);
                container.appendChild(groupEl);
            });
        }

        // ── Step navigation ───────────────────────────────────────────
        var stepInfo     = document.getElementById('stepInfo');
        var stepWeek     = document.getElementById('stepWeek');
        var stepSymptoms = document.getElementById('stepSymptoms');
        var stepResult   = document.getElementById('stepResult');
        var prog1 = document.getElementById('progStep1');
        var prog2 = document.getElementById('progStep2');
        var prog3 = document.getElementById('progStep3');
        var prog4 = document.getElementById('progStep4');

        function showStep(n) {
            stepInfo.hidden     = (n !== 1);
            stepWeek.hidden     = (n !== 2);
            stepSymptoms.hidden = (n !== 3);
            stepResult.hidden   = (n !== 4);

            prog1.className = 'diag-step' + (n >= 1 ? ' active' : '') + (n > 1 ? ' completed' : '');
            prog2.className = 'diag-step' + (n >= 2 ? ' active' : '') + (n > 2 ? ' completed' : '');
            prog3.className = 'diag-step' + (n >= 3 ? ' active' : '') + (n > 3 ? ' completed' : '');
            prog4.className = 'diag-step' + (n >= 4 ? ' active' : '');

            document.querySelector('.diag-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        toStep3Btn.addEventListener('click', function () {
            buildSymptomList();
            showStep(3);
        });
        document.getElementById('backToStep2').addEventListener('click', function () { showStep(2); });
        document.getElementById('toStep4').addEventListener('click', function () {
            buildAssessment();
            showStep(4);
        });
        document.getElementById('backToStep3').addEventListener('click', function () { showStep(3); });
        document.getElementById('retakeBtn').addEventListener('click', function () {
            currentWeek = 0; currentTrimester = 0; intakeData = {};
            weekLabel.textContent = '—';
            weekHint.textContent  = 'Drag the slider or enter your week below.';
            weekHint.innerHTML    = weekHint.textContent;
            numInput.value        = '';
            toStep3Btn.disabled   = true;
            document.getElementById('symptomGroups').innerHTML = '';
            document.getElementById('assessmentResult').innerHTML = '';
            showStep(1);
        });

        // Download report
        document.getElementById('downloadReportBtn').addEventListener('click', function () {
            var checked  = Array.from(document.querySelectorAll('#symptomGroups input[type=checkbox]:checked'));
            var symptoms = checked.map(function (cb) {
                var sym = SYMPTOMS.find(function (s) { return s.id === cb.value; });
                if (!sym) return null;
                var inTrimester = sym.trimester.includes(currentTrimester);
                var level = sym.level;
                if (level === 'normal' && !inTrimester && currentTrimester > 0) level = 'watch';
                return { label: sym.label, level: level };
            }).filter(Boolean);
            var form = document.getElementById('reportDownloadForm');
            form.elements['intake_json'].value   = JSON.stringify(intakeData);
            form.elements['week'].value          = currentWeek;
            form.elements['trimester'].value     = currentTrimester;
            form.elements['symptoms_json'].value = JSON.stringify(symptoms);
            form.submit();
        });

        // ── Assessment ────────────────────────────────────────────────
        function esc(str) {
            var d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function buildAssessment() {
            var checked  = Array.from(document.querySelectorAll('#symptomGroups input[type=checkbox]:checked'));
            var result   = document.getElementById('assessmentResult');
            result.innerHTML = '';

            if (checked.length === 0) {
                result.innerHTML =
                    '<div class="assess-block assess-clean">' +
                    '<i class="fas fa-circle-check" aria-hidden="true"></i>' +
                    '<strong>No symptoms selected</strong>' +
                    '<p>You haven\'t checked any symptoms — that\'s great! Keep attending your scheduled prenatal appointments.</p>' +
                    '</div>';
                return;
            }

            var emergencies = [], warnings = [], watches = [], normals = [];

            checked.forEach(function (cb) {
                var symptom = SYMPTOMS.find(function (s) { return s.id === cb.value; });
                if (!symptom) return;

                var inTrimester = symptom.trimester.includes(currentTrimester);
                var level = symptom.level;
                if (level === 'normal' && !inTrimester && currentTrimester > 0) level = 'watch';

                if      (level === 'emergency') emergencies.push(symptom);
                else if (level === 'warning')   warnings.push(symptom);
                else if (level === 'watch')      watches.push(symptom);
                else                             normals.push(symptom);
            });

            var t      = trimesterInfo[currentTrimester];
            var tLabel = t ? t.label : '';
            var tColor = t ? t.color : '#555';
            var tBg    = t ? t.bg    : '#f3f4f6';

            // Summary row
            result.insertAdjacentHTML('beforeend',
                '<div class="assess-summary-row">' +
                '<span class="assess-trim-tag" style="background:' + tBg + ';color:' + tColor + ';border-color:' + tColor + '44">' +
                '<i class="fas fa-baby" aria-hidden="true"></i> Week ' + currentWeek + ' &mdash; ' + esc(tLabel) + '</span>' +
                '<span class="assess-count">' + checked.length + ' symptom' + (checked.length !== 1 ? 's' : '') + ' checked</span>' +
                '</div>'
            );

            // ── Emergency ──
            if (emergencies.length) {
                var html = '<div class="assess-block assess-emergency">' +
                    '<div class="assess-block-hdr"><i class="fas fa-circle-exclamation" aria-hidden="true"></i>' +
                    '<strong>Emergency &mdash; Call 911 Immediately</strong></div>' +
                    '<p>You have reported one or more emergency symptoms. Call emergency services or go to the nearest hospital <strong>right now</strong>. Do not drive yourself.</p>' +
                    '<ul class="assess-list">';
                emergencies.forEach(function (s) {
                    html += '<li><i class="fas fa-diamond" aria-hidden="true"></i>' + esc(s.label) + '</li>';
                });
                html += '</ul><a href="tel:911" class="assess-call-btn"><i class="fas fa-phone-volume" aria-hidden="true"></i> Call 911</a></div>';
                result.insertAdjacentHTML('beforeend', html);
            }

            // ── See Doctor ──
            if (warnings.length) {
                var html = '<div class="assess-block assess-warning">' +
                    '<div class="assess-block-hdr"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i>' +
                    '<strong>See Your Doctor or Midwife Today</strong></div>' +
                    '<p>These symptoms require prompt medical evaluation. Please contact your OB/GYN or go to a clinic today.</p>' +
                    '<ul class="assess-list">';
                warnings.forEach(function (s) {
                    html += '<li><i class="fas fa-circle-dot" aria-hidden="true"></i>' + esc(s.label) + '</li>';
                });
                html += '</ul></div>';
                result.insertAdjacentHTML('beforeend', html);
            }

            // ── Monitor ──
            if (watches.length) {
                var html = '<div class="assess-block assess-watch">' +
                    '<div class="assess-block-hdr"><i class="fas fa-eye" aria-hidden="true"></i>' +
                    '<strong>Worth Mentioning at Your Next Appointment</strong></div>' +
                    '<p>These symptoms are common in other trimesters but not typically expected during your current <strong>' + esc(tLabel) + '</strong>. Mention them at your next prenatal visit.</p>' +
                    '<ul class="assess-list">';
                watches.forEach(function (s) {
                    html += '<li><i class="fas fa-circle-dot" aria-hidden="true"></i>' + esc(s.label) + '</li>';
                });
                html += '</ul></div>';
                result.insertAdjacentHTML('beforeend', html);
            }

            // ── Normal ──
            if (normals.length) {
                var html = '<div class="assess-block assess-normal">' +
                    '<div class="assess-block-hdr"><i class="fas fa-circle-check" aria-hidden="true"></i>' +
                    '<strong>Expected for ' + esc(tLabel) + '</strong></div>' +
                    '<p>These are common, expected symptoms for your trimester. They are generally not cause for concern, but continue to discuss them at your regular prenatal check-ups.</p>' +
                    '<ul class="assess-list">';
                normals.forEach(function (s) {
                    html += '<li><i class="fas fa-check" aria-hidden="true"></i>' + esc(s.label) + '</li>';
                });
                html += '</ul></div>';
                result.insertAdjacentHTML('beforeend', html);
            }

            // Closing note
            result.insertAdjacentHTML('beforeend',
                '<div class="assess-footer-note">' +
                '<i class="fas fa-stethoscope" aria-hidden="true"></i>' +
                '<p>This self-assessment is for educational purposes only. The findings above do not constitute a medical diagnosis. Always keep your scheduled prenatal appointments and consult a qualified healthcare provider before acting on any of these results.</p>' +
                '</div>'
            );
        }

        // ── Theme & dropdown (shared pattern) ────────────────────────
        (function () {
            var KEY  = 'pregnatrack_theme';
            var html = document.documentElement;
            var iconDark  = document.getElementById('themeIconDark');
            var iconLight = document.getElementById('themeIconLight');
            var pill      = document.getElementById('themePill');
            var label     = document.getElementById('dropdownThemeLabel');

            function applyTheme(dark) {
                dark ? html.setAttribute('data-theme', 'dark') : html.removeAttribute('data-theme');
                if (iconDark)  iconDark.hidden  =  dark;
                if (iconLight) iconLight.hidden = !dark;
                if (pill)  pill.textContent  = dark ? 'ON'  : 'OFF';
                if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
            }
            function toggleTheme() {
                var next = html.getAttribute('data-theme') !== 'dark';
                localStorage.setItem(KEY, next ? 'dark' : 'light');
                applyTheme(next);
            }
            var saved = localStorage.getItem(KEY);
            applyTheme(saved ? saved === 'dark' : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));

            var hBtn = document.getElementById('themeToggle');
            var dBtn = document.getElementById('dropdownThemeToggle');
            if (hBtn) hBtn.addEventListener('click', toggleTheme);
            if (dBtn) dBtn.addEventListener('click', toggleTheme);

            // Dropdown
            var trigger  = document.getElementById('userMenuTrigger');
            var dropdown = document.getElementById('userDropdown');
            var menu     = document.getElementById('userMenu');
            if (trigger && dropdown) {
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var open = !dropdown.hidden;
                    dropdown.hidden = open;
                    trigger.setAttribute('aria-expanded', String(!open));
                    if (!open) dropdown.style.animation = 'dropdownIn 0.2s cubic-bezier(0.4,0,0.2,1)';
                });
                document.addEventListener('click', function (e) {
                    if (menu && !menu.contains(e.target)) {
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

    })();
    </script>
</body>
</html>
