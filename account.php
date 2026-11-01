<?php
/**
 * Customer Account Portal
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/app/Models/User.php';
require_once __DIR__ . '/app/Models/Inquiry.php';

require_login();

$user = current_user();
$userId = $user['id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    require_csrf();
    $name = clean_string($_POST['name'] ?? '');
    $phone = clean_phone($_POST['phone'] ?? '');
    $company = clean_string($_POST['company'] ?? '');

    if (!empty($name)) {
        User::updateProfile($userId, [
            'name'    => $name,
            'phone'   => $phone,
            'company' => $company
        ]);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_company'] = $company;
        flash('success', 'Profile updated successfully.');
    }
    redirect('account.php');
}

$inquiries = Inquiry::getByUser($userId);
$pageTitle = "My Account & Inquiries";

require_once __DIR__ . '/includes/header.php';
?>

<div style="background: var(--cream); padding: 40px 20px; border-bottom: 1px solid var(--border);">
  <div class="section-inner" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
    <div>
      <span class="eyebrow">Customer Portal</span>
      <h1 style="font-family:'Playfair Display',serif; font-size:32px; color:var(--dark);">
        Welcome, <?= e($user['name']) ?>
      </h1>
      <p style="color:var(--muted); font-size:14px;">
        Track your wholesale quotation status and manage your account details
      </p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="<?= url('products.php') ?>" class="btn btn-outline btn-sm">Browse Products</a>
      <a href="<?= url('index.php#wholesale') ?>" class="btn btn-primary btn-sm">+ New Quote Request</a>
    </div>
  </div>
</div>

<main class="section" style="padding: 30px 20px 80px;">
  <div class="section-inner">
    <?php if (!empty($_SESSION['registration_success'])): 
      $regMeta = $_SESSION['registration_success'];
      unset($_SESSION['registration_success']);
    ?>
      <div style="background: linear-gradient(135deg, #eaf6ec, #dcfce7); border: 1.5px solid #86efac; border-radius: 14px; padding: 20px 24px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; gap: 20px; box-shadow: 0 4px 15px rgba(22, 101, 52, 0.08);">
        <div style="display:flex; align-items:center; gap:16px;">
          <div style="width:48px; height:48px; border-radius:50%; background:#15803d; color:#fff; display:grid; place-items:center; font-size:24px; flex-shrink:0;">✓</div>
          <div>
            <h4 style="font-size:16px; color:#14532d; margin-bottom:3px;">🎉 Welcome to Durva Eco Ware, <?= e($regMeta['name']) ?>!</h4>
            <p style="font-size:13.5px; color:#166534; margin:0;">Your wholesale account is active for <strong><?= e($regMeta['email']) ?></strong>. You can now request quotes and track orders below.</p>
          </div>
        </div>
        <a href="<?= url('products.php') ?>" class="btn btn-primary btn-sm" style="white-space:nowrap;">Browse Catalog 🌿</a>
      </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 2.5fr; gap: 35px; align-items: flex-start;">

    <!-- PROFILE DETAILS -->
    <div style="background:#fff; border:1px solid var(--border); border-radius:14px; padding:24px; box-shadow:var(--shadow);">
      <h3 style="font-size:16px; margin-bottom:15px; color:var(--dark); padding-bottom:10px; border-bottom:1px solid var(--border);">
        Business Profile
      </h3>

      <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px;">Full Name</label>
          <input type="text" name="name" class="field" value="<?= e($user['name']) ?>" required>
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px;">Email (Permanent)</label>
          <input type="email" class="field" value="<?= e($user['email']) ?>" disabled style="background:#f2f4f1; cursor:not-allowed;">
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px;">Phone / WhatsApp</label>
          <input type="text" name="phone" class="field" value="<?= e($user['phone']) ?>">
        </div>

        <div style="margin-bottom:18px;">
          <label style="display:block; font-size:12.5px; font-weight:700; margin-bottom:4px;">Company Name</label>
          <input type="text" name="company" class="field" value="<?= e($user['company']) ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">
          Update Information
        </button>
      </form>
    </div>

    <!-- MY INQUIRIES LIST -->
    <div style="background:#fff; border:1px solid var(--border); border-radius:14px; padding:24px; box-shadow:var(--shadow);">
      <h3 style="font-size:16px; margin-bottom:15px; color:var(--dark); padding-bottom:10px; border-bottom:1px solid var(--border);">
        My Wholesale Quote Requests (<?= count($inquiries) ?>)
      </h3>

      <?php if (empty($inquiries)): ?>
        <div style="text-align:center; padding:40px 15px; color:var(--muted);">
          <div style="font-size:35px; margin-bottom:10px;">📋</div>
          <p>You have not submitted any wholesale inquiries yet.</p>
          <a href="<?= url('index.php#wholesale') ?>" class="btn btn-primary btn-sm" style="margin-top:12px;">
            Request a Wholesale Quote &rarr;
          </a>
        </div>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; font-size:13.5px;">
            <thead>
              <tr style="background:#fbfdfa; border-bottom:1px solid var(--border); text-align:left;">
                <th style="padding:10px 12px; font-size:12px; color:var(--muted);">Inquiry ID</th>
                <th style="padding:10px 12px; font-size:12px; color:var(--muted);">Product / Scope</th>
                <th style="padding:10px 12px; font-size:12px; color:var(--muted);">Quantity</th>
                <th style="padding:10px 12px; font-size:12px; color:var(--muted);">Date</th>
                <th style="padding:10px 12px; font-size:12px; color:var(--muted);">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($inquiries as $inq): ?>
                <tr style="border-bottom:1px solid #edf1eb;">
                  <td style="padding:12px; font-weight:700;">#DW-<?= $inq['id'] ?></td>
                  <td style="padding:12px;">
                    <strong><?= e($inq['product_interest'] ?: ($inq['product_name'] ?? 'General Wholesale')) ?></strong>
                    <?php if (!empty($inq['message'])): ?>
                      <div style="font-size:11.5px; color:var(--muted);"><?= e(substr($inq['message'], 0, 45)) ?>...</div>
                    <?php endif; ?>
                  </td>
                  <td style="padding:12px;"><?= e($inq['quantity'] ?? 'N/A') ?></td>
                  <td style="padding:12px; color:var(--muted); font-size:12px;"><?= time_ago($inq['created_at']) ?></td>
                  <td style="padding:12px;">
                    <?php
                      $st = $inq['status'];
                      $badgeClass = match($st) {
                        'new' => 'background:#dbeafe; color:#1e40af;',
                        'contacted' => 'background:#e0f2fe; color:#0369a1;',
                        'in_progress' => 'background:#fae8ff; color:#86198f;',
                        'closed' => 'background:#dcfce7; color:#15803d;',
                        default => 'background:#f3f4f6; color:#4b5563;'
                      };
                    ?>
                    <span style="padding:3px 8px; border-radius:12px; font-size:11px; font-weight:700; text-transform:capitalize; <?= $badgeClass ?>">
                      <?= str_replace('_', ' ', $st) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
