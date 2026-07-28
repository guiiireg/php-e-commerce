<?php
/**
 * CONTRÔLEUR DE MODIFICATION D'UN ARTICLE ET DE SON STOCK (ADMIN)
 * - Restreint aux administrateurs.
 * - Récupère les informations existantes d'un article par son ID.
 * - Permet de mettre à jour le nom, la description, le prix, l'image et la quantité en stock.
 */

require_once __DIR__ . '/config/config.php';

// Contrôle d'accès Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Accès refusé.";
    header('Location: /backend/auth/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /backend/admin.php');
    exit();
}

$error = null;

// Chargement des données de l'article et du stock actuel
try {
    $stmt = $pdo->prepare("
        SELECT article.*, COALESCE(stock.nombre, 0) AS stock_qty 
        FROM article 
        LEFT JOIN stock ON stock.article_id = article.id 
        WHERE article.id = ?
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        $_SESSION['error'] = "Article introuvable.";
        header('Location: /backend/admin.php');
        exit();
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération : " . htmlspecialchars($e->getMessage()));
}

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = (float)($_POST['prix'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $image = trim($_POST['image'] ?? 'default.jpg');

    if (empty($nom) || empty($description) || $prix <= 0) {
        $error = "Veuillez vérifier les informations saisies.";
    } else {
        try {
            $pdo->beginTransaction();

            // Mise à jour de l'article
            $stmtUpArt = $pdo->prepare("
                UPDATE article 
                SET nom = ?, description = ?, prix = ?, image = ? 
                WHERE id = ?
            ");
            $stmtUpArt->execute([$nom, $description, $prix, empty($image) ? 'default.jpg' : $image, $id]);

            // Mise à jour ou insertion du stock
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

            $_SESSION['success'] = "L'article #$id a été mis à jour avec succès !";
            header('Location: /backend/admin.php');
            exit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Erreur lors de la mise à jour : " . htmlspecialchars($e->getMessage());
        }
    }
}

require_once __DIR__ . '/../frontend/pages/edit_article.php';
