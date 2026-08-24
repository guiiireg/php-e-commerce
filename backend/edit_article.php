<?php
/**
 * Administrator Product Update Controller
 *
 * Handles editing existing product specifications and synchronizing
 * inventory stock levels within an atomic transaction.
 */

require_once __DIR__ . '/config/config.php';

// Role Guard: Restrict product modifications to administrators
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Access denied.";
    header('Location: /backend/auth/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Reject missing or malformed query parameter IDs to prevent ambiguous database errors
if ($id <= 0) {
    header('Location: /backend/admin.php');
    exit();
}

$error = null;

try {
    // Eagerly join stock to pre-populate both product details and current inventory count in the form
    $stmt = $pdo->prepare("
        SELECT article.*, COALESCE(stock.nombre, 0) AS stock_qty 
        FROM article 
        LEFT JOIN stock ON stock.article_id = article.id 
        WHERE article.id = ?
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        $_SESSION['error'] = "Article not found.";
        header('Location: /backend/admin.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Failed to load article #$id for editing: " . $e->getMessage());
    die("An error occurred while fetching article details.");
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = "Form session expired. Please refresh the page and try again.";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = (float)($_POST['prix'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $rawImage = trim($_POST['image'] ?? 'default.jpg');

        // Path Traversal Mitigation: Strip directories to confine images to /frontend/assets/img/
        $image = basename($rawImage);
        if (empty($image)) {
            $image = 'default.jpg';
        }

        if (empty($nom) || empty($description) || $prix <= 0) {
            $error = "Please review your inputs (name, description, and positive price required).";
        } else {
            try {
                // Atomic transaction guarantees that product metadata and stock levels are updated together
                $pdo->beginTransaction();

                // Update core article metadata
                $stmtUpArt = $pdo->prepare("
                    UPDATE article 
                    SET nom = ?, description = ?, prix = ?, image = ? 
                    WHERE id = ?
                ");
                $stmtUpArt->execute([$nom, $description, $prix, $image, $id]);

                // Defensive stock synchronization: Update existing stock row, or create one if missing
                $stmtCheckStock = $pdo->prepare("SELECT id FROM stock WHERE article_id = ?");
                $stmtCheckStock->execute([$id]);
                if ($stmtCheckStock->fetch()) {
                    $stmtUpStock = $pdo->prepare("UPDATE stock SET nombre = ? WHERE article_id = ?");
                    $stmtUpStock->execute([max(0, $stock), $id]);
                } else {
                    $stmtInsStock = $pdo->prepare("INSERT INTO stock (article_id, nombre) VALUES (?, ?)");
                    $stmtInsStock->execute([$id, max(0, $stock)]);
                }

                $pdo->commit();

                $_SESSION['success'] = "Article #$id updated successfully!";
                header('Location: /backend/admin.php');
                exit();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Failed to update article #$id: " . $e->getMessage());
                $error = "An error occurred while updating the article.";
            }
        }
    }
}

require_once __DIR__ . '/../frontend/pages/edit_article.php';
