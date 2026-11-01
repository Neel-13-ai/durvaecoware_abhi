<?php
/**
 * Setting Model
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';

class Setting {
    private static array $cache = [];

    public static function getAll(): array {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        try {
            $db = get_db();
            $stmt = $db->query("SELECT `key_name`, `value` FROM `settings`");
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key_name']] = $row['value'];
            }
            self::$cache = $settings;
            return $settings;
        } catch (Exception $e) {
            return [];
        }
    }

    public static function get(string $key, ?string $default = null): ?string {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): bool {
        $db = get_db();
        $stmt = $db->prepare("
            INSERT INTO `settings` (`key_name`, `value`, `group_name`, `updated_at`)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()
        ");
        $res = $stmt->execute([$key, $value, $group]);
        self::$cache[$key] = $value;
        return $res;
    }

    public static function updateBatch(array $settings): bool {
        $db = get_db();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO `settings` (`key_name`, `value`, `updated_at`)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()
            ");
            foreach ($settings as $key => $value) {
                $stmt->execute([$key, $value]);
                self::$cache[$key] = $value;
            }
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
