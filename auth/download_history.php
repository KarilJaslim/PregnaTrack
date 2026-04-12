<?php
/**
 * auth/download_history.php
 * Generates a Microsoft Word-compatible (.doc) medical history report.
 */
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user   = $_SESSION['user'];
$userId = (string)($user['id'] ?? '');

// Load full record
$profileHistory = [];
$currentProfile = $user['profile'] ?? null;
foreach (loadUsers() as $u) {
    if (($u['id'] ?? '') === $userId) {
        $profileHistory = array_reverse($u['profile_history'] ?? []);
        if ($currentProfile === null) {
            $currentProfile = $u['profile'] ?? null;
        }
        break;
    }
}

$entries = [];
if ($currentProfile) $entries[] = ['profile' => $currentProfile, 'current' => true];
foreach ($profileHistory as $snap) $entries[] = ['profile' => $snap, 'current' => false];

// ── Helpers ───────────────────────────────────────────────────────────────────
function calcBmi(array $p): array {
    $h  = (float)($p['height'] ?? 0);
    $w  = (float)($p['weight'] ?? 0);
    $hu = $p['height_unit'] ?? 'cm';
    $wu = $p['weight_unit'] ?? 'kg';
    $hm = ($hu === 'ft') ? $h * 0.3048 : $h / 100;
    $wk = ($wu === 'lbs') ? $w * 0.453592 : $w;
    $bmi = ($hm > 0) ? round($wk / ($hm * $hm), 1) : null;
    $label = '—';
    if ($bmi !== null) {
        if      ($bmi < 18.5) $label = 'Underweight';
        elseif  ($bmi < 25.0) $label = 'Normal';
        elseif  ($bmi < 30.0) $label = 'Overweight';
        else                  $label = 'Obese';
    }
    return ['bmi' => $bmi, 'label' => $label];
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function fmtDate(string $iso): string {
    $ts = strtotime($iso);
    return $ts ? date('j F Y, g:i A', $ts) : '—';
}

// ── Patient name for filename ─────────────────────────────────────────────────
$patientName = $currentProfile['name'] ?? ($user['name'] ?? 'Patient');
$safeName    = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $patientName);

// ── Entry selection (?entry=N selects a single snapshot) ─────────────────────
$entryParam  = isset($_GET['entry']) ? (string) $_GET['entry'] : 'all';
$singleEntry = false;
if ($entryParam !== 'all' && ctype_digit($entryParam)) {
    $idx = (int) $entryParam;
    if ($idx >= 0 && isset($entries[$idx])) {
        $entries     = [$entries[$idx]];
        $singleEntry = true;
    }
}

// ── Output headers ──────────────────────────────────────────────────
if ($singleEntry && isset($entries[0])) {
    $entryTs  = strtotime($entries[0]['profile']['updated_at'] ?? '') ?: time();
    $filename = 'PregnaTrack_Record_' . str_replace(' ', '_', trim($safeName)) . '_' . date('Ymd', $entryTs) . '.doc';
} else {
    $filename = 'PregnaTrack_History_' . str_replace(' ', '_', trim($safeName)) . '_' . date('Ymd') . '.doc';
}

