<?php
/**
 * Admin Dashboard Overview
 * Durva Eco Ware
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Product.php';
require_once BASE_PATH . '/app/Models/Category.php';
require_once BASE_PATH . '/app/Models/Inquiry.php';
require_once BASE_PATH . '/app/Models/User.php';

require_admin();

$pageTitle = "Executive Dashboard";
$pageSubtitle = "Operational metrics, catalog statistics, and wholesale quote inquiries";

$inquiryCounts = Inquiry::countByStatus();
$productCount = Product::count(['is_active' => 1]);
$categoryCount = Category::count();
$userCount = User::count('customer');

$recentInquiries = Inquiry::getAll(null, 6);

require_once __DIR__ . '/includes/header.php';
?>

<!-- KPI COUNTERS -->
<div class="kpi-grid">
  <div class="kpi-card">
    <div>
      <div class="kpi-title">Total Inquiries</div>
      <div class="kpi-value"><?= $inquiryCounts['total'] ?></div>
      <div class="kpi-sub">All-time leads</div>
    </div>
    <div class="kpi-icon">📋</div>
  </div>

  <div class="kpi-card">
    <div>
      <div class="kpi-title">New Quotes Pending</div>
      <div class="kpi-value" style="color:#b91c1c;"><?= $inquiryCounts['new'] ?></div>
      <div class="kpi-sub" style="color:#b91c1c;">Requires follow-up</div>
    </div>
    <div class="kpi-icon" style="background:#fee2e2; color:#b91c1c;">⚡</div>
  </div>

  <div class="kpi-card">
    <div>
      <div class="kpi-title">Active Products</div>
      <div class="kpi-value"><?= $productCount ?></div>
      <div class="kpi-sub"><?= $categoryCount ?> categories</div>
    </div>
    <div class="kpi-icon">🍽️</div>
  </div>

  <div class="kpi-card">
    <div>
      <div class="kpi-title">Registered Customers</div>
      <div class="kpi-value"><?= $userCount ?></div>
      <div class="kpi-sub">Wholesale buyers</div>
    </div>
    <div class="kpi-icon">👥</div>
  </div>
</div>

<!-- QUICK ACTIONS -->
<div style="display:flex; gap:12px; margin-bottom:25px; flex-wrap:wrap;">
  <a href="<?= url('admin/product-form.php') ?>" class="btn-admin btn-admin-primary">
    ➕ Add New Product
  </a>
  <a href="<?= url('admin/inquiries.php?status=new') ?>" class="btn-admin btn-admin-secondary">
    📬 View New Inquiries (<?= $inquiryCounts['new'] ?>)
  </a>
  <a href="<?= url('admin/categories.php') ?>" class="btn-admin btn-admin-secondary">
    📁 Manage Categories
  </a>
  <a href="<?= url('admin/settings.php') ?>" class="btn-admin btn-admin-secondary">
    ⚙️ Company & Contact Info
  </a>
</div>

<!-- RECENT INQUIRIES -->
<div class="admin-card">
  <div class="card-header">
    <h3>Recent Wholesale Quote Requests</h3>
    <a href="<?= url('admin/inquiries.php') ?>" class="btn-admin btn-admin-secondary btn-admin-sm">
      View All Inquiries &rarr;
    </a>
  </div>

  <div class="card-body" style="padding:0;">
    <?php if (empty($recentInquiries)): ?>
      <div style="text-align:center; padding:40px; color:var(--admin-muted);">
        No inquiries recorded yet.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Product / Requirement</th>
              <th>Quantity</th>
              <th>Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentInquiries as $inq): ?>
              <tr>
                <td><strong>#DW-<?= $inq['id'] ?></strong></td>
                <td>
                  <strong><?= e($inq['name']) ?></strong>
                  <?php if (!empty($inq['company'])): ?>
                    <div style="font-size:11.5px; color:var(--admin-muted);"><?= e($inq['company']) ?></div>
                  <?php endif; ?>
                  <div style="font-size:11px; color:#2e7d32;">📞 <?= e($inq['phone']) ?></div>
                </td>
                <td>
                  <strong><?= e($inq['product_interest'] ?: ($inq['product_name'] ?? 'General Wholesale')) ?></strong>
                  <?php if (!empty($inq['message'])): ?>
                    <div style="font-size:11.5px; color:var(--admin-muted); max-width:280px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                      <?= e($inq['message']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= e($inq['quantity'] ?? 'Not specified') ?></td>
                <td><?= time_ago($inq['created_at']) ?></td>
                <td>
                  <span class="status-pill status-<?= $inq['status'] ?>">
                    <?= str_replace('_', ' ', $inq['status']) ?>
                  </span>
                </td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <a href="<?= url('admin/inquiries.php?id=' . $inq['id']) ?>" class="btn-admin btn-admin-primary btn-admin-sm">
                      Details
                    </a>
                    <?php
                      $waClean = preg_replace('/[^0-9]/', '', $inq['phone']);
                      if (!empty($waClean)):
                    ?>
                      <a href="https://wa.me/<?= $waClean ?>?text=Hello%20<?= urlencode($inq['name']) ?>,%20thank%20you%20for%20your%20inquiry%20with%20Durva%20Eco%20Ware."
                         target="_blank" class="btn-admin btn-admin-secondary btn-admin-sm" title="Contact on WhatsApp">
                        💬
                      </a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
