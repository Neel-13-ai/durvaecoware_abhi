<?php
/**
 * Public Homepage
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/app/Models/Product.php';
require_once __DIR__ . '/app/Models/Category.php';
require_once __DIR__ . '/app/Models/Setting.php';
require_once __DIR__ . '/app/Controllers/InquiryController.php';

// Handle Quote Submission if POSTed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_quote') {
    InquiryController::handleSubmit();
}

$pageTitle = "Sustainable Tableware & Wholesale Eco Products";

// Fetch featured products from Database
try {
    $featuredProducts = Product::getAll(['is_active' => 1], 8);
    $categories = Category::getAll();
} catch (Exception $e) {
    // If DB is not yet installed, redirect to install.php
    redirect('install.php');
}

$user = current_user();

require_once __DIR__ . '/includes/header.php';
?>

  <!-- HERO SECTION -->
  <main id="home">
    <section class="hero">
      <div class="hero-inner">
        <div class="hero-content">
          <div class="eyebrow">Sustainable • Quality • Reliable</div>
          <h1>Eco-Friendly<br>Disposable Products<br><span>for Every Occasion</span></h1>
          <p>
            Durva Eco Ware offers a wide range of eco-friendly disposable
            products made from natural, renewable, and biodegradable materials.
            Safe for food, safe for your family, safe for the planet.
          </p>

          <div class="hero-buttons">
            <a href="<?= url('products.php') ?>" class="btn btn-primary">Explore Products 🌿</a>
            <a href="#wholesale" class="btn btn-outline">Get Wholesale Quote ↗</a>
          </div>
        </div>

        <div class="hero-visual">
          <div class="eco-badge">
            100%<br>ECO<br><small>FRIENDLY</small>
          </div>

          <div class="product-scene" aria-label="Eco-friendly disposable product illustration">
            <div class="leaf l1"></div>
            <div class="leaf l2"></div>
            <div class="leaf l3"></div>
            <div class="leaf l4"></div>

            <div class="plate p1"></div>
            <div class="plate p2"></div>
            <div class="plate p3"></div>
            <div class="bowl"></div>
            <div class="cup"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES BAR -->
    <div class="features-wrap" id="why">
      <div class="features">
        <div class="feature">
          <div class="feature-icon">🌱</div>
          <h4>100% Eco-Friendly</h4>
          <p>Made from natural & renewable plant resources</p>
        </div>
        <div class="feature">
          <div class="feature-icon">🏅</div>
          <h4>Premium Quality</h4>
          <p>Strong, durable, grease & food-safe grade</p>
        </div>
        <div class="feature">
          <div class="feature-icon">📦</div>
          <h4>Bulk Supply</h4>
          <p>Direct manufacturer prices for wholesale</p>
        </div>
        <div class="feature">
          <div class="feature-icon">🚚</div>
          <h4>On-Time Delivery</h4>
          <p>Fast and dependable dispatch across India</p>
        </div>
        <div class="feature">
          <div class="feature-icon">🏷️</div>
          <h4>Custom Branding</h4>
          <p>Custom printing & packaging for cafes/hotels</p>
        </div>
      </div>
    </div>

    <!-- PRODUCTS SHOWCASE -->
    <section class="section products" id="products">
      <div class="section-inner">
        <div class="section-head">
          <div class="eyebrow">Our Catalog</div>
          <h2>Our Eco-Friendly Products <span>🌿</span></h2>
          <p>Compostable, hygienic, and heavy-duty tableware engineered for hospitality & retail</p>
        </div>

        <div class="product-grid">
          <?php foreach ($featuredProducts as $prod): ?>
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
                <a href="<?= url('product-detail.php?id=' . $prod['id']) ?>" class="btn btn-outline btn-sm">Details</a>
                <a href="<?= url('product-detail.php?id=' . $prod['id'] . '#quote') ?>" class="btn btn-primary btn-sm">Quote</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div class="center-btn" style="text-align:center; margin-top:35px;">
          <a href="<?= url('products.php') ?>" class="btn btn-outline" style="padding:13px 28px;">
            View All Products & Categories &rarr;
          </a>
        </div>
      </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="section about" id="about">
      <div class="section-inner about-grid">
        <div class="about-art">
          <div class="large-leaf"></div>
        </div>

        <div class="about-content">
          <div class="eyebrow">About Durva Eco Ware</div>
          <h2>Better Products.<br><span style="color:#2f7d20">Better Planet.</span></h2>
          <p>
            <?= e(Setting::get('about_story', 'Durva Eco Ware is focused on providing quality eco-friendly disposable products for restaurants, caterers, retailers, distributors, events and everyday food service needs.')) ?>
          </p>
          <p>
            Our goal is simple: provide dependable products that help
            businesses reduce their dependence on single-use conventional plastics
            without compromising durability, temperature resistance, or aesthetics.
          </p>

          <div class="checks">
            <div class="check"><span>✓</span> 100% Food-Grade and chemical-free materials</div>
            <div class="check"><span>✓</span> Bulk & wholesale factory supply with consistent quality</div>
            <div class="check"><span>✓</span> Transparent, competitive volume pricing tiers</div>
            <div class="check"><span>✓</span> Custom brand packaging and private labeling options</div>
          </div>
        </div>
      </div>
    </section>

    <!-- WHOLESALE INQUIRY SECTION -->
    <section class="section wholesale" id="wholesale">
      <div class="wholesale-inner">
        <div>
          <div class="eyebrow" style="color:#a9d88c">Wholesale & Distribution</div>
          <h2><?= e(Setting::get('wholesale_headline', 'Looking for Bulk Eco-Friendly Products?')) ?></h2>
          <p>
            <?= e(Setting::get('wholesale_subhead', 'Partner with Durva Eco Ware for wholesale orders, retail supply, restaurant requirements, and distribution across India.')) ?>
          </p>
          <br>
          <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', Setting::get('whatsapp_number', '919876543210')) ?>?text=Hello%20Durva%20Eco%20Ware,%20I%20am%20interested%20in%20wholesale%20products."
               target="_blank" class="btn btn-primary">💬 WhatsApp Us Direct</a>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', Setting::get('contact_phone', '+919876543210')) ?>"
               class="btn btn-outline" style="border-color:#fff; color:#fff; background:transparent;">☎ Call Our Desk</a>
          </div>
        </div>

        <div class="quote-box">
          <h3>Request a Wholesale Quote</h3>
          <form id="quoteForm" method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="submit_quote">

            <div class="form-row">
              <input class="field" type="text" name="name" placeholder="Your Full Name *" required
                     value="<?= $user ? e($user['name']) : '' ?>">
              <input class="field" type="tel" name="phone" placeholder="Phone / WhatsApp Number *" required
                     value="<?= $user ? e($user['phone']) : '' ?>">
            </div>

            <div class="form-row">
              <input class="field" type="email" name="email" placeholder="Email Address"
                     value="<?= $user ? e($user['email']) : '' ?>">
              <input class="field" type="text" name="company" placeholder="Business / Company Name"
                     value="<?= $user ? e($user['company']) : '' ?>">
            </div>

            <div class="form-row">
              <select class="field" name="product_interest">
                <option value="">Select Product of Interest</option>
                <?php foreach ($featuredProducts as $p): ?>
                  <option value="<?= e($p['name']) ?>"><?= e($p['name']) ?> (<?= e($p['pack_size']) ?>)</option>
                <?php endforeach; ?>
                <option value="Mixed / Full Range">Mixed / Full Product Line</option>
              </select>
              <input class="field" type="text" name="quantity" placeholder="Estimated Quantity (e.g. 5,000 Pcs)">
            </div>

            <textarea class="field" name="message" placeholder="Tell us your specific requirement, delivery location, or timeline..."></textarea>

            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px; font-size:13px;">
              <input type="checkbox" name="open_whatsapp" id="open_whatsapp_cb" value="1" checked>
              <label for="open_whatsapp_cb" style="cursor:pointer;">Also open WhatsApp with this inquiry</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:13px;">
              🚀 Send Wholesale Inquiry
            </button>
          </form>
        </div>
      </div>
    </section>
  </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
