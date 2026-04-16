-- ============================================================
-- CampusMarket — Full Database Schema (ERD v2)
-- Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS campusmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusmarket;

-- ─────────────────────────────────────────
-- 1. users
-- ─────────────────────────────────────────
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    phone         VARCHAR(20)  NULL,
    avatar        VARCHAR(255) NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 2. categories
-- ─────────────────────────────────────────
CREATE TABLE categories (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(100) NOT NULL,
    slug  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 3. tags
-- ─────────────────────────────────────────
CREATE TABLE tags (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50)  NOT NULL UNIQUE,
    slug VARCHAR(50)  NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 4. products
-- ─────────────────────────────────────────
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT            NOT NULL,
    category_id INT            NOT NULL,
    title       VARCHAR(200)   NOT NULL,
    description TEXT,
    price       DECIMAL(10,2)  NOT NULL,
    condition   ENUM('new', 'like_new', 'used', 'poor') NOT NULL DEFAULT 'used',
    status      ENUM('active', 'sold', 'flagged')       NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 5. product_images
-- ─────────────────────────────────────────
CREATE TABLE product_images (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT          NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN      NOT NULL DEFAULT 0,
    uploaded_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 6. product_tags (pivot)
-- ─────────────────────────────────────────
CREATE TABLE product_tags (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    tag_id     INT NOT NULL,
    UNIQUE KEY uq_product_tag (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 7. wishlists
-- ─────────────────────────────────────────
CREATE TABLE wishlists (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wishlist (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 8. orders
-- ─────────────────────────────────────────
CREATE TABLE orders (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id      INT            NOT NULL,
    product_id    INT            NOT NULL,
    amount        DECIMAL(10,2)  NOT NULL,
    status        ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    meeting_point VARCHAR(255)   NULL,
    notes         TEXT           NULL,
    created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id)   REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 9. transactions
-- ─────────────────────────────────────────
CREATE TABLE transactions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT           NOT NULL UNIQUE,
    payment_method  ENUM('cash', 'venmo', 'zelle', 'other') NOT NULL DEFAULT 'cash',
    transaction_ref VARCHAR(255)  NULL,
    amount          DECIMAL(10,2) NOT NULL,
    status          ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 10. messages
-- ─────────────────────────────────────────
CREATE TABLE messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT     NOT NULL,
    receiver_id INT     NOT NULL,
    product_id  INT     NOT NULL,
    body        TEXT    NOT NULL,
    is_read     BOOLEAN NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 11. ratings
-- ─────────────────────────────────────────
CREATE TABLE ratings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT         NOT NULL,
    seller_id   INT         NOT NULL,
    product_id  INT         NOT NULL,
    rating      TINYINT     NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT        NULL,
    created_at  TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rating (reviewer_id, product_id),
    FOREIGN KEY (reviewer_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (seller_id)   REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 12. reports
-- ─────────────────────────────────────────
CREATE TABLE reports (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT  NOT NULL,
    product_id  INT  NOT NULL,
    reason      TEXT NOT NULL,
    status      ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id)  REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- 13. notifications
-- ─────────────────────────────────────────
CREATE TABLE notifications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,
    type         ENUM('message', 'order', 'wishlist', 'system') NOT NULL,
    title        VARCHAR(200) NOT NULL,
    body         TEXT         NOT NULL,
    is_read      BOOLEAN      NOT NULL DEFAULT 0,
    reference_id INT          NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
