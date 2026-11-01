<?php
/**
 * Category Model (MongoDB)
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';

class Category {
    private static function getCollection(): \MongoDB\Collection {
        return get_collection('categories');
    }

    public static function getAll(): array {
        $col = self::getCollection();
        $categories = normalize_docs($col->find([], ['sort' => ['sort_order' => 1, 'name' => 1]]));
        
        $prodCol = get_collection('products');
        foreach ($categories as &$cat) {
            $catId = $cat['id'];
            $oid = to_object_id($catId);
            $conditions = [
                ['category_id' => $catId],
                ['category_slug' => $cat['slug']]
            ];
            if ($oid) {
                $conditions[] = ['category_id' => $oid];
            }
            $cat['product_count'] = $prodCol->countDocuments([
                'is_active' => 1,
                '$or' => $conditions
            ]);
        }
        unset($cat);

        return $categories;
    }

    public static function getById(string $id): ?array {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $doc = $col->findOne($filter);
        return normalize_doc($doc);
    }

    public static function getBySlug(string $slug): ?array {
        $col = self::getCollection();
        $doc = $col->findOne(['slug' => $slug]);
        return normalize_doc($doc);
    }

    public static function create(array $data): string {
        $col = self::getCollection();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);
        
        // Ensure slug is unique
        $check = $col->findOne(['slug' => $slug]);
        if ($check) {
            $slug .= '-' . time();
        }

        $insertData = [
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? '🌱',
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'created_at'  => date('Y-m-d H:i:s')
        ];

        $res = $col->insertOne($insertData);
        return (string)$res->getInsertedId();
    }

    public static function update(string $id, array $data): bool {
        $col = self::getCollection();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        $oid = to_object_id($id);
        $idFilter = $oid ? $oid : $id;

        // Check if slug belongs to another category
        $check = $col->findOne(['slug' => $slug, '_id' => ['$ne' => $idFilter]]);
        if ($check) {
            $slug .= '-' . time();
        }

        $res = $col->updateOne(
            ['_id' => $idFilter],
            [
                '$set' => [
                    'name'        => $data['name'],
                    'slug'        => $slug,
                    'description' => $data['description'] ?? null,
                    'icon'        => $data['icon'] ?? '🌱',
                    'sort_order'  => (int)($data['sort_order'] ?? 0),
                    'updated_at'  => date('Y-m-d H:i:s')
                ]
            ]
        );

        // Also sync updated category name/slug/icon to products
        try {
            get_collection('products')->updateMany(
                ['$or' => [['category_id' => (string)$id], ['category_id' => $oid]]],
                ['$set' => ['category_name' => $data['name'], 'category_slug' => $slug, 'category_icon' => $data['icon'] ?? '🌱']]
            );
        } catch (\Throwable $e) {}

        return $res->getModifiedCount() > 0 || $res->getMatchedCount() > 0;
    }

    public static function delete(string $id): bool {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $res = $col->deleteOne($filter);
        return $res->getDeletedCount() > 0;
    }

    public static function count(): int {
        return self::getCollection()->countDocuments();
    }
}
