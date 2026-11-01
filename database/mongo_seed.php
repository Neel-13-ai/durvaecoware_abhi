<?php
/**
 * MongoDB Migration & Seeder Script
 * Durva Eco Ware
 *
 * Usage: php database/mongo_seed.php
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

function seed_mongodb(?string $uri = null, ?string $dbName = null, ?string $adminEmail = null, ?string $adminPass = null): array {
    $cfg = Database::getConfig();
    $uri = $uri ?? $cfg['uri'];
    $dbName = $dbName ?? $cfg['database'];
    $adminEmail = $adminEmail ?? 'admin@durvaecoware.com';
    $adminPass = $adminPass ?? 'AdminPassword@123';

    $logs = [];
    $logs[] = "Initializing connection to MongoDB database '$dbName'...";

    if (!extension_loaded('mongodb')) {
        return [
            'success' => false,
            'logs' => $logs,
            'error' => "PHP extension 'mongodb' (php_mongodb.dll) is not loaded in php.ini."
        ];
    }

    if (!class_exists('MongoDB\Client')) {
        return [
            'success' => false,
            'logs' => $logs,
            'error' => "MongoDB PHP library is missing. Please ensure 'composer install' has been executed."
        ];
    }

    try {
        $client = new \MongoDB\Client($uri, [], ['serverSelectionTimeoutMS' => 8000]);
        $db = $client->selectDatabase($dbName);

        // Ping to test connectivity
        $pingCmd = new \MongoDB\Driver\Command(['ping' => 1]);
        $db->command($pingCmd);
        $logs[] = "✓ Successfully connected to MongoDB server/cluster.";
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'logs' => $logs,
            'error' => "Could not connect to MongoDB: " . $e->getMessage()
        ];
    }

    try {
        // 1. Create Indexes
        $logs[] = "Creating MongoDB indexes...";
        $db->users->createIndex(['email' => 1], ['unique' => true]);
        $db->categories->createIndex(['slug' => 1], ['unique' => true]);
        $db->products->createIndex(['slug' => 1], ['unique' => true]);
        $db->products->createIndex(['category_id' => 1]);
        $db->products->createIndex(['is_active' => 1]);
        $db->inquiries->createIndex(['created_at' => -1]);
        $db->inquiries->createIndex(['status' => 1]);
        $db->settings->createIndex(['key_name' => 1], ['unique' => true]);
        $logs[] = "✓ Indexes created for users, categories, products, inquiries, and settings.";

        // 2. Seed Default Settings
        $logs[] = "Seeding site settings...";
        $defaultSettings = [
            'company_name'       => 'Durva Eco Ware',
            'company_tagline'    => 'Better Products. Better Planet.',
            'contact_phone'      => '+91 98765 43210',
            'contact_email'      => 'info@durvaecoware.com',
            'whatsapp_number'    => '919876543210',
            'company_address'    => 'Plot No. 42, Green Industrial Park, Gujarat, India',
            'about_story'        => 'Durva Eco Ware is focused on providing quality eco-friendly disposable products for restaurants, caterers, retailers, distributors, events and everyday food service needs.',
            'wholesale_headline' => 'Looking for Bulk Eco-Friendly Products?',
            'wholesale_subhead'  => 'Partner with Durva Eco Ware for wholesale orders, retail supply, restaurant requirements, and distribution across India.'
        ];

        foreach ($defaultSettings as $key => $val) {
            $db->settings->updateOne(
                ['key_name' => $key],
                ['$set' => ['key_name' => $key, 'value' => $val, 'updated_at' => date('Y-m-d H:i:s')]],
                ['upsert' => true]
            );
        }
        $logs[] = "✓ Site settings seeded.";

        // 3. Seed Users
        $logs[] = "Seeding administrator and customer accounts...";
        $adminHash = password_hash($adminPass, PASSWORD_BCRYPT);
        $custHash = password_hash('Customer@123', PASSWORD_BCRYPT);

        $db->users->updateOne(
            ['email' => strtolower($adminEmail)],
            ['$set' => [
                'name'          => 'Administrator',
                'email'         => strtolower($adminEmail),
                'password_hash' => $adminHash,
                'role'          => 'admin',
                'phone'         => '+91 98765 43210',
                'company'       => 'Durva Eco Ware HQ',
                'created_at'    => date('Y-m-d H:i:s')
            ]],
            ['upsert' => true]
        );

        $db->users->updateOne(
            ['email' => 'customer@gmail.com'],
            ['$set' => [
                'name'          => 'Rohit Sharma',
                'email'         => 'customer@gmail.com',
                'password_hash' => $custHash,
                'role'          => 'customer',
                'phone'         => '+91 98123 45678',
                'company'       => 'Green Bites Catering',
                'created_at'    => date('Y-m-d H:i:s')
            ]],
            ['upsert' => true]
        );
        $logs[] = "✓ Admin ($adminEmail) and customer (customer@gmail.com) created.";

        // 4. Seed Categories
        $logs[] = "Seeding product categories...";
        $categoriesData = [
            [
                'name' => 'Paper Plates',
                'slug' => 'paper-plates',
                'description' => 'Heavy duty biodegradable plates for dinners, snacks, and catering events.',
                'icon' => '🍽️',
                'sort_order' => 1
            ],
            [
                'name' => 'Paper Bowls',
                'slug' => 'paper-bowls',
                'description' => 'Leak-proof eco bowls for hot soups, gravies, desserts, and ice creams.',
                'icon' => '🥣',
                'sort_order' => 2
            ],
            [
                'name' => 'Food Containers',
                'slug' => 'food-containers',
                'description' => 'Secure locking kraft boxes and bagasse containers for delivery & take-away.',
                'icon' => '📦',
                'sort_order' => 3
            ],
            [
                'name' => 'Meal Trays & Compartments',
                'slug' => 'meal-trays',
                'description' => 'Multi-compartment disposable meal trays for corporate lunches and thalis.',
                'icon' => '🍱',
                'sort_order' => 4
            ],
            [
                'name' => 'Eco Cups & Glasses',
                'slug' => 'eco-cups',
                'description' => 'Insulated ripple cups and compostable glasses for hot and cold beverages.',
                'icon' => '☕',
                'sort_order' => 5
            ]
        ];

        $categoryMap = []; // slug -> category doc
        foreach ($categoriesData as $catData) {
            $catData['created_at'] = date('Y-m-d H:i:s');
            $db->categories->updateOne(
                ['slug' => $catData['slug']],
                ['$set' => $catData],
                ['upsert' => true]
            );
            $catDoc = $db->categories->findOne(['slug' => $catData['slug']]);
            $categoryMap[$catData['slug']] = $catDoc;
        }
        $logs[] = "✓ 5 Tableware categories seeded.";

        // 5. Seed Products
        $logs[] = "Seeding tableware catalog products...";
        $productsData = [
            [
                'category_slug' => 'paper-plates',
                'name'          => '6 Inch Paper Plate',
                'slug'          => '6-inch-paper-plate',
                'pack_size'     => '100 Pcs / Pack',
                'material'      => '100% Bagasse & Virgin Kraft Paper',
                'price'         => 120.00,
                'moq'           => '1,000 Pcs',
                'badge'         => 'Bestseller',
                'description'   => 'Durable 6-inch round eco-friendly plate designed for appetizers, snacks, side dishes, and breakfast servings. Resistant to oil, grease, and moisture.',
                'image_url'     => 'assets/uploads/products/plate_6in.svg',
                'is_featured'   => 1,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ],
            [
                'category_slug' => 'paper-plates',
                'name'          => '7 Inch Paper Plate',
                'slug'          => '7-inch-paper-plate',
                'pack_size'     => '100 Pcs / Pack',
                'material'      => 'Heavy Duty Sugarcane Fiber / Paper',
                'price'         => 150.00,
                'moq'           => '1,000 Pcs',
                'badge'         => '100% Compostable',
                'description'   => 'Sturdy 7-inch round disposable plate suitable for snacks, desserts, chaat, and light meals. Rigid construction prevents bending when loaded.',
                'image_url'     => 'assets/uploads/products/plate_7in.svg',
                'is_featured'   => 1,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ],
            [
                'category_slug' => 'paper-plates',
                'name'          => '10 Inch Compartment Plate (Thali)',
                'slug'          => '10-inch-compartment-plate-thali',
                'pack_size'     => '50 Pcs / Pack',
                'material'      => 'Dense Fiber Biodegradable Board',
                'price'         => 280.00,
                'moq'           => '500 Pcs',
                'badge'         => 'Heavy Duty',
                'description'   => 'Spacious 4-compartment eco-thali plate for full meals, catering, weddings, and buffets. Deep dividers keep gravies and sides completely separate.',
                'image_url'     => 'assets/uploads/products/plate_10in.svg',
                'is_featured'   => 1,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ],
            [
                'category_slug' => 'paper-bowls',
                'name'          => '250ml Paper Bowl',
                'slug'          => '250ml-paper-bowl',
                'pack_size'     => '100 Pcs / Pack',
                'material'      => 'PLA Coated Food-Safe Paper',
                'price'         => 135.00,
                'moq'           => '1,000 Pcs',
                'badge'         => 'Leak-Proof',
                'description'   => 'High-walled 250ml disposable bowl perfect for hot dal, curries, soups, salads, and raita. Guaranteed leak-proof with grease resistant coating.',
                'image_url'     => 'assets/uploads/products/bowl_250ml.svg',
                'is_featured'   => 1,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ],
            [
                'category_slug' => 'paper-bowls',
                'name'          => '180ml Dessert & Ice Cream Bowl',
                'slug'          => '180ml-dessert-ice-cream-bowl',
                'pack_size'     => '100 Pcs / Pack',
                'material'      => 'Food-Grade Virgin Paper',
                'price'         => 110.00,
                'moq'           => '1,000 Pcs',
                'badge'         => 'Popular',
                'description'   => 'Compact 180ml eco bowl designed for sweets, gulab jamun, kheer, ice creams, and condiment servings.',
                'image_url'     => 'assets/uploads/products/bowl_180ml.svg',
                'is_featured'   => 0,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ],
            [
                'category_slug' => 'food-containers',
                'name'          => '500ml Kraft Food Container with Lid',
                'slug'          => '500ml-kraft-food-container-with-lid',
                'pack_size'     => '50 Pcs / Pack',
                'material'      => 'Natural Brown Kraft Board + PP Lid',
                'price'         => 240.00,
                'moq'           => '500 Pcs',
                'badge'         => 'Microwave Safe',
                'description'   => 'Premium takeaway food container with tight-fitting spill-proof lid. Ideal for biryanis, noodles, gravies, and restaurant deliveries.',
                'image_url'     => 'assets/uploads/products/container_500ml.svg',
                'is_featured'   => 1,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ],
            [
                'category_slug' => 'food-containers',
                'name'          => '750ml 2-Compartment Meal Box',
                'slug'          => '750ml-2-compartment-meal-box',
                'pack_size'     => '50 Pcs / Pack',
                'material'      => 'Unbleached Plant Bagasse',
                'price'         => 310.00,
                'moq'           => '500 Pcs',
                'badge'         => 'Commercial Grade',
                'description'   => 'Eco-friendly hinged-lid meal box with dual compartments for rice and gravy combo meals. Retains heat and resists softening.',
                'image_url'     => 'assets/uploads/products/container_750ml.svg',
                'is_featured'   => 1,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ],
            [
                'category_slug' => 'eco-cups',
                'name'          => '250ml Ripple Paper Cup',
                'slug'          => '250ml-ripple-paper-cup',
                'pack_size'     => '100 Pcs / Pack',
                'material'      => 'Triple Layer Insulated Paper',
                'price'         => 95.00,
                'moq'           => '2,000 Pcs',
                'badge'         => 'Heat Insulated',
                'description'   => 'Triple-wall insulated ripple cups for hot tea, coffee, espresso, and herbal beverages. Eliminates need for extra sleeves.',
                'image_url'     => 'assets/uploads/products/cup_250ml.svg',
                'is_featured'   => 0,
                'is_active'     => 1,
                'stock_status'  => 'in_stock'
            ]
        ];

        foreach ($productsData as $prodData) {
            $catSlug = $prodData['category_slug'];
            $catDoc = $categoryMap[$catSlug] ?? null;
            if ($catDoc) {
                $prodData['category_id'] = (string)$catDoc['_id'];
                $prodData['category_name'] = $catDoc['name'];
                $prodData['category_icon'] = $catDoc['icon'] ?? '🌱';
            }
            $prodData['created_at'] = date('Y-m-d H:i:s');

            $db->products->updateOne(
                ['slug' => $prodData['slug']],
                ['$set' => $prodData],
                ['upsert' => true]
            );
        }
        $logs[] = "✓ 8 Tableware products imported with specifications and images.";

        // 6. Seed Sample Inquiry
        $logs[] = "Seeding sample wholesale inquiry...";
        $firstProd = $db->products->findOne(['slug' => '6-inch-paper-plate']);
        $custUser = $db->users->findOne(['email' => 'customer@gmail.com']);

        $inquiryCount = $db->inquiries->countDocuments();
        if ($inquiryCount === 0) {
            $db->inquiries->insertOne([
                'user_id'          => $custUser ? (string)$custUser['_id'] : null,
                'user_name'        => $custUser ? $custUser['name'] : 'Rohit Sharma',
                'product_id'       => $firstProd ? (string)$firstProd['_id'] : null,
                'product_name'     => $firstProd ? $firstProd['name'] : '6 Inch Paper Plate',
                'product_slug'     => $firstProd ? $firstProd['slug'] : '6-inch-paper-plate',
                'name'             => 'Rohit Sharma',
                'email'            => 'rohit@greenbites.in',
                'phone'            => '+91 98123 45678',
                'company'          => 'Green Bites Catering',
                'product_interest' => '6 Inch Paper Plate',
                'quantity'         => '5,000 Pcs',
                'message'          => 'Need wholesale quotation for regular weekly supply for our central catering kitchen in Ahmedabad.',
                'status'           => 'contacted',
                'admin_notes'      => 'Spoke on phone on 28th Aug. Sent initial price catalog on WhatsApp.',
                'created_at'       => date('Y-m-d H:i:s', strtotime('-2 days'))
            ]);
            $logs[] = "✓ Sample wholesale inquiry seeded.";
        }

        $logs[] = "🎉 All MongoDB collections, indexes, and initial data successfully initialized!";
        return [
            'success' => true,
            'logs' => $logs
        ];

    } catch (\Throwable $e) {
        return [
            'success' => false,
            'logs' => $logs,
            'error' => "Seeding error: " . $e->getMessage()
        ];
    }
}

// CLI Execution support
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "=== Durva Eco Ware - MongoDB Seeder ===\n";
    $res = seed_mongodb();
    foreach ($res['logs'] as $log) {
        echo $log . "\n";
    }
    if (!$res['success']) {
        echo "\nERROR: " . ($res['error'] ?? 'Unknown error') . "\n";
        exit(1);
    }
    echo "\nMongoDB setup completed successfully!\n";
    exit(0);
}
