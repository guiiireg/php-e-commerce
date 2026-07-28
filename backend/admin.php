<?php
/**
 * CONTRÔLEUR TABLEAU DE BORD D'ADMINISTRATION
 * - Vérifie que l'utilisateur a le rôle 'admin'.
 * - Récupère la liste de tous les utilisateurs (table `users`).
 * - Récupère la liste de tous les articles (table `article`) avec leurs stocks correspondants.
 */

require_once __DIR__ . "/config/config.php";

// Contrôle d'accès : Réservé uniquement aux administrateurs
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Accès refusé. Vous devez être connecté en tant qu'administrateur.";
    header('Location: /backend/auth/login.php');
    exit();
}

try {
    // Récupération de l'ensemble des utilisateurs enregistrés
    $stmtUsers = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des articles avec leur stock actuel
    $stmtArticles = $pdo->query("
        SELECT article.*, COALESCE(stock.nombre, 0) AS stock_qty, users.username AS auteur_name
        FROM article
        LEFT JOIN users ON article.auteur_id = users.id
        LEFT JOIN stock ON stock.article_id = article.id
        ORDER BY article.id DESC
    ");
    $articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);

    // Récupération du nombre de commandes passées
    $stmtInvoices = $pdo->query("
        SELECT invoice.*, users.username AS client_name
        FROM invoice
        JOIN users ON invoice.user_id = users.id
        ORDER BY invoice.id DESC
    ");
    $invoices = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des données d'administration : " . htmlspecialchars($e->getMessage()));
}

// Inclusions de la vue HTML du tableau de bord d'administration
require_once __DIR__ . '/../frontend/pages/admin.php';