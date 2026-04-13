<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($name === '' || strlen($name) < 2) {
    jsonResponse(['ok' => false, 'message' => 'Enter your full name.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['ok' => false, 'message' => 'Enter a valid email address.'], 422);
}

if (strlen($password) < 8) {
    jsonResponse(['ok' => false, 'message' => 'Password must be at least 8 characters.'], 422);
}

if (findUserByEmail($email)) {
    jsonResponse(['ok' => false, 'message' => 'Email is already registered.'], 409);
}

$state = $_SESSION['signup_otp'] ?? null;
if (!$state || empty($state['verified']) || strtolower((string) ($state['email'] ?? '')) !== strtolower($email)) {
    jsonResponse(['ok' => false, 'message' => 'Verify your email OTP first.'], 400);
}

if ((time() - (int) ($state['verified_at'] ?? 0)) > 600) {
    unset($_SESSION['signup_otp']);
    jsonResponse(['ok' => false, 'message' => 'OTP verification expired. Verify again.'], 400);
}

$users = loadUsers();
$userId = 'usr_' . bin2hex(random_bytes(8));
$users[] = [
    'id' => $userId,
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'provider' => 'local',
    'created_at' => date('c'),
    'profile_history' => [],
    'assessment_history' => [],
];

if (!saveUsers($users)) {
    jsonResponse(['ok' => false, 'message' => 'Could not save user. Try again.'], 500);
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => $userId,
    'name' => $name,
    'email' => $email,
    'picture' => '',
    'given_name' => explode(' ', $name)[0] ?? $name,
    'logged_in' => time(),
    'provider' => 'local',
];
unset($_SESSION['signup_otp']);

jsonResponse(['ok' => true, 'message' => 'Account created successfully.', 'redirect' => BASE_URL . '/index.php']);
