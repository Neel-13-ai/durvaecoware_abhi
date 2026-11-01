<?php
/**
 * Admin Topbar & Header
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Setting.php';

require_admin();
$admin = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' | Admin Console' : 'Admin Console | Durva Eco Ware' ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>

<?php require_once __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
  <!-- TOPBAR -->
  <header class="admin-topbar">
    <div style="display:flex; align-items:center; gap:12px;">
      <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Navigation">☰</button>
      <div class="page-title">
        <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
        <?php if (isset($pageSubtitle)): ?>
          <p><?= e($pageSubtitle) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-topbar-actions">
      <a href="<?= url('index.php') ?>" target="_blank" class="btn-admin btn-admin-secondary btn-admin-sm">
        👁 View Live Website
      </a>
      <a href="<?= url('logout.php') ?>" class="btn-admin btn-admin-danger btn-admin-sm">
        Logout
      </a>
    </div>
  </header>

  <div class="admin-body">
    <?php require_once BASE_PATH . '/includes/alerts.php'; ?>
