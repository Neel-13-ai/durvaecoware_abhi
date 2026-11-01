<?php
/**
 * 1-Click Web Installer & Setup Wizard (MongoDB)
 * Durva Eco Ware
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/database/mongo_seed.php';

$requirements = [
    'php_version' => [
        'name' => 'PHP Version (>= 7.4)',
        'pass' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'detail' => 'Current: ' . PHP_VERSION
    ],
    'ext_mongodb' => [
        'name' => 'PHP MongoDB Extension (php_mongodb.dll)',
        'pass' => extension_loaded('mongodb'),
        'detail' => extension_loaded('mongodb') ? 'Installed & Enabled' : 'Missing from php.ini'
    ],
    'mongo_library' => [
        'name' => 'MongoDB PHP Library (mongodb/mongodb)',
        'pass' => class_exists('MongoDB\Client'),
        'detail' => class_exists('MongoDB\Client') ? 'Installed in vendor/' : 'Missing (run composer install)'
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
    $mongoUri = trim($_POST['mongo_uri'] ?? '');
    $dbName = trim($_POST['db_name'] ?? 'durva_eco_ware');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@durvaecoware.com');
    $adminPass = $_POST['admin_pass'] ?? 'AdminPassword@123';

    if (empty($mongoUri)) {
        $error = "Please provide your MongoDB Connection URI (e.g. mongodb+srv://... or mongodb://127.0.0.1:27017).";
    } else {
        $res = seed_mongodb($mongoUri, $dbName, $adminEmail, $adminPass);
        $logs = $res['logs'] ?? [];

        if ($res['success']) {
            // Write updated connection details to .env
            $envPath = BASE_PATH . '/.env';
            $envContent = "# Durva Eco Ware - Environment Configuration\n" .
                          "APP_NAME=\"Durva Eco Ware\"\n" .
                          "APP_ENV=development\n" .
                          "APP_DEBUG=true\n\n" .
                          "# Database Connection (MongoDB)\n" .
                          "DB_CONNECTION=mongodb\n" .
                          "MONGODB_URI=\"" . addslashes($mongoUri) . "\"\n" .
                          "MONGODB_DATABASE=" . $dbName . "\n\n" .
                          "# Session & Security\n" .
                          "SESSION_LIFETIME=86400\n";
            @file_put_contents($envPath, $envContent);

            $logs[] = "✓ Updated .env with active MongoDB URI.";
            $success = true;
        } else {
            $error = $res['error'] ?? 'Installation encountered an error.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Durva Eco Ware - MongoDB Setup & Installer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #1b6330;
      --primary-dark: #124520;
      --accent: #d97706;
      --bg: #f8faf7;
      --card-bg: #ffffff;
      --text: #1a221b;
      --text-muted: #5f7062;
      --border: #e0e7df;
      --danger: #dc2626;
      --success: #16a34a;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 40px 20px;
    }
    .install-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      max-width: 680px;
      width: 100%;
      overflow: hidden;
    }
    .install-header {
      background: #11381c;
      color: #fff;
      padding: 35px 30px;
      text-align: center;
    }
    .install-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      margin-bottom: 8px;
    }
    .install-header p {
      color: #bbf7d0;
      font-size: 14px;
    }
    .badge {
      display: inline-block;
      background: rgba(255,255,255,0.15);
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 12px;
    }
    .install-body {
      padding: 30px;
    }
    .req-list {
      background: #fbfcf9;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 24px;
    }
    .req-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px dashed var(--border);
      font-size: 13.5px;
    }
    .req-item:last-child { border-bottom: none; }
    .status {
      font-weight: 700;
      font-size: 12px;
      padding: 3px 8px;
      border-radius: 6px;
    }
    .status-pass { background: #dcfce7; color: var(--success); }
    .status-fail { background: #fee2e2; color: var(--danger); }
    .guide-box {
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 24px;
      font-size: 13px;
      line-height: 1.5;
    }
    .guide-box h4 {
      color: #b45309;
      margin-bottom: 8px;
      font-size: 14px;
    }
    .guide-box ol {
      margin-left: 20px;
      margin-top: 6px;
    }
    .guide-box code {
      background: #fef3c7;
      padding: 2px 5px;
      border-radius: 4px;
      font-size: 12px;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 15px;
      margin-bottom: 24px;
    }
    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .form-group label {
      font-size: 13px;
      font-weight: 600;
      color: var(--text);
    }
    .form-group input, .form-group textarea {
      padding: 11px 14px;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 14px;
      background: #fbfcf9;
      font-family: inherit;
    }
    .form-group input:focus, .form-group textarea:focus {
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
      max-height: 200px;
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
    <span class="badge">MongoDB Setup Wizard</span>
    <h1>Durva Eco Ware</h1>
    <p>Connect your MongoDB Atlas cluster or local database in 1 click</p>
  </div>

  <div class="install-body">
    <!-- Server Requirements -->
    <h3 style="font-size: 15px; margin-bottom: 12px;">1. System & Extension Check</h3>
    <div class="req-list">
      <?php foreach ($requirements as $req): ?>
        <div class="req-item">
          <div>
            <strong><?= e($req['name']) ?></strong>
            <div style="font-size: 11px; color: var(--text-muted);"><?= e($req['detail']) ?></div>
          </div>
          <span class="status <?= $req['pass'] ? 'status-pass' : 'status-fail' ?>">
            <?= $req['pass'] ? '✓ Ready' : '✗ Action Needed' ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!extension_loaded('mongodb')): ?>
      <div class="guide-box">
        <h4>⚠️ PHP MongoDB Extension (php_mongodb.dll) Needed</h4>
        To allow PHP to connect to MongoDB on Windows (XAMPP):
        <ol>
          <li>Download the matching <code>php_mongodb.dll</code> for PHP <?= PHP_VERSION ?> (Thread Safe x64) from PECL: <a href="https://pecl.php.net/package/mongodb" target="_blank" style="color:#b45309; font-weight:700;">pecl.php.net/package/mongodb</a></li>
          <li>Place the downloaded <code>php_mongodb.dll</code> file into <code>C:\xampp\php\ext\</code></li>
          <li>Open <code>C:\xampp\php\php.ini</code> and add: <code>extension=mongodb</code></li>
          <li>Restart Apache in the XAMPP Control Panel and refresh this page.</li>
        </ol>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <strong>Setup Error:</strong><br>
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
        <strong>🎉 MongoDB Initialization Successful!</strong><br>
        Collections created, indexes built, sample products imported, and admin credentials configured.
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
        <h3 style="font-size: 15px; margin-bottom: 14px;">2. MongoDB Cluster Connection</h3>
        <div class="form-grid">
          <div class="form-group">
            <label>MongoDB Connection URI (Cluster Link or Local)</label>
            <textarea name="mongo_uri" rows="3" required placeholder="e.g. mongodb+srv://username:password@cluster0.abcde.mongodb.net/?retryWrites=true&w=majority"><?= e(env('MONGODB_URI', '')) ?></textarea>
            <small style="color: var(--text-muted); font-size: 11px;">Paste your connection string from MongoDB Atlas or local MongoDB instance.</small>
          </div>
          <div class="form-group">
            <label>Database Name</label>
            <input type="text" name="db_name" value="<?= e(env('MONGODB_DATABASE', 'durva_eco_ware')) ?>" required>
          </div>
        </div>

        <h3 style="font-size: 15px; margin-top: 15px; margin-bottom: 14px;">3. Admin Account Setup</h3>
        <div class="form-grid-2" style="margin-bottom: 20px;">
          <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="admin_email" value="admin@durvaecoware.com" required>
          </div>
          <div class="form-group">
            <label>Admin Password</label>
            <input type="password" name="admin_pass" value="AdminPassword@123" required>
          </div>
        </div>

        <button type="submit" name="run_install" value="1" class="btn">
          🚀 Connect & Initialize MongoDB Collections
        </button>
      </form>

    <?php endif; ?>
  </div>
</div>

</body>
</html>
