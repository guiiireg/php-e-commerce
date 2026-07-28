<?php
/**
 * CONTRÔLEUR PAGE DÉTAIL D'UN ARTICLE
 * - Récupère les détails complets d'un article par son ID.
 * - Récupère la quantité en stock disponible.
 * - Gère l'ajout de l'article au panier de l'utilisateur.
 */

require_once __DIR__ . '/config/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: /backend/home.php");
    exit;
}

$error = null;
$success = null;

// Récupération de l'article avec jointures sur l'auteur et le stock
try {
    $stmt = $pdo->prepare("
        SELECT article.*, users.username AS auteur_name, COALESCE(stock.nombre, 0) AS stock_qty
        FROM article
        LEFT JOIN users ON article.auteur_id = users.id
        LEFT JOIN stock ON stock.article_id = article.id
        WHERE article.id = ?
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        header("Location: /backend/home.php");
        exit;
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du produit : " . htmlspecialchars($e->getMessage()));
}

// Traitement de l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    // Vérification si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_error'] = "Veuillez vous connecter pour ajouter un article à votre panier.";
        header("Location: /backend/auth/login.php");
        exit;
    }

    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;

    if ($quantite <= 0) {
        $error = "Veuillez choisir une quantité valide.";
    } elseif ($quantite > $article['stock_qty']) {
        $error = "La quantité demandée dépasse le stock disponible.";
    } else {
        $userId = $_SESSION['user_id'];

        try {
            // Vérification si l'article est déjà présent dans le panier de l'utilisateur
            $stmtCheckCart = $pdo->prepare("SELECT id, quantite FROM cart WHERE user_id = ? AND article_id = ?");
            $stmtCheckCart->execute([$userId, $id]);
            $cartItem = $stmtCheckCart->fetch();

            if ($cartItem) {
                // Mise à jour de la quantité
                $newQty = $cartItem['quantite'] + $quantite;
                if ($newQty > $article['stock_qty']) {
                    $error = "La quantité totale dans votre panier dépasserait le stock disponible.";
                } else {
                    $stmtUpdateCart = $pdo->prepare("UPDATE cart SET quantite = ? WHERE id = ?");
                    $stmtUpdateCart->execute([$newQty, $cartItem['id']]);
                    $_SESSION['flash_success'] = "Quantité mise à jour dans votre panier !";
                    header("Location: /backend/cart.php");
                    exit;
                }
            } else {
                // Insertion dans la table cart
                $stmtInsertCart = $pdo->prepare("INSERT INTO cart (user_id, article_id, quantite) VALUES (?, ?, ?)");
                $stmtInsertCart->execute([$userId, $id, $quantite]);
                $_SESSION['flash_success'] = "Article ajouté à votre panier avec succès !";
                header("Location: /backend/cart.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout au panier : " . htmlspecialchars($e->getMessage());
        }
    }
}

// Inclusions de la vue HTML de détail produit
require_once __DIR__ . '/../frontend/pages/detail.php';
