<?php
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['ok' => false, 'message' => 'Enter a valid email address.'], 422);
}

if ($password === '') {
    jsonResponse(['ok' => false, 'message' => 'Enter your password.'], 422);
}

$user = findUserByEmail($email);
if (!$user || empty($user['password_hash'])) {
    jsonResponse(['ok' => false, 'message' => 'Invalid email or password.'], 401);
}

if (!password_verify($password, (string) $user['password_hash'])) {
    jsonResponse(['ok' => false, 'message' => 'Invalid email or password.'], 401);
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id' => $user['id'] ?? ('usr_' . bin2hex(random_bytes(8))),
    'name' => $user['name'] ?? 'User',
    'email' => $user['email'] ?? $email,
    'picture' => '',
    'given_name' => explode(' ', (string) ($user['name'] ?? 'User'))[0] ?? 'User',
    'logged_in' => time(),
    'provider' => 'local',
];

jsonResponse([
    'ok' => true,
    'message' => 'Signed in successfully.',
    'redirect' => BASE_URL . '/index.php',
]);
