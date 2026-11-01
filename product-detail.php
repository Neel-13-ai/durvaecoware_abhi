<?php
/**
 * Product Detail Page
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/app/Models/Product.php';
require_once __DIR__ . '/app/Models/Category.php';
require_once __DIR__ . '/app/Models/Setting.php';
require_once __DIR__ . '/app/Controllers/InquiryController.php';

// Handle quote inquiry submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_quote') {
    InquiryController::handleSubmit();
}

$id = clean_id($_GET['id'] ?? '');
$slug = clean_string($_GET['slug'] ?? '');
$product = !empty($slug) ? Product::getBySlug($slug) : (!empty($id) ? Product::getById($id) : null);

if (!$product) {
    flash('error', 'The requested product was not found.', 'warning');
    redirect('products.php');
}

$pageTitle = $product['name'] . " - Eco Tableware";
$user = current_user();

// Related products
$relatedProducts = Product::getAll(['category_id' => $product['category_id'], 'is_active' => 1], 4);
// Exclude current product
$relatedProducts = array_filter($relatedProducts, fn($p) => $p['id'] != $product['id']);

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: var(--cream); padding: 30px 20px; border-bottom: 1px solid var(--border);">
  <div class="section-inner" style="font-size:13.5px; color:var(--muted);">
    <a href="<?= url('index.php') ?>">Home</a> &nbsp;/&nbsp;
    <a href="<?= url('products.php') ?>">Products</a> &nbsp;/&nbsp;
    <a href="<?= url('products.php?category=' . urlencode($product['category_slug'])) ?>"><?= e($product['category_name']) ?></a> &nbsp;/&nbsp;
    <span style="color:var(--dark); font-weight:600;"><?= e($product['name']) ?></span>
  </div>
</div>

<main class="section">
  <div class="section-inner">
    <div style="display:grid; grid-template-columns: 1fr 1.1fr; gap: 50px; align-items: flex-start;">
      
      <!-- PRODUCT VISUAL -->
      <div style="background: #fbfdf9; border: 1px solid var(--border); border-radius: 20px; padding: 40px; text-align: center; position: relative;">
        <?php if (!empty($product['badge'])): ?>
          <span class="product-card-badge" style="top:20px; left:20px; font-size:12px; padding:5px 12px;">
            <?= e($product['badge']) ?>
          </span>
        <?php endif; ?>

        <div style="min-height: 280px; display: flex; align-items: center; justify-content: center;">
          <?php if (!empty($product['image_url'])): ?>
            <img src="<?= url($product['image_url']) ?>" alt="<?= e($product['name']) ?>" style="max-height: 260px; object-fit: contain;">
          <?php else: ?>
            <div style="font-size:80px;">🍽️</div>
          <?php endif; ?>
        </div>

        <div style="display:flex; justify-content:center; gap:15px; margin-top:25px; border-top:1px dashed var(--border); padding-top:18px;">
          <span style="font-size:13px; color:#2e7d32; font-weight:600;">🌿 100% Biodegradable</span>
          <span style="font-size:13px; color:#2e7d32; font-weight:600;">🛡️ Food Grade Safe</span>
          <span style="font-size:13px; color:#2e7d32; font-weight:600;">🔥 Hot & Cold Safe</span>
        </div>
      </div>

      <!-- PRODUCT SPECS & DETAILS -->
      <div>
        <span class="eyebrow"><?= e($product['category_name']) ?></span>
        <h1 style="font-family:'Playfair Display',serif; font-size:36px; color:var(--dark); margin-bottom:12px;">
          <?= e($product['name']) ?>
        </h1>

        <div style="display:flex; align-items:baseline; gap:12px; margin-bottom:20px;">
          <span style="font-size:26px; font-weight:800; color:var(--green);"><?= format_currency($product['price']) ?></span>
          <span style="font-size:13px; color:var(--muted);">/ pack (Wholesale Tier Available)</span>
        </div>

        <p style="color:#4e5e52; font-size:15.5px; margin-bottom:24px; line-height:1.7;">
          <?= nl2br(e($product['description'])) ?>
        </p>

        <!-- SPECIFICATIONS TABLE -->
        <table style="width:100%; border-collapse:collapse; margin-bottom:30px; font-size:13.5px;">
          <tr style="border-bottom:1px solid #edf1eb;">
            <td style="padding:10px 0; font-weight:700; color:var(--dark); width:35%;">Standard Packaging:</td>
            <td style="padding:10px 0; color:#506154;"><?= e($product['pack_size']) ?></td>
          </tr>
          <tr style="border-bottom:1px solid #edf1eb;">
            <td style="padding:10px 0; font-weight:700; color:var(--dark);">Material Composition:</td>
            <td style="padding:10px 0; color:#506154;"><?= e($product['material']) ?></td>
          </tr>
          <tr style="border-bottom:1px solid #edf1eb;">
            <td style="padding:10px 0; font-weight:700; color:var(--dark);">Minimum Order (MOQ):</td>
            <td style="padding:10px 0; color:#506154;"><?= e($product['moq']) ?></td>
          </tr>
          <tr style="border-bottom:1px solid #edf1eb;">
            <td style="padding:10px 0; font-weight:700; color:var(--dark);">Stock Status:</td>
            <td style="padding:10px 0; color:#15803d; font-weight:700;">
              <?= $product['stock_status'] === 'in_stock' ? '✓ Ready for Immediate Bulk Dispatch' : 'Made to Order / Contact Us' ?>
            </td>
          </tr>
        </table>

        <!-- INQUIRY FORM ANCHOR -->
        <div id="quote" class="quote-box" style="box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid var(--border);">
          <h3 style="margin-bottom:10px;">Request Bulk Quote for this Product</h3>
          <p style="font-size:13px; color:var(--muted); margin-bottom:16px;">
            Get immediate factory-direct pricing for <strong><?= e($product['name']) ?></strong>.
          </p>

          <form method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="submit_quote">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="product_interest" value="<?= e($product['name']) ?>">

            <div class="form-row">
              <input class="field" type="text" name="name" placeholder="Your Name *" required
                     value="<?= $user ? e($user['name']) : '' ?>">
              <input class="field" type="tel" name="phone" placeholder="Phone / WhatsApp *" required
                     value="<?= $user ? e($user['phone']) : '' ?>">
            </div>

            <div class="form-row">
              <input class="field" type="email" name="email" placeholder="Email Address"
                     value="<?= $user ? e($user['email']) : '' ?>">
              <input class="field" type="text" name="company" placeholder="Business Name"
                     value="<?= $user ? e($user['company']) : '' ?>">
            </div>

            <input class="field" type="text" name="quantity" placeholder="Required Quantity (e.g. 5,000 Pcs, 10 cartons)" required>

            <textarea class="field" name="message" placeholder="Optional notes (destination city, custom branding, etc.)"></textarea>

            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px; font-size:13px;">
              <input type="checkbox" name="open_whatsapp" id="detail_wa" value="1" checked>
              <label for="detail_wa" style="cursor:pointer;">Send inquiry via WhatsApp as well</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">
              Submit Bulk Inquiry
            </button>
          </form>
        </div>

      </div>
    </div>

    <!-- RELATED PRODUCTS -->
    <?php if (!empty($relatedProducts)): ?>
      <div style="margin-top:70px; border-top:1px solid var(--border); padding-top:40px;">
        <div class="section-head" style="text-align:left; margin-bottom:25px;">
          <h2 style="font-size:26px;">Similar Products in <?= e($product['category_name']) ?></h2>
        </div>
        <div class="product-grid" style="grid-template-columns: repeat(3, 1fr);">
          <?php foreach ($relatedProducts as $rel): ?>
            <article class="product-card">
              <div class="product-image">
                <?php if (!empty($rel['image_url'])): ?>
                  <img src="<?= url($rel['image_url']) ?>" alt="<?= e($rel['name']) ?>">
                <?php endif; ?>
              </div>
              <h3><?= e($rel['name']) ?></h3>
              <p class="pack-info"><?= e($rel['pack_size']) ?></p>
              <div class="actions">
                <a href="<?= url('product-detail.php?id=' . $rel['id']) ?>" class="btn btn-outline btn-sm" style="width:100%;">View Product</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
