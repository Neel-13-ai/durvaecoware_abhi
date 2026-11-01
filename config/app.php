<?php
/**
 * Application Core Configuration & Global Helpers
 * Durva Eco Ware
 */

if (session_status() === PHP_SESSION_NONE) {
    // 24 hour session cookie lifetime
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Define Base Filesystem Path
define('BASE_PATH', dirname(__DIR__));

// Load .env file if it exists (Lightweight native parser)
$envFile = BASE_PATH . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            $val = trim($val, '"\'');
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $val;
                putenv("$key=$val");
            }
        }
    }
}

// Helper to get environment variable with fallback
function env($key, $default = null) {
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    $val = getenv($key);
    return ($val !== false) ? $val : $default;
}

// Determine Dynamic Base URL
function get_base_url(): string {
    if (php_sapi_name() === 'cli') {
        return 'http://localhost';
    }

    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Calculate subfolder path relative to DocumentRoot
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    
    // Find index or admin or public entry point directory
    $scriptDir = dirname($scriptName);
    
    // If inside admin/ or database/ or other subfolder, resolve back to project root
    $scriptDir = preg_replace('#/(admin|database|app|includes|config).*$#', '', $scriptDir);
    
    $baseSubdir = rtrim($scriptDir, '/');

    return $protocol . $host . $baseSubdir;
}

define('BASE_URL', get_base_url());
define('APP_NAME', env('APP_NAME', 'Durva Eco Ware'));

// Helper: generate full URL to a page or endpoint
function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return empty($path) ? BASE_URL : BASE_URL . '/' . $path;
}

// Helper: generate URL for public asset
function asset(string $path = ''): string {
    $path = ltrim($path, '/');
    return BASE_URL . '/assets/' . $path;
}

// Helper: Escape HTML to prevent XSS
function e(?string $string): string {
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
}

// Helper: Redirect to URL
function redirect(string $path): void {
    if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
        $path = url($path);
    }
    header("Location: $path");
    exit;
}

// Helper: Set or Get Flash Message
function flash(string $key, ?string $message = null, string $type = 'success'): ?array {
    if ($message !== null) {
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
        return null;
    }

    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }

    return null;
}

// Helper: Check if request is AJAX
function is_ajax(): bool {
    return (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    );
}

// Helper: JSON response
function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Helper: Format Indian Rupee currency
function format_currency(float|int $amount): string {
    return '₹' . number_format($amount, 2);
}

// Helper: Time ago string
function time_ago(string $datetime): string {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $timestamp);
}
