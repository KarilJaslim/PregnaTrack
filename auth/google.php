<?php
/**
 * auth/google.php
 * Builds the Google OAuth authorization URL and redirects the user.
 * A CSRF `state` token is stored in the session to validate the callback.
 */
require_once dirname(__DIR__) . '/config.php';
session_start();

// Stop early with a clear message if credentials are still placeholders.
if (
    GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' ||
    GOOGLE_CLIENT_SECRET === 'YOUR_GOOGLE_CLIENT_SECRET' ||
    GOOGLE_CLIENT_ID === '' ||
    GOOGLE_CLIENT_SECRET === ''
) {
    header('Location: ' . BASE_URL . '/login.php?error=' . urlencode('Google OAuth is not configured yet. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in config.php.'));
    exit;
}

// Generate and store a CSRF state token
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id'             => GOOGLE_CLIENT_ID,
    'redirect_uri'          => GOOGLE_REDIRECT_URI,
    'response_type'         => 'code',
    'scope'                 => 'openid email profile',
    'access_type'           => 'online',
    'prompt'                => 'select_account',
    'state'                 => $state,
]);

header('Location: ' . GOOGLE_AUTH_URL . '?' . $params);
exit;
