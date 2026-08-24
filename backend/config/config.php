<?php
/**
 * Core Application & Security Configuration
 *
 * Centralizes session initialization, database connection management,
 * environment loading (.env support), security headers, and CSRF protection utilities.
 */

// Harden session cookie attributes to mitigate session hijacking and XSS exposure.
if (session_status() === PHP_SESSION_NONE) {
    // Detect SSL termination behind reverse proxies (e.g. Alwaysdata, Cloudflare)
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0, // Expire session cookie when browser closes
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps, // Restrict cookie transmission to encrypted TLS channels only
        'httponly' => true,   // Prevent client-side scripts from reading the session cookie (XSS mitigation)
        'samesite' => 'Lax'   // Defend against Cross-Site Request Forgery on top-level navigations
    ]);
    session_start();
}

// Security headers:
// - SAMEORIGIN prevents clickjacking attacks by forbidding external domains from framing this app.
// - nosniff stops browsers from MIME-sniffing responses away from the declared content-type.
// - strict-origin-when-cross-origin protects referrer privacy on third-party links.
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Automatically load .env file if present at the project root for environments where system env vars aren't injected into PHP-FPM
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            list($envKey, $envVal) = explode('=', $line, 2);
            $envKey = trim($envKey);
            $envVal = trim($envVal, " \t\n\r\0\x0B\"'");
            putenv("$envKey=$envVal");
            $_ENV[$envKey] = $envVal;
            $_SERVER[$envKey] = $envVal;
        }
    }
}

// Helper to reliably read environment variables across CLI, Apache, and PHP-FPM / FastCGI
function env(string $key, ?string $default = null): ?string {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    return $default;
}

// Read database credentials with fallback defaults for local development
$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$dbname = env('DB_NAME', 'php_exam');
$username = env('DB_USER', 'php_user');
$password = env('DB_PASS', 'root123');

try {
    // - ATTR_EMULATE_PREPARES=false forces native MySQL prepared statements (server-side parameters),
    //   preventing SQL injection vulnerabilities that could occur with client-side emulation.
    // - utf8mb4 supports full 4-byte Unicode (including emojis) and prevents truncation vulnerabilities.
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // Log database connectivity failures internally to prevent leaking table structures or database credentials to end-users.
    error_log("Database connection error [$host:$port / $dbname / user: $username]: " . $e->getMessage());
    die("A database connection error occurred. Please try again later.");
}

/**
 * Lazily generates or retrieves a unique per-session cryptographic CSRF token.
 */
function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        // 32 cryptographically secure random bytes (64 hex characters) ensure unguessable tokens
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Renders a hidden HTML input field containing the current session's CSRF token.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(get_csrf_token()) . '">';
}

/**
 * Validates submitted token against the session token.
 * Uses hash_equals() to prevent timing attacks where attackers guess token bytes by measuring comparison latency.
 */
function verify_csrf_token(?string $token = null): bool {
    $token = $token ?? $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}