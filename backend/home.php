<?php
/**
 * CONTRÔLEUR PAGE D'ACCUEIL / CATALOGUE PRODUITS
 * - Récupère la liste des articles en base de données.
 * - Permet le filtrage par mot-clé (recherche sur le nom ou la description).
 * - Gère le tri par prix croissant, décroissant ou date de publication.
 * - Joint la table `stock` pour afficher la quantité disponible de chaque article.
 */

require_once __DIR__ . '/config/config.php';

// Récupération et nettoyage des paramètres de recherche et tri
$search = trim($_GET['search'] ?? '');
$sort = trim($_GET['sort'] ?? '');

// Construction dynamique de la requête SQL avec jointure sur les stocks
$sql = "SELECT article.*, users.username AS auteur_name, COALESCE(stock.nombre, 0) AS stock_qty
        FROM article
        LEFT JOIN users ON article.auteur_id = users.id
        LEFT JOIN stock ON stock.article_id = article.id";

$params = [];

// Filtre de recherche par mot-clé
if (!empty($search)) {
    $sql .= " WHERE article.nom LIKE :search OR article.description LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Application du tri selon la sélection de l'utilisateur
switch ($sort) {
    case 'prix_asc':
        $orderBy = "article.prix ASC";
        break;
    case 'prix_desc':
        $orderBy = "article.prix DESC";
        break;
    default:
        $orderBy = "article.date_publication DESC";
        break;
}

$sql .= " ORDER BY " . $orderBy;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des articles : " . htmlspecialchars($e->getMessage()));
}

// Inclusions de la vue HTML d'accueil
require_once __DIR__ . '/../frontend/pages/home.php';
