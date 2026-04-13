<?php
// ============================================================
//  Load environment variables from .env (never commit .env)
// ============================================================
(static function (): void {
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        return;
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        // Strip surrounding quotes
        if (preg_match('/^"(.*)"$/s', $val, $m) || preg_match("/^'(.*)'$/s", $val, $m)) {
            $val = $m[1];
        }
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]  = $val;
            putenv("{$key}={$val}");
        }
    }
})();

function envValue(string $key, string $default = ''): string
{
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }

    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return (string) $value;
    }

    return $default;
}

// ============================================================
//  STEP 1 — Set credentials in .env (see .env.example)
// ============================================================
define('GOOGLE_CLIENT_ID',     envValue('GOOGLE_CLIENT_ID'));
define('GOOGLE_CLIENT_SECRET', envValue('GOOGLE_CLIENT_SECRET'));
define('GOOGLE_REDIRECT_URI',  envValue('GOOGLE_REDIRECT_URI', 'http://localhost/Pregnancy/auth/callback.php'));

// ============================================================
//  App settings
// ============================================================
define('APP_NAME',    'PregnaTrack');
define('APP_VERSION', '1.0.0');
define('BASE_URL',    rtrim(envValue('APP_URL', 'http://localhost/Pregnancy'), '/'));

// Session lifetime in seconds (1 hour)
define('SESSION_LIFETIME', 3600);

// ============================================================
//  SMTP settings for Sign Up OTP email (Gmail)
// ============================================================
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // tls or ssl
define('SMTP_USERNAME',   envValue('SMTP_USERNAME'));
define('SMTP_PASSWORD',   envValue('SMTP_PASSWORD'));
define('SMTP_FROM_EMAIL', envValue('SMTP_FROM_EMAIL'));
define('SMTP_FROM_NAME', APP_NAME . ' OTP');

// ============================================================
//  Google OAuth endpoints (do not change)
// ============================================================
define('GOOGLE_AUTH_URL',  'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_URL',  'https://www.googleapis.com/oauth2/v3/userinfo');
