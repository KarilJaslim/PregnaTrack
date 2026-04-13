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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Finder &mdash; <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="assets/img/pregna-logo.png">
    <link rel="apple-touch-icon" href="assets/img/pregna-logo.png">
    <script>(function(){var s=localStorage.getItem('pregnatrack_theme');var p=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;if(s?s==='dark':p)document.documentElement.setAttribute('data-theme','dark');})()</script>
    <link rel="stylesheet" href="assets/css/style.css?v=6">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
</head>
<body class="home-body">

    <div class="home-blob blob-a" aria-hidden="true"></div>
    <div class="home-blob blob-b" aria-hidden="true"></div>
    <div class="home-blob blob-c" aria-hidden="true"></div>

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

            <a href="diagnose.php" class="btn-nav-outline nav-page-link">Self-Diagnose</a>
            <a href="dashboard.php" class="btn-nav-outline nav-page-link">Dashboard</a>

            <!-- User dropdown -->
            <div class="user-menu" id="userMenu">
                <button class="user-menu-trigger" id="userMenuTrigger"
                        aria-haspopup="true" aria-expanded="false" aria-controls="userDropdown">
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
                        <i class="fas fa-house" aria-hidden="true"></i> Home
                    </a>
                    <a href="dashboard.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-gauge-high" aria-hidden="true"></i> Dashboard
                    </a>
                    <a href="diagnose.php" class="dropdown-item" role="menuitem">
                        <i class="fas fa-stethoscope" aria-hidden="true"></i> Self-Diagnose
                    </a>
                    <a href="hospitals.php" class="dropdown-item dropdown-item-active" role="menuitem">
                        <i class="fas fa-hospital" aria-hidden="true"></i> Hospital Finder
                    </a>
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item dropdown-theme-row" id="dropdownThemeToggle" role="menuitem" type="button">
                        <i class="fas fa-circle-half-stroke" aria-hidden="true"></i>
                        <span id="dropdownThemeLabel">Dark Mode</span>
                        <span class="theme-pill" id="themePill">OFF</span>
                    </button>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item dropdown-item-danger" role="menuitem">
                        <i class="fas fa-right-from-bracket" aria-hidden="true"></i> Sign Out
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- ── Main ────────────────────────────────────────────────────── -->
    <main class="hosp-page">
        <div class="hosp-container">

            <!-- Page Heading -->
            <div class="diag-heading">
                <div class="diag-icon-wrap" aria-hidden="true" style="background:linear-gradient(135deg,#ec4899,#a855f7)">
                    <i class="fas fa-hospital"></i>
                </div>
                <div>
                    <h1 class="diag-title">Zamboanga City Hospital Finder</h1>
                    <p class="diag-desc">
                        Select your barangay to find the nearest maternal care hospitals.
                        Hospitals are also listed from <strong>lowest to highest cost</strong>
                        to help you plan ahead.
                    </p>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="disclaimer-ribbon" role="note" aria-label="Disclaimer">
                <i class="fas fa-circle-info" aria-hidden="true"></i>
                <span>
                    Hospital information is for reference only. Always call ahead to confirm
                    availability, hours, and current fees. For life-threatening emergencies,
                    call <strong>911</strong> immediately.
                </span>
            </div>

            <!-- ── Barangay Selector Card ──────────────────────────── -->
            <div class="hosp-selector-card">
                <div class="hosp-selector-inner">
                    <label class="hosp-selector-label" for="barangaySelect">
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        Select Your Barangay
                    </label>
                    <div class="hosp-selector-wrap">
                        <select id="barangaySelect" class="hosp-select" aria-label="Select barangay">
                            <option value="">— Choose your barangay —</option>
                            <optgroup label="Barangays">
                                <?php
                                $named = [
                                    'Ayala','Baliwasan','Baluno','Bolong','Cabaluay','Calabasa',
                                    'Calarian','Camino Nuevo','Campo Islam','Canelar','Capisan',
                                    'Cawit','Culianan','Curuan','Dita','Don Basilio','Dulian',
                                    'Guiwan','Kasanyangan','La Paz','Labuan','Limpapa',
                                    'Lunzuran','Maasin','Mampang','Manicahan','Mariki',
                                    'Mercedes','Motosawa','Municipio','Pamucutan','Pangulayan',
                                    'Pasonanca','Pasobolong','Patalon','Perez','Putik',
                                    'Quiniput','Recodo','Rio Hondo','Sangali',
                                    'San Jose Cawa-Cawa','San Jose Gusu','San Ramon','San Roque',
                                    'Sinubung','Sinunoc','Tagasilay','Talon-Talon',
                                    'Taluksangay','Tetuan','Tictapul','Tigbalabag','Tigtabon',
                                    'Tolosa','Tugbungan','Tumaga','Turno','Vitali','Zambowood',
                                ];
                                foreach ($named as $n): ?>
                                    <option value="<?= htmlspecialchars($n, ENT_QUOTES) ?>"><?= htmlspecialchars($n, ENT_QUOTES) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                        <i class="fas fa-chevron-down hosp-select-caret" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <!-- ── Nearest Hospitals (shown after selection) ────────── -->
            <div id="nearestSection" hidden>
                <div class="hosp-section-hdr">
                    <i class="fas fa-location-crosshairs" aria-hidden="true"></i>
                    <div>
                        <h2>Nearest Hospitals to <span id="nearestBarangayName"></span></h2>
                        <p>Based on your barangay's location within Zamboanga City</p>
                    </div>
                </div>
                <div class="hosp-grid" id="nearestGrid"></div>
            </div>

            <!-- ── All Hospitals by Cost ──────────────────────────── -->
            <div class="hosp-section-hdr" style="margin-top:2.5rem">
                <i class="fas fa-peso-sign" aria-hidden="true"></i>
                <div>
                    <h2>All Hospitals &mdash; Lowest to Highest Cost</h2>
                    <p>All maternal care hospitals available in Zamboanga City</p>
                </div>
            </div>
            <div class="hosp-grid" id="allHospitalsGrid"></div>

        </div>
    </main>

    <footer class="home-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="brand-heart" aria-hidden="true">&#10084;</span>
                <span><?= APP_NAME ?></span>
            </div>
            <p class="footer-disclaimer">
                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                Hospital information is provided for reference only and may change without notice.
                Always verify directly with the hospital. <?= APP_NAME ?> is not liable for any
                decisions made based on this information.
            </p>
            <p class="footer-copy">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Built for modern maternal care.</p>
        </div>
    </footer>

    <script>
    (function () {
        'use strict';

        // ── Hospital Data ──────────────────────────────────────────────
        // costTier: 1 = free/minimal, 2 = affordable, 3 = mid-range, 4 = higher-end
        // zone: proximity cluster (A=city center, B=north, C=south, D=inland east, E=west)
        var HOSPITALS = [
            {
                id: 'zcmc',
                name: 'Zamboanga City Medical Center (ZCMC)',
                type: 'City Government Hospital',
                level: 'Level 3',
                address: 'Mayor Jaldon Street, Zamboanga City',
                phone: '(062) 991-0011',
                costTier: 1,
                costLabel: 'Free / Minimal Cost',
                costPesos: '₱',
                costNote: 'Free for indigent patients. PhilHealth, PCSO & DSWD accepted.',
                services: ['Obstetrics & Gynecology','Prenatal Care','Labor & Delivery Room','NICU','Emergency OB'],
                emergency: true,
                priorityZones: ['A','B','C','E'],
            },
            {
                id: 'wmmc',
                name: 'Western Mindanao Medical Center (WMMC)',
                type: 'DOH Regional Hospital',
                level: 'Level 4',
                address: 'Veterans Avenue, Zamboanga City',
                phone: '(062) 991-2489',
                costTier: 1,
                costLabel: 'Free / Minimal Cost',
                costPesos: '₱',
                costNote: 'National government hospital. Free for indigent, PhilHealth & PCB accredited.',
                services: ['High-Risk Obstetrics','Prenatal Care','Labor & Delivery','Level 3 NICU','Maternal-Fetal Medicine','Emergency'],
                emergency: true,
                priorityZones: ['A','E','B'],
            },
            {
                id: 'brent',
                name: 'Brent Hospital and Colleges',
                type: 'Private Hospital',
                level: 'Level 3',
                address: 'Jose Rizal Avenue, Zamboanga City',
                phone: '(062) 991-3990',
                costTier: 2,
                costLabel: 'Affordable',
                costPesos: '₱₱',
                costNote: 'Private hospital. PhilHealth, most HMOs & personal payment accepted.',
                services: ['Obstetrics & Gynecology','Prenatal Care','Labor & Delivery','NICU','Emergency'],
                emergency: true,
                priorityZones: ['A','B','C','D','E'],
            },
            {
                id: 'adventist',
                name: 'Zamboanga Adventist Hospital',
                type: 'Private — Mission Hospital',
                level: 'Level 2',
                address: 'Zamboanga City',
                phone: '(062) 992-2700',
                costTier: 3,
                costLabel: 'Mid-Range',
                costPesos: '₱₱₱',
                costNote: 'Private mission hospital. PhilHealth & HMO plans accepted.',
                services: ['Obstetrics & Gynecology','Prenatal Consultations','Normal Delivery','Postpartum Care'],
                emergency: false,
                priorityZones: ['C','D','A'],
            },
            {
                id: 'zcsh',
                name: 'Zamboanga City Specialist Hospital',
                type: 'Private Specialist Hospital',
                level: 'Level 2',
                address: 'Zamboanga City',
                phone: '(062) 993-5000',
                costTier: 4,
                costLabel: 'Higher-End',
                costPesos: '₱₱₱₱',
                costNote: 'Private specialist hospital. HMO preferred. Higher amenities & private rooms.',
                services: ['Obstetrics & Gynecology','Maternal-Fetal Medicine','Prenatal Ultrasound','Planned Delivery','Private Rooms'],
                emergency: true,
                priorityZones: ['B','D','A'],
            },
        ];

        // ── Barangay → Zone mapping ────────────────────────────────────
        var BARANGAY_ZONE = {};

        // Named barangay zones
        var zoneA = ['Baliwasan','Canelar','Guiwan','Lunzuran','Mariki','Mercedes','Municipio',
                     'Rio Hondo','San Jose Gusu','San Roque','Zambowood','Camino Nuevo'];
        var zoneB = ['Cabaluay','Dita','Don Basilio','Dulian','Mampang','Patalon','Putik',
                     'San Ramon','Tetuan','Tictapul','Tigbalabag','Tigtabon','Tugbungan','Tumaga'];
        var zoneC = ['Calarian','Cawit','Limpapa','San Jose Cawa-Cawa','Sangali',
                     'Talon-Talon','Taluksangay','Pasobolong'];
        var zoneD = ['Bolong','Calabasa','Culianan','Curuan','La Paz','Maasin','Manicahan',
                     'Motosawa','Pamucutan','Pangulayan','Pasonanca','Recodo','Sinubung',
                     'Sinunoc','Tagasilay','Vitali'];
        var zoneE = ['Ayala','Baluno','Campo Islam','Capisan','Kasanyangan','Labuan',
                     'Perez','Quiniput','Tolosa','Turno'];

        zoneA.forEach(function(b){ BARANGAY_ZONE[b] = 'A'; });
        zoneB.forEach(function(b){ BARANGAY_ZONE[b] = 'B'; });
        zoneC.forEach(function(b){ BARANGAY_ZONE[b] = 'C'; });
        zoneD.forEach(function(b){ BARANGAY_ZONE[b] = 'D'; });
        zoneE.forEach(function(b){ BARANGAY_ZONE[b] = 'E'; });

        // Fallback for any unmapped barangay
        function getZone(barangay) {
            return BARANGAY_ZONE[barangay] || 'A';
        }

        // ── Get ordered hospital list for a zone ───────────────────────
        function getHospitalsByZone(zone) {
            // Sort: hospitals where zone is in their priorityZones come first,
            // ordered by their index in priorityZones (lower index = nearer)
            return HOSPITALS.slice().sort(function(a, b) {
                var ai = a.priorityZones.indexOf(zone);
                var bi = b.priorityZones.indexOf(zone);
                if (ai === -1) ai = 99;
                if (bi === -1) bi = 99;
                if (ai !== bi) return ai - bi;
                return a.costTier - b.costTier;
            });
        }

        // ── Build hospital card HTML ──────────────────────────────────
        function costColor(tier) {
            return ['','#15803d','#b45309','#c2410c','#9333ea'][tier] || '#555';
        }
        function costBg(tier) {
            return ['','#f0fdf4','#fffbeb','#fff7ed','#faf5ff'][tier] || '#f3f4f6';
        }
        function costBorder(tier) {
            return ['','#86efac','#fcd34d','#fdba74','#d8b4fe'][tier] || '#e5e7eb';
        }

        function buildCard(h, badge) {
            var servHtml = h.services.map(function(s){
                return '<span class="hosp-service-tag"><i class="fas fa-check" aria-hidden="true"></i>' + s + '</span>';
            }).join('');

            var emergHtml = h.emergency
                ? '<span class="hosp-emrg-badge"><i class="fas fa-truck-medical" aria-hidden="true"></i>24h Emergency</span>'
                : '<span class="hosp-emrg-badge hosp-emrg-no"><i class="fas fa-clock" aria-hidden="true"></i>No 24h Emergency</span>';

            var badgeHtml = badge
                ? '<div class="hosp-nearest-badge"><i class="fas fa-location-crosshairs" aria-hidden="true"></i> ' + badge + '</div>'
                : '';

            return '<div class="hosp-card">' +
                badgeHtml +
                '<div class="hosp-card-top">' +
                    '<div class="hosp-icon-wrap" aria-hidden="true"><i class="fas fa-hospital-user"></i></div>' +
                    '<div class="hosp-card-title-group">' +
                        '<h3 class="hosp-name">' + h.name + '</h3>' +
                        '<span class="hosp-type-tag">' + h.type + '</span>' +
                        '<span class="hosp-level-tag">DOH ' + h.level + '</span>' +
                    '</div>' +
                '</div>' +

                '<div class="hosp-cost-banner" style="background:' + costBg(h.costTier) + ';border-color:' + costBorder(h.costTier) + '">' +
                    '<span class="hosp-cost-pesos" style="color:' + costColor(h.costTier) + '">' + h.costPesos + '</span>' +
                    '<div>' +
                        '<div class="hosp-cost-label" style="color:' + costColor(h.costTier) + '">' + h.costLabel + '</div>' +
                        '<div class="hosp-cost-note">' + h.costNote + '</div>' +
                    '</div>' +
                '</div>' +

                '<div class="hosp-info-row">' +
                    '<i class="fas fa-location-dot" aria-hidden="true"></i>' +
                    '<span>' + h.address + '</span>' +
                '</div>' +
                '<div class="hosp-info-row">' +
                    '<i class="fas fa-phone" aria-hidden="true"></i>' +
                    '<a href="tel:' + h.phone.replace(/[^+\d]/g,'') + '" class="hosp-phone-link">' + h.phone + '</a>' +
                '</div>' +

                '<div class="hosp-services">' + servHtml + '</div>' +

                '<div class="hosp-card-foot">' +
                    emergHtml +
                    '<a href="https://www.google.com/maps/search/' + encodeURIComponent(h.name + ' Zamboanga City') + '" target="_blank" rel="noopener noreferrer" class="hosp-map-btn">' +
                        '<i class="fas fa-map-location-dot" aria-hidden="true"></i> View on Map' +
                    '</a>' +
                '</div>' +
            '</div>';
        }

        // ── Render all hospitals sorted by cost ───────────────────────
        function renderAllByСost() {
            var sorted = HOSPITALS.slice().sort(function(a,b){ return a.costTier - b.costTier; });
            var grid = document.getElementById('allHospitalsGrid');
            grid.innerHTML = sorted.map(function(h){ return buildCard(h, null); }).join('');
        }

        // ── Barangay select handler ───────────────────────────────────
        document.getElementById('barangaySelect').addEventListener('change', function() {
            var val = this.value;
            var nearestSection = document.getElementById('nearestSection');
            var grid = document.getElementById('nearestGrid');

            if (!val) {
                nearestSection.hidden = true;
                grid.innerHTML = '';
                return;
            }

            var zone = getZone(val);
            var ordered = getHospitalsByZone(zone);
            var nearest3 = ordered.slice(0, 3);

            var badges = ['Nearest', '2nd Nearest', '3rd Nearest'];
            grid.innerHTML = nearest3.map(function(h, idx){
                return buildCard(h, badges[idx]);
            }).join('');

            document.getElementById('nearestBarangayName').textContent = val;
            nearestSection.hidden = false;
            nearestSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Render cost list on load
        renderAllByСost();

        // ── Theme & Dropdown ──────────────────────────────────────────
        (function () {
            var KEY  = 'pregnatrack_theme';
            var html = document.documentElement;
            var iconDark  = document.getElementById('themeIconDark');
            var iconLight = document.getElementById('themeIconLight');
            var pill      = document.getElementById('themePill');
            var label     = document.getElementById('dropdownThemeLabel');

            function applyTheme(dark) {
                dark ? html.setAttribute('data-theme','dark') : html.removeAttribute('data-theme');
                if (iconDark)  iconDark.hidden  =  dark;
                if (iconLight) iconLight.hidden = !dark;
                if (pill)  pill.textContent  = dark ? 'ON'  : 'OFF';
                if (label) label.textContent = dark ? 'Light Mode' : 'Dark Mode';
            }
            function toggle() {
                var next = html.getAttribute('data-theme') !== 'dark';
                localStorage.setItem(KEY, next ? 'dark' : 'light');
                applyTheme(next);
            }
            var saved = localStorage.getItem(KEY);
            applyTheme(saved ? saved === 'dark' : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));

            document.getElementById('themeToggle').addEventListener('click', toggle);
            var dBtn = document.getElementById('dropdownThemeToggle');
            if (dBtn) dBtn.addEventListener('click', toggle);

            var trigger  = document.getElementById('userMenuTrigger');
            var dropdown = document.getElementById('userDropdown');
            var menu     = document.getElementById('userMenu');
            if (trigger && dropdown) {
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var open = !dropdown.hidden;
                    dropdown.hidden = open;
                    trigger.setAttribute('aria-expanded', String(!open));
                    if (!open) dropdown.style.animation = 'dropdownIn 0.2s cubic-bezier(0.4,0,0.2,1)';
                });
                document.addEventListener('click', function(e) {
                    if (menu && !menu.contains(e.target)) {
                        dropdown.hidden = true;
                        trigger.setAttribute('aria-expanded','false');
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

    })();
    </script>
</body>
</html>
