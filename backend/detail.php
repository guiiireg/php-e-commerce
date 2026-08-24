<?php
/**
 * Product Detail & Add-to-Cart Controller
 *
 * Displays full item specifications, seller info, real-time inventory levels,
 * and handles adding items to the user's persistent cart.
 */

require_once __DIR__ . '/config/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect unroutable or malformed product IDs back to the catalog to maintain clean navigation
if ($id <= 0) {
    header("Location: /backend/home.php");
    exit;
}

$error = null;
$success = null;

try {
    // Eagerly join author and stock to render complete product metadata in a single round-trip
    $stmt = $pdo->prepare("
        SELECT article.*, users.username AS auteur_name, COALESCE(stock.nombre, 0) AS stock_qty
        FROM article
        LEFT JOIN users ON article.auteur_id = users.id
        LEFT JOIN stock ON stock.article_id = article.id
        WHERE article.id = ?
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    // If an article was deleted or unpublished, bail early rather than rendering a blank template
    if (!$article) {
        header("Location: /backend/home.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Failed to load article detail #$id: " . $e->getMessage());
    die("An error occurred while fetching product details.");
}

// Handle cart additions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    // Enforce authentication because cart rows are tied to foreign key user_id in the database
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_error'] = "Please log in to add items to your cart.";
        header("Location: /backend/auth/login.php");
        exit;
    }

    if (!verify_csrf_token()) {
        $error = "Form session expired. Please refresh the page and try again.";
    } else {
        $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;

        // Re-validate limits server-side because HTML5 input min/max attributes can be altered in DevTools
        if ($quantite <= 0) {
            $error = "Please select a valid quantity.";
        } elseif ($quantite > $article['stock_qty']) {
            $error = "The requested quantity exceeds available stock.";
        } else {
            $userId = (int)$_SESSION['user_id'];

            try {
                // Upsert logic: Increment quantity if product is already in cart, otherwise insert a new row.
                // This guarantees a single row per (user_id, article_id) tuple for easy summing.
                $stmtCheckCart = $pdo->prepare("SELECT id, quantite FROM cart WHERE user_id = ? AND article_id = ?");
                $stmtCheckCart->execute([$userId, $id]);
                $cartItem = $stmtCheckCart->fetch();

                if ($cartItem) {
                    $newQty = $cartItem['quantite'] + $quantite;
                    if ($newQty > $article['stock_qty']) {
                        $error = "The total quantity in your cart would exceed available stock.";
                    } else {
                        $stmtUpdateCart = $pdo->prepare("UPDATE cart SET quantite = ? WHERE id = ?");
                        $stmtUpdateCart->execute([$newQty, $cartItem['id']]);
                        $_SESSION['flash_success'] = "Cart quantity updated!";
                        header("Location: /backend/cart.php");
                        exit;
                    }
                } else {
                    $stmtInsertCart = $pdo->prepare("INSERT INTO cart (user_id, article_id, quantite) VALUES (?, ?, ?)");
                    $stmtInsertCart->execute([$userId, $id, $quantite]);
                    $_SESSION['flash_success'] = "Item successfully added to your cart!";
                    header("Location: /backend/cart.php");
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Failed to insert/update cart for user #$userId: " . $e->getMessage());
                $error = "An error occurred while updating your cart.";
            }
        }
    }
}

require_once __DIR__ . '/../frontend/pages/detail.php';
