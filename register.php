<?php
/**
 * Customer Registration Page
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';

$isSuccess = isset($_GET['success']) && !empty($_SESSION['registration_success']);
$successData = $_SESSION['registration_success'] ?? null;

// Redirect if already logged in (unless viewing the success confirmation)
if (is_logged_in() && !$isSuccess) {
    redirect('account.php');
}

// Handle POST Register
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::handleRegister();
}

$flashError = flash('error');
$flashWarning = flash('warning');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isSuccess ? 'Account Created!' : 'Create Customer Account' ?> | Durva Eco Ware</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
</head>
<body class="auth-page">

<div class="auth-container">
  <!-- LEFT BRAND PANEL -->
  <div class="auth-brand-side">
    <div class="auth-brand-logo">
      <svg viewBox="0 0 60 70" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M30 66C26 53 18 44 7 36C7 22 16 8 31 4C43 13 51 26 49 40C47 51 39 59 30 66Z" stroke="#4ade80" stroke-width="3"/>
        <path d="M30 62C30 45 30 25 31 8" stroke="#4ade80" stroke-width="2.5"/>
        <path d="M30 39C22 34 17 29 13 22M30 47C39 40 44 33 46 25" stroke="#4ade80" stroke-width="2"/>
      </svg>
      <div>
        <strong>DURVA ECO WARE</strong>
        <span style="font-size:10px; color:#b0dcb7; display:block;">Better Products. Better Planet.</span>
      </div>
    </div>

    <div class="auth-brand-content">
      <h2>Join Durva Eco Ware</h2>
      <p>Register a wholesale customer account to streamline bulk orders, get custom quotation estimates, and access commercial volume rates.</p>
      
      <div class="auth-brand-features">
        <div><span>🌿</span> Factory-direct wholesale pricing</div>
        <div><span>📦</span> Priority dispatch across India</div>
        <div><span>⚡</span> Saved quotes & order history dashboard</div>
      </div>
    </div>

    <div class="auth-brand-footer">
      &copy; <?= date('Y') ?> Durva Eco Ware. All rights reserved.
    </div>
  </div>

  <!-- RIGHT FORM / SUCCESS PANEL -->
  <div class="auth-form-side">

    <?php if ($isSuccess && $successData): ?>
      <!-- CELEBRATORY SUCCESS STATE -->
      <div class="auth-success-card">
        <div class="auth-success-icon">✓</div>
        <h1 class="auth-success-title">Account Created Successfully!</h1>
        <p class="auth-success-desc">
          Welcome to Durva Eco Ware, <strong><?= e($successData['name']) ?></strong>! Your wholesale customer account is now active and ready to use.
        </p>

        <div class="auth-success-meta">
          <div>
            <span style="color:var(--auth-muted);">Account Holder:</span>
            <strong><?= e($successData['name']) ?></strong>
          </div>
          <div>
            <span style="color:var(--auth-muted);">Registered Email:</span>
            <strong><?= e($successData['email']) ?></strong>
          </div>
          <?php if (!empty($successData['company'])): ?>
            <div>
              <span style="color:var(--auth-muted);">Company / Business:</span>
              <strong><?= e($successData['company']) ?></strong>
            </div>
          <?php endif; ?>
          <div>
            <span style="color:var(--auth-muted);">Account Type:</span>
            <strong style="color:#059669;">Wholesale Customer (Active)</strong>
          </div>
        </div>

        <div class="auth-success-actions">
          <a href="<?= url('account.php') ?>" class="auth-btn">
            Go to My Customer Portal &rarr;
          </a>
          <a href="<?= url('products.php') ?>" class="auth-btn auth-btn-secondary">
            Browse Product Catalog 🌿
          </a>
        </div>
      </div>

    <?php else: ?>

      <!-- REGISTRATION FORM -->
      <div class="auth-header">
        <h1>Create Customer Account</h1>
        <p>Fill in your business details to get started</p>
      </div>

      <?php if ($flashError): ?>
        <div style="background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:12px 16px; border-radius:8px; font-size:13.5px; margin-bottom:18px;">
          <?= e($flashError['message']) ?>
        </div>
      <?php endif; ?>

      <?php if ($flashWarning): ?>
        <div style="background:#fef3c7; border:1px solid #fde68a; color:#92400e; padding:12px 16px; border-radius:8px; font-size:13.5px; margin-bottom:18px;">
          <?= e($flashWarning['message']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <?= csrf_field() ?>

        <div class="auth-grid-2">
          <div class="auth-form-group">
            <label>Full Name *</label>
            <input type="text" name="name" class="auth-input" placeholder="e.g. Ramesh Patel" required
                   value="<?= e($_POST['name'] ?? '') ?>">
          </div>
          <div class="auth-form-group">
            <label>Phone / WhatsApp *</label>
            <input type="tel" name="phone" class="auth-input" placeholder="+91 98765 43210" required
                   value="<?= e($_POST['phone'] ?? '') ?>">
          </div>
        </div>

        <div class="auth-grid-2">
          <div class="auth-form-group">
            <label>Email Address *</label>
            <input type="email" name="email" class="auth-input" placeholder="you@company.com" required
                   value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="auth-form-group">
            <label>Company / Organization</label>
            <input type="text" name="company" class="auth-input" placeholder="e.g. Green Caterers Ltd"
                   value="<?= e($_POST['company'] ?? '') ?>">
          </div>
        </div>

        <div class="auth-grid-2">
          <div class="auth-form-group">
            <label>Password (Min. 6 chars) *</label>
            <input type="password" name="password" class="auth-input" placeholder="••••••••" required>
          </div>
          <div class="auth-form-group">
            <label>Confirm Password *</label>
            <input type="password" name="password_confirm" class="auth-input" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="auth-btn">
          Create Wholesale Account &rarr;
        </button>
      </form>

      <div class="auth-footer-links">
        Already registered?
        <a href="<?= url('login.php') ?>">Sign In</a>
        <div style="margin-top:8px;">
          <a href="<?= url('index.php') ?>" style="color:#6e7d72;">&larr; Back to Homepage</a>
        </div>
      </div>

    <?php endif; ?>

  </div>
</div>

</body>
</html>
