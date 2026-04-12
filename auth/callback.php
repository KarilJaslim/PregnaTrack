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

// ── 5. Store user in session ──────────────────────────────────────────────────
session_regenerate_id(true); // prevent session fixation

$_SESSION['user'] = [
    'id'         => $userInfo['sub'],
    'name'       => $userInfo['name']    ?? 'User',
    'email'      => $userInfo['email']   ?? '',
    'picture'    => $userInfo['picture'] ?? '',
    'given_name' => $userInfo['given_name'] ?? '',
    'logged_in'  => time(),
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
