<?php
/**
 * auth/save_profile.php
 * Saves the patient intake profile to the user's record in users.json.
 * Requires an active authenticated session.
 */
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

// ── Auth check ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user'])) {
    jsonResponse(['ok' => false, 'message' => 'Authentication required.'], 401);
}

$userId = (string) ($_SESSION['user']['id'] ?? '');
if ($userId === '') {
    jsonResponse(['ok' => false, 'message' => 'Invalid session.'], 401);
}

// ── Validate required fields ──────────────────────────────────────────────────
$age    = (int)   ($_POST['age']    ?? 0);
$height = (float) ($_POST['height'] ?? 0.0);
$weight = (float) ($_POST['weight'] ?? 0.0);

if ($age < 12 || $age > 60) {
    jsonResponse(['ok' => false, 'message' => 'Please enter a valid age (12–60).'], 422);
}
// Height: 50–300 cm or 1.5–9.9 ft
$heightUnit = in_array($_POST['height_unit'] ?? '', ['cm', 'ft'], true)
    ? (string) $_POST['height_unit'] : 'cm';
$heightMin = $heightUnit === 'ft' ? 1.5  : 50.0;
$heightMax = $heightUnit === 'ft' ? 9.9  : 300.0;
if ($height <= 0 || $height < $heightMin || $height > $heightMax) {
    jsonResponse(['ok' => false, 'message' => "Please enter a valid height ({$heightMin}–{$heightMax} {$heightUnit})."], 422);
}
// Weight: 20–300 kg or 44–660 lbs
$weightUnit = in_array($_POST['weight_unit'] ?? '', ['kg', 'lbs'], true)
    ? (string) $_POST['weight_unit'] : 'kg';
$weightMin = $weightUnit === 'lbs' ? 44.0  : 20.0;
$weightMax = $weightUnit === 'lbs' ? 660.0 : 300.0;
if ($weight <= 0 || $weight < $weightMin || $weight > $weightMax) {
    jsonResponse(['ok' => false, 'message' => "Please enter a valid weight ({$weightMin}–{$weightMax} {$weightUnit})."], 422);
}

$firstPregnancy = (string) ($_POST['first_pregnancy'] ?? '');
if (!in_array($firstPregnancy, ['yes', 'no'], true)) {
    jsonResponse(['ok' => false, 'message' => 'Please indicate if this is your first pregnancy.'], 422);
}

// Units already resolved during validation above

$firstName = substr(strip_tags(trim((string) ($_POST['first_name'] ?? ''))), 0, 60);
$lastName = substr(strip_tags(trim((string) ($_POST['last_name'] ?? ''))), 0, 60);
$middleInitialRaw = substr(strip_tags(trim((string) ($_POST['middle_initial'] ?? ''))), 0, 10);
$middleInitial = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $middleInitialRaw) ?? '', 0, 1));
$legacyName = substr(strip_tags(trim((string) ($_POST['name'] ?? ''))), 0, 120);

if (($firstName === '') !== ($lastName === '')) {
    jsonResponse(['ok' => false, 'message' => 'Please provide both first and last name.'], 422);
}

if ($firstName === '' && $lastName === '' && $legacyName !== '') {
    $parts = preg_split('/\s+/', $legacyName) ?: [];
    $parts = array_values(array_filter($parts, static function ($p) {
        return $p !== '';
    }));
    if (isset($parts[0])) {
        $firstName = $parts[0];
    }
    if (count($parts) >= 2) {
        $lastName = $parts[count($parts) - 1];
    }
    if ($middleInitial === '' && count($parts) >= 3) {
        $middleInitial = strtoupper(substr($parts[1], 0, 1));
    }
}

if ($firstName === '' || $lastName === '') {
    jsonResponse(['ok' => false, 'message' => 'Please enter your first and last name.'], 422);
}

$fullName = trim($firstName . ' ' . ($middleInitial !== '' ? ($middleInitial . '. ') : '') . $lastName);

// ── Build profile record ──────────────────────────────────────────────────────
$profile = [
    'name'            => $fullName,
    'first_name'      => $firstName,
    'last_name'       => $lastName,
    'middle_initial'  => $middleInitial,
    'age'             => $age,
    'height'          => $height,
    'height_unit'     => $heightUnit,
    'weight'          => $weight,
    'weight_unit'     => $weightUnit,
    'first_pregnancy' => $firstPregnancy,
    'updated_at'      => date('c'),
];

// ── GTPAL (only when not first pregnancy) ────────────────────────────────────
if ($firstPregnancy === 'no') {
    $g = min(25, max(0, (int) ($_POST['gtpal_g'] ?? 0)));
    $t = min(25, max(0, (int) ($_POST['gtpal_t'] ?? 0)));
    $p = min(25, max(0, (int) ($_POST['gtpal_p'] ?? 0)));
    $a = min(25, max(0, (int) ($_POST['gtpal_a'] ?? 0)));
    $l = min(25, max(0, (int) ($_POST['gtpal_l'] ?? 0)));

    if (($t + $p + $a) > $g) {
        jsonResponse(['ok' => false, 'message' => 'GTPAL error: T + P + A cannot exceed G (total pregnancies).'], 422);
    }
    if ($l > ($t + $p)) {
        jsonResponse(['ok' => false, 'message' => 'GTPAL error: Living children (L) cannot exceed total deliveries (T + P).'], 422);
    }

    $profile['gtpal_g'] = $g;
    $profile['gtpal_t'] = $t;
    $profile['gtpal_p'] = $p;
    $profile['gtpal_a'] = $a;
    $profile['gtpal_l'] = $l;
}

// ── Persist to users.json ────────────────────────────────────────────────────
$users   = loadUsers();
$updated = false;

foreach ($users as &$u) {
    if (($u['id'] ?? '') === $userId) {
        // Push the current profile into history before overwriting
        if (!empty($u['profile'])) {
            $u['profile_history']   = $u['profile_history'] ?? [];
            $u['profile_history'][] = $u['profile'];
            // Keep only the last 20 snapshots
            if (count($u['profile_history']) > 20) {
                $u['profile_history'] = array_slice($u['profile_history'], -20);
            }
        }
        $u['profile'] = $profile;
        $updated = true;
        break;
    }
}
unset($u);

if (!$updated) {
    // Google OAuth users may not be in users.json — insert a minimal record
    $users[] = [
        'id'              => $userId,
        'name'            => $_SESSION['user']['name']  ?? 'User',
        'email'           => $_SESSION['user']['email'] ?? '',
        'provider'        => $_SESSION['user']['provider'] ?? 'google',
        'created_at'      => date('c'),
        'profile'         => $profile,
        'profile_history' => [],
    ];
}

if (!saveUsers($users)) {
    jsonResponse(['ok' => false, 'message' => 'Could not save profile. Please try again.'], 500);
}

// ── Update session ────────────────────────────────────────────────────────────
$_SESSION['user']['profile'] = $profile;
// Reload full user record into session so history is available
foreach ($users as $u) {
    if (($u['id'] ?? '') === $userId) {
        $_SESSION['user']['profile_history'] = $u['profile_history'] ?? [];
        break;
    }
}
if ($profile['name'] !== '') {
    $_SESSION['user']['name']       = $profile['name'];
    $_SESSION['user']['given_name'] = explode(' ', $profile['name'])[0] ?? $profile['name'];
}

jsonResponse([
    'ok'       => true,
    'message'  => 'Profile saved! Starting your assessment…',
    'redirect' => BASE_URL . '/diagnose.php',
]);
