<?php
/**
 * Admin Add / Edit Product Form
 * Durva Eco Ware
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Product.php';
require_once BASE_PATH . '/app/Models/Category.php';
require_once BASE_PATH . '/app/Helpers/upload.php';

require_admin();

$id = clean_id($_GET['id'] ?? '');
$isEdit = !empty($id);
$product = $isEdit ? Product::getById($id) : null;

if ($isEdit && !$product) {
    flash('error', 'Product not found.', 'warning');
    redirect('admin/products.php');
}

$categories = Category::getAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $name = clean_string($_POST['name'] ?? '');
    $categoryId = clean_id($_POST['category_id'] ?? '');
    $packSize = clean_string($_POST['pack_size'] ?? '100 Pcs / Pack');
    $material = clean_string($_POST['material'] ?? 'Eco-Friendly Paper');
    $price = clean_float($_POST['price'] ?? 0);
    $moq = clean_string($_POST['moq'] ?? '500 Pcs');
    $badge = clean_string($_POST['badge'] ?? '');
    $description = clean_string($_POST['description'] ?? '');
    $stockStatus = clean_string($_POST['stock_status'] ?? 'in_stock');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name) || empty($categoryId)) {
        flash('error', 'Product name and Category are required.', 'error');
    } else {
        $imageUrl = $product['image_url'] ?? null;

        // Check if an image was uploaded
        if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadRes = handle_image_upload($_FILES['image'], 'products');
            if ($uploadRes['success']) {
                $imageUrl = 'assets/' . $uploadRes['path'];
            } else {
                flash('error', 'Image upload failed: ' . $uploadRes['error'], 'error');
            }
        }

        $data = [
            'category_id'  => $categoryId,
            'name'         => $name,
            'pack_size'    => $packSize,
            'material'     => $material,
            'price'        => $price,
            'moq'          => $moq,
            'badge'        => $badge,
            'description'  => $description,
            'image_url'    => $imageUrl,
            'is_featured'  => $isFeatured,
            'is_active'    => $isActive,
            'stock_status' => $stockStatus
        ];

        if ($isEdit) {
            Product::update($id, $data);
            flash('success', 'Product updated successfully.');
        } else {
            $newId = Product::create($data);
            flash('success', 'Product created successfully.');
        }
        redirect('admin/products.php');
    }
}

$pageTitle = $isEdit ? "Edit Product: " . $product['name'] : "Add New Product";
$pageSubtitle = "Manage product specifications, commercial volume packaging and images";

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width:850px; margin:auto;">
  <div style="margin-bottom:15px;">
    <a href="<?= url('admin/products.php') ?>" class="btn-admin btn-admin-secondary btn-admin-sm">
      &larr; Back to Products List
    </a>
  </div>

  <div class="admin-card">
    <div class="card-header">
      <h3><?= $isEdit ? 'Edit Product Details' : 'New Product Information' ?></h3>
    </div>

    <div class="card-body">
      <form method="POST" action="" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-grid-2">
          <div class="admin-form-group">
            <label>Product Name *</label>
            <input type="text" name="name" class="admin-input" required
                   value="<?= e($product['name'] ?? $_POST['name'] ?? '') ?>" placeholder="e.g. 9 Inch Sugarcane Bagasse Plate">
          </div>

          <div class="admin-form-group">
            <label>Category *</label>
            <select name="category_id" class="admin-select" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                  <?= ($product && $product['category_id'] == $cat['id']) || (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-grid-3">
          <div class="admin-form-group">
            <label>Standard Pack Size</label>
            <input type="text" name="pack_size" class="admin-input"
                   value="<?= e($product['pack_size'] ?? $_POST['pack_size'] ?? '100 Pcs / Pack') ?>" placeholder="e.g. 100 Pcs / Pack">
          </div>

          <div class="admin-form-group">
            <label>Material Composition</label>
            <input type="text" name="material" class="admin-input"
                   value="<?= e($product['material'] ?? $_POST['material'] ?? 'Biodegradable Plant Fiber') ?>" placeholder="e.g. Bagasse / Kraft">
          </div>

          <div class="admin-form-group">
            <label>Indicative Price / Pack (₹)</label>
            <input type="number" step="0.01" name="price" class="admin-input"
                   value="<?= e($product['price'] ?? $_POST['price'] ?? '0.00') ?>">
          </div>
        </div>

        <div class="form-grid-3">
          <div class="admin-form-group">
            <label>Minimum Order (MOQ)</label>
            <input type="text" name="moq" class="admin-input"
                   value="<?= e($product['moq'] ?? $_POST['moq'] ?? '500 Pcs') ?>" placeholder="e.g. 1,000 Pcs">
          </div>

          <div class="admin-form-group">
            <label>Highlight Badge</label>
            <input type="text" name="badge" class="admin-input"
                   value="<?= e($product['badge'] ?? $_POST['badge'] ?? '') ?>" placeholder="e.g. Bestseller, 100% Compostable">
          </div>

          <div class="admin-form-group">
            <label>Stock Availability</label>
            <select name="stock_status" class="admin-select">
              <option value="in_stock" <?= ($product['stock_status'] ?? '') === 'in_stock' ? 'selected' : '' ?>>In Stock / Ready Dispatch</option>
              <option value="made_to_order" <?= ($product['stock_status'] ?? '') === 'made_to_order' ? 'selected' : '' ?>>Made to Order</option>
              <option value="out_of_stock" <?= ($product['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Temporarily Unavailable</option>
            </select>
          </div>
        </div>

        <div class="admin-form-group">
          <label>Product Description & Hospitality Specs</label>
          <textarea name="description" class="admin-textarea" rows="4"
                    placeholder="Describe temperature resistance, oil/grease resistance, recommended food uses, and dimensions..."><?= e($product['description'] ?? $_POST['description'] ?? '') ?></textarea>
        </div>

        <!-- IMAGE UPLOAD -->
        <div class="admin-form-group" style="background:#fcfdfc; border:1px dashed var(--admin-border); padding:20px; border-radius:8px;">
          <label>Product Image (PNG, JPG, WEBP, SVG)</label>
          <div style="display:flex; align-items:center; gap:20px; margin-top:8px;">
            <?php if (!empty($product['image_url'])): ?>
              <div style="width:80px; height:80px; border:1px solid var(--admin-border); border-radius:8px; overflow:hidden; background:#fff; display:grid; place-items:center;">
                <img src="<?= url($product['image_url']) ?>" alt="" style="max-height:70px; max-width:70px; object-fit:contain;">
              </div>
            <?php endif; ?>
            <div style="flex:1;">
              <input type="file" name="image" accept="image/*" class="admin-input" style="background:#fff;">
              <small style="color:var(--admin-muted); display:block; margin-top:4px;">
                Leave empty to keep existing image. Recommended size: 500x500 square transparent or clean background.
              </small>
            </div>
          </div>
        </div>

        <!-- FLAGS -->
        <div style="display:flex; gap:30px; margin:20px 0;">
          <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; font-weight:600;">
            <input type="checkbox" name="is_featured" value="1" <?= (!empty($product['is_featured'])) ? 'checked' : '' ?>>
            ⭐ Feature on Homepage
          </label>
          <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; font-weight:600;">
            <input type="checkbox" name="is_active" value="1" <?= (!isset($product['is_active']) || $product['is_active']) ? 'checked' : '' ?>>
            ✓ Active & Visible in Catalog
          </label>
        </div>

        <div style="display:flex; gap:12px; margin-top:25px;">
          <button type="submit" class="btn-admin btn-admin-primary" style="padding:12px 24px;">
            💾 <?= $isEdit ? 'Save Changes' : 'Create Product' ?>
          </button>
          <a href="<?= url('admin/products.php') ?>" class="btn-admin btn-admin-secondary">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
