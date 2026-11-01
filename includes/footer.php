<?php
/**
 * Public Footer Component
 * Durva Eco Ware
 */

require_once BASE_PATH . '/app/Models/Category.php';
require_once BASE_PATH . '/app/Models/Setting.php';

$footerCategories = Category::getAll();
$contactPhone = Setting::get('contact_phone', '+91 98765 43210');
$contactEmail = Setting::get('contact_email', 'info@durvaecoware.com');
$companyAddress = Setting::get('company_address', 'Gujarat, India');
$waNumber = Setting::get('whatsapp_number', '919876543210');
$waClean = preg_replace('/[^0-9]/', '', $waNumber);
?>
  <!-- FOOTER -->
  <footer id="contact">
    <div class="footer-grid">

      <div>
        <h3><?= e(Setting::get('company_name', 'Durva Eco Ware')) ?></h3>
        <p>
          Eco-friendly disposable products for restaurants,
          caterers, retailers, events and wholesale customers across India.
        </p>
        <br>
        <p><strong><?= e(Setting::get('company_tagline', 'Better Products. Better Planet.')) ?></strong></p>
      </div>

      <div>
        <h3>Quick Links</h3>
        <div class="footer-links">
          <a href="<?= url('index.php#home') ?>">Home</a>
          <a href="<?= url('products.php') ?>">Product Catalog</a>
          <a href="<?= url('index.php#about') ?>">About Us</a>
          <a href="<?= url('index.php#why') ?>">Why Choose Us</a>
          <a href="<?= url('index.php#wholesale') ?>">Wholesale Supply</a>
          <?php if (is_logged_in()): ?>
            <a href="<?= url('account.php') ?>">My Inquiries</a>
          <?php else: ?>
            <a href="<?= url('login.php') ?>">Customer Portal</a>
          <?php endif; ?>
        </div>
      </div>

      <div>
        <h3>Product Categories</h3>
        <div class="footer-links">
          <?php foreach (array_slice($footerCategories, 0, 5) as $cat): ?>
            <a href="<?= url('products.php?category=' . urlencode($cat['slug'])) ?>">
              <?= e($cat['icon']) ?> <?= e($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <h3>Contact Us</h3>
        <div class="footer-links">
          <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contactPhone) ?>">☎ <?= e($contactPhone) ?></a>
          <a href="mailto:<?= e($contactEmail) ?>">✉ <?= e($contactEmail) ?></a>
          <a href="https://wa.me/<?= $waClean ?>?text=Hello%20Durva%20Eco%20Ware,%20I%20am%20interested%20in%20your%20products." target="_blank">💬 WhatsApp Support</a>
          <span>📍 <?= e($companyAddress) ?></span>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      &copy; <span id="year"><?= date('Y') ?></span> <?= e(Setting::get('company_name', 'Durva Eco Ware')) ?>. All Rights Reserved.
    </div>
  </footer>

  <!-- FLOATING WHATSAPP BUTTON -->
  <a class="floating-whatsapp"
     href="https://wa.me/<?= $waClean ?>?text=Hello%20Durva%20Eco%20Ware,%20I%20have%20an%20inquiry."
     target="_blank"
     aria-label="Contact us on WhatsApp">💬</a>

  <script src="<?= asset('js/main.js') ?>"></script>
  <?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
  <?php endif; ?>

</body>
</html>
