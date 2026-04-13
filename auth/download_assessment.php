<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user = $_SESSION['user'];
$userId = (string)($user['id'] ?? '');

$assessmentHistory = [];
foreach (loadUsers() as $u) {
    if (($u['id'] ?? '') === $userId) {
        $assessmentHistory = array_reverse($u['assessment_history'] ?? []);
        break;
    }
}

if (empty($assessmentHistory)) {
    header('Location: ' . BASE_URL . '/dashboard.php#assessment-history');
    exit;
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function bmiFromIntake(array $intake): array {
    $height = (float)($intake['height'] ?? 0);
    $weight = (float)($intake['weight'] ?? 0);
    $heightUnit = (string)($intake['height_unit'] ?? 'cm');
    $weightUnit = (string)($intake['weight_unit'] ?? 'kg');

    $heightMeters = $heightUnit === 'ft' ? $height * 0.3048 : $height / 100;
    $weightKg = $weightUnit === 'lbs' ? $weight * 0.453592 : $weight;
    $bmi = $heightMeters > 0 && $weightKg > 0 ? round($weightKg / ($heightMeters * $heightMeters), 1) : null;

    if ($bmi === null) {
        return ['value' => null, 'label' => 'Unavailable', 'class' => 'bmi-muted'];
    }
    if ($bmi < 18.5) {
        return ['value' => $bmi, 'label' => 'Underweight', 'class' => 'bmi-underweight'];
    }
    if ($bmi < 25) {
        return ['value' => $bmi, 'label' => 'Normal', 'class' => 'bmi-normal'];
    }
    if ($bmi < 30) {
        return ['value' => $bmi, 'label' => 'Overweight', 'class' => 'bmi-overweight'];
    }
    return ['value' => $bmi, 'label' => 'Obese', 'class' => 'bmi-obese'];
}

function riskLabel(string $risk): string {
    $risk = strtolower($risk);
    if ($risk === 'emergency') {
        return 'Emergency';
    }
    if ($risk === 'warning') {
        return 'See Doctor';
    }
    if ($risk === 'watch') {
        return 'Monitor';
    }
    return 'Normal';
}

$target = null;
$id = trim((string)($_GET['id'] ?? ''));
if ($id !== '') {
    foreach ($assessmentHistory as $assessment) {
        if ((string)($assessment['id'] ?? '') === $id) {
            $target = $assessment;
            break;
        }
    }
}

if ($target === null) {
    $entryParam = isset($_GET['entry']) ? (string)$_GET['entry'] : '';
    if ($entryParam !== '' && ctype_digit($entryParam)) {
        $entryIndex = (int)$entryParam;
        if (isset($assessmentHistory[$entryIndex])) {
            $target = $assessmentHistory[$entryIndex];
        }
    }
}

if ($target === null) {
    $target = $assessmentHistory[0];
}

$savedTs = strtotime((string)($target['saved_at'] ?? '')) ?: time();
$savedAt = date('j F Y, g:i A', $savedTs);

$week = (int)($target['week'] ?? 0);
$trimesterLabel = trim((string)($target['trimester_label'] ?? ''));
if ($trimesterLabel === '') {
    $trimester = (int)($target['trimester'] ?? 0);
    $trimesterLabel = $trimester === 1 ? '1st Trimester' : ($trimester === 2 ? '2nd Trimester' : '3rd Trimester');
}

$overallRisk = strtolower((string)($target['overall_level'] ?? 'normal'));
$counts = is_array($target['counts'] ?? null) ? $target['counts'] : [];
$symptoms = is_array($target['symptoms'] ?? null) ? $target['symptoms'] : [];
$totalSymptoms = (int)($counts['total'] ?? count($symptoms));

$intake = is_array($target['intake'] ?? null) ? $target['intake'] : [];
$patientName = trim((string)($intake['name'] ?? ''));
if ($patientName === '') {
    $patientName = (string)($user['name'] ?? 'Patient');
}

$age = (int)($intake['age'] ?? 0);
$height = (float)($intake['height'] ?? 0);
$heightUnit = (string)($intake['height_unit'] ?? 'cm');
$weight = (float)($intake['weight'] ?? 0);
$weightUnit = (string)($intake['weight_unit'] ?? 'kg');
$bmiData = bmiFromIntake($intake);

$safeName = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $patientName);
$filename = 'PregnaTrack_Assessment_' . str_replace(' ', '_', trim($safeName)) . '_' . date('Ymd', $savedTs) . '.doc';

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
<title>PregnaTrack Assessment Report</title>
<!--[if gte mso 9]>
<xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom></w:WordDocument></xml>
<![endif]-->
<style>
  body { font-family: Calibri, 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #1f2937; margin: 0; padding: 0; }
  .doc-page { width: 690px; margin: 0 auto; padding: 34pt 46pt; }
  .doc-header { border-bottom: 3pt solid #ec4899; padding-bottom: 12pt; margin-bottom: 18pt; }
  .brand { font-size: 22pt; font-weight: 700; color: #ec4899; }
  .sub { font-size: 9pt; color: #6b7280; }
  .title { font-size: 16pt; font-weight: 700; margin: 16pt 0 2pt; }
  .meta { font-size: 9pt; color: #6b7280; margin-bottom: 10pt; }
  .section { font-size: 8pt; text-transform: uppercase; letter-spacing: 1.1pt; color: #9333ea; margin-top: 18pt; margin-bottom: 6pt; border-bottom: 1pt solid #f3e8ff; padding-bottom: 3pt; font-weight: 700; }
  .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 10pt; }
  .stats-table td { border: 1pt solid #f3e8ff; padding: 6pt 8pt; font-size: 9.6pt; vertical-align: top; }
  .stats-table td:first-child { width: 30%; font-weight: 700; color: #7c3aed; }
  .risk { font-weight: 700; }
  .risk-normal { color: #166534; }
  .risk-watch { color: #1d4ed8; }
  .risk-warning { color: #9a3412; }
  .risk-emergency { color: #9f1239; }
  .bmi-normal { color: #166534; font-weight: 700; }
  .bmi-underweight { color: #1e3a8a; font-weight: 700; }
  .bmi-overweight { color: #9a3412; font-weight: 700; }
  .bmi-obese { color: #9f1239; font-weight: 700; }
  .bmi-muted { color: #6b7280; font-weight: 700; }
  .sym-table { width: 100%; border-collapse: collapse; }
  .sym-table th { background: #fdf2f8; color: #9d174d; border: 1pt solid #fbcfe8; font-size: 9pt; text-align: left; padding: 6pt 8pt; }
  .sym-table td { border: 1pt solid #e5e7eb; font-size: 9.5pt; padding: 6pt 8pt; }
  .disclaimer { margin-top: 18pt; background: #fffbeb; border: 1pt solid #fde68a; border-radius: 6pt; padding: 9pt 11pt; font-size: 8.5pt; color: #92400e; }
</style>
</head>
<body>
<div class="doc-page">
  <div class="doc-header">
    <div class="brand">&#10084; PregnaTrack</div>
    <div class="sub">Maternal Health Companion</div>
  </div>

  <div class="title">Assessment History Record</div>
  <div class="meta">Generated on <?= e(date('j F Y, g:i A')) ?></div>

  <div class="section">Patient Overview</div>
  <table class="stats-table">
    <tr><td>Patient Name</td><td><?= e($patientName) ?></td></tr>
    <tr><td>Assessment Date</td><td><?= e($savedAt) ?></td></tr>
    <tr><td>Pregnancy Week</td><td><?= $week > 0 ? $week : '—' ?></td></tr>
    <tr><td>Trimester</td><td><?= e($trimesterLabel) ?></td></tr>
    <tr><td>Age</td><td><?= $age > 0 ? $age . ' years old' : '—' ?></td></tr>
    <tr><td>Height</td><td><?= $height > 0 ? e((string)$height) . ' ' . e($heightUnit) : '—' ?></td></tr>
    <tr><td>Weight</td><td><?= $weight > 0 ? e((string)$weight) . ' ' . e($weightUnit) : '—' ?></td></tr>
    <tr>
      <td>BMI</td>
      <td>
        <?php if ($bmiData['value'] !== null): ?>
          <span class="<?= e($bmiData['class']) ?>"><?= number_format((float)$bmiData['value'], 1) ?> (<?= e($bmiData['label']) ?>)</span>
        <?php else: ?>
          <span class="bmi-muted">Unavailable</span>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <div class="section">Assessment Summary</div>
  <table class="stats-table">
    <tr>
      <td>Overall Risk</td>
      <td>
        <span class="risk risk-<?= e($overallRisk) ?>"><?= e(riskLabel($overallRisk)) ?></span>
      </td>
    </tr>
    <tr><td>Total Symptoms</td><td><?= $totalSymptoms ?></td></tr>
    <tr><td>Warning Symptoms</td><td><?= (int)($counts['warning'] ?? 0) ?></td></tr>
    <tr><td>Emergency Symptoms</td><td><?= (int)($counts['emergency'] ?? 0) ?></td></tr>
    <tr><td>Watch Symptoms</td><td><?= (int)($counts['watch'] ?? 0) ?></td></tr>
    <tr><td>Normal Symptoms</td><td><?= (int)($counts['normal'] ?? 0) ?></td></tr>
  </table>

  <div class="section">Selected Symptoms</div>
  <?php if (!empty($symptoms)): ?>
    <table class="sym-table">
      <tr>
        <th style="width: 70%;">Symptom</th>
        <th style="width: 30%;">Level</th>
      </tr>
      <?php foreach ($symptoms as $symptom): ?>
      <tr>
        <td><?= e((string)($symptom['label'] ?? '')) ?></td>
        <td><?= e(ucfirst((string)($symptom['level'] ?? 'normal'))) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No symptoms were saved for this assessment.</p>
  <?php endif; ?>

  <div class="disclaimer">
    This report supports self-monitoring and is not a final clinical diagnosis. Seek immediate medical care for severe symptoms.
  </div>
</div>
</body>
</html>