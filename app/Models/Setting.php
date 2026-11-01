<?php
/**
 * Setting Model (MongoDB)
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';

class Setting {
    private static array $cache = [];

    private static function getCollection(): \MongoDB\Collection {
        return get_collection('settings');
    }

    public static function getAll(): array {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        try {
            $col = self::getCollection();
            $cursor = $col->find();
            $settings = [];
            foreach ($cursor as $doc) {
                if (isset($doc['key_name'])) {
                    $settings[$doc['key_name']] = (string)($doc['value'] ?? '');
                }
            }
            self::$cache = $settings;
            return $settings;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function get(string $key, ?string $default = null): ?string {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): bool {
        try {
            $col = self::getCollection();
            $col->updateOne(
                ['key_name' => $key],
                [
                    '$set' => [
                        'key_name'   => $key,
                        'value'      => (string)$value,
                        'group_name' => $group,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]
                ],
                ['upsert' => true]
            );
            self::$cache[$key] = (string)$value;
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function updateBatch(array $settings): bool {
        try {
            $col = self::getCollection();
            $now = date('Y-m-d H:i:s');
            foreach ($settings as $key => $value) {
                $col->updateOne(
                    ['key_name' => $key],
                    [
                        '$set' => [
                            'key_name'   => $key,
                            'value'      => (string)$value,
                            'updated_at' => $now
                        ]
                    ],
                    ['upsert' => true]
                );
                self::$cache[$key] = (string)$value;
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
