<?php
/**
 * Inquiry Model (Wholesale Quote Requests)
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';

class Inquiry {
    public static function create(array $data): int {
        $db = get_db();
        $stmt = $db->prepare("
            INSERT INTO `inquiries`
            (`user_id`, `product_id`, `name`, `email`, `phone`, `company`, `product_interest`, `quantity`, `message`, `status`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', NOW())
        ");
        $stmt->execute([
            !empty($data['user_id']) ? (int)$data['user_id'] : null,
            !empty($data['product_id']) ? (int)$data['product_id'] : null,
            $data['name'],
            !empty($data['email']) ? trim(strtolower($data['email'])) : null,
            $data['phone'],
            $data['company'] ?? null,
            $data['product_interest'] ?? null,
            $data['quantity'] ?? null,
            $data['message'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function getAll(?string $status = null, ?int $limit = null, ?int $offset = null): array {
        $db = get_db();
        $sql = "
            SELECT i.*, p.name AS product_name, p.slug AS product_slug, u.name AS user_name
            FROM `inquiries` i
            LEFT JOIN `products` p ON i.product_id = p.id
            LEFT JOIN `users` u ON i.user_id = u.id
        ";
        $params = [];

        if ($status) {
            $sql .= " WHERE i.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY i.created_at DESC";

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

    public static function getById(int $id): ?array {
        $db = get_db();
        $stmt = $db->prepare("
            SELECT i.*, p.name AS product_name, p.slug AS product_slug, u.name AS user_name
            FROM `inquiries` i
            LEFT JOIN `products` p ON i.product_id = p.id
            LEFT JOIN `users` u ON i.user_id = u.id
            WHERE i.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByUser(int $userId): array {
        $db = get_db();
        $stmt = $db->prepare("
            SELECT i.*, p.name AS product_name, p.slug AS product_slug
            FROM `inquiries` i
            LEFT JOIN `products` p ON i.product_id = p.id
            WHERE i.user_id = ?
            ORDER BY i.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus(int $id, string $status, ?string $notes = null): bool {
        $db = get_db();
        if ($notes !== null) {
            $stmt = $db->prepare("UPDATE `inquiries` SET `status` = ?, `admin_notes` = ? WHERE `id` = ?");
            return $stmt->execute([$status, $notes, $id]);
        } else {
            $stmt = $db->prepare("UPDATE `inquiries` SET `status` = ? WHERE `id` = ?");
            return $stmt->execute([$status, $id]);
        }
    }

    public static function delete(int $id): bool {
        $db = get_db();
        $stmt = $db->prepare("DELETE FROM `inquiries` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }

    public static function countByStatus(): array {
        $db = get_db();
        $stmt = $db->query("
            SELECT `status`, COUNT(*) as count
            FROM `inquiries`
            GROUP BY `status`
        ");
        $counts = [
            'total'       => 0,
            'new'         => 0,
            'contacted'   => 0,
            'in_progress' => 0,
            'closed'      => 0
        ];
        while ($row = $stmt->fetch()) {
            $st = $row['status'];
            $c = (int)$row['count'];
            $counts[$st] = $c;
            $counts['total'] += $c;
        }
        return $counts;
    }
}
