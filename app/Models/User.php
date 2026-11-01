<?php
/**
 * User Model (MongoDB)
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';

class User {
    private static function getCollection(): \MongoDB\Collection {
        return get_collection('users');
    }

    public static function findByEmail(string $email): ?array {
        $col = self::getCollection();
        $doc = $col->findOne(['email' => trim(strtolower($email))]);
        return normalize_doc($doc);
    }

    public static function findById(string $id): ?array {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $doc = $col->findOne($filter);
        return normalize_doc($doc);
    }

    public static function create(array $data): string {
        $col = self::getCollection();
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $role = $data['role'] ?? 'customer';

        $insertData = [
            'name'          => $data['name'],
            'email'         => trim(strtolower($data['email'])),
            'password_hash' => $hash,
            'role'          => $role,
            'phone'         => $data['phone'] ?? null,
            'company'       => $data['company'] ?? null,
            'created_at'    => date('Y-m-d H:i:s')
        ];

        $res = $col->insertOne($insertData);
        return (string)$res->getInsertedId();
    }

    public static function verifyPassword(array $user, string $password): bool {
        return password_verify($password, $user['password_hash']);
    }

    public static function getAll(?string $role = null): array {
        $col = self::getCollection();
        $filter = $role ? ['role' => $role] : [];
        $cursor = $col->find($filter, [
            'sort' => ['created_at' => -1],
            'projection' => [
                'name' => 1,
                'email' => 1,
                'role' => 1,
                'phone' => 1,
                'company' => 1,
                'created_at' => 1
            ]
        ]);
        return normalize_docs($cursor);
    }

    public static function updateRole(string $id, string $role): bool {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $res = $col->updateOne($filter, ['$set' => ['role' => $role]]);
        return $res->getModifiedCount() > 0 || $res->getMatchedCount() > 0;
    }

    public static function updateProfile(string $id, array $data): bool {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $res = $col->updateOne($filter, [
            '$set' => [
                'name'    => $data['name'],
                'phone'   => $data['phone'] ?? null,
                'company' => $data['company'] ?? null
            ]
        ]);
        return $res->getModifiedCount() > 0 || $res->getMatchedCount() > 0;
    }

    public static function updatePassword(string $id, string $newPassword): bool {
        $col = self::getCollection();
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $res = $col->updateOne($filter, ['$set' => ['password_hash' => $hash]]);
        return $res->getModifiedCount() > 0 || $res->getMatchedCount() > 0;
    }

    public static function count(?string $role = null): int {
        $col = self::getCollection();
        $filter = $role ? ['role' => $role] : [];
        return $col->countDocuments($filter);
    }
}
