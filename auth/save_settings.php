<?php
/**
 * auth/save_settings.php
 * Handles two actions via POST:
 *   action=preferences  — saves height_unit, weight_unit
 *   action=password     — changes password (local accounts only)
 */
require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/signup_common.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

if (!isset($_SESSION['user'])) {
    jsonResponse(['ok' => false, 'message' => 'Authentication required.'], 401);
}

$userId = (string) ($_SESSION['user']['id'] ?? '');
if ($userId === '') {
    jsonResponse(['ok' => false, 'message' => 'Invalid session.'], 401);
}

$action = (string) ($_POST['action'] ?? '');

// ── ACTION: Save preferences ──────────────────────────────────────────────────
if ($action === 'preferences') {
    $heightUnit = in_array($_POST['height_unit'] ?? '', ['cm', 'ft'], true)
        ? (string) $_POST['height_unit'] : 'cm';
    $weightUnit = in_array($_POST['weight_unit'] ?? '', ['kg', 'lbs'], true)
        ? (string) $_POST['weight_unit'] : 'kg';

    $users = loadUsers();
    $found = false;
    foreach ($users as &$u) {
        if (($u['id'] ?? '') === $userId) {
            if (!isset($u['settings'])) {
                $u['settings'] = [];
            }
            $u['settings']['height_unit'] = $heightUnit;
            $u['settings']['weight_unit'] = $weightUnit;
            $found = true;
            break;
        }
    }
    unset($u);

    if (!$found) {
        jsonResponse(['ok' => false, 'message' => 'User not found.'], 404);
    }

    if (!saveUsers($users)) {
        jsonResponse(['ok' => false, 'message' => 'Could not save settings. Try again.'], 500);
    }

    // Update session
    if (!isset($_SESSION['user']['settings'])) {
        $_SESSION['user']['settings'] = [];
    }
    $_SESSION['user']['settings']['height_unit'] = $heightUnit;
    $_SESSION['user']['settings']['weight_unit'] = $weightUnit;

    jsonResponse(['ok' => true, 'message' => 'Preferences saved.']);
}

// ── ACTION: Change password ───────────────────────────────────────────────────
if ($action === 'password') {
    if (($_SESSION['user']['provider'] ?? '') !== 'local') {
        jsonResponse(['ok' => false, 'message' => 'Password change is only available for email accounts.'], 403);
    }

    $current = (string) ($_POST['current_password'] ?? '');
    $new     = (string) ($_POST['new_password']     ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($current === '') {
        jsonResponse(['ok' => false, 'message' => 'Enter your current password.'], 422);
    }
    if (strlen($new) < 8) {
        jsonResponse(['ok' => false, 'message' => 'New password must be at least 8 characters.'], 422);
    }
    if ($new !== $confirm) {
        jsonResponse(['ok' => false, 'message' => 'New passwords do not match.'], 422);
    }
    if ($current === $new) {
        jsonResponse(['ok' => false, 'message' => 'New password must be different from your current password.'], 422);
    }

    $users = loadUsers();
    $found = false;
    foreach ($users as &$u) {
        if (($u['id'] ?? '') === $userId) {
            if (empty($u['password_hash']) || !password_verify($current, (string) $u['password_hash'])) {
                jsonResponse(['ok' => false, 'message' => 'Current password is incorrect.'], 401);
            }
            $u['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
            $found = true;
            break;
        }
    }
    unset($u);

    if (!$found) {
        jsonResponse(['ok' => false, 'message' => 'User not found.'], 404);
    }

    if (!saveUsers($users)) {
        jsonResponse(['ok' => false, 'message' => 'Could not update password. Try again.'], 500);
    }

    jsonResponse(['ok' => true, 'message' => 'Password updated successfully.']);
}

jsonResponse(['ok' => false, 'message' => 'Unknown action.'], 400);
