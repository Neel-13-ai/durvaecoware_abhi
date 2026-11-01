<?php
/**
 * CSRF Protection Helper
 * Durva Eco Ware
 */

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!verify_csrf_token($token)) {
            if (is_ajax()) {
                json_response(['success' => false, 'message' => 'CSRF security token mismatch.'], 403);
            }
            flash('error', 'Session expired or invalid security token. Please try again.', 'error');
            redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
        }
    }
}
