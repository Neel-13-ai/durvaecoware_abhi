<?php
/**
 * Database Connection & PDO Management
 * Durva Eco Ware
 */

require_once __DIR__ . '/app.php';

class Database {
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function getConfig(): array {
        if (empty(self::$config)) {
            self::$config = [
                'host'     => env('DB_HOST', '127.0.0.1'),
                'port'     => (int)env('DB_PORT', 3306),
                'database' => env('DB_DATABASE', 'durva_eco_ware'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset'  => 'utf8mb4'
            ];
        }
        return self::$config;
    }

    public static function setConfig(array $config): void {
        self::$config = array_merge(self::getConfig(), $config);
        self::$instance = null; // Reset connection if config changes
    }

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $cfg = self::getConfig();
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 3,
            ];

            try {
                self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
            } catch (PDOException $e) {
                // If standard port 3306 fails on XAMPP, check if 3307 succeeds (common XAMPP alternate)
                if ($cfg['port'] == 3306) {
                    try {
                        $fallbackDsn = "mysql:host={$cfg['host']};port=3307;dbname={$cfg['database']};charset={$cfg['charset']}";
                        self::$instance = new PDO($fallbackDsn, $cfg['username'], $cfg['password'], $options);
                        self::$config['port'] = 3307;
                        return self::$instance;
                    } catch (PDOException $fallbackError) {
                        // Fallback also failed
                    }
                }
                
                // If database does not exist or connection failed and installer is available
                if (php_sapi_name() !== 'cli' && !str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'install.php')) {
                    redirect('install.php');
                }
                throw $e;
            }
        }

        return self::$instance;
    }

    /**
     * Test connection credentials without specifying a database
     */
    public static function testServerConnection(string $host, int $port, string $user, string $pass): array {
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            return ['success' => true, 'pdo' => $pdo];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Convenience function
function get_db(): PDO {
    return Database::getConnection();
}
