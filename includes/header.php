<?php
/**
 * Public Header Component
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Setting.php';

$companyName = Setting::get('company_name', 'Durva Eco Ware');
$tagline = Setting::get('company_tagline', 'Better Products. Better Planet.');
$contactPhone = Setting::get('contact_phone', '+91 98765 43210');
$contactEmail = Setting::get('contact_email', 'info@durvaecoware.com');
$user = current_user();

$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($companyName) ?> - Eco-friendly disposable plates, bowls, containers and tableware for wholesale and bulk orders.">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . e($companyName) : e($companyName) . ' | Sustainable Tableware' ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
  <?php if (isset($extraCss)): ?>
    <?= $extraCss ?>
  <?php endif; ?>
</head>
<body>

  <!-- TOP BAR -->
  <div class="topbar">
    <div class="topbar-inner">
      <div class="topbar-left">
        <span>☎ <?= e($contactPhone) ?></span>
        <span>✉ <?= e($contactEmail) ?></span>
      </div>
      <div>Eco-Friendly Products for a Better Tomorrow</div>
      <div class="topbar-right">
        <?php if ($user): ?>
          <span>Signed in as <strong><?= e($user['name']) ?></strong></span>
        <?php else: ?>
          <a href="<?= url('login.php') ?>" style="color:#d5ebd6; font-weight:600;">Customer / Admin Login ↗</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- HEADER -->
  <header>
    <nav class="nav">
      <a href="<?= url('index.php') ?>" class="logo">
        <div class="logo-mark">
          <svg viewBox="0 0 60 70" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M30 66C26 53 18 44 7 36C7 22 16 8 31 4C43 13 51 26 49 40C47 51 39 59 30 66Z" stroke="#2f7d20" stroke-width="2.5"/>
            <path d="M30 62C30 45 30 25 31 8" stroke="#2f7d20" stroke-width="2.2"/>
            <path d="M30 39C22 34 17 29 13 22M30 47C39 40 44 33 46 25" stroke="#2f7d20" stroke-width="1.8"/>
          </svg>
        </div>
        <div class="logo-text">
          <strong>DURVA<br>ECO WARE</strong>
          <span><?= e($tagline) ?></span>
        </div>
      </a>

      <div class="nav-links" id="navLinks">
        <a href="<?= url('index.php#home') ?>" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
        <a href="<?= url('products.php') ?>" class="<?= $currentPage === 'products.php' ? 'active' : '' ?>">Products</a>
        <a href="<?= url('index.php#about') ?>">About Us</a>
        <a href="<?= url('index.php#why') ?>">Why Durva</a>
        <a href="<?= url('index.php#wholesale') ?>">Wholesale</a>
        <a href="<?= url('index.php#contact') ?>">Contact Us</a>
      </div>

      <div class="nav-actions">
        <?php if ($user): ?>
          <?php if (is_admin()): ?>
            <a href="<?= url('admin/index.php') ?>" class="btn btn-outline btn-sm">⚙ Admin Panel</a>
          <?php else: ?>
            <a href="<?= url('account.php') ?>" class="btn btn-outline btn-sm">👤 My Account</a>
          <?php endif; ?>
          <a href="<?= url('logout.php') ?>" class="btn btn-outline btn-sm" style="border-color:#e0dede;">Logout</a>
        <?php else: ?>
          <a href="<?= url('login.php') ?>" class="btn btn-outline btn-sm">Login</a>
          <a href="<?= url('index.php#wholesale') ?>" class="btn btn-primary nav-cta">Get Quote</a>
        <?php endif; ?>
        <button class="menu-toggle" id="menuToggle" aria-label="Open menu">☰</button>
      </div>
    </nav>
  </header>

  <?php require_once BASE_PATH . '/includes/alerts.php'; ?>
