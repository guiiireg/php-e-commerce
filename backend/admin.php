<?php
/**
 * Administration Dashboard Controller
 *
 * Provides high-level administrative oversight for user accounts,
 * catalog inventory management, and historical customer invoices.
 */

require_once __DIR__ . "/config/config.php";

// Role-Based Access Control (RBAC): Restrict endpoint strictly to verified administrators
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Access denied. Administrator privileges required.";
    header('Location: /backend/auth/login.php');
    exit();
}

try {
    // Order newest first so administrators immediately see recent member registrations
    $stmtUsers = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    // Eagerly aggregate inventory counts to highlight out-of-stock items in the admin table
    $stmtArticles = $pdo->query("
        SELECT article.*, COALESCE(stock.nombre, 0) AS stock_qty, users.username AS auteur_name
        FROM article
        LEFT JOIN users ON article.auteur_id = users.id
        LEFT JOIN stock ON stock.article_id = article.id
        ORDER BY article.id DESC
    ");
    $articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);

    // Join invoices with user table to display customer name rather than raw foreign key user_id
    $stmtInvoices = $pdo->query("
        SELECT invoice.*, users.username AS client_name
        FROM invoice
        JOIN users ON invoice.user_id = users.id
        ORDER BY invoice.id DESC
    ");
    $invoices = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Admin dashboard data fetch failed: " . $e->getMessage());
    die("An error occurred while loading administrative dashboard data.");
}

require_once __DIR__ . '/../frontend/pages/admin.php';