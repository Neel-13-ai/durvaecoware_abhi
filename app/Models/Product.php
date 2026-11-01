<?php
/**
 * Product Model (MongoDB)
 * Durva Eco Ware
 */

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/sanitizer.php';
require_once BASE_PATH . '/app/Models/Category.php';

class Product {
    private static function getCollection(): \MongoDB\Collection {
        return get_collection('products');
    }

    public static function getAll(array $filters = [], ?int $limit = null, ?int $offset = null): array {
        $col = self::getCollection();
        $query = [];

        if (isset($filters['is_active'])) {
            $query['is_active'] = (int)$filters['is_active'];
        }

        if (isset($filters['is_featured'])) {
            $query['is_featured'] = (int)$filters['is_featured'];
        }

        if (!empty($filters['category_id'])) {
            $catId = (string)$filters['category_id'];
            $oid = to_object_id($catId);
            $conds = [['category_id' => $catId]];
            if ($oid) {
                $conds[] = ['category_id' => $oid];
            }
            $query['$or'] = $conds;
        }

        if (!empty($filters['category_slug'])) {
            $query['category_slug'] = $filters['category_slug'];
        }

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $regex = class_exists('MongoDB\BSON\Regex') ? new \MongoDB\BSON\Regex($term, 'i') : ['$regex' => $term, '$options' => 'i'];
            $query['$or'] = [
                ['name' => $regex],
                ['description' => $regex],
                ['material' => $regex]
            ];
        }

        $options = [
            'sort' => ['is_featured' => -1, 'created_at' => -1]
        ];

        if ($limit !== null) {
            $options['limit'] = (int)$limit;
        }
        if ($offset !== null) {
            $options['skip'] = (int)$offset;
        }

        $cursor = $col->find($query, $options);
        $products = normalize_docs($cursor);

        // Ensure category details exist on each product
        foreach ($products as &$p) {
            if (empty($p['category_name']) && !empty($p['category_id'])) {
                $cat = Category::getById((string)$p['category_id']);
                if ($cat) {
                    $p['category_name'] = $cat['name'];
                    $p['category_slug'] = $cat['slug'];
                    $p['category_icon'] = $cat['icon'] ?? '🌱';
                }
            }
        }
        unset($p);

        return $products;
    }

    public static function getFeatured(int $limit = 6): array {
        return self::getAll(['is_featured' => 1, 'is_active' => 1], $limit);
    }

    public static function getById(string $id): ?array {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $doc = $col->findOne($filter);
        $product = normalize_doc($doc);
        if ($product && empty($product['category_name']) && !empty($product['category_id'])) {
            $cat = Category::getById((string)$product['category_id']);
            if ($cat) {
                $product['category_name'] = $cat['name'];
                $product['category_slug'] = $cat['slug'];
                $product['category_icon'] = $cat['icon'] ?? '🌱';
            }
        }
        return $product;
    }

    public static function getBySlug(string $slug): ?array {
        $col = self::getCollection();
        $doc = $col->findOne(['slug' => $slug]);
        $product = normalize_doc($doc);
        if ($product && empty($product['category_name']) && !empty($product['category_id'])) {
            $cat = Category::getById((string)$product['category_id']);
            if ($cat) {
                $product['category_name'] = $cat['name'];
                $product['category_slug'] = $cat['slug'];
                $product['category_icon'] = $cat['icon'] ?? '🌱';
            }
        }
        return $product;
    }

    public static function create(array $data): string {
        $col = self::getCollection();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);
        
        // Ensure slug is unique
        $check = $col->findOne(['slug' => $slug]);
        if ($check) {
            $slug .= '-' . time();
        }

        // Fetch category details to denormalize
        $cat = !empty($data['category_id']) ? Category::getById((string)$data['category_id']) : null;

        $insertData = [
            'category_id'   => (string)$data['category_id'],
            'category_name' => $cat['name'] ?? '',
            'category_slug' => $cat['slug'] ?? '',
            'category_icon' => $cat['icon'] ?? '🌱',
            'name'          => $data['name'],
            'slug'          => $slug,
            'pack_size'     => $data['pack_size'] ?? '100 Pcs / Pack',
            'material'      => $data['material'] ?? 'Eco Bagasse / Kraft Paper',
            'price'         => clean_float($data['price'] ?? 0),
            'moq'           => $data['moq'] ?? '500 Pcs',
            'badge'         => $data['badge'] ?? null,
            'description'   => $data['description'] ?? null,
            'image_url'     => $data['image_url'] ?? null,
            'is_featured'   => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            'is_active'     => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'stock_status'  => $data['stock_status'] ?? 'in_stock',
            'created_at'    => date('Y-m-d H:i:s')
        ];

        $res = $col->insertOne($insertData);
        return (string)$res->getInsertedId();
    }

    public static function update(string $id, array $data): bool {
        $col = self::getCollection();
        $slug = !empty($data['slug']) ? slugify($data['slug']) : slugify($data['name']);

        $oid = to_object_id($id);
        $idFilter = $oid ? $oid : $id;

        // Check if slug belongs to another product
        $check = $col->findOne(['slug' => $slug, '_id' => ['$ne' => $idFilter]]);
        if ($check) {
            $slug .= '-' . time();
        }

        $cat = !empty($data['category_id']) ? Category::getById((string)$data['category_id']) : null;

        $updateFields = [
            'category_id'   => (string)$data['category_id'],
            'name'          => $data['name'],
            'slug'          => $slug,
            'pack_size'     => $data['pack_size'] ?? '100 Pcs / Pack',
            'material'      => $data['material'] ?? 'Eco Bagasse / Kraft Paper',
            'price'         => clean_float($data['price'] ?? 0),
            'moq'           => $data['moq'] ?? '500 Pcs',
            'badge'         => $data['badge'] ?? null,
            'description'   => $data['description'] ?? null,
            'is_featured'   => isset($data['is_featured']) ? (int)$data['is_featured'] : 0,
            'is_active'     => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'stock_status'  => $data['stock_status'] ?? 'in_stock',
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        if ($cat) {
            $updateFields['category_name'] = $cat['name'];
            $updateFields['category_slug'] = $cat['slug'];
            $updateFields['category_icon'] = $cat['icon'] ?? '🌱';
        }

        if (!empty($data['image_url'])) {
            $updateFields['image_url'] = $data['image_url'];
        }

        $res = $col->updateOne(['_id' => $idFilter], ['$set' => $updateFields]);
        return $res->getModifiedCount() > 0 || $res->getMatchedCount() > 0;
    }

    public static function delete(string $id): bool {
        $col = self::getCollection();
        $oid = to_object_id($id);
        $filter = $oid ? ['_id' => $oid] : ['_id' => $id];
        $res = $col->deleteOne($filter);
        return $res->getDeletedCount() > 0;
    }

    public static function count(array $filters = []): int {
        $col = self::getCollection();
        $query = [];

        if (isset($filters['is_active'])) {
            $query['is_active'] = (int)$filters['is_active'];
        }
        if (!empty($filters['category_id'])) {
            $catId = (string)$filters['category_id'];
            $oid = to_object_id($catId);
            $conds = [['category_id' => $catId]];
            if ($oid) {
                $conds[] = ['category_id' => $oid];
            }
            $query['$or'] = $conds;
        }

        return $col->countDocuments($query);
    }
}
