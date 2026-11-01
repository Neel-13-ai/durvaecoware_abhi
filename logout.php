<?php
/**
 * Logout Action
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';

AuthController::handleLogout();
