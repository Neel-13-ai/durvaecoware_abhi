<?php
/**
 * MongoDB Connection & Database Management
 * Durva Eco Ware
 */

require_once __DIR__ . '/app.php';

class Database {
    private static ?\MongoDB\Client $client = null;
    private static ?\MongoDB\Database $database = null;
    private static array $config = [];

    public static function getConfig(): array {
        if (empty(self::$config)) {
            self::$config = [
                'uri'      => env('MONGODB_URI', 'mongodb://127.0.0.1:27017'),
                'database' => env('MONGODB_DATABASE', 'durva_eco_ware')
            ];
        }
        return self::$config;
    }

    public static function setConfig(array $config): void {
        self::$config = array_merge(self::getConfig(), $config);
        self::$client = null;
        self::$database = null;
    }

    public static function isDriverAvailable(): bool {
        return extension_loaded('mongodb') && class_exists('MongoDB\Client');
    }

    public static function getClient(): \MongoDB\Client {
        if (!self::isDriverAvailable()) {
            if (php_sapi_name() !== 'cli' && !str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'install.php')) {
                redirect('install.php');
            }
            throw new RuntimeException("The MongoDB PHP extension (php_mongodb.dll) or library is not installed/enabled. Please enable 'extension=mongodb' in your php.ini.");
        }

        if (self::$client === null) {
            $cfg = self::getConfig();
            try {
                self::$client = new \MongoDB\Client($cfg['uri'], [], [
                    'serverSelectionTimeoutMS' => 5000
                ]);
            } catch (\Throwable $e) {
                if (php_sapi_name() !== 'cli' && !str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'install.php')) {
                    redirect('install.php');
                }
                throw $e;
            }
        }

        return self::$client;
    }

    public static function getDatabase(): \MongoDB\Database {
        if (self::$database === null) {
            $client = self::getClient();
            $cfg = self::getConfig();
            self::$database = $client->selectDatabase($cfg['database']);
        }

        return self::$database;
    }

    public static function getCollection(string $name): \MongoDB\Collection {
        return self::getDatabase()->selectCollection($name);
    }

    /**
     * Test connection credentials against MongoDB server / Atlas cluster
     */
    public static function testConnection(?string $uri = null, ?string $database = null): array {
        if (!extension_loaded('mongodb')) {
            return [
                'success' => false,
                'error' => "PHP 'mongodb' extension (php_mongodb.dll) is not enabled in php.ini."
            ];
        }

        if (!class_exists('MongoDB\Client')) {
            return [
                'success' => false,
                'error' => "MongoDB PHP library is missing. Run 'composer install' in the project directory."
            ];
        }

        $uri = $uri ?? self::getConfig()['uri'];
        $dbName = $database ?? self::getConfig()['database'];

        try {
            $client = new \MongoDB\Client($uri, [], [
                'serverSelectionTimeoutMS' => 5000
            ]);
            // Ping database to verify connection
            $command = new \MongoDB\Driver\Command(['ping' => 1]);
            $client->selectDatabase($dbName)->command($command);

            return [
                'success' => true,
                'client' => $client
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// Global convenience helpers
function get_db(): \MongoDB\Database {
    return Database::getDatabase();
}

function get_collection(string $name): \MongoDB\Collection {
    return Database::getCollection($name);
}
