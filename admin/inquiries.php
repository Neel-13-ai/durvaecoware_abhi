<?php
/**
 * Admin Wholesale Quotes & Inquiries Pipeline
 * Durva Eco Ware
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once BASE_PATH . '/app/Models/Inquiry.php';

require_admin();

// Handle Status & Note Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_csrf();
    $action = $_POST['action'];

    if ($action === 'update_status') {
        $id = clean_id($_POST['id'] ?? '');
        $status = clean_string($_POST['status'] ?? 'new');
        $notes = clean_string($_POST['admin_notes'] ?? '');

        if (!empty($id)) {
            Inquiry::updateStatus($id, $status, $notes);
            flash('success', "Inquiry updated to " . ucfirst(str_replace('_', ' ', $status)) . ".");
        }
        redirect('admin/inquiries.php?id=' . $id);
    }

    if ($action === 'delete') {
        $id = clean_id($_POST['id'] ?? '');
        if (!empty($id)) {
            Inquiry::delete($id);
            flash('success', "Inquiry deleted.");
        }
        redirect('admin/inquiries.php');
    }
}

$pageTitle = "Wholesale Quotes & Inquiries";
$pageSubtitle = "Manage customer leads, pricing requests and follow-up status";

$statusFilter = $_GET['status'] ?? null;
if (!in_array($statusFilter, ['new', 'contacted', 'in_progress', 'closed'])) {
    $statusFilter = null;
}

$inquiries = Inquiry::getAll($statusFilter);
$counts = Inquiry::countByStatus();

$selectedId = clean_id($_GET['id'] ?? '');
$selectedInquiry = !empty($selectedId) ? Inquiry::getById($selectedId) : null;

require_once __DIR__ . '/includes/header.php';
?>

<!-- STATUS PIPELINE TABS -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
  <div style="display:flex; gap:8px; flex-wrap:wrap;">
    <a href="<?= url('admin/inquiries.php') ?>"
       class="btn-admin <?= $statusFilter === null ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">
      All Inquiries (<?= $counts['total'] ?>)
    </a>
    <a href="<?= url('admin/inquiries.php?status=new') ?>"
       class="btn-admin <?= $statusFilter === 'new' ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">
      ⚡ New Leads (<?= $counts['new'] ?>)
    </a>
    <a href="<?= url('admin/inquiries.php?status=contacted') ?>"
       class="btn-admin <?= $statusFilter === 'contacted' ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">
      💬 Contacted (<?= $counts['contacted'] ?>)
    </a>
    <a href="<?= url('admin/inquiries.php?status=in_progress') ?>"
       class="btn-admin <?= $statusFilter === 'in_progress' ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">
      🔄 In Negotiation (<?= $counts['in_progress'] ?>)
    </a>
    <a href="<?= url('admin/inquiries.php?status=closed') ?>"
       class="btn-admin <?= $statusFilter === 'closed' ? 'btn-admin-primary' : 'btn-admin-secondary' ?>">
      ✓ Closed / Converted (<?= $counts['closed'] ?>)
    </a>
  </div>
</div>

<div style="display:grid; grid-template-columns: <?= $selectedInquiry ? '1.4fr 1fr' : '1fr' ?>; gap:25px; align-items:flex-start;">
  <!-- INQUIRIES LIST TABLE -->
  <div class="admin-card">
    <div class="card-body" style="padding:0;">
      <?php if (empty($inquiries)): ?>
        <div style="text-align:center; padding:50px; color:var(--admin-muted);">
          No inquiries found for this status.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Client / Company</th>
                <th>Product / Qty</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($inquiries as $inq): ?>
                <tr style="<?= ($selectedInquiry && $selectedInquiry['id'] == $inq['id']) ? 'background:#f0f8f1;' : '' ?>">
                  <td><strong>#DW-<?= $inq['id'] ?></strong></td>
                  <td>
                    <strong><?= e($inq['name']) ?></strong>
                    <?php if (!empty($inq['company'])): ?>
                      <div style="font-size:11.5px; color:var(--admin-muted);"><?= e($inq['company']) ?></div>
                    <?php endif; ?>
                    <div style="font-size:11px; color:#2e7d32;">📞 <?= e($inq['phone']) ?></div>
                  </td>
                  <td>
                    <strong><?= e($inq['product_interest'] ?: ($inq['product_name'] ?? 'General Wholesale')) ?></strong>
                    <?php if (!empty($inq['quantity'])): ?>
                      <div style="font-size:11px; color:var(--admin-muted);">Qty: <?= e($inq['quantity']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><?= time_ago($inq['created_at']) ?></td>
                  <td>
                    <span class="status-pill status-<?= $inq['status'] ?>">
                      <?= str_replace('_', ' ', $inq['status']) ?>
                    </span>
                  </td>
                  <td>
                    <div style="display:flex; gap:6px;">
                      <a href="<?= url('admin/inquiries.php?id=' . $inq['id'] . ($statusFilter ? '&status=' . $statusFilter : '')) ?>"
                         class="btn-admin btn-admin-primary btn-admin-sm">
                        View
                      </a>
                      <?php
                        $cleanPhone = preg_replace('/[^0-9]/', '', $inq['phone']);
                        if (!empty($cleanPhone)):
                      ?>
                        <a href="https://wa.me/<?= $cleanPhone ?>?text=Hello%20<?= urlencode($inq['name']) ?>,%20Durva%20Eco%20Ware%20here%20regarding%20your%20wholesale%20quote%20request."
                           target="_blank" class="btn-admin btn-admin-secondary btn-admin-sm" title="WhatsApp Lead">
                          💬
                        </a>
                      <?php endif; ?>
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

  <!-- INQUIRY DETAILS & STATUS MODIFIER PANEL -->
  <?php if ($selectedInquiry): ?>
    <div class="admin-card" style="border: 2px solid #cce5cf; position:sticky; top:90px;">
      <div class="card-header" style="background:#f4f9f4;">
        <h3>Inquiry #DW-<?= $selectedInquiry['id'] ?> Details</h3>
        <a href="<?= url('admin/inquiries.php' . ($statusFilter ? '?status=' . $statusFilter : '')) ?>"
           style="font-size:16px; color:var(--admin-muted); cursor:pointer;">&times;</a>
      </div>

      <div class="card-body">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; padding-bottom:12px; border-bottom:1px solid var(--admin-border);">
          <div>
            <h4 style="font-size:16px; color:var(--admin-text);"><?= e($selectedInquiry['name']) ?></h4>
            <div style="font-size:12.5px; color:var(--admin-muted);"><?= e($selectedInquiry['company'] ?? 'Individual Inquiry') ?></div>
          </div>
          <span class="status-pill status-<?= $selectedInquiry['status'] ?>">
            <?= str_replace('_', ' ', $selectedInquiry['status']) ?>
          </span>
        </div>

        <div style="display:grid; gap:10px; font-size:13px; margin-bottom:20px;">
          <div><strong>📞 Phone:</strong> <a href="tel:<?= preg_replace('/[^0-9+]/', '', $selectedInquiry['phone']) ?>" style="color:#1b6330; font-weight:700;"><?= e($selectedInquiry['phone']) ?></a></div>
          <?php if (!empty($selectedInquiry['email'])): ?>
            <div><strong>✉ Email:</strong> <a href="mailto:<?= e($selectedInquiry['email']) ?>" style="color:#1b6330;"><?= e($selectedInquiry['email']) ?></a></div>
          <?php endif; ?>
          <div><strong>📦 Product Requested:</strong> <?= e($selectedInquiry['product_interest'] ?: ($selectedInquiry['product_name'] ?? 'General Catalog')) ?></div>
          <div><strong>🔢 Target Volume:</strong> <?= e($selectedInquiry['quantity'] ?? 'Not specified') ?></div>
          <div><strong>🕒 Received:</strong> <?= date('d M Y, h:i A', strtotime($selectedInquiry['created_at'])) ?></div>
        </div>

        <?php if (!empty($selectedInquiry['message'])): ?>
          <div style="background:#fcfdfc; border:1px solid var(--admin-border); border-radius:8px; padding:12px; margin-bottom:20px; font-size:13px;">
            <strong style="display:block; margin-bottom:4px; font-size:11.5px; color:var(--admin-muted); text-transform:uppercase;">Customer Notes:</strong>
            <?= nl2br(e($selectedInquiry['message'])) ?>
          </div>
        <?php endif; ?>

        <!-- DIRECT ACTIONS (WHATSAPP / CALL) -->
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:24px;">
          <?php $cleanPhone = preg_replace('/[^0-9]/', '', $selectedInquiry['phone']); ?>
          <a href="https://wa.me/<?= $cleanPhone ?>?text=Hello%20<?= urlencode($selectedInquiry['name']) ?>,%20Durva%20Eco%20Ware%20sales%20team%20here%20regarding%20your%20inquiry%20#DW-<?= $selectedInquiry['id'] ?>."
             target="_blank" class="btn-admin btn-admin-primary" style="justify-content:center; background:#1ea851;">
            💬 Open WhatsApp
          </a>
          <a href="tel:<?= preg_replace('/[^0-9+]/', '', $selectedInquiry['phone']) ?>"
             class="btn-admin btn-admin-secondary" style="justify-content:center;">
            📞 Direct Dial
          </a>
        </div>

        <!-- STATUS UPDATE FORM -->
        <form method="POST" action="">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" name="id" value="<?= $selectedInquiry['id'] ?>">

          <div class="admin-form-group">
            <label>Update Pipeline Stage</label>
            <select name="status" class="admin-select">
              <option value="new" <?= $selectedInquiry['status'] === 'new' ? 'selected' : '' ?>>⚡ New Lead (Pending)</option>
              <option value="contacted" <?= $selectedInquiry['status'] === 'contacted' ? 'selected' : '' ?>>💬 Contacted Customer</option>
              <option value="in_progress" <?= $selectedInquiry['status'] === 'in_progress' ? 'selected' : '' ?>>🔄 Price Quoted / In Negotiation</option>
              <option value="closed" <?= $selectedInquiry['status'] === 'closed' ? 'selected' : '' ?>>✓ Closed / Deal Finalized</option>
            </select>
          </div>

          <div class="admin-form-group">
            <label>Internal Follow-up Notes</label>
            <textarea name="admin_notes" class="admin-textarea" rows="3"
                      placeholder="Add private team notes (e.g. Sent PDF catalog on WhatsApp, offered ₹1.15 per plate for 10K MOQ)..."><?= e($selectedInquiry['admin_notes'] ?? '') ?></textarea>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center;">
            <button type="submit" class="btn-admin btn-admin-primary">
              💾 Save Status & Notes
            </button>
            <button type="submit" name="action" value="delete" class="btn-admin btn-admin-danger btn-admin-sm"
                    onclick="return confirm('Permanently delete this inquiry?');">
              Delete
            </button>
          </div>
        </form>

      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
