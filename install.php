<?php
/**
 * 1-Click Web Installer & Setup Wizard
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/database/migrate.php';

$requirements = [
    'php_version' => [
        'name' => 'PHP Version (>= 7.4)',
        'pass' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'detail' => 'Current: ' . PHP_VERSION
    ],
    'pdo' => [
        'name' => 'PDO Extension',
        'pass' => extension_loaded('pdo'),
        'detail' => extension_loaded('pdo') ? 'Installed' : 'Missing'
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL Driver',
        'pass' => extension_loaded('pdo_mysql'),
        'detail' => extension_loaded('pdo_mysql') ? 'Installed' : 'Missing'
    ],
    'uploads_dir' => [
        'name' => 'Uploads Directory Writable',
        'pass' => is_writable(BASE_PATH . '/assets/uploads') || @mkdir(BASE_PATH . '/assets/uploads', 0755, true),
        'detail' => 'assets/uploads/'
    ]
];

$allPassed = !in_array(false, array_column($requirements, 'pass'), true);

$error = null;
$success = false;
$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_install'])) {
    $host = trim($_POST['db_host'] ?? '127.0.0.1');
    $port = (int)($_POST['db_port'] ?? 3306);
    $user = trim($_POST['db_user'] ?? 'root');
    $pass = $_POST['db_pass'] ?? '';
    $dbName = trim($_POST['db_name'] ?? 'durva_eco_ware');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@durvaecoware.com');
    $adminPass = $_POST['admin_pass'] ?? 'AdminPassword@123';

    $res = run_migrations($host, $port, $user, $pass, $dbName);
    $logs = $res['logs'] ?? [];

    if ($res['success']) {
        // Update admin password if custom provided
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $hash = password_hash($adminPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE `users` SET `email` = ?, `password_hash` = ? WHERE `role` = 'admin' LIMIT 1");
            $stmt->execute([$adminEmail, $hash]);
            $logs[] = "✓ Admin account configured: $adminEmail";
        } catch (Exception $e) {
            $logs[] = "⚠ Could not update admin credentials: " . $e->getMessage();
        }
        $success = true;
    } else {
        $error = $res['error'] ?? 'Installation encountered an error.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Durva Eco Ware - 1-Click Setup & Installer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #1b6330;
      --primary-dark: #0e3a1b;
      --primary-light: #2f8546;
      --accent: #d4e7cb;
      --bg: #f4f7f2;
      --card-bg: #ffffff;
      --text: #1b261e;
      --text-muted: #5e6d62;
      --border: #e0e8dc;
      --radius: 16px;
      --shadow: 0 16px 40px rgba(18, 56, 28, 0.08);
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px 20px;
    }
    .install-card {
      background: var(--card-bg);
      max-width: 680px;
      width: 100%;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
      overflow: hidden;
    }
    .install-header {
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      color: #fff;
      padding: 35px 35px 30px;
      text-align: center;
    }
    .install-header .badge {
      display: inline-block;
      background: rgba(255,255,255,0.18);
      padding: 4px 14px;
      border-radius: 50px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      margin-bottom: 12px;
    }
    .install-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 30px;
      margin-bottom: 6px;
    }
    .install-header p {
      color: #d1e3d4;
      font-size: 14px;
    }
    .install-body {
      padding: 35px;
    }
    .req-list {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-bottom: 25px;
      padding-bottom: 25px;
      border-bottom: 1px solid var(--border);
    }
    .req-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 14px;
      background: #fbfdfa;
      border-radius: 8px;
      border: 1px solid #edf2ea;
      font-size: 13px;
      font-weight: 500;
    }
    .req-item .status {
      font-weight: 700;
      font-size: 12px;
      padding: 2px 8px;
      border-radius: 4px;
    }
    .status-pass { background: #dcfce7; color: #15803d; }
    .status-fail { background: #fee2e2; color: #b91c1c; }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }
    .form-group {
      margin-bottom: 16px;
    }
    .form-group.full {
      grid-column: 1 / -1;
    }
    label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 6px;
      color: var(--text);
    }
    input[type="text"], input[type="password"], input[type="email"], input[type="number"] {
      width: 100%;
      padding: 11px 14px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      font-size: 14px;
      font-family: inherit;
      background: #fbfdfa;
      transition: border-color .2s;
    }
    input:focus {
      outline: none;
      border-color: var(--primary);
      background: #fff;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 14px 24px;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      width: 100%;
      transition: background .2s, transform .2s;
      text-decoration: none;
    }
    .btn:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: #eef3eb;
      color: var(--primary);
      border: 1px solid var(--border);
    }
    .btn-secondary:hover {
      background: #e1ecdc;
    }
    .alert {
      padding: 14px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
    }
    .alert-error {
      background: #fee2e2;
      border: 1px solid #fca5a5;
      color: #991b1b;
    }
    .alert-success {
      background: #ecfdf5;
      border: 1px solid #a7f3d0;
      color: #065f46;
    }
    .log-box {
      background: #0f1712;
      color: #4ade80;
      font-family: monospace;
      font-size: 12px;
      padding: 15px;
      border-radius: 8px;
      margin: 20px 0;
      max-height: 180px;
      overflow-y: auto;
      line-height: 1.5;
    }
    .success-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-top: 20px;
    }
  </style>
</head>
<body>

<div class="install-card">
  <div class="install-header">
    <span class="badge">Automatic Setup Wizard</span>
    <h1>Durva Eco Ware</h1>
    <p>Configure database and install default assets in 1 click</p>
  </div>

  <div class="install-body">
    <!-- Server Requirements -->
    <h3 style="font-size: 15px; margin-bottom: 12px;">1. System Health Check</h3>
    <div class="req-list">
      <?php foreach ($requirements as $req): ?>
        <div class="req-item">
          <span><?= e($req['name']) ?></span>
          <span class="status <?= $req['pass'] ? 'status-pass' : 'status-fail' ?>">
            <?= $req['pass'] ? '✓ Ready' : '✗ Failed' ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <strong>Installation Error:</strong><br>
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($logs)): ?>
      <div class="log-box">
        <?php foreach ($logs as $log): ?>
          <div><?= e($log) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <strong>🎉 Installation Successful!</strong><br>
        Database created, tables migrated, sample eco-products and admin credentials loaded.
      </div>
      <div style="background: #fbfcf9; border: 1px solid var(--border); padding: 15px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
        <strong>Admin Login Credentials:</strong><br>
        Email: <code><?= e($_POST['admin_email'] ?? 'admin@durvaecoware.com') ?></code><br>
        Password: <code><?= e($_POST['admin_pass'] ?? 'AdminPassword@123') ?></code>
      </div>
      <div class="success-actions">
        <a href="<?= url('index.php') ?>" class="btn">View Public Website 🌿</a>
        <a href="<?= url('login.php') ?>" class="btn btn-secondary">Go to Admin Dashboard ↗</a>
      </div>

    <?php else: ?>

      <form method="POST" action="">
        <h3 style="font-size: 15px; margin-bottom: 14px;">2. MySQL Database Settings (XAMPP Defaults)</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>Database Host</label>
            <input type="text" name="db_host" value="127.0.0.1" required>
          </div>
          <div class="form-group">
            <label>Database Port</label>
            <input type="number" name="db_port" value="3306" required>
          </div>
          <div class="form-group">
            <label>Database Username</label>
            <input type="text" name="db_user" value="root" required>
          </div>
          <div class="form-group">
            <label>Database Password</label>
            <input type="password" name="db_pass" value="" placeholder="Empty by default in XAMPP">
          </div>
          <div class="form-group full">
            <label>Database Name</label>
            <input type="text" name="db_name" value="durva_eco_ware" required>
            <small style="color: var(--text-muted); font-size: 11px;">If this database does not exist, the installer will create it automatically.</small>
          </div>
        </div>

        <h3 style="font-size: 15px; margin-top: 15px; margin-bottom: 14px;">3. Admin Account Setup</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="admin_email" value="admin@durvaecoware.com" required>
          </div>
          <div class="form-group">
            <label>Admin Password</label>
            <input type="password" name="admin_pass" value="AdminPassword@123" required>
          </div>
        </div>

        <button type="submit" name="run_install" value="1" class="btn" style="margin-top: 15px;">
          🚀 Install & Run Migrations
        </button>
      </form>

    <?php endif; ?>
  </div>
</div>

</body>
</html>
