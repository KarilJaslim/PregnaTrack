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
    $override = trim((string) getenv('USERS_FILE_PATH'));
    if ($override !== '') {
        return $override;
    }

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
    $dir = dirname($file);

    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        return false;
    }

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

    if (SMTP_USERNAME === '' || SMTP_PASSWORD === '' || SMTP_FROM_EMAIL === '') {
        setSmtpLastError('SMTP credentials are incomplete. Set SMTP_USERNAME, SMTP_PASSWORD, and SMTP_FROM_EMAIL.');
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

    if (EMAIL_TRANSPORT === 'resend') {
        return resendSendMail($toEmail, $subject, $message);
    }

    $smtpOk = smtpSendMail(SMTP_FROM_EMAIL, $toEmail, $payload);
    if ($smtpOk) {
        return true;
    }

    $smtpError = getSmtpLastError();

    if ((EMAIL_TRANSPORT === 'auto' || EMAIL_TRANSPORT === 'smtp') && RESEND_API_KEY !== '') {
        if (resendSendMail($toEmail, $subject, $message)) {
            return true;
        }
        setSmtpLastError('SMTP failed: ' . $smtpError . ' | Resend failed: ' . getSmtpLastError());
        return false;
    }

    return false;
}

function resendSendMail(string $toEmail, string $subject, string $plainText): bool
{
    if (RESEND_API_KEY === '') {
        setSmtpLastError('RESEND_API_KEY is missing.');
        return false;
    }

    if (RESEND_FROM_EMAIL === '') {
        setSmtpLastError('RESEND_FROM_EMAIL is missing.');
        return false;
    }

    if (!function_exists('curl_init')) {
        setSmtpLastError('cURL extension is required for Resend transport.');
        return false;
    }

    $payload = json_encode([
        'from' => RESEND_FROM_EMAIL,
        'to' => [$toEmail],
        'subject' => $subject,
        'text' => $plainText,
    ], JSON_UNESCAPED_SLASHES);

    if ($payload === false) {
        setSmtpLastError('Could not encode Resend payload.');
        return false;
    }

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => SMTP_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $responseBody = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($responseBody === false) {
        setSmtpLastError('Resend request failed: ' . ($curlError !== '' ? $curlError : 'Unknown cURL error'));
        return false;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $decoded = json_decode($responseBody, true);
        $detail = '';
        if (is_array($decoded)) {
            $detail = (string) ($decoded['message'] ?? ($decoded['error']['message'] ?? ''));
        }
        setSmtpLastError('Resend returned HTTP ' . $httpCode . ($detail !== '' ? ' - ' . $detail : ''));
        return false;
    }

    return true;
}

function smtpSendMail(string $fromEmail, string $toEmail, string $payload): bool
{
    $attempts = [[
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'secure' => SMTP_SECURE,
    ]];

    if (strtolower(SMTP_HOST) === 'smtp.gmail.com') {
        if (!(SMTP_PORT === 465 && SMTP_SECURE === 'ssl')) {
            $attempts[] = ['host' => 'smtp.gmail.com', 'port' => 465, 'secure' => 'ssl'];
        }
        if (!(SMTP_PORT === 587 && SMTP_SECURE === 'tls')) {
            $attempts[] = ['host' => 'smtp.gmail.com', 'port' => 587, 'secure' => 'tls'];
        }
    }

    $attemptErrors = [];
    foreach ($attempts as $attempt) {
        if (smtpSendMailAttempt(
            $fromEmail,
            $toEmail,
            $payload,
            (string) $attempt['host'],
            (int) $attempt['port'],
            (string) $attempt['secure']
        )) {
            return true;
        }

        $attemptErrors[] = (string) $attempt['host'] . ':' . (int) $attempt['port'] . ' [' . (string) $attempt['secure'] . '] ' . getSmtpLastError();
    }

    setSmtpLastError('SMTP connection failed on all attempts. ' . implode(' | ', array_unique($attemptErrors)));
    return false;
}

function smtpSendMailAttempt(string $fromEmail, string $toEmail, string $payload, string $host, int $port, string $secure): bool
{
    $transport = $secure === 'ssl' ? 'ssl://' : '';
    $socket = @stream_socket_client(
        $transport . $host . ':' . $port,
        $errno,
        $errstr,
        SMTP_TIMEOUT,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        setSmtpLastError('Connection failed to ' . $host . ':' . $port . ' - ' . $errstr . ' (' . $errno . ')');
        return false;
    }

    stream_set_timeout($socket, SMTP_TIMEOUT);
    $ehloHost = smtpEhloHost();

    if (!smtpExpect($socket, [220])) {
        setSmtpLastError('SMTP server did not return 220 on connect.');
        fclose($socket);
        return false;
    }

    if (!smtpCommand($socket, 'EHLO ' . $ehloHost, [250])) {
        setSmtpLastError('EHLO command failed.');
        fclose($socket);
        return false;
    }

    if ($secure === 'tls') {
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

        if (!smtpCommand($socket, 'EHLO ' . $ehloHost, [250])) {
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

function smtpEhloHost(): string
{
    $host = parse_url(BASE_URL, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        return 'localhost';
    }

    $clean = preg_replace('/[^A-Za-z0-9.-]/', '', $host);
    return $clean !== '' ? $clean : 'localhost';
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
