<?php
/**
 * Product Model
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';

class Product {
    public static function getAll(array $filters = [], ?int $limit = null, ?int $offset = null): array {
        $db = get_db();
        $conditions = [];
        $params = [];

        if (!empty($filters['category_id'])) {
            $conditions[] = "p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['category_slug'])) {
            $conditions[] = "c.slug = ?";
            $params[] = $filters['category_slug'];
        }

        if (isset($filters['is_active'])) {
            $conditions[] = "p.is_active = ?";
            $params[] = (int)$filters['is_active'];
        }

        if (isset($filters['is_featured'])) {
            $conditions[] = "p.is_featured = ?";
            $params[] = (int)$filters['is_featured'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.material LIKE ?)";
            $searchTerm = '%' . trim($filters['search']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $orderBy = $filters['order_by'] ?? "p.is_featured DESC, p.created_at DESC";

        $sql = "
            SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
            FROM `products` p
            JOIN `categories` c ON p.category_id = c.id
            $whereClause
            ORDER BY $orderBy
        ";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getFeatured(int $limit = 6): array {
        return self::getAll(['is_featured' => 1, 'is_active' => 1], $limit);
    }

    public static function getById(int $id): ?array {
        $db = get_db();
        $stmt = $db->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM `products` p
            JOIN `categories` c ON p.category_id = c.id
            WHERE p.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getBySlug(string $slug): ?array {
        $db = get_db();
        $stmt = $db->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM `products` p
            JOIN `categories` c ON p.category_id = c.id
            WHERE p.slug = ?
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int {
        $db = get_db();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);
        
        // Ensure slug is unique
        $check = $db->prepare("SELECT id FROM `products` WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $slug .= '-' . time();
        }

        $stmt = $db->prepare("
            INSERT INTO `products`
            (`category_id`, `name`, `slug`, `pack_size`, `material`, `price`, `moq`, `badge`, `description`, `image_url`, `is_featured`, `is_active`, `stock_status`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            (int)$data['category_id'],
            $data['name'],
            $slug,
            $data['pack_size'] ?? '100 Pcs / Pack',
            $data['material'] ?? 'Eco Bagasse / Kraft Paper',
            clean_float($data['price'] ?? 0),
            $data['moq'] ?? '500 Pcs',
            $data['badge'] ?? null,
            $data['description'] ?? null,
            $data['image_url'] ?? null,
            isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $data['stock_status'] ?? 'in_stock'
        ]);

        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = get_db();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        // Check if slug belongs to another product
        $check = $db->prepare("SELECT id FROM `products` WHERE slug = ? AND id != ?");
        $check->execute([$slug, $id]);
        if ($check->fetch()) {
            $slug .= '-' . time();
        }

        $fields = [
            '`category_id` = ?',
            '`name` = ?',
            '`slug` = ?',
            '`pack_size` = ?',
            '`material` = ?',
            '`price` = ?',
            '`moq` = ?',
            '`badge` = ?',
            '`description` = ?',
            '`is_featured` = ?',
            '`is_active` = ?',
            '`stock_status` = ?'
        ];

        $params = [
            (int)$data['category_id'],
            $data['name'],
            $slug,
            $data['pack_size'] ?? '100 Pcs / Pack',
            $data['material'] ?? 'Eco Bagasse / Kraft Paper',
            clean_float($data['price'] ?? 0),
            $data['moq'] ?? '500 Pcs',
            $data['badge'] ?? null,
            $data['description'] ?? null,
            isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            $data['stock_status'] ?? 'in_stock'
        ];

        if (!empty($data['image_url'])) {
            $fields[] = '`image_url` = ?';
            $params[] = $data['image_url'];
        }

        $params[] = $id;
        $sql = "UPDATE `products` SET " . implode(', ', $fields) . " WHERE `id` = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool {
        $db = get_db();
        $stmt = $db->prepare("DELETE FROM `products` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }

    public static function count(array $filters = []): int {
        $db = get_db();
        $conditions = [];
        $params = [];

        if (!empty($filters['category_id'])) {
            $conditions[] = "category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (isset($filters['is_active'])) {
            $conditions[] = "is_active = ?";
            $params[] = (int)$filters['is_active'];
        }

        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $stmt = $db->prepare("SELECT COUNT(*) FROM `products` $where");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
