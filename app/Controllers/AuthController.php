<?php
/**
 * Auth Controller
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/auth.php';
require_once BASE_PATH . '/app/Models/User.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';
require_once BASE_PATH . '/app/Helpers/csrf.php';

class AuthController {
    public static function handleLogin(): void {
        require_csrf();

        $email = clean_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            flash('error', 'Please enter both email and password.', 'error');
            redirect('login.php');
        }

        $user = User::findByEmail($email);
        if (!$user || !User::verifyPassword($user, $password)) {
            flash('error', 'Invalid email address or password.', 'error');
            redirect('login.php');
        }

        // Success: Log user in
        login_user($user);

        flash('success', 'Welcome back, ' . e($user['name']) . '!');

        // Check intended URL or role redirect
        if (!empty($_SESSION['intended_url'])) {
            $redirectUrl = $_SESSION['intended_url'];
            unset($_SESSION['intended_url']);
            redirect($redirectUrl);
        }

        if ($user['role'] === 'admin') {
            redirect('admin/index.php');
        } else {
            redirect('account.php');
        }
    }

    public static function handleRegister(): void {
        require_csrf();

        $name = clean_string($_POST['name'] ?? '');
        $email = clean_email($_POST['email'] ?? '');
        $phone = clean_phone($_POST['phone'] ?? '');
        $company = clean_string($_POST['company'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            flash('error', 'Please fill in all required fields.', 'error');
            redirect('register.php');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please provide a valid email address.', 'error');
            redirect('register.php');
        }

        if (strlen($password) < 6) {
            flash('error', 'Password must be at least 6 characters long.', 'error');
            redirect('register.php');
        }

        if ($password !== $passwordConfirm) {
            flash('error', 'Password confirmation does not match.', 'error');
            redirect('register.php');
        }

        if (User::findByEmail($email)) {
            flash('error', 'An account with this email address already exists. Please log in.', 'warning');
            redirect('login.php');
        }

        $userId = User::create([
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'company'  => $company,
            'password' => $password,
            'role'     => 'customer'
        ]);

        $user = User::findById($userId);
        login_user($user);

        $_SESSION['registration_success'] = [
            'name'    => $name,
            'email'   => $email,
            'company' => $company
        ];

        flash('success', 'Registration successful! Welcome to Durva Eco Ware.');
        redirect('register.php?success=1');
    }

    public static function handleLogout(): void {
        logout_user();
        flash('success', 'You have been successfully logged out.');
        redirect('index.php');
    }
}
