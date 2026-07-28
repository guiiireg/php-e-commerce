-- ==============================================================================
-- BASE DE DONNÉES : php_exam
-- Ce fichier contient les requêtes de création des tables et des données d'exemple.
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS php_exam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE php_exam;

-- ------------------------------------------------------------------------------
-- 1. Table `users` (Gestion des comptes utilisateurs et rôles)
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
-- 2. Table `article` (Catalogue des produits en vente)
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
-- 3. Table `stock` (Gestion de la quantité disponible par article)
-- ------------------------------------------------------------------------------
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    nombre INT DEFAULT 0,
    FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------------------------
-- 4. Table `cart` (Panier d'achat pour chaque utilisateur)
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
-- 5. Table `invoice` (Historique des commandes et factures générées)
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
-- DONNÉES D'EXEMPLE (Seeding initial pour les tests)
-- ==============================================================================

-- Insertion d'un compte Administrateur (Mot de passe: Admin123456!)
-- Hash généré via password_hash('Admin123456!', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, solde, role) VALUES 
('Administrator', 'admin@example.com', '$2y$10$wK1Gv/1J/uWk48u9p.J8s.1bVbH0fW19G.Yg6dG1vH1234567890', 500.00, 'admin'),
('JeanDupont', 'jean@example.com', '$2y$10$wK1Gv/1J/uWk48u9p.J8s.1bVbH0fW19G.Yg6dG1vH1234567890', 250.00, 'user');

-- Insertion d'articles de démonstration
INSERT INTO article (nom, description, prix, auteur_id, image) VALUES 
('Ordinateur Portable Pro', 'Un PC performant pour le développement et la création graphique.', 899.99, 1, 'default.jpg'),
('Casque Audio Sans Fil', 'Casque à réduction de bruit active avec autonomie de 30 heures.', 149.50, 1, 'default.jpg'),
('Clavier Mécanique RGB', 'Switchs silencieux et rétroéclairage personnalisable.', 79.90, 1, 'default.jpg'),
('Souris Ergonomique', 'Précision optimale et confort prolongé pour les longues sessions.', 45.00, 1, 'default.jpg');

-- Initialisation des stocks pour les articles créés
INSERT INTO stock (article_id, nombre) VALUES 
(1, 10),
(2, 25),
(3, 15),
(4, 30);
