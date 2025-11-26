-- Creation bdd
CREATE DATABASE IF NOT EXISTS shop_db;
USE shop_db;

-- table utilisateur
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table produits
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    category VARCHAR(50)
);

-- Table commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10, 2),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table info commandes
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10, 2),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Table info livraison
CREATE TABLE IF NOT EXISTS shipping_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    prenom VARCHAR(100),
    nom VARCHAR(100),
    email VARCHAR(150),
    telephone VARCHAR(20),
    adresse VARCHAR(255),
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    pays VARCHAR(100),
    complement_adresse TEXT,
    carte_derniers_chiffres VARCHAR(4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- 1er utilisqteur par defaut (mot de passe: ayb2008)
INSERT INTO users (username, password, email) VALUES
('ayoub', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ayoub@shop.com');

-- ajouter des produit par defauts
INSERT INTO products (name, description, price, image, stock, category) VALUES
('Laptop Pro 15"', 'Ordinateur portable haute performance avec processeur i7', 1299.99, 'laptop.jpg', 15, 'Électronique'),
('Smartphone X', 'Smartphone dernière génération avec caméra 108MP', 899.99, 'phone.jpg', 25, 'Électronique'),
('Casque Audio Premium', 'Casque sans fil avec réduction de bruit active', 249.99, 'headphones.jpg', 40, 'Audio'),
('Montre Connectée', 'Montre intelligente avec suivi fitness', 349.99, 'watch.jpg', 30, 'Accessoires'),
('Tablette 10"', 'Tablette tactile idéale pour le multimédia', 499.99, 'tablet.jpg', 20, 'Électronique'),
('Clavier Mécanique RGB', 'Clavier gaming avec éclairage personnalisable', 149.99, 'keyboard.jpg', 35, 'Gaming'),
('Souris Gaming', 'Souris haute précision 16000 DPI', 79.99, 'mouse.jpg', 50, 'Gaming'),
('Webcam HD', 'Webcam 1080p pour visioconférence', 89.99, 'webcam.jpg', 28, 'Accessoires');