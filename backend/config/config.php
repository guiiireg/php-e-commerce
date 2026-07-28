<?php
/**
 * Fichier de configuration principale de l'application PHP E-Commerce.
 * - Gestion du démarrage de la session utilisateur.
 * - Connexion sécurisée à la base de données MySQL avec PDO.
 */

// Démarrage de la session PHP si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Identifiants de connexion à la base de données MySQL
$host = 'localhost';
$dbname = 'php_exam';
$username = 'php_user';
$password = 'root123';

try {
    // Création de l'instance PDO avec encodage UTF-8
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Gestion des erreurs via des exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Mode de récupération par défaut sous forme de tableau associatif
    ]);
} catch (PDOException $e) {
    // Interception des erreurs de connexion pour éviter la divulgation d'informations sensibles
    die("Erreur de connexion à la base de données : " . htmlspecialchars($e->getMessage()));
}