header('Content-Type: application/vnd.ms-word');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
?>
<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:w='urn:schemas-microsoft-com:office:word'
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
<meta charset="UTF-8">
<title>PregnaTrack Medical History — <?= e($patientName) ?></title>
<!--[if gte mso 9]>
<xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml>
<![endif]-->
<style>
  body {
    font-family: Calibri, 'Segoe UI', Arial, sans-serif;
    font-size: 11pt;
    color: #1a1a2e;
    margin: 0;
    padding: 0;
  }
  .doc-page {
    width: 680px;
    margin: 0 auto;
    padding: 36pt 48pt;
  }
  /* Cover header */
  .doc-header {
    border-bottom: 3px solid #ec4899;
    padding-bottom: 14pt;
    margin-bottom: 20pt;
  }
  .doc-brand {
    font-size: 22pt;
    font-weight: bold;
    color: #ec4899;
    letter-spacing: -0.5pt;
  }
  .doc-brand-sub {
    font-size: 9pt;
    color: #9ca3af;
    margin-top: 2pt;
  }
  .doc-title {
    font-size: 17pt;
    font-weight: bold;
    color: #1f2937;
    margin: 20pt 0 4pt;
  }
  .doc-meta {
    font-size: 9pt;
    color: #6b7280;
    margin-bottom: 6pt;
  }
  /* Section */
  .section-label {
    font-size: 8pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.2pt;
    color: #9333ea;
    margin-top: 24pt;
    margin-bottom: 6pt;
    border-bottom: 1pt solid #f3e8ff;
    padding-bottom: 3pt;
  }
  /* Entry card */
  .entry-card {
    border: 1pt solid #fce7f3;
    border-radius: 8pt;
    margin-bottom: 16pt;
    overflow: hidden;
    page-break-inside: avoid;
  }
  .entry-header {
    background: #fdf4ff;
    padding: 8pt 12pt;
    border-bottom: 1pt solid #fce7f3;
    display: table;
    width: 100%;
  }
  .entry-header-left  { display: table-cell; vertical-align: middle; }
  .entry-header-right { display: table-cell; vertical-align: middle; text-align: right; }
  .entry-date {
    font-size: 10pt;
    font-weight: bold;
    color: #374151;
  }
  .entry-time { font-size: 8.5pt; color: #9ca3af; margin-left: 6pt; }
  .badge-current {
    background: #dcfce7;
    color: #166534;
    font-size: 8pt;
    font-weight: bold;
    padding: 2pt 7pt;
    border-radius: 20pt;
    border: 1pt solid #bbf7d0;
  }
  .badge-snapshot {
    background: #f3f4f6;
    color: #6b7280;
    font-size: 8pt;
    padding: 2pt 7pt;
    border-radius: 20pt;
  }
  .entry-body { padding: 10pt 12pt 12pt; }
  /* Stats table */
  .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
  .stats-table td {
    padding: 5pt 8pt;
    font-size: 9.5pt;
    border: 1pt solid #f3e8ff;
    vertical-align: top;
  }
  .stats-table td:first-child { font-weight: bold; color: #7c3aed; width: 22%; }
  /* BMI color-coding */
  .bmi-normal      { color: #16a34a; font-weight: bold; }
  .bmi-underweight { color: #2563eb; font-weight: bold; }
  .bmi-overweight  { color: #d97706; font-weight: bold; }
  .bmi-obese       { color: #dc2626; font-weight: bold; }
  /* GTPAL table */
  .gtpal-table { width: 100%; border-collapse: collapse; margin-top: 8pt; }
  .gtpal-table th {
    background: #f5f3ff;
    color: #7c3aed;
    font-size: 9pt;
    padding: 5pt 8pt;
    text-align: center;
    border: 1pt solid #ede9fe;
    font-weight: bold;
  }
  .gtpal-table td {
    text-align: center;
    font-size: 12pt;
    font-weight: bold;
    color: #1f2937;
    padding: 6pt 8pt;
    border: 1pt solid #ede9fe;
  }
  .gtpal-sub { font-size: 7.5pt; color: #9ca3af; font-weight: normal; display: block; }
  /* First pregnancy badge */
  .first-preg-badge {
    display: inline-block;
    background: #fdf4ff;
    border: 1pt solid #f0abfc;
    color: #86198f;
    font-size: 9pt;
    font-weight: bold;
    padding: 4pt 10pt;
    border-radius: 20pt;
    margin-top: 6pt;
  }
  /* Disclaimer */
  .disclaimer {
    margin-top: 28pt;
    padding: 10pt 14pt;
    background: #fffbeb;
    border: 1pt solid #fde68a;
    border-radius: 6pt;
    font-size: 8pt;
    color: #92400e;
  }
  /* Footer */
  .doc-footer {
    margin-top: 24pt;
    padding-top: 10pt;
    border-top: 1pt solid #e5e7eb;
    font-size: 8pt;
    color: #9ca3af;
    text-align: center;
  }
  @page { margin: 1in; }
</style>
</head>
<body>
<div class="doc-page">

  <!-- Brand header -->
  <div class="doc-header">
    <div class="doc-brand">&#10084; PregnaTrack</div>
    <div class="doc-brand-sub">Maternal Health Companion</div>
  </div>

  <!-- Document title -->
  <div class="doc-title">Medical History Report</div>
  <div class="doc-meta">Patient: <strong><?= e($patientName) ?></strong></div>
  <div class="doc-meta">Email: <?= e($user['email'] ?? '—') ?></div>
  <div class="doc-meta">Report generated: <?= date('j F Y, g:i A') ?></div>
  <div class="doc-meta">Total entries: <?= count($entries) ?></div>

  <?php if (empty($entries)): ?>
    <p style="color:#9ca3af;margin-top:20pt;">No profile data has been recorded yet.</p>
  <?php else: ?>

  <div class="section-label">Profile History</div>

  <?php foreach ($entries as $e2): ?>
  <?php
    $p   = $e2['profile'];
    $ts  = strtotime($p['updated_at'] ?? '') ?: 0;
    $bmiData = calcBmi($p);
    $bmi = $bmiData['bmi'];
    $bmiLabel = $bmiData['label'];
    $bmiClass = match($bmiLabel) {
        'Normal'      => 'bmi-normal',
        'Underweight' => 'bmi-underweight',
        'Overweight'  => 'bmi-overweight',
        'Obese'       => 'bmi-obese',
        default       => ''
    };
  ?>
  <div class="entry-card">
    <div class="entry-header">
      <div class="entry-header-left">
        <span class="entry-date"><?= $ts ? date('j F Y', $ts) : '—' ?></span>
        <?php if ($ts): ?>
          <span class="entry-time"><?= date('g:i A', $ts) ?></span>
        <?php endif; ?>
      </div>
      <div class="entry-header-right">
        <?php if ($e2['current']): ?>
          <span class="badge-current">&#10003; Current Record</span>
        <?php else: ?>
          <span class="badge-snapshot">Snapshot</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="entry-body">
      <table class="stats-table">
        <tr>
          <td>Name</td>
          <td><?= e($p['name'] ?: '—') ?></td>
          <td>Age</td>
          <td><?= (int)($p['age'] ?? 0) ?> years</td>
        </tr>
        <tr>
          <td>Height</td>
          <td><?= e($p['height'] . ' ' . ($p['height_unit'] ?? '')) ?></td>
          <td>Weight</td>
          <td><?= e($p['weight'] . ' ' . ($p['weight_unit'] ?? '')) ?></td>
        </tr>
        <tr>
          <td>BMI</td>
          <td colspan="3">
            <?php if ($bmi !== null): ?>
              <span class="<?= $bmiClass ?>"><?= $bmi ?></span>
              &nbsp;— <?= e($bmiLabel) ?>
            <?php else: ?>
              — (insufficient data)
            <?php endif; ?>
          </td>
        </tr>
      </table>

      <?php if (($p['first_pregnancy'] ?? '') === 'yes'): ?>
        <div class="first-preg-badge">&#9733; First Pregnancy</div>
      <?php elseif (isset($p['gtpal_g'])): ?>
        <table class="gtpal-table">
          <thead>
            <tr>
              <th>G<span class="gtpal-sub">Gravida</span></th>
              <th>T<span class="gtpal-sub">Term</span></th>
              <th>P<span class="gtpal-sub">Preterm</span></th>
              <th>A<span class="gtpal-sub">Abortus</span></th>
              <th>L<span class="gtpal-sub">Living</span></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?= (int)($p['gtpal_g'] ?? 0) ?></td>
              <td><?= (int)($p['gtpal_t'] ?? 0) ?></td>
              <td><?= (int)($p['gtpal_p'] ?? 0) ?></td>
              <td><?= (int)($p['gtpal_a'] ?? 0) ?></td>
              <td><?= (int)($p['gtpal_l'] ?? 0) ?></td>
            </tr>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php endif; ?>

  <!-- Disclaimer -->
  <div class="disclaimer">
    <strong>&#9888; Medical Disclaimer:</strong>
    This document is generated from self-reported data entered into PregnaTrack and is intended
    for personal record-keeping purposes only. It does not constitute medical advice, diagnosis,
    or treatment. Always consult a qualified healthcare professional for medical guidance.
  </div>

  <!-- Footer -->
  <div class="doc-footer">
    PregnaTrack &mdash; Maternal Health Companion &nbsp;|&nbsp;
    Report generated <?= date('j F Y') ?> &nbsp;|&nbsp;
    <?= e($user['email'] ?? '') ?>
  </div>

</div>
</body>
</html>
