<?php
/**
 * Admin Product Catalog Management
 * Durva Eco Ware
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Product.php';
require_once BASE_PATH . '/app/Models/Category.php';

require_admin();

// Handle Delete Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    require_csrf();
    $id = clean_id($_POST['id'] ?? '');
    if (!empty($id)) {
        Product::delete($id);
        flash('success', 'Product deleted successfully.');
    }
    redirect('admin/products.php');
}

$pageTitle = "Product Management";
$pageSubtitle = "Create, edit, organize categories and manage bulk packaging specifications";

$selectedCatId = clean_id($_GET['category_id'] ?? '');
$searchTerm = trim($_GET['q'] ?? '');

$filters = [];
if (!empty($selectedCatId)) {
    $filters['category_id'] = $selectedCatId;
}
if (!empty($searchTerm)) {
    $filters['search'] = $searchTerm;
}

$products = Product::getAll($filters);
$categories = Category::getAll();

require_once __DIR__ . '/includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px;">
  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <form method="GET" action="" style="display:flex; gap:8px;">
      <select name="category_id" class="admin-select" style="width:200px;" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= (string)$selectedCatId === (string)$cat['id'] ? 'selected' : '' ?>>
            <?= e($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="q" value="<?= e($searchTerm) ?>" placeholder="Search product..." class="admin-input" style="width:220px;">
      <button type="submit" class="btn-admin btn-admin-secondary">Search</button>
      <?php if (!empty($selectedCatId) || !empty($searchTerm)): ?>
        <a href="<?= url('admin/products.php') ?>" class="btn-admin btn-admin-secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <a href="<?= url('admin/product-form.php') ?>" class="btn-admin btn-admin-primary">
    ➕ Add New Product
  </a>
</div>

<div class="admin-card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($products)): ?>
      <div style="text-align:center; padding:50px; color:var(--admin-muted);">
        No products found matching your filter criteria.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:60px;">Image</th>
              <th>Product Name</th>
              <th>Category</th>
              <th>Packaging & MOQ</th>
              <th>Price / Pack</th>
              <th>Stock Status</th>
              <th>Featured</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td>
                  <div style="width:48px; height:48px; border-radius:8px; background:#f4f7f2; display:grid; place-items:center; overflow:hidden; border:1px solid var(--admin-border);">
                    <?php if (!empty($p['image_url'])): ?>
                      <img src="<?= url($p['image_url']) ?>" alt="" style="max-height:40px; object-fit:contain;">
                    <?php else: ?>
                      <span style="font-size:20px;">🍽️</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <strong><?= e($p['name']) ?></strong>
                  <?php if (!empty($p['badge'])): ?>
                    <span style="font-size:10px; background:#e8f4e6; color:#1e5e2e; font-weight:700; padding:2px 6px; border-radius:4px; margin-left:6px;">
                      <?= e($p['badge']) ?>
                    </span>
                  <?php endif; ?>
                  <div style="font-size:11.5px; color:var(--admin-muted);"><?= e($p['material']) ?></div>
                </td>
                <td>
                  <span style="font-weight:600; font-size:13px; color:#2e7d32;">
                    <?= e($p['category_name']) ?>
                  </span>
                </td>
                <td>
                  <div>📦 <?= e($p['pack_size']) ?></div>
                  <small style="color:var(--admin-muted);">MOQ: <?= e($p['moq']) ?></small>
                </td>
                <td>
                  <strong><?= format_currency($p['price']) ?></strong>
                </td>
                <td>
                  <span class="status-pill status-<?= $p['stock_status'] === 'in_stock' ? 'active' : 'inactive' ?>">
                    <?= str_replace('_', ' ', $p['stock_status']) ?>
                  </span>
                </td>
                <td>
                  <?= $p['is_featured'] ? '⭐ Yes' : '—' ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <a href="<?= url('admin/product-form.php?id=' . $p['id']) ?>" class="btn-admin btn-admin-secondary btn-admin-sm">
                      Edit
                    </a>
                    <form method="POST" action="" style="display:inline;"
                          onsubmit="return confirm('Are you sure you want to delete \'<?= e(addslashes($p['name'])) ?>\'?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                      <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm">
                        Delete
                      </button>
                    </form>
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
