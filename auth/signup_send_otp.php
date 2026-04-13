<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

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
