CREATE DATABASE IF NOT EXISTS clothing_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clothing_store;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    product_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category ENUM('T-Shirts','Hoodies','Pants','Jackets','Shorts','Accessories') NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    sizes VARCHAR(30) NOT NULL DEFAULT 'S,M,L,XL',
    color VARCHAR(30) NOT NULL DEFAULT 'ink',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    order_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address VARCHAR(250) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    size VARCHAR(10) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

INSERT INTO products (name, category, price, stock, sizes, color) VALUES
('[Product]', 'T-Shirts', 0.00, 20, 'S,M,L,XL', 'clay'),
('[Product]', 'Hoodies', 0.00, 20, 'S,M,L,XL', 'forest'),
('[Product]', 'Pants', 0.00, 20, 'S,M,L,XL', 'olive'),
('[Product]', 'Jackets', 0.00, 20, 'S,M,L,XL', 'ink'),
('[Product]', 'Shorts', 0.00, 20, 'S,M,L,XL', 'sky'),
('[Product]', 'Accessories', 0.00, 20, 'S,M,L,XL', 'oat');

-- After registering a normal account, promote it to admin with:
-- UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
