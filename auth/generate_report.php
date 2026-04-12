<?php
/**
 * auth/generate_report.php
 * Generates a combined Pregnancy Intake + Self-Assessment Word-compatible (.doc) report.
 */
require_once dirname(__DIR__) . '/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/diagnose.php');
    exit;
}

$user = $_SESSION['user'];

// ── Input ─────────────────────────────────────────────────────────────────────
$intakeRaw   = (string)($_POST['intake_json']   ?? '{}');
$symptomsRaw = (string)($_POST['symptoms_json'] ?? '[]');
$week        = max(0, min(40, (int)($_POST['week']      ?? 0)));
$trimester   = max(0, min(3,  (int)($_POST['trimester'] ?? 0)));

$intake   = json_decode($intakeRaw,   true) ?? [];
$symptoms = json_decode($symptomsRaw, true) ?? [];

// sanitise each symptom item
$symptoms = array_values(array_filter(array_map(static function ($s) {
    if (!is_array($s)) return null;
    $allowed = ['normal', 'watch', 'warning', 'emergency'];
    return [
        'label' => substr(strip_tags((string)($s['label'] ?? '')), 0, 200),
        'level' => in_array($s['level'] ?? '', $allowed, true) ? $s['level'] : 'normal',
    ];
}, $symptoms), static fn($s) => $s !== null && $s['label'] !== ''));

// ── Helpers ───────────────────────────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function calcBmi(float $h, string $hu, float $w, string $wu): array {
    $hm  = ($hu === 'ft') ? $h * 0.3048 : $h / 100;
    $wk  = ($wu === 'lbs') ? $w * 0.453592 : $w;
    $bmi = ($hm > 0) ? round($wk / ($hm * $hm), 1) : null;
    $label = '—'; $cls = '';
    if ($bmi !== null) {
        if      ($bmi < 18.5) { $label = 'Underweight'; $cls = 'bmi-underweight'; }
        elseif  ($bmi < 25.0) { $label = 'Normal weight'; $cls = 'bmi-normal'; }
        elseif  ($bmi < 30.0) { $label = 'Overweight';  $cls = 'bmi-overweight'; }
        else                  { $label = 'Obese';        $cls = 'bmi-obese'; }
    }
    return ['bmi' => $bmi, 'label' => $label, 'cls' => $cls];
}

// ── Patient details ───────────────────────────────────────────────────────────
$fn  = trim((string)($intake['firstName']     ?? ($user['given_name'] ?? '')));
$ln  = trim((string)($intake['lastName']      ?? ''));
$mi  = strtoupper(substr(trim((string)($intake['middleInitial'] ?? '')), 0, 1));
$displayName = trim($fn . ($mi ? " $mi." : '') . ($ln ? " $ln" : '')) ?: ($user['name'] ?? 'Patient');
$safeName    = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $displayName);

$age        = trim((string)($intake['age']        ?? '—'));
$height     = trim((string)($intake['height']     ?? '—'));
$heightUnit = trim((string)($intake['heightUnit'] ?? 'cm'));
$weight     = trim((string)($intake['weight']     ?? '—'));
$weightUnit = trim((string)($intake['weightUnit'] ?? 'kg'));

$bmiData = ($height !== '—' && $weight !== '—')
    ? calcBmi((float)$height, $heightUnit, (float)$weight, $weightUnit)
    : ['bmi' => null, 'label' => '—', 'cls' => ''];

$trimesterLabels = [
    1 => '1st Trimester (Weeks 1–13)',
    2 => '2nd Trimester (Weeks 14–26)',
    3 => '3rd Trimester (Weeks 27–40)',
];
$trimesterLabel = $trimesterLabels[$trimester] ?? 'Not specified';

// ── Categorise symptoms ───────────────────────────────────────────────────────
$emergencies = array_values(array_filter($symptoms, fn($s) => $s['level'] === 'emergency'));
$warnings    = array_values(array_filter($symptoms, fn($s) => $s['level'] === 'warning'));
$watches     = array_values(array_filter($symptoms, fn($s) => $s['level'] === 'watch'));
$normals     = array_values(array_filter($symptoms, fn($s) => $s['level'] === 'normal'));

