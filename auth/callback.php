<?php
/**
 * auth/callback.php
 * Google redirects here after the user grants (or denies) permission.
 * 1. Validate CSRF state
 * 2. Exchange the authorization code for an access token
 * 3. Fetch the user's profile from Google
 * 4. Store the profile in the session and redirect to the dashboard
 */
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

// ── 1. CSRF check ────────────────────────────────────────────────────────────
if (
    empty($_GET['state']) ||
    empty($_SESSION['oauth_state']) ||
    !hash_equals($_SESSION['oauth_state'], $_GET['state'])
) {
    unset($_SESSION['oauth_state']);
    header('Location: ' . BASE_URL . '/login.php?error=' . urlencode('Invalid request. Please try again.'));
    exit;
}
unset($_SESSION['oauth_state']); // one-time use

// ── 2. Handle access-denied by user ─────────────────────────────────────────
if (isset($_GET['error'])) {
    $msg = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
    header('Location: ' . BASE_URL . '/login.php?error=' . urlencode('Google sign-in was cancelled.'));
    exit;
}

if (empty($_GET['code'])) {
    header('Location: ' . BASE_URL . '/login.php?error=' . urlencode('No authorisation code received.'));
    exit;
}

// ── 3. Exchange code for access token ────────────────────────────────────────
$tokenResponse = curlPost(GOOGLE_TOKEN_URL, [
    'code'          => $_GET['code'],
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

if (empty($tokenResponse['access_token'])) {
    header('Location: ' . BASE_URL . '/login.php?error=' . urlencode('Could not obtain access token. Please try again.'));
    exit;
}

// ── 4. Fetch user profile ─────────────────────────────────────────────────────
$userInfo = curlGet(GOOGLE_USER_URL, $tokenResponse['access_token']);

if (empty($userInfo['sub'])) {
    header('Location: ' . BASE_URL . '/login.php?error=' . urlencode('Could not retrieve your Google profile. Please try again.'));
    exit;
}

// ── 5. Reconcile user record and store session ───────────────────────────────
$googleId = (string) ($userInfo['sub'] ?? '');
$googleEmail = trim((string) ($userInfo['email'] ?? ''));
$googleEmailLower = strtolower($googleEmail);
$googleName = (string) ($userInfo['name'] ?? 'User');
$googleGivenName = (string) ($userInfo['given_name'] ?? '');
$googlePicture = (string) ($userInfo['picture'] ?? '');

$users = loadUsers();
$idxById = null;
$idxByEmail = null;

foreach ($users as $i => $storedUser) {
    if ((string) ($storedUser['id'] ?? '') === $googleId) {
        $idxById = $i;
    }

    if ($googleEmailLower !== '' && strtolower((string) ($storedUser['email'] ?? '')) === $googleEmailLower) {
        $idxByEmail = $i;
    }
}

if ($idxById !== null && $idxByEmail !== null && $idxById !== $idxByEmail) {
    $primary = $users[$idxById];
    $secondary = $users[$idxByEmail];

    $primaryProfileHistory = is_array($primary['profile_history'] ?? null) ? $primary['profile_history'] : [];
    $secondaryProfileHistory = is_array($secondary['profile_history'] ?? null) ? $secondary['profile_history'] : [];
    $primary['profile_history'] = array_values(array_slice(array_merge($secondaryProfileHistory, $primaryProfileHistory), -20));

    $primaryAssessments = is_array($primary['assessment_history'] ?? null) ? $primary['assessment_history'] : [];
    $secondaryAssessments = is_array($secondary['assessment_history'] ?? null) ? $secondary['assessment_history'] : [];
    $assessmentByKey = [];
    foreach (array_merge($secondaryAssessments, $primaryAssessments) as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $entryId = trim((string) ($entry['id'] ?? ''));
        $entrySaved = trim((string) ($entry['saved_at'] ?? ''));
        $key = $entryId !== '' ? 'id:' . $entryId : 'ts:' . $entrySaved;
        if (!isset($assessmentByKey[$key])) {
            $assessmentByKey[$key] = $entry;
        }
    }
    $primary['assessment_history'] = array_values(array_slice(array_values($assessmentByKey), -30));

    if (empty($primary['profile']) && !empty($secondary['profile'])) {
        $primary['profile'] = $secondary['profile'];
    }

    if (empty($primary['created_at']) && !empty($secondary['created_at'])) {
        $primary['created_at'] = $secondary['created_at'];
    }

    $users[$idxById] = $primary;
    array_splice($users, $idxByEmail, 1);
    $idxById = $idxByEmail < $idxById ? $idxById - 1 : $idxById;
    $idxByEmail = null;
}

if ($idxById === null && $idxByEmail !== null) {
    $users[$idxByEmail]['id'] = $googleId;
    $idxById = $idxByEmail;
}

if ($idxById === null) {
    $users[] = [
        'id' => $googleId,
        'name' => $googleName,
        'email' => $googleEmail,
        'picture' => $googlePicture,
        'provider' => 'google',
        'created_at' => date('c'),
        'profile_history' => [],
        'assessment_history' => [],
    ];
    $idxById = count($users) - 1;
}

if (!isset($users[$idxById]['profile_history']) || !is_array($users[$idxById]['profile_history'])) {
    $users[$idxById]['profile_history'] = [];
}
if (!isset($users[$idxById]['assessment_history']) || !is_array($users[$idxById]['assessment_history'])) {
    $users[$idxById]['assessment_history'] = [];
}

$users[$idxById]['id'] = $googleId;
$users[$idxById]['name'] = $googleName;
$users[$idxById]['email'] = $googleEmail;
$users[$idxById]['picture'] = $googlePicture;
$users[$idxById]['provider'] = 'google';
$users[$idxById]['last_login_at'] = date('c');
if (empty($users[$idxById]['created_at'])) {
    $users[$idxById]['created_at'] = date('c');
}

saveUsers($users);

$storedUser = $users[$idxById];
if (!empty($storedUser['profile']['name'])) {
    $googleName = (string) $storedUser['profile']['name'];
    if ($googleGivenName === '') {
        $googleGivenName = explode(' ', $googleName)[0] ?? $googleName;
    }
}

session_regenerate_id(true);

$_SESSION['user'] = [
    'id' => $googleId,
    'name' => $googleName,
    'email' => $googleEmail,
    'picture' => $googlePicture,
    'given_name' => $googleGivenName,
    'logged_in' => time(),
    'provider' => 'google',
    'profile' => $storedUser['profile'] ?? null,
    'profile_history' => $storedUser['profile_history'] ?? [],
    'assessment_history' => $storedUser['assessment_history'] ?? [],
];

header('Location: ' . BASE_URL . '/index.php');
exit;

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * POST request via cURL, returns decoded JSON array.
 */
function curlPost(string $url, array $fields): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}

/**
 * GET request with Bearer token via cURL, returns decoded JSON array.
 */
function curlGet(string $url, string $accessToken): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}
