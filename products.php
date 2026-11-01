<?php
/**
 * Product Catalog Page
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/app/Models/Product.php';
require_once __DIR__ . '/app/Models/Category.php';
require_once __DIR__ . '/app/Models/Setting.php';

$pageTitle = "Product Catalog - Eco-Friendly Tableware";

$selectedCatSlug = $_GET['category'] ?? null;
$searchTerm = trim($_GET['q'] ?? '');

$categories = Category::getAll();

$filters = ['is_active' => 1];
if (!empty($selectedCatSlug)) {
    $filters['category_slug'] = $selectedCatSlug;
}
if (!empty($searchTerm)) {
    $filters['search'] = $searchTerm;
}

$products = Product::getAll($filters);

$activeCategory = null;
if ($selectedCatSlug) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $selectedCatSlug) {
            $activeCategory = $cat;
            break;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

  <div class="catalog-header">
    <div class="section-inner">
      <div class="eyebrow">Eco-Ware Collection</div>
      <h1><?= $activeCategory ? e($activeCategory['name']) : 'All Eco-Friendly Products' ?></h1>
      <p>
        <?= $activeCategory && !empty($activeCategory['description']) 
            ? e($activeCategory['description']) 
            : 'Explore our comprehensive selection of 100% biodegradable and compostable disposables.' ?>
      </p>
    </div>
  </div>

  <div class="catalog-wrap">
    <!-- FILTER SIDEBAR -->
    <aside class="filter-sidebar">
      <div class="filter-title">Categories</div>
      <div class="category-list">
        <a href="<?= url('products.php') ?>" class="<?= empty($selectedCatSlug) ? 'active' : '' ?>">
          <span>🌿 All Categories</span>
          <span class="count"><?= count($products) ?></span>
        </a>
        <?php foreach ($categories as $cat): ?>
          <a href="<?= url('products.php?category=' . urlencode($cat['slug'])) ?>" 
             class="<?= $selectedCatSlug === $cat['slug'] ? 'active' : '' ?>">
            <span><?= e($cat['icon']) ?> <?= e($cat['name']) ?></span>
            <span class="count"><?= (int)$cat['product_count'] ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="filter-title" style="margin-top:25px;">Search Catalog</div>
      <form method="GET" action="<?= url('products.php') ?>">
        <?php if ($selectedCatSlug): ?>
          <input type="hidden" name="category" value="<?= e($selectedCatSlug) ?>">
        <?php endif; ?>
        <input type="text" name="q" value="<?= e($searchTerm) ?>" placeholder="Search product..." 
               class="field" style="margin-bottom:8px;">
        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">Filter</button>
      </form>
    </aside>

    <!-- PRODUCT GRID -->
    <main>
      <?php if (empty($products)): ?>
        <div style="text-align:center; padding:60px 20px; background:#fbfcf9; border:1px dashed var(--border); border-radius:14px;">
          <div style="font-size:40px; margin-bottom:10px;">🍃</div>
          <h3>No products found</h3>
          <p style="color:var(--muted); margin-bottom:15px;">No products match your selected filter criteria.</p>
          <a href="<?= url('products.php') ?>" class="btn btn-primary btn-sm">Reset Filters</a>
        </div>
      <?php else: ?>
        <div class="product-grid" style="grid-template-columns: repeat(3, 1fr);">
          <?php foreach ($products as $prod): ?>
            <article class="product-card">
              <?php if (!empty($prod['badge'])): ?>
                <span class="product-card-badge"><?= e($prod['badge']) ?></span>
              <?php endif; ?>

              <div class="product-image">
                <?php if (!empty($prod['image_url'])): ?>
                  <img src="<?= url($prod['image_url']) ?>" alt="<?= e($prod['name']) ?>">
                <?php else: ?>
                  <div style="font-size:45px;">🍽️</div>
                <?php endif; ?>
              </div>

              <h3><?= e($prod['name']) ?></h3>
              <p class="pack-info">📦 <?= e($prod['pack_size']) ?> &bull; <?= e($prod['material']) ?></p>

              <div class="price-row">
                <div>
                  <span class="price-rate"><?= format_currency($prod['price']) ?></span>
                  <small style="color:var(--muted); font-size:10px;">/ pack</small>
                </div>
                <span class="moq-badge">MOQ: <?= e($prod['moq']) ?></span>
              </div>

              <div class="actions">
                <a href="<?= url('product-detail.php?id=' . $prod['id']) ?>" class="btn btn-outline btn-sm">View Details</a>
                <a href="<?= url('product-detail.php?id=' . $prod['id'] . '#quote') ?>" class="btn btn-primary btn-sm">Get Quote</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
