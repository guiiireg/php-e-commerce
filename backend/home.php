<?php
/**
 * Catalog & Home Page Controller
 *
 * Handles public product catalog discovery, multi-column search filtering,
 * and user-controlled sorting.
 */

require_once __DIR__ . '/config/config.php';

$search = trim($_GET['search'] ?? '');
$sort = trim($_GET['sort'] ?? '');

// Use LEFT JOINs to aggregate product details, author metadata, and stock levels in a single query
// to eliminate N+1 query performance overhead. COALESCE ensures a predictable integer (0) if stock is uninitialized.
$sql = "SELECT article.*, users.username AS auteur_name, COALESCE(stock.nombre, 0) AS stock_qty
        FROM article
        LEFT JOIN users ON article.auteur_id = users.id
        LEFT JOIN stock ON stock.article_id = article.id";

$params = [];

// Apply wildcard search matching across both name and description to improve discovery
if (!empty($search)) {
    $sql .= " WHERE article.nom LIKE :search OR article.description LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Map user sort choices through a strict whitelist.
// SQL identifiers and keywords (like ORDER BY directions) cannot be passed as PDO bound parameters,
// so explicit whitelisting prevents SQL injection vulnerabilities here.
switch ($sort) {
    case 'prix_asc':
        $orderBy = "article.prix ASC";
        break;
    case 'prix_desc':
        $orderBy = "article.prix DESC";
        break;
    default:
        // Default to newest items first so returning customers always see recent products
        $orderBy = "article.date_publication DESC";
        break;
}

$sql .= " ORDER BY " . $orderBy;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Failed to load catalog articles: " . $e->getMessage());
    die("An error occurred while loading the product catalog. Please try again later.");
}

require_once __DIR__ . '/../frontend/pages/home.php';
