-- Durva Eco Ware - Initial Seed Data
-- Run this after schema.sql

-- 1. Default Users
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `phone`, `company`, `created_at`) VALUES
(1, 'Administrator', 'admin@durvaecoware.com', '$2y$10$4WB12sc92OfAV/7sXpliFOj1Jqx6CqEMUSIkfuZQnVHjbTLi/wxHW', 'admin', '+91 98765 43210', 'Durva Eco Ware HQ', NOW()),
(2, 'Rohit Sharma', 'customer@gmail.com', '$2y$10$ucQkz7h3pIJXyhtrjkUhLuZnK.vuS/m1/r5t4efveWr//N5QVAeQC', 'customer', '+91 98123 45678', 'Green Bites Catering', NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 2. Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `sort_order`) VALUES
(1, 'Paper Plates', 'paper-plates', 'Heavy duty biodegradable plates for dinners, snacks, and catering events.', '🍽️', 1),
(2, 'Paper Bowls', 'paper-bowls', 'Leak-proof eco bowls for hot soups, gravies, desserts, and ice creams.', '🥣', 2),
(3, 'Food Containers', 'food-containers', 'Secure locking kraft boxes and bagasse containers for delivery & take-away.', '📦', 3),
(4, 'Meal Trays & Compartments', 'meal-trays', 'Multi-compartment disposable meal trays for corporate lunches and thalis.', '🍱', 4),
(5, 'Eco Cups & Glasses', 'eco-cups', 'Insulated ripple cups and compostable glasses for hot and cold beverages.', '☕', 5)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 3. Products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `pack_size`, `material`, `price`, `moq`, `badge`, `description`, `image_url`, `is_featured`, `is_active`, `stock_status`) VALUES
(1, 1, '6 Inch Paper Plate', '6-inch-paper-plate', '100 Pcs / Pack', '100% Bagasse & Virgin Kraft Paper', 120.00, '1,000 Pcs', 'Bestseller', 'Durable 6-inch round eco-friendly plate designed for appetizers, snacks, side dishes, and breakfast servings. Resistant to oil, grease, and moisture.', 'assets/uploads/products/plate_6in.svg', 1, 1, 'in_stock'),
(2, 1, '7 Inch Paper Plate', '7-inch-paper-plate', '100 Pcs / Pack', 'Heavy Duty Sugarcane Fiber / Paper', 150.00, '1,000 Pcs', '100% Compostable', 'Sturdy 7-inch round disposable plate suitable for snacks, desserts, chaat, and light meals. Rigid construction prevents bending when loaded.', 'assets/uploads/products/plate_7in.svg', 1, 1, 'in_stock'),
(3, 1, '10 Inch Compartment Plate (Thali)', '10-inch-compartment-plate-thali', '50 Pcs / Pack', 'Dense Fiber Biodegradable Board', 280.00, '500 Pcs', 'Heavy Duty', 'Spacious 4-compartment eco-thali plate for full meals, catering, weddings, and buffets. Deep dividers keep gravies and sides completely separate.', 'assets/uploads/products/plate_10in.svg', 1, 1, 'in_stock'),
(4, 2, '250ml Paper Bowl', '250ml-paper-bowl', '100 Pcs / Pack', 'PLA Coated Food-Safe Paper', 135.00, '1,000 Pcs', 'Leak-Proof', 'High-walled 250ml disposable bowl perfect for hot dal, curries, soups, salads, and raita. Guaranteed leak-proof with grease resistant coating.', 'assets/uploads/products/bowl_250ml.svg', 1, 1, 'in_stock'),
(5, 2, '180ml Dessert & Ice Cream Bowl', '180ml-dessert-ice-cream-bowl', '100 Pcs / Pack', 'Food-Grade Virgin Paper', 110.00, '1,000 Pcs', 'Popular', 'Compact 180ml eco bowl designed for sweets, gulab jamun, kheer, ice creams, and condiment servings.', 'assets/uploads/products/bowl_180ml.svg', 0, 1, 'in_stock'),
(6, 3, '500ml Kraft Food Container with Lid', '500ml-kraft-food-container-with-lid', '50 Pcs / Pack', 'Natural Brown Kraft Board + PP Lid', 240.00, '500 Pcs', 'Microwave Safe', 'Premium takeaway food container with tight-fitting spill-proof lid. Ideal for biryanis, noodles, gravies, and restaurant deliveries.', 'assets/uploads/products/container_500ml.svg', 1, 1, 'in_stock'),
(7, 3, '750ml 2-Compartment Meal Box', '750ml-2-compartment-meal-box', '50 Pcs / Pack', 'Unbleached Plant Bagasse', 310.00, '500 Pcs', 'Commercial Grade', 'Eco-friendly hinged-lid meal box with dual compartments for rice and gravy combo meals. Retains heat and resists softening.', 'assets/uploads/products/container_750ml.svg', 1, 1, 'in_stock'),
(8, 5, '250ml Ripple Paper Cup', '250ml-ripple-paper-cup', '100 Pcs / Pack', 'Triple Layer Insulated Paper', 95.00, '2,000 Pcs', 'Heat Insulated', 'Triple-wall insulated ripple cups for hot tea, coffee, espresso, and herbal beverages. Eliminates need for extra sleeves.', 'assets/uploads/products/cup_250ml.svg', 0, 1, 'in_stock')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 4. Sample Wholesale Inquiries
INSERT INTO `inquiries` (`id`, `user_id`, `product_id`, `name`, `email`, `phone`, `company`, `product_interest`, `quantity`, `message`, `status`, `admin_notes`, `created_at`) VALUES
(1, 2, 1, 'Rohit Sharma', 'rohit@greenbites.in', '+91 98123 45678', 'Green Bites Catering', '6 Inch Paper Plate', '5000 Pcs', 'Need wholesale quotation for regular weekly supply for our central catering kitchen in Ahmedabad.', 'contacted', 'Spoke on phone on 28th Aug. Sent initial price catalog on WhatsApp.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, NULL, 6, 'Priya Patel', 'priya@freshbowls.com', '+91 97234 56789', 'Fresh Bowls Cafe', '500ml Kraft Food Container with Lid', '2500 Pcs', 'Looking for biodegradable takeout containers with custom brand logo printing.', 'new', NULL, DATE_SUB(NOW(), INTERVAL 4 HOUR))
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 5. Site Settings
INSERT INTO `settings` (`key_name`, `value`, `group_name`) VALUES
('company_name', 'Durva Eco Ware', 'general'),
('company_tagline', 'Better Products. Better Planet.', 'general'),
('contact_phone', '+91 98765 43210', 'contact'),
('contact_email', 'info@durvaecoware.com', 'contact'),
('whatsapp_number', '919876543210', 'contact'),
('company_address', 'Plot No. 42, Green Industrial Park, Gujarat, India', 'contact'),
('about_story', 'Durva Eco Ware is focused on providing quality eco-friendly disposable products for restaurants, caterers, retailers, distributors, events and everyday food service needs.', 'about'),
('wholesale_headline', 'Looking for Bulk Eco-Friendly Products?', 'wholesale'),
('wholesale_subhead', 'Partner with Durva Eco Ware for wholesale orders, retail supply, restaurant requirements, and distribution across India.', 'wholesale')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);
