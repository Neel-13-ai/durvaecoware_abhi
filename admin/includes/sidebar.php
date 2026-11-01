<?php
/**
 * Admin Sidebar Navigation
 * Durva Eco Ware
 */

require_once BASE_PATH . '/app/Models/Inquiry.php';

$inquiryCounts = Inquiry::countByStatus();
$newInquiryCount = $inquiryCounts['new'] ?? 0;

$currentScript = basename($_SERVER['SCRIPT_NAME']);
?>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="logo-badge">🌱</div>
    <div>
      <h2>Durva Eco Ware</h2>
      <span>Executive Admin Console</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-heading">Main</div>
    <a href="<?= url('admin/index.php') ?>" class="sidebar-link <?= $currentScript === 'index.php' ? 'active' : '' ?>">
      <span>📊</span>
      <span>Dashboard</span>
    </a>

    <a href="<?= url('admin/inquiries.php') ?>" class="sidebar-link <?= $currentScript === 'inquiries.php' ? 'active' : '' ?>">
      <span>📋</span>
      <span>Wholesale Quotes</span>
      <?php if ($newInquiryCount > 0): ?>
        <span class="badge"><?= $newInquiryCount ?></span>
      <?php endif; ?>
    </a>

    <div class="nav-heading">Catalog Management</div>
    <a href="<?= url('admin/products.php') ?>" class="sidebar-link <?= in_array($currentScript, ['products.php', 'product-form.php']) ? 'active' : '' ?>">
      <span>🍽️</span>
      <span>Products</span>
    </a>

    <a href="<?= url('admin/categories.php') ?>" class="sidebar-link <?= $currentScript === 'categories.php' ? 'active' : '' ?>">
      <span>📁</span>
      <span>Categories</span>
    </a>

    <div class="nav-heading">System & Users</div>
    <a href="<?= url('admin/users.php') ?>" class="sidebar-link <?= $currentScript === 'users.php' ? 'active' : '' ?>">
      <span>👥</span>
      <span>Customers & Roles</span>
    </a>

    <a href="<?= url('admin/settings.php') ?>" class="sidebar-link <?= $currentScript === 'settings.php' ? 'active' : '' ?>">
      <span>⚙️</span>
      <span>Company Settings</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar">
        <?= strtoupper(substr($admin['name'] ?? 'A', 0, 1)) ?>
      </div>
      <div class="admin-info">
        <strong><?= e($admin['name'] ?? 'Admin') ?></strong>
        <small>Administrator</small>
      </div>
    </div>
  </div>
</aside>
