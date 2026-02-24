CREATE DATABASE IF NOT EXISTS php_exam;
USE php_exam;

-- ==========================================
-- 1. Table USER
-- ==========================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    solde DECIMAL(10,2) DEFAULT 0.00,
    photo VARCHAR(255) DEFAULT 'default.jpg',
    role ENUM('user', 'admin') DEFAULT 'user'
) ENGINE=InnoDB;

-- ==========================================
-- 2. Table ARTICLE
-- ==========================================
CREATE TABLE IF NOT EXISTS Article (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    auteur_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (auteur_id) REFERENCES User(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 3. Table STOCK
-- ==========================================
CREATE TABLE IF NOT EXISTS Stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    nombre INT DEFAULT 0,
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 4. Table CART (Panier)
-- ==========================================
CREATE TABLE IF NOT EXISTS Cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================
-- 5. Table INVOICE (Factures)
-- ==========================================
CREATE TABLE IF NOT EXISTS Invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant DECIMAL(10, 2) NOT NULL,
    adresse_facturation VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    cp VARCHAR(20) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
) ENGINE=InnoDB;
