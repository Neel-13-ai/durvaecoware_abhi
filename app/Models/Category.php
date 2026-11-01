<?php
/**
 * Category Model
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';

class Category {
    public static function getAll(): array {
        $db = get_db();
        $sql = "
            SELECT c.*, COUNT(p.id) AS product_count
            FROM `categories` c
            LEFT JOIN `products` p ON c.id = p.category_id AND p.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC, c.name ASC
        ";
        return $db->query($sql)->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM `categories` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getBySlug(string $slug): ?array {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM `categories` WHERE `slug` = ? LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = get_db();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);
        
        $stmt = $db->prepare("
            INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `sort_order`)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $slug,
            $data['description'] ?? null,
            $data['icon'] ?? '🌱',
            (int)($data['sort_order'] ?? 0)
        ]);

        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = get_db();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        $stmt = $db->prepare("
            UPDATE `categories`
            SET `name` = ?, `slug` = ?, `description` = ?, `icon` = ?, `sort_order` = ?
            WHERE `id` = ?
        ");
        return $stmt->execute([
            $data['name'],
            $slug,
            $data['description'] ?? null,
            $data['icon'] ?? '🌱',
            (int)($data['sort_order'] ?? 0),
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $db = get_db();
        $stmt = $db->prepare("DELETE FROM `categories` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }

    public static function count(): int {
        $db = get_db();
        return (int)$db->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
    }
}
