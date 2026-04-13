<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

ini_set('display_errors', '0');

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if (!$error) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'] ?? 0, $fatalTypes, true)) {
        return;
    }

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
    }

    echo json_encode([
        'ok' => false,
        'message' => 'Server error while sending OTP. Please try again.',
    ]);
});

try {

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$email = trim((string) ($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['ok' => false, 'message' => 'Enter a valid email address.'], 422);
}

if (findUserByEmail($email)) {
    jsonResponse(['ok' => false, 'message' => 'This email is already registered.'], 409);
}

$otpState = $_SESSION['signup_otp'] ?? [];
$lastSentAt = (int) ($otpState['last_sent_at'] ?? 0);
$remaining = SIGNUP_OTP_COOLDOWN - (time() - $lastSentAt);
if ($remaining > 0 && strtolower((string) ($otpState['email'] ?? '')) === strtolower($email)) {
    jsonResponse(['ok' => false, 'message' => 'Please wait ' . $remaining . ' seconds before requesting another OTP.'], 429);
}

$otp = generateNumericOtp();
if (!sendSignupOtpEmail($email, $otp)) {
    jsonResponse([
        'ok' => false,
        'message' => 'SMTP send failed: ' . getSmtpLastError()
    ], 500);
}

$_SESSION['signup_otp'] = [
    'email' => $email,
    'hash' => password_hash($otp, PASSWORD_DEFAULT),
    'expires_at' => time() + SIGNUP_OTP_EXPIRY,
    'attempts' => 0,
    'verified' => false,
    'verified_at' => 0,
    'last_sent_at' => time(),
];

jsonResponse(['ok' => true, 'message' => 'OTP sent to ' . $email . '.']);
} catch (Throwable $e) {
    jsonResponse(['ok' => false, 'message' => 'Server error while sending OTP.'], 500);
}
