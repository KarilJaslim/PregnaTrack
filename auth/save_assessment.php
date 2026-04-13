<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

if (!isset($_SESSION['user'])) {
    jsonResponse(['ok' => false, 'message' => 'Authentication required.'], 401);
}

$userId = (string) ($_SESSION['user']['id'] ?? '');
if ($userId === '') {
    jsonResponse(['ok' => false, 'message' => 'Invalid session.'], 401);
}

$week = (int) ($_POST['week'] ?? 0);
if ($week < 1 || $week > 40) {
    jsonResponse(['ok' => false, 'message' => 'Enter a valid pregnancy week (1-40).'], 422);
}

$trimester = (int) ($_POST['trimester'] ?? 0);
if ($trimester < 1 || $trimester > 3) {
    if ($week >= 1 && $week <= 13) {
        $trimester = 1;
    } elseif ($week <= 26) {
        $trimester = 2;
    } else {
        $trimester = 3;
    }
}

$intakeRaw = (string) ($_POST['intake_json'] ?? '{}');
$intake = json_decode($intakeRaw, true);
if (!is_array($intake)) {
    $intake = [];
}

$symptomsRaw = (string) ($_POST['symptoms_json'] ?? '[]');
$decodedSymptoms = json_decode($symptomsRaw, true);
if (!is_array($decodedSymptoms)) {
    $decodedSymptoms = [];
}

$allowedLevels = ['normal', 'watch', 'warning', 'emergency'];
$symptoms = [];
foreach ($decodedSymptoms as $item) {
    if (!is_array($item)) {
        continue;
    }

    $id = preg_replace('/[^a-z0-9_\-]/i', '', (string) ($item['id'] ?? ''));
    $label = substr(strip_tags(trim((string) ($item['label'] ?? ''))), 0, 200);
    $level = strtolower(trim((string) ($item['level'] ?? 'normal')));
    if (!in_array($level, $allowedLevels, true)) {
        $level = 'normal';
    }

    if ($id === '' || $label === '') {
        continue;
    }

    $symptoms[] = [
        'id' => $id,
        'label' => $label,
        'level' => $level,
    ];
}

$counts = [
    'total' => count($symptoms),
    'emergency' => 0,
    'warning' => 0,
    'watch' => 0,
    'normal' => 0,
];

foreach ($symptoms as $symptom) {
    $level = (string) $symptom['level'];
    if (isset($counts[$level])) {
        $counts[$level]++;
    }
}

$overallLevel = 'normal';
if ($counts['emergency'] > 0) {
    $overallLevel = 'emergency';
} elseif ($counts['warning'] > 0) {
    $overallLevel = 'warning';
} elseif ($counts['watch'] > 0) {
    $overallLevel = 'watch';
}

$firstName = substr(strip_tags(trim((string) ($intake['firstName'] ?? ''))), 0, 60);
$lastName = substr(strip_tags(trim((string) ($intake['lastName'] ?? ''))), 0, 60);
$middleInitial = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', (string) ($intake['middleInitial'] ?? '')) ?? '', 0, 1));

$intakeName = trim($firstName . ' ' . ($middleInitial !== '' ? ($middleInitial . '. ') : '') . $lastName);
if ($intakeName === '') {
    $intakeName = (string) ($_SESSION['user']['name'] ?? 'User');
}

$heightUnit = in_array(($intake['heightUnit'] ?? ''), ['cm', 'ft'], true)
    ? (string) $intake['heightUnit']
    : 'cm';
$weightUnit = in_array(($intake['weightUnit'] ?? ''), ['kg', 'lbs'], true)
    ? (string) $intake['weightUnit']
    : 'kg';

$assessment = [
    'id' => 'asmt_' . bin2hex(random_bytes(8)),
    'saved_at' => date('c'),
    'week' => $week,
    'trimester' => $trimester,
    'trimester_label' => $trimester === 1 ? '1st Trimester' : ($trimester === 2 ? '2nd Trimester' : '3rd Trimester'),
    'overall_level' => $overallLevel,
    'counts' => $counts,
    'intake' => [
        'name' => $intakeName,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'middle_initial' => $middleInitial,
        'age' => (int) ($intake['age'] ?? 0),
        'height' => (float) ($intake['height'] ?? 0),
        'height_unit' => $heightUnit,
        'weight' => (float) ($intake['weight'] ?? 0),
        'weight_unit' => $weightUnit,
    ],
    'symptoms' => $symptoms,
];

$users = loadUsers();
$updated = false;

foreach ($users as &$user) {
    if (($user['id'] ?? '') === $userId) {
        $user['assessment_history'] = $user['assessment_history'] ?? [];
        $user['assessment_history'][] = $assessment;

        if (count($user['assessment_history']) > 30) {
            $user['assessment_history'] = array_slice($user['assessment_history'], -30);
        }

        $updated = true;
        break;
    }
}
unset($user);

if (!$updated) {
    $users[] = [
        'id' => $userId,
        'name' => $_SESSION['user']['name'] ?? 'User',
        'email' => $_SESSION['user']['email'] ?? '',
        'provider' => $_SESSION['user']['provider'] ?? 'google',
        'created_at' => date('c'),
        'profile_history' => [],
        'assessment_history' => [$assessment],
    ];
}

if (!saveUsers($users)) {
    jsonResponse(['ok' => false, 'message' => 'Could not save assessment. Please try again.'], 500);
}

$_SESSION['user']['last_assessment_at'] = $assessment['saved_at'];

jsonResponse([
    'ok' => true,
    'message' => 'Assessment saved successfully.',
    'assessment_id' => $assessment['id'],
]);
