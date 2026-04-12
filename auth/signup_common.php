<?php
require_once dirname(__DIR__) . '/config.php';

const SIGNUP_OTP_LENGTH = 6;
const SIGNUP_OTP_EXPIRY = 300;
const SIGNUP_OTP_MAX_ATTEMPTS = 5;
const SIGNUP_OTP_COOLDOWN = 30;

$GLOBALS['smtp_last_error'] = '';

function setSmtpLastError(string $message): void
{
    $GLOBALS['smtp_last_error'] = $message;
}

function getSmtpLastError(): string
{
    return (string) ($GLOBALS['smtp_last_error'] ?? 'unknown SMTP error');
}

function usersFilePath(): string
{
    return dirname(__DIR__) . '/storage/users.json';
}

function loadUsers(): array
{
    $file = usersFilePath();
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    if ($json === false || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function saveUsers(array $users): bool
{
    $file = usersFilePath();
    $fp = fopen($file, 'c+');
    if (!$fp) {
        return false;
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }

    ftruncate($fp, 0);
    rewind($fp);
    $ok = fwrite($fp, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $ok;
}

function findUserByEmail(string $email): ?array
{
    $email = strtolower(trim($email));
    foreach (loadUsers() as $user) {
        if (strtolower((string) ($user['email'] ?? '')) === $email) {
            return $user;
        }
    }
    return null;
}

function generateNumericOtp(int $length = SIGNUP_OTP_LENGTH): string
{
    $min = (int) str_pad('1', $length, '0');
    $max = (int) str_pad('', $length, '9');
    return (string) random_int($min, $max);
}

function sendSignupOtpEmail(string $toEmail, string $otp): bool
{
    setSmtpLastError('');

    if (!SMTP_ENABLED) {
        setSmtpLastError('SMTP is disabled.');
        return false;
    }

    if (
        SMTP_USERNAME === 'YOUR_GMAIL_ADDRESS@gmail.com' ||
        SMTP_PASSWORD === 'YOUR_GMAIL_APP_PASSWORD' ||
        SMTP_FROM_EMAIL === 'YOUR_GMAIL_ADDRESS@gmail.com'
    ) {
        setSmtpLastError('SMTP placeholders are still in config.');
        return false;
    }

    $subject = APP_NAME . ' Sign Up Verification Code';
    $message = "Hello,\n\nUse this OTP to verify your email for sign up: " . $otp . "\n\n" .
               "This code expires in " . (int) (SIGNUP_OTP_EXPIRY / 60) . " minutes.\n\n" .
               "If you did not request this, ignore this email.\n";

    $headers = [
        'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
        'Reply-To: ' . SMTP_FROM_EMAIL,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $payload = implode("\r\n", $headers) . "\r\n" .
               'To: <' . $toEmail . '>' . "\r\n" .
               'Subject: ' . $subject . "\r\n\r\n" .
               $message;

    return smtpSendMail(SMTP_FROM_EMAIL, $toEmail, $payload);
}

function smtpSendMail(string $fromEmail, string $toEmail, string $payload): bool
{
    $transport = SMTP_SECURE === 'ssl' ? 'ssl://' : '';
    $socket = @stream_socket_client(
        $transport . SMTP_HOST . ':' . SMTP_PORT,
        $errno,
        $errstr,
        20,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        setSmtpLastError('Connection failed: ' . $errstr . ' (' . $errno . ')');
        return false;
    }

    stream_set_timeout($socket, 20);

    if (!smtpExpect($socket, [220])) {
        setSmtpLastError('SMTP server did not return 220 on connect.');
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'EHLO localhost', [250])) {
        setSmtpLastError('EHLO command failed.');
        fclose($socket);
        return false;
    }

    if (SMTP_SECURE === 'tls') {
        if (!smtpCommand($socket, 'STARTTLS', [220])) {
            setSmtpLastError('STARTTLS command failed.');
            fclose($socket);
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            setSmtpLastError('TLS negotiation failed.');
            fclose($socket);
            return false;
        }

        if (!smtpCommand($socket, 'EHLO localhost', [250])) {
            setSmtpLastError('EHLO after STARTTLS failed.');
            fclose($socket);
            return false;
        }
    }

    if (!smtpCommand($socket, 'AUTH LOGIN', [334])) {
        setSmtpLastError('AUTH LOGIN command failed.');
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, base64_encode(SMTP_USERNAME), [334])) {
        setSmtpLastError('SMTP username rejected.');
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, base64_encode(SMTP_PASSWORD), [235])) {
        setSmtpLastError('SMTP password/app password rejected.');
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250])) {
        setSmtpLastError('MAIL FROM command rejected.');
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251])) {
        setSmtpLastError('Recipient email rejected by SMTP server.');
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'DATA', [354])) {
        setSmtpLastError('DATA command rejected.');
        fclose($socket);
        return false;
    }

    fwrite($socket, $payload . "\r\n.\r\n");
    if (!smtpExpect($socket, [250])) {
        setSmtpLastError('SMTP server rejected message body.');
        fclose($socket);
        return false;
    }

    smtpCommand($socket, 'QUIT', [221]);
    fclose($socket);
    return true;
}

function smtpCommand($socket, string $command, array $expectedCodes): bool
{
    fwrite($socket, $command . "\r\n");
    return smtpExpect($socket, $expectedCodes);
}

function smtpExpect($socket, array $expectedCodes): bool
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] !== '-') {
            break;
        }
    }

    if ($response === '' || strlen($response) < 3) {
        return false;
    }

    $code = (int) substr($response, 0, 3);
    return in_array($code, $expectedCodes, true);
}

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload);
    exit;
}