// ── Output headers ────────────────────────────────────────────────────────────
$filename = 'PregnaTrack_Assessment_' . str_replace(' ', '_', trim($safeName)) . '_' . date('Ymd') . '.doc';
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
<title>PregnaTrack Assessment &mdash; <?= e($displayName) ?></title>
<!--[if gte mso 9]>
<xml>
  <w:WordDocument>
    <w:View>Print</w:View>
    <w:Zoom>100</w:Zoom>
    <w:DoNotOptimizeForBrowser/>
  </w:WordDocument>
</xml>
<![endif]-->
<style>
  /* ── Force Calibri throughout — prevents Word/WPS fallback to SimSun ── */
  @page { margin: 2.5cm 2.8cm; mso-header-margin: 1cm; mso-footer-margin: 1cm; }

  * {
    font-family: Calibri, "Calibri Light", Arial, Helvetica, sans-serif;
    mso-ascii-font-family: Calibri;
    mso-ascii-theme-font: minor-latin;
    mso-hansi-font-family: Calibri;
    mso-hansi-theme-font: minor-latin;
    mso-bidi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman"; /* stops SimSun fallback */
    mso-fareast-theme-font: minor-fareast;
    mso-font-charset: 0;
  }

  body {
    font-size: 10.5pt;
    line-height: 1.55;
    color: #1a1a2e;
    margin: 0;
    padding: 0;
    background: #ffffff;
  }

  .doc-page { width: 680px; margin: 0 auto; padding: 28pt 42pt; }

  /* ── Header band ────────────────────────────────────────────────────── */
  .doc-header {
    background: #9d174d;
    padding: 14pt 18pt 10pt;
    margin-bottom: 20pt;
    mso-element: para-border-div;
  }
  .doc-brand {
    font-size: 20pt;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3pt;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .doc-heart { color: #fda4af; }
  .doc-sub {
    font-size: 8.5pt;
    color: #fce7f3;
    margin-top: 3pt;
    letter-spacing: 0.2pt;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }

  /* ── Document title block ───────────────────────────────────────────── */
  .doc-title {
    font-size: 15pt;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6pt;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .doc-meta {
    font-size: 9pt;
    color: #374151;
    margin-bottom: 3pt;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .doc-note {
    font-size: 8pt;
    color: #6b7280;
    font-style: italic;
    margin-top: 5pt;
    margin-bottom: 0;
    padding: 5pt 8pt;
    border-left: 2.5pt solid #e5e7eb;
    background: #f9fafb;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }

  /* ── Section labels ─────────────────────────────────────────────────── */
  .section-label {
    font-size: 7.5pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5pt;
    color: #9d174d;
    background: #fff8fb;
    padding: 4pt 8pt;
    margin-top: 20pt;
    margin-bottom: 6pt;
    border-left: 3pt solid #db2777;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }

  /* ── Info tables ────────────────────────────────────────────────────── */
  .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
  .stats-table td {
    padding: 5.5pt 9pt;
    font-size: 9.5pt;
    border: 0.75pt solid #d1d5db;
    vertical-align: middle;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .stats-table tr:nth-child(odd) td { background: #fafafa; }
  .stats-table td:first-child {
    font-weight: 700;
    color: #7c3d52;
    background: #fdf2f8;
    width: 28%;
    font-size: 9pt;
    letter-spacing: 0.1pt;
  }

  .bmi-normal      { color: #16a34a; font-weight: 700; }
  .bmi-underweight { color: #2563eb; font-weight: 700; }
  .bmi-overweight  { color: #d97706; font-weight: 700; }
  .bmi-obese       { color: #dc2626; font-weight: 700; }

  /* ── Symptom table ──────────────────────────────────────────────────── */
  .sym-table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
  .sym-table th {
    background: #fdf2f8;
    color: #7c3d52;
    font-size: 8.5pt;
    font-weight: 700;
    padding: 5pt 9pt;
    text-align: left;
    border: 0.75pt solid #f0abcd;
    letter-spacing: 0.2pt;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .sym-table td {
    padding: 5pt 9pt;
    font-size: 9.5pt;
    border: 0.75pt solid #e5e7eb;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .sym-table tr:nth-child(even) td { background: #fafafa; }

  .level-normal    { color: #15803d; font-weight: 700; font-size: 8.5pt; }
  .level-watch     { color: #1d4ed8; font-weight: 700; font-size: 8.5pt; }
  .level-warning   { color: #b45309; font-weight: 700; font-size: 8.5pt; }
  .level-emergency { color: #dc2626; font-weight: 700; font-size: 8.5pt; }

  /* ── Result alert boxes ─────────────────────────────────────────────── */
  .alert-box {
    border: 0.75pt solid;
    padding: 9pt 12pt;
    margin-bottom: 10pt;
    page-break-inside: avoid;
    mso-element: para-border-div;
  }
  .alert-emergency { border-color: #fca5a5; background: #fff1f2; }
  .alert-warning   { border-color: #fcd34d; background: #fffbeb; }
  .alert-watch     { border-color: #93c5fd; background: #eff6ff; }
  .alert-normal    { border-color: #86efac; background: #f0fdf4; }
  .alert-none      { border-color: #86efac; background: #f0fdf4; }

  .alert-title {
    font-size: 10.5pt;
    font-weight: 700;
    margin-bottom: 4pt;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .alert-body {
    font-size: 9pt;
    margin: 0 0 5pt;
    color: #374151;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
  .alert-box ul { margin: 4pt 0 0; padding-left: 16pt; }
  .alert-box li {
    font-size: 9.5pt;
    margin-bottom: 2.5pt;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }

  /* ── Disclaimer ─────────────────────────────────────────────────────── */
  .disclaimer-box {
    border: 0.75pt solid #d1d5db;
    background: #f9fafb;
    padding: 8pt 12pt;
    margin-top: 24pt;
  }
  .disclaimer-box p {
    font-size: 8pt;
    color: #6b7280;
    margin: 0;
    line-height: 1.6;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }

  /* ── Footer line ────────────────────────────────────────────────────── */
  .doc-footer {
    margin-top: 18pt;
    padding-top: 6pt;
    border-top: 0.75pt solid #e5e7eb;
    font-size: 7.5pt;
    color: #9ca3af;
    text-align: center;
    mso-ascii-font-family: Calibri;
    mso-hansi-font-family: Calibri;
    mso-fareast-font-family: "Times New Roman";
  }
</style>
</head>
<body>
<div class="doc-page">

  <!-- ── Header ──────────────────────────────────────────────────────── -->
  <div class="doc-header">
    <div class="doc-brand"><span class="doc-heart">&#10084;</span> PregnaTrack</div>
    <div class="doc-sub">Pregnancy Care Companion &nbsp;&bull;&nbsp; Confidential Self-Assessment Report</div>
  </div>

  <!-- ── Title ────────────────────────────────────────────────────────── -->
  <div class="doc-title">Pregnancy Assessment Report</div>
  <div class="doc-meta">Patient: <strong><?= e($displayName) ?></strong></div>
  <div class="doc-meta">Generated: <strong><?= date('j F Y, g:i A') ?></strong> &nbsp;&bull;&nbsp; Email: <strong><?= e($user['email'] ?? '—') ?></strong></div>
  <p class="doc-note">This report combines a patient intake and self-assessment. It is for personal reference only and does not constitute a clinical diagnosis.</p>

  <!-- ── Section 1: Patient Information ──────────────────────────────── -->
  <div class="section-label">1. Patient Information</div>
  <table class="stats-table">
    <tr><td>Full Name</td><td><?= e($displayName) ?></td></tr>
    <tr><td>Age</td><td><?= $age !== '—' ? e($age) . ' years' : '—' ?></td></tr>
    <tr><td>Height</td><td><?= $height !== '—' ? e($height) . ' ' . e($heightUnit) : '—' ?></td></tr>
    <tr><td>Weight</td><td><?= $weight !== '—' ? e($weight) . ' ' . e($weightUnit) : '—' ?></td></tr>
    <tr>
      <td>Pre-pregnancy BMI</td>
      <td><?php if ($bmiData['bmi'] !== null): ?>
          <span class="<?= e($bmiData['cls']) ?>"><?= $bmiData['bmi'] ?> — <?= e($bmiData['label']) ?></span>
        <?php else: ?>—<?php endif; ?></td>
    </tr>
  </table>

  <!-- ── Section 2: Assessment Details ───────────────────────────────── -->
  <div class="section-label">2. Assessment Details</div>
  <table class="stats-table">
    <tr><td>Pregnancy Week</td><td><?= $week > 0 ? 'Week ' . $week . ' of 40' : '—' ?></td></tr>
    <tr><td>Trimester</td><td><?= e($trimesterLabel) ?></td></tr>
    <tr><td>Symptoms Checked</td><td><?= count($symptoms) ?></td></tr>
    <tr><td>Emergency Flags</td><td><?= count($emergencies) > 0 ? '<span style="color:#dc2626;font-weight:bold">' . count($emergencies) . ' emergency symptom(s) reported</span>' : '<span style="color:#15803d">None</span>' ?></td></tr>
  </table>

  <!-- ── Section 3: Symptom List ──────────────────────────────────────── -->
<?php if (!empty($symptoms)): ?>
  <div class="section-label">3. Reported Symptoms</div>
  <table class="sym-table">
    <thead>
      <tr>
        <th style="width:70%">Symptom</th>
        <th style="width:30%">Classification</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $levelLabels = [
          'normal'    => 'Normal / Expected',
          'watch'     => 'Worth Monitoring',
          'warning'   => 'See Doctor',
          'emergency' => 'EMERGENCY',
      ];
      foreach ($symptoms as $sym):
          $lvl = $sym['level'];
      ?>
      <tr>
        <td><?= e($sym['label']) ?></td>
        <td><span class="level-<?= e($lvl) ?>"><?= e($levelLabels[$lvl] ?? $lvl) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

  <!-- ── Section 4: Assessment Results ───────────────────────────────── -->
  <div class="section-label"><?= empty($symptoms) ? '3' : '4' ?>. Assessment Results</div>

<?php if (empty($symptoms)): ?>
  <div class="alert-box alert-none">
    <div class="alert-title" style="color:#15803d">&#x2713; No Symptoms Reported</div>
    <p class="alert-body">No symptoms were checked during this assessment.
    Continue attending your scheduled prenatal appointments.</p>
  </div>
<?php else: ?>
  <?php if (!empty($emergencies)): ?>
  <div class="alert-box alert-emergency">
    <div class="alert-title" style="color:#dc2626">&#x26A0; EMERGENCY — Call 911 Immediately</div>
    <p class="alert-body">You reported one or more emergency symptoms.
    Call emergency services or go to the nearest hospital <strong>right now</strong>. Do not drive yourself.</p>
    <ul>
      <?php foreach ($emergencies as $s): ?><li><?= e($s['label']) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (!empty($warnings)): ?>
  <div class="alert-box alert-warning">
    <div class="alert-title" style="color:#d97706">&#x26A0; See Your Doctor or Midwife Today</div>
    <p class="alert-body">These symptoms require prompt medical evaluation.
    Contact your OB/GYN or go to a clinic today.</p>
    <ul>
      <?php foreach ($warnings as $s): ?><li><?= e($s['label']) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (!empty($watches)): ?>
  <div class="alert-box alert-watch">
    <div class="alert-title" style="color:#1d4ed8">&#x1F441; Worth Mentioning at Your Next Appointment</div>
    <p class="alert-body">These symptoms are worth monitoring and discussing at your next prenatal visit.</p>
    <ul>
      <?php foreach ($watches as $s): ?><li><?= e($s['label']) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (!empty($normals)): ?>
  <div class="alert-box alert-normal">
    <div class="alert-title" style="color:#15803d">&#x2713; Expected for <?= e($trimesterLabel) ?></div>
    <p class="alert-body">Common, expected symptoms for your trimester.
    Generally not cause for concern &mdash; continue regular prenatal check-ups.</p>
    <ul>
      <?php foreach ($normals as $s): ?><li><?= e($s['label']) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
<?php endif; ?>

  <!-- ── Disclaimer ───────────────────────────────────────────────────── -->
  <div class="disclaimer-box">
    <p><strong>Disclaimer:</strong> This self-assessment report is for personal educational reference
    only. It does not constitute a medical diagnosis or professional medical advice. Always consult a
    qualified healthcare provider before acting on any of these findings. In an emergency, call 911
    immediately.</p>
  </div>

  <div class="doc-footer">
    PregnaTrack &bull; Generated <?= date('j F Y') ?> &bull; For personal reference only
  </div>

</div>
</body>
</html>
