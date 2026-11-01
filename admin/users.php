<?php
/**
 * Admin User & Role Management
 * Durva Eco Ware
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once BASE_PATH . '/app/Models/User.php';

require_admin();

$currentAdmin = current_user();

// Handle Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_role') {
    require_csrf();
    $targetId = clean_int($_POST['user_id'] ?? 0);
    $newRole = clean_string($_POST['new_role'] ?? 'customer');

    if ($targetId === $currentAdmin['id']) {
        flash('error', 'You cannot change your own administrative role.', 'warning');
    } else if (in_array($newRole, ['admin', 'customer'])) {
        User::updateRole($targetId, $newRole);
        flash('success', 'User role updated successfully.');
    }
    redirect('admin/users.php');
}

$pageTitle = "Users & Customer Accounts";
$pageSubtitle = "Manage registered wholesale customer profiles and administrative access";

$users = User::getAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h3>Registered Users (<?= count($users) ?>)</h3>
  </div>

  <div class="card-body" style="padding:0;">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Company</th>
            <th>Role</th>
            <th>Joined</th>
            <th>Role Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <strong><?= e($u['name']) ?></strong>
                <?php if ($u['id'] === $currentAdmin['id']): ?>
                  <span style="font-size:10px; background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:4px; margin-left:6px; font-weight:700;">(You)</span>
                <?php endif; ?>
              </td>
              <td><?= e($u['email']) ?></td>
              <td><?= e($u['phone'] ?? '—') ?></td>
              <td><?= e($u['company'] ?? '—') ?></td>
              <td>
                <span class="status-pill status-<?= $u['role'] === 'admin' ? 'active' : 'contacted' ?>">
                  <?= ucfirst($u['role']) ?>
                </span>
              </td>
              <td><?= time_ago($u['created_at']) ?></td>
              <td>
                <?php if ($u['id'] !== $currentAdmin['id']): ?>
                  <form method="POST" action="" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_role">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <?php if ($u['role'] === 'admin'): ?>
                      <input type="hidden" name="new_role" value="customer">
                      <button type="submit" class="btn-admin btn-admin-secondary btn-admin-sm"
                              onclick="return confirm('Demote this administrator to customer?');">
                        Demote to Customer
                      </button>
                    <?php else: ?>
                      <input type="hidden" name="new_role" value="admin">
                      <button type="submit" class="btn-admin btn-admin-primary btn-admin-sm"
                              onclick="return confirm('Grant full administrative privileges to this user?');">
                        Make Admin
                      </button>
                    <?php endif; ?>
                  </form>
                <?php else: ?>
                  <span style="color:var(--admin-muted); font-size:12px;">Active Session</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
