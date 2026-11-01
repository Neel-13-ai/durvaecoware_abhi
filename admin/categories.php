<?php
/**
 * Admin Category Management
 * Durva Eco Ware
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Category.php';

require_admin();

$editId = clean_int($_GET['edit'] ?? 0);
$editCategory = $editId > 0 ? Category::getById($editId) : null;

// Handle Form Submissions (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $name = clean_string($_POST['name'] ?? '');
        $slug = clean_string($_POST['slug'] ?? '');
        $icon = clean_string($_POST['icon'] ?? '🌱');
        $sortOrder = clean_int($_POST['sort_order'] ?? 0);
        $description = clean_string($_POST['description'] ?? '');

        if (!empty($name)) {
            $data = [
                'name'        => $name,
                'slug'        => $slug,
                'icon'        => $icon,
                'sort_order'  => $sortOrder,
                'description' => $description
            ];

            $id = clean_int($_POST['id'] ?? 0);
            if ($id > 0) {
                Category::update($id, $data);
                flash('success', 'Category updated successfully.');
            } else {
                Category::create($data);
                flash('success', 'New category created successfully.');
            }
        }
        redirect('admin/categories.php');
    }

    if ($action === 'delete') {
        $id = clean_int($_POST['id'] ?? 0);
        if ($id > 0) {
            Category::delete($id);
            flash('success', 'Category deleted successfully.');
        }
        redirect('admin/categories.php');
    }
}

$pageTitle = "Category Management";
$pageSubtitle = "Organize product taxonomy, catalog navigation and icons";

$categories = Category::getAll();

require_once __DIR__ . '/includes/header.php';
?>

<div style="display:grid; grid-template-columns: 1fr 1.6fr; gap:25px; align-items:flex-start;">

  <!-- ADD / EDIT CATEGORY FORM -->
  <div class="admin-card">
    <div class="card-header">
      <h3><?= $editCategory ? 'Edit Category: ' . e($editCategory['name']) : 'Add New Category' ?></h3>
      <?php if ($editCategory): ?>
        <a href="<?= url('admin/categories.php') ?>" class="btn-admin btn-admin-secondary btn-admin-sm">Cancel</a>
      <?php endif; ?>
    </div>

    <div class="card-body">
      <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <?php if ($editCategory): ?>
          <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
        <?php endif; ?>

        <div class="admin-form-group">
          <label>Category Name *</label>
          <input type="text" name="name" class="admin-input" required
                 value="<?= e($editCategory['name'] ?? '') ?>" placeholder="e.g. Biodegradable Plates">
        </div>

        <div class="form-grid-2">
          <div class="admin-form-group">
            <label>Icon Emoji</label>
            <input type="text" name="icon" class="admin-input"
                   value="<?= e($editCategory['icon'] ?? '🌱') ?>" placeholder="e.g. 🍽️, 🥣, 📦">
          </div>
          <div class="admin-form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" class="admin-input"
                   value="<?= e($editCategory['sort_order'] ?? 0) ?>">
          </div>
        </div>

        <div class="admin-form-group">
          <label>URL Slug (Optional)</label>
          <input type="text" name="slug" class="admin-input"
                 value="<?= e($editCategory['slug'] ?? '') ?>" placeholder="Auto-generated if left empty">
        </div>

        <div class="admin-form-group">
          <label>Brief Description</label>
          <textarea name="description" class="admin-textarea" rows="3"
                    placeholder="Short description shown at top of catalog filter..."><?= e($editCategory['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;">
          💾 <?= $editCategory ? 'Update Category' : 'Create Category' ?>
        </button>
      </form>
    </div>
  </div>

  <!-- CATEGORIES TABLE -->
  <div class="admin-card">
    <div class="card-header">
      <h3>Active Categories (<?= count($categories) ?>)</h3>
    </div>

    <div class="card-body" style="padding:0;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:50px;">Icon</th>
              <th>Category</th>
              <th>Slug</th>
              <th>Products</th>
              <th>Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $cat): ?>
              <tr>
                <td style="font-size:22px; text-align:center;"><?= e($cat['icon']) ?></td>
                <td>
                  <strong><?= e($cat['name']) ?></strong>
                  <?php if (!empty($cat['description'])): ?>
                    <div style="font-size:11.5px; color:var(--admin-muted);"><?= e(substr($cat['description'], 0, 50)) ?>...</div>
                  <?php endif; ?>
                </td>
                <td><code><?= e($cat['slug']) ?></code></td>
                <td>
                  <span style="font-weight:700; color:#1b6330;"><?= (int)$cat['product_count'] ?></span>
                </td>
                <td><?= (int)$cat['sort_order'] ?></td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <a href="<?= url('admin/categories.php?edit=' . $cat['id']) ?>" class="btn-admin btn-admin-secondary btn-admin-sm">
                      Edit
                    </a>
                    <form method="POST" action="" style="display:inline;"
                          onsubmit="return confirm('Delete category \'<?= e(addslashes($cat['name'])) ?>\'?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
