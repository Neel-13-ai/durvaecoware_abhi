<?php
/**
 * Inquiry Model (MongoDB)
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';
require_once BASE_PATH . '/app/Models/Product.php';
require_once BASE_PATH . '/app/Models/User.php';

class Inquiry {
    private static function getCollection(): \MongoDB\Collection {
        return get_collection('inquiries');
    }

    public static function create(array $data): string {
        $col = self::getCollection();

        $productId = !empty($data['product_id']) ? (string)$data['product_id'] : null;
        $productName = null;
        $productSlug = null;
        if ($productId) {
            $product = Product::getById($productId);
            if ($product) {
                $productName = $product['name'];
                $productSlug = $product['slug'];
            }
        }

        $userId = !empty($data['user_id']) ? (string)$data['user_id'] : null;
        $userName = null;
        if ($userId) {
            $user = User::findById($userId);
            if ($user) {
                $userName = $user['name'];
            }
        }

        $insertData = [
            'user_id'          => $userId,
            'user_name'        => $userName,
            'product_id'       => $productId,
            'product_name'     => $productName,
            'product_slug'     => $productSlug,
            'name'             => $data['name'],
            'email'            => !empty($data['email']) ? trim(strtolower($data['email'])) : null,
            'phone'            => $data['phone'],
            'company'          => $data['company'] ?? null,
            'product_interest' => $data['product_interest'] ?? null,
            'quantity'         => $data['quantity'] ?? null,
            'message'          => $data['message'] ?? null,
            'status'           => 'new',
            'admin_notes'      => null,
            'created_at'       => date('Y-m-d H:i:s')
        ];

        $res = $col->insertOne($insertData);
        return (string)$res->getInsertedId();
    }

    public static function getAll(?string $status = null, ?int $limit = null, ?int $offset = null): array {
        $col = self::getCollection();
        $query = [];

        if ($status) {
            $query['status'] = $status;
        }

        $options = [
            'sort' => ['created_at' => -1]
        ];

        if ($limit !== null) {
            $options['limit'] = (int)$limit;
        }
        if ($offset !== null) {
            $options['skip'] = (int)$offset;
        }

        $cursor = $col->find($query, $options);
        return normalize_docs($cursor);
    }

    public static function getById(string $id): ?array {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $doc = $col->findOne($filter);
        return normalize_doc($doc);
    }

    public static function getByUser(string $userId): array {
        $col = self::getCollection();
        $oid = to_object_id($userId);
        $conds = [['user_id' => (string)$userId]];
        if ($oid) {
            $conds[] = ['user_id' => $oid];
        }

        $cursor = $col->find(['$or' => $conds], [
            'sort' => ['created_at' => -1]
        ]);
        return normalize_docs($cursor);
    }

    public static function updateStatus(string $id, string $status, ?string $notes = null): bool {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];

        $update = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($notes !== null) {
            $update['admin_notes'] = $notes;
        }

        $res = $col->updateOne($filter, ['$set' => $update]);
        return $res->getModifiedCount() > 0 || $res->getMatchedCount() > 0;
    }

    public static function delete(string $id): bool {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $res = $col->deleteOne($filter);
        return $res->getDeletedCount() > 0;
    }

    public static function countByStatus(): array {
        $col = self::getCollection();
        $counts = [
            'total'       => 0,
            'new'         => 0,
            'contacted'   => 0,
            'in_progress' => 0,
            'closed'      => 0
        ];

        try {
            $pipeline = [
                ['$group' => ['_id' => '$status', 'count' => ['$sum' => 1]]]
            ];
            $cursor = $col->aggregate($pipeline);
            foreach ($cursor as $doc) {
                $st = (string)$doc['_id'];
                $c = (int)$doc['count'];
                if (isset($counts[$st])) {
                    $counts[$st] = $c;
                }
                $counts['total'] += $c;
            }
        } catch (\Throwable $e) {
            // Fallback
            $counts['total'] = $col->countDocuments();
        }

        return $counts;
    }
}
