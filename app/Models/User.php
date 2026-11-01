<?php
/**
 * User Model
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';

class User {
    public static function findByEmail(string $email): ?array {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->execute([trim(strtolower($email))]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int {
        $db = get_db();
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'] ?? 'customer';

        $stmt = $db->prepare("
            INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `phone`, `company`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['name'],
            trim(strtolower($data['email'])),
            $hash,
            $role,
            $data['phone'] ?? null,
            $data['company'] ?? null
        ]);

        return (int)$db->lastInsertId();
    }

    public static function verifyPassword(array $user, string $password): bool {
        return password_verify($password, $user['password_hash']);
    }

    public static function getAll(?string $role = null): array {
        $db = get_db();
        if ($role) {
            $stmt = $db->prepare("SELECT `id`, `name`, `email`, `role`, `phone`, `company`, `created_at` FROM `users` WHERE `role` = ? ORDER BY `created_at` DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $db->query("SELECT `id`, `name`, `email`, `role`, `phone`, `company`, `created_at` FROM `users` ORDER BY `created_at` DESC");
        }
        return $stmt->fetchAll();
    }

    public static function updateRole(int $id, string $role): bool {
        $db = get_db();
        $stmt = $db->prepare("UPDATE `users` SET `role` = ? WHERE `id` = ?");
        return $stmt->execute([$role, $id]);
    }

    public static function updateProfile(int $id, array $data): bool {
        $db = get_db();
        $stmt = $db->prepare("UPDATE `users` SET `name` = ?, `phone` = ?, `company` = ? WHERE `id` = ?");
        return $stmt->execute([$data['name'], $data['phone'] ?? null, $data['company'] ?? null, $id]);
    }

    public static function updatePassword(int $id, string $newPassword): bool {
        $db = get_db();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE `users` SET `password_hash` = ? WHERE `id` = ?");
        return $stmt->execute([$hash, $id]);
    }

    public static function count(?string $role = null): int {
        $db = get_db();
        if ($role) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `role` = ?");
            $stmt->execute([$role]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$db->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    }
}
