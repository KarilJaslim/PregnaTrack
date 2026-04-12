<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$email = trim((string) ($_POST['email'] ?? ''));
$otp = trim((string) ($_POST['otp'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['ok' => false, 'message' => 'Enter a valid email address.'], 422);
}

if (!preg_match('/^\\d{' . SIGNUP_OTP_LENGTH . '}$/', $otp)) {
    jsonResponse(['ok' => false, 'message' => 'Enter a valid ' . SIGNUP_OTP_LENGTH . '-digit OTP.'], 422);
}

$state = $_SESSION['signup_otp'] ?? null;
if (!$state || empty($state['email']) || empty($state['hash'])) {
    jsonResponse(['ok' => false, 'message' => 'No OTP session found. Send OTP first.'], 400);
}

if (strtolower($state['email']) !== strtolower($email)) {
    jsonResponse(['ok' => false, 'message' => 'OTP email does not match.'], 400);
}

if (time() > (int) ($state['expires_at'] ?? 0)) {
    unset($_SESSION['signup_otp']);
    jsonResponse(['ok' => false, 'message' => 'OTP expired. Request a new one.'], 400);
}

$_SESSION['signup_otp']['attempts'] = (int) ($state['attempts'] ?? 0) + 1;
if ($_SESSION['signup_otp']['attempts'] > SIGNUP_OTP_MAX_ATTEMPTS) {
    unset($_SESSION['signup_otp']);
    jsonResponse(['ok' => false, 'message' => 'Too many attempts. Request a new OTP.'], 429);
}

if (!password_verify($otp, $state['hash'])) {
    $remaining = SIGNUP_OTP_MAX_ATTEMPTS - $_SESSION['signup_otp']['attempts'];
    jsonResponse(['ok' => false, 'message' => 'Invalid OTP. ' . max($remaining, 0) . ' attempts left.'], 401);
}

$_SESSION['signup_otp']['verified'] = true;
$_SESSION['signup_otp']['verified_at'] = time();

jsonResponse(['ok' => true, 'message' => 'Email verified successfully.']);
