-- ============================================================
-- CampusMarket — Seed Data  (Run AFTER schema.sql)
-- ============================================================
USE campusmarket;

-- ─── Admin & Demo Users ───────────────────────────────────
-- All passwords are: Password@123  (bcrypt hashed)
INSERT INTO users (username, email, password_hash, role, phone) VALUES
('admin',       'admin@campusmarket.com',   '$2y$12$eEagliqPqCkFRGfkKPMJkuAFDXPrCVuq3XRDIw6kqvJ7kJVUbqNuu', 'admin', NULL),
('alice_sells',  'alice@student.edu',        '$2y$12$eEagliqPqCkFRGfkKPMJkuAFDXPrCVuq3XRDIw6kqvJ7kJVUbqNuu', 'user',  '0712345001'),
('bob_buys',     'bob@student.edu',          '$2y$12$eEagliqPqCkFRGfkKPMJkuAFDXPrCVuq3XRDIw6kqvJ7kJVUbqNuu', 'user',  '0712345002'),
('carol_uni',    'carol@student.edu',        '$2y$12$eEagliqPqCkFRGfkKPMJkuAFDXPrCVuq3XRDIw6kqvJ7kJVUbqNuu', 'user',  '0712345003'),
('dave_campus',  'dave@student.edu',         '$2y$12$eEagliqPqCkFRGfkKPMJkuAFDXPrCVuq3XRDIw6kqvJ7kJVUbqNuu', 'user',  '0712345004');

-- ─── Categories ───────────────────────────────────────────
INSERT INTO categories (name, slug) VALUES
('Electronics',  'electronics'),
('Furniture',    'furniture'),
('Books',        'books'),
('Clothing',     'clothing'),
('Sports',       'sports'),
('Kitchen',      'kitchen'),
('Stationery',   'stationery'),
('Appliances',   'appliances');

-- ─── Tags ─────────────────────────────────────────────────
INSERT INTO tags (name, slug) VALUES
('laptop',    'laptop'),
('phone',     'phone'),
('chair',     'chair'),
('desk',      'desk'),
('textbook',  'textbook'),
('hoodie',    'hoodie'),
('bicycle',   'bicycle'),
('microwave', 'microwave'),
('lamp',      'lamp'),
('fan',       'fan');

-- ─── Products ─────────────────────────────────────────────
INSERT INTO products (user_id, category_id, title, description, price, `condition`, status) VALUES
(2, 1, 'HP Pavilion Laptop 15"',       'Used for 1 semester only. No scratches, charger included.',     18000.00, 'like_new', 'active'),
(2, 1, 'Samsung Galaxy A52',           'Phone in great condition, minor screen scratch, comes with box.',  8500.00, 'used',     'active'),
(3, 2, 'Study Desk with Drawer',       'Solid wood desk, fits dorm room perfectly.',                      3200.00, 'used',     'active'),
(3, 2, 'Ergonomic Office Chair',       'Bought last semester, still very comfortable and sturdy.',         2800.00, 'like_new', 'active'),
(4, 3, 'Engineering Mathematics Vol.2','Stroud 7th Edition. Highlighted but complete.',                     600.00, 'used',     'active'),
(4, 3, 'Introduction to Python (Book)','Unopened, sealed. Got a PDF so selling this.',                      900.00, 'new',      'active'),
(5, 4, 'Nike Campus Hoodie (L)',        'Worn twice, excellent condition, washed.',                         950.00, 'like_new', 'active'),
(5, 5, 'Mountain Bicycle',             'Trek 21-speed, tyres recently replaced. Must pick up.',           12000.00, 'used',     'active'),
(2, 6, 'Russell Hobbs Microwave 20L',  'Works perfectly, selling because moving to catered halls.',         4500.00, 'like_new', 'active'),
(3, 8, 'Desk Fan — Rechargeable',      'USB rechargeable, quiet motor. Ideal for power cuts.',              1200.00, 'used',     'active');

-- ─── Product Images (placeholder paths) ──────────────────
INSERT INTO product_images (product_id, image_path, is_primary) VALUES
(1,  'uploads/products/laptop_hp_1.jpg',     1),
(2,  'uploads/products/samsung_a52_1.jpg',   1),
(3,  'uploads/products/desk_1.jpg',          1),
(4,  'uploads/products/chair_1.jpg',         1),
(5,  'uploads/products/eng_math_1.jpg',      1),
(6,  'uploads/products/python_book_1.jpg',   1),
(7,  'uploads/products/hoodie_1.jpg',        1),
(8,  'uploads/products/bicycle_1.jpg',       1),
(9,  'uploads/products/microwave_1.jpg',     1),
(10, 'uploads/products/fan_1.jpg',           1);

-- ─── Product Tags ─────────────────────────────────────────
INSERT INTO product_tags (product_id, tag_id) VALUES
(1, 1), (2, 2), (3, 4), (4, 3), (5, 5), (6, 5),
(7, 6), (8, 7), (9, 8), (10, 10);
