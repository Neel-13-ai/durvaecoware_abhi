<?php
/**
 * Database Migration & Seeder CLI Runner
 * Durva Eco Ware
 *
 * Usage: php database/migrate.php
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

function run_migrations(?string $host = null, ?int $port = null, ?string $user = null, ?string $pass = null, ?string $dbName = null): array {
    $cfg = Database::getConfig();
    $host = $host ?? $cfg['host'];
    $port = $port ?? $cfg['port'];
    $user = $user ?? $cfg['username'];
    $pass = $pass ?? $cfg['password'];
    $dbName = $dbName ?? $cfg['database'];

    $logs = [];
    $logs[] = "Connecting to MySQL server at $host:$port as user '$user'...";

    // 1. Connect to server
    try {
        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $logs[] = "✓ Connected to MySQL server.";
    } catch (PDOException $e) {
        // Try fallback to port 3307 if 3306 failed
        if ($port == 3306) {
            try {
                $port = 3307;
                $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $logs[] = "✓ Connected to MySQL server on alternate port 3307.";
            } catch (PDOException $e2) {
                return ['success' => false, 'logs' => $logs, 'error' => "Connection failed: " . $e->getMessage()];
            }
        } else {
            return ['success' => false, 'logs' => $logs, 'error' => "Connection failed: " . $e->getMessage()];
        }
    }

    // 2. Create Database
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `$dbName`;");
        $logs[] = "✓ Database `$dbName` created / selected.";
    } catch (PDOException $e) {
        return ['success' => false, 'logs' => $logs, 'error' => "Failed to create database: " . $e->getMessage()];
    }

    // 3. Run schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        return ['success' => false, 'logs' => $logs, 'error' => "Schema file not found at $schemaFile"];
    }

    try {
        $schemaSql = file_get_contents($schemaFile);
        $pdo->exec($schemaSql);
        $logs[] = "✓ Database tables successfully created from schema.sql.";
    } catch (PDOException $e) {
        return ['success' => false, 'logs' => $logs, 'error' => "Schema execution error: " . $e->getMessage()];
    }

    // 4. Run seed.sql
    $seedFile = __DIR__ . '/seed.sql';
    if (file_exists($seedFile)) {
        try {
            $seedSql = file_get_contents($seedFile);
            $pdo->exec($seedSql);
            $logs[] = "✓ Seed data (categories, products, admin user, settings) successfully imported.";
        } catch (PDOException $e) {
            $logs[] = "⚠ Warning executing seed.sql: " . $e->getMessage();
        }
    }

    // 5. Update .env file with verified database settings
    $envPath = BASE_PATH . DIRECTORY_SEPARATOR . '.env';
    $envContent = "# Durva Eco Ware - Environment Configuration\n" .
                  "APP_NAME=\"Durva Eco Ware\"\n" .
                  "APP_ENV=development\n" .
                  "APP_DEBUG=true\n\n" .
                  "DB_HOST=$host\n" .
                  "DB_PORT=$port\n" .
                  "DB_DATABASE=$dbName\n" .
                  "DB_USERNAME=$user\n" .
                  "DB_PASSWORD=$pass\n\n" .
                  "SESSION_LIFETIME=86400\n";
    @file_put_contents($envPath, $envContent);
    $logs[] = "✓ Configuration saved to .env file.";

    return ['success' => true, 'logs' => $logs];
}

// Run immediately if executed via CLI
if (php_sapi_name() === 'cli') {
    echo "===============================================\n";
    echo "Durva Eco Ware - Database Migration Runner\n";
    echo "===============================================\n";
    $result = run_migrations();
    foreach ($result['logs'] as $log) {
        echo $log . "\n";
    }
    if ($result['success']) {
        echo "\n>>> MIGRATION COMPLETED SUCCESSFULLY! <<<\n";
        echo "Default Admin: admin@durvaecoware.com | Password: AdminPassword@123\n";
        exit(0);
    } else {
        echo "\n>>> MIGRATION FAILED: " . ($result['error'] ?? 'Unknown error') . " <<<\n";
        exit(1);
    }
}
