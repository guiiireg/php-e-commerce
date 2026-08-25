-- ==============================================================================
-- DATABASE: Schema and Seed Data
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 1. Table `users` (User Accounts and Roles)
-- ------------------------------------------------------------------------------
DROP TABLE IF EXISTS invoice;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS stock;
DROP TABLE IF EXISTS article;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    solde DECIMAL(10,2) DEFAULT 100.00,
    photo VARCHAR(255) DEFAULT 'default.jpg',
    role ENUM('user', 'admin') DEFAULT 'user'
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 2. Table `article` (Product Catalog)
-- ------------------------------------------------------------------------------
CREATE TABLE article (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    auteur_id INT NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
    FOREIGN KEY (auteur_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 3. Table `stock` (Inventory and Available Quantity per Article)
-- ------------------------------------------------------------------------------
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    nombre INT DEFAULT 0,
    FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 4. Table `cart` (Shopping Cart per User)
-- ------------------------------------------------------------------------------
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 5. Table `invoice` (Order History and Generated Customer Invoices)
-- ------------------------------------------------------------------------------
CREATE TABLE invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant DECIMAL(10, 2) NOT NULL,
    adresse_facturation VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    cp VARCHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================================================================
-- DEMO SEED DATA
-- ==============================================================================

-- Seed Administrator Account (Password: Admin123456!)
-- Hash generated using password_hash('Admin123456!', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, solde, role) VALUES 
('Administrator', 'admin@example.com', '$2y$12$uLcOklub2RNOuQaQptbtruqvmCgAcPYVilwTglzz.Wm.jsdeMCI5K', 500.00, 'admin'),
('JeanDupont', 'jean@example.com', '$2y$12$uLcOklub2RNOuQaQptbtruqvmCgAcPYVilwTglzz.Wm.jsdeMCI5K', 250.00, 'user');

-- Seed Catalog Products
INSERT INTO article (nom, description, prix, auteur_id, image) VALUES 
('Ordinateur Portable Pro', 'Un PC performant pour le développement et la création graphique.', 899.99, 1, 'default.jpg'),
('Casque Audio Sans Fil', 'Casque à réduction de bruit active avec autonomie de 30 heures.', 149.50, 1, 'default.jpg'),
('Clavier Mécanique RGB', 'Switchs silencieux et rétroéclairage personnalisable.', 79.90, 1, 'default.jpg'),
('Souris Ergonomique', 'Précision optimale et confort prolongé pour les longues sessions.', 45.00, 1, 'default.jpg');

-- Seed Initial Inventory Stock Levels
INSERT INTO stock (article_id, nombre) VALUES 
(1, 10),
(2, 25),
(3, 15),
(4, 30);

