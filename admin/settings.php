<?php
/**
 * Admin Company Settings Management
 * Durva Eco Ware
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Setting.php';

require_admin();

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $settings = [
        'company_name'       => clean_string($_POST['company_name'] ?? 'Durva Eco Ware'),
        'company_tagline'    => clean_string($_POST['company_tagline'] ?? ''),
        'contact_phone'      => clean_string($_POST['contact_phone'] ?? ''),
        'contact_email'      => clean_email($_POST['contact_email'] ?? ''),
        'whatsapp_number'    => clean_string($_POST['whatsapp_number'] ?? ''),
        'company_address'    => clean_string($_POST['company_address'] ?? ''),
        'wholesale_headline' => clean_string($_POST['wholesale_headline'] ?? ''),
        'wholesale_subhead'  => clean_string($_POST['wholesale_subhead'] ?? ''),
        'about_story'        => clean_string($_POST['about_story'] ?? '')
    ];

    Setting::updateBatch($settings);
    flash('success', 'Company settings updated successfully.');
    redirect('admin/settings.php');
}

$pageTitle = "Company & Site Settings";
$pageSubtitle = "Update business contact numbers, WhatsApp hotline and website details";

$settings = Setting::getAll();

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width:850px; margin:auto;">
  <div class="admin-card">
    <div class="card-header">
      <h3>Business Identity & Hotline Configuration</h3>
    </div>

    <div class="card-body">
      <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="form-grid-2">
          <div class="admin-form-group">
            <label>Company / Brand Name</label>
            <input type="text" name="company_name" class="admin-input"
                   value="<?= e($settings['company_name'] ?? 'Durva Eco Ware') ?>" required>
          </div>

          <div class="admin-form-group">
            <label>Tagline</label>
            <input type="text" name="company_tagline" class="admin-input"
                   value="<?= e($settings['company_tagline'] ?? 'Better Products. Better Planet.') ?>">
          </div>
        </div>

        <div class="form-grid-3">
          <div class="admin-form-group">
            <label>Primary Phone</label>
            <input type="text" name="contact_phone" class="admin-input"
                   value="<?= e($settings['contact_phone'] ?? '+91 98765 43210') ?>" required>
          </div>

          <div class="admin-form-group">
            <label>Contact Email</label>
            <input type="email" name="contact_email" class="admin-input"
                   value="<?= e($settings['contact_email'] ?? 'info@durvaecoware.com') ?>" required>
          </div>

          <div class="admin-form-group">
            <label>WhatsApp Number (With Country Code)</label>
            <input type="text" name="whatsapp_number" class="admin-input"
                   value="<?= e($settings['whatsapp_number'] ?? '919876543210') ?>"
                   placeholder="e.g. 919876543210" required>
            <small style="font-size:11px; color:var(--admin-muted);">No '+' or dashes for WhatsApp link</small>
          </div>
        </div>

        <div class="admin-form-group">
          <label>Office / Factory Address</label>
          <input type="text" name="company_address" class="admin-input"
                 value="<?= e($settings['company_address'] ?? 'Plot No. 42, Green Industrial Park, Gujarat, India') ?>">
        </div>

        <div class="admin-form-group" style="margin-top:20px; border-top:1px solid var(--admin-border); padding-top:18px;">
          <label>Wholesale Section Headline</label>
          <input type="text" name="wholesale_headline" class="admin-input"
                 value="<?= e($settings['wholesale_headline'] ?? 'Looking for Bulk Eco-Friendly Products?') ?>">
        </div>

        <div class="admin-form-group">
          <label>Wholesale Subtitle</label>
          <textarea name="wholesale_subhead" class="admin-textarea" rows="2"><?= e($settings['wholesale_subhead'] ?? 'Partner with Durva Eco Ware for wholesale orders, retail supply, restaurant requirements, and distribution.') ?></textarea>
        </div>

        <div class="admin-form-group">
          <label>About Us Story</label>
          <textarea name="about_story" class="admin-textarea" rows="3"><?= e($settings['about_story'] ?? 'Durva Eco Ware is focused on providing quality eco-friendly disposable products for restaurants, caterers, retailers, distributors, events and everyday food service needs.') ?></textarea>
        </div>

        <button type="submit" class="btn-admin btn-admin-primary" style="padding:12px 24px; margin-top:10px;">
          💾 Save All Settings
        </button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
