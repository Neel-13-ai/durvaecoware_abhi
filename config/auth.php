<?php
/**
 * Authentication & Authorization Helper
 * Durva Eco Ware
 */

require_once __DIR__ . '/app.php';
require_once BASE_PATH . '/app/Helpers/csrf.php';

function is_logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'      => $_SESSION['user_id'] ?? null,
        'name'    => $_SESSION['user_name'] ?? 'User',
        'email'   => $_SESSION['user_email'] ?? '',
        'role'    => $_SESSION['user_role'] ?? 'customer',
        'company' => $_SESSION['user_company'] ?? '',
        'phone'   => $_SESSION['user_phone'] ?? ''
    ];
}

function is_admin(): bool {
    return is_logged_in() && (($_SESSION['user_role'] ?? '') === 'admin');
}

function require_login(string $redirectTo = 'login.php'): void {
    if (!is_logged_in()) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        flash('error', 'Please log in to access this page.', 'warning');
        redirect($redirectTo);
    }
}

function require_admin(): void {
    if (!is_logged_in()) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? 'admin/index.php';
        flash('error', 'Administrator login required.', 'warning');
        redirect('login.php');
    }
    if (!is_admin()) {
        flash('error', 'Access denied. Administrator privileges required.', 'error');
        redirect('index.php');
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_company'] = $user['company'] ?? '';
    $_SESSION['user_phone'] = $user['phone'] ?? '';
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
