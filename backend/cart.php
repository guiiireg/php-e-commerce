<?php
/**
 * Shopping Cart & Checkout Controller
 *
 * Manages persistent user cart items, computes live order totals,
 * and executes atomic ACID transactions for order validation and inventory deduction.
 */

require_once __DIR__ . '/config/config.php';

// Cart operations depend on an authenticated user account
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Please log in to access your cart.";
    header("Location: /backend/auth/login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$error = null;
$success = null;

// Handle cart item deletion via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verify_csrf_token()) {
        $_SESSION['flash_error'] = "Session expired. Please try again.";
    } else {
        $cartId = (int)($_POST['cart_id'] ?? 0);
        if ($cartId > 0) {
            // Scope deletion strictly by user_id to prevent IDOR (Insecure Direct Object Reference) attacks
            $stmtDel = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmtDel->execute([$cartId, $userId]);
            $_SESSION['flash_success'] = "Item removed from your cart.";
        }
    }
    header("Location: /backend/cart.php");
    exit;
}

// Handle cart item quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    if (!verify_csrf_token()) {
        $_SESSION['flash_error'] = "Session expired. Please try again.";
    } else {
        $cartId = (int)($_POST['cart_id'] ?? 0);
        $newQty = (int)($_POST['quantite'] ?? 1);

        if ($cartId > 0 && $newQty > 0) {
            // Scope update by user_id to prevent unauthorized cart manipulation
            $stmtUp = $pdo->prepare("UPDATE cart SET quantite = ? WHERE id = ? AND user_id = ?");
            $stmtUp->execute([$newQty, $cartId, $userId]);
            $_SESSION['flash_success'] = "Quantity updated.";
        }
    }
    header("Location: /backend/cart.php");
    exit;
}

// Handle order checkout and settlement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    if (!verify_csrf_token()) {
        $error = "Form session expired. Please refresh the page and try again.";
    } else {
        $adresse = trim($_POST['adresse_facturation'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $cp = trim($_POST['cp'] ?? '');

        if (empty($adresse) || empty($ville) || empty($cp)) {
            $error = "Please fill in all billing address fields.";
        } else {
            try {
                // Re-fetch current article prices and stock directly from the database at checkout time.
                // This prevents race conditions or client-tampered totals if prices/stock changed during checkout.
                $stmtCartCheck = $pdo->prepare("
                    SELECT cart.id AS cart_id, cart.quantite, article.id AS article_id, article.nom, article.prix, COALESCE(stock.nombre, 0) AS stock_qty
                    FROM cart
                    JOIN article ON cart.article_id = article.id
                    LEFT JOIN stock ON stock.article_id = article.id
                    WHERE cart.user_id = ?
                ");
                $stmtCartCheck->execute([$userId]);
                $cartItems = $stmtCartCheck->fetchAll(PDO::FETCH_ASSOC);

                if (empty($cartItems)) {
                    $error = "Your cart is empty.";
                } else {
                    $totalAmount = 0;
                    $stockIssues = [];

                    foreach ($cartItems as $item) {
                        $totalAmount += $item['prix'] * $item['quantite'];
                        if ($item['quantite'] > $item['stock_qty']) {
                            $stockIssues[] = "Item '" . htmlspecialchars($item['nom']) . "' only has " . (int)$item['stock_qty'] . " units in stock (requested: " . (int)$item['quantite'] . ").";
                        }
                    }

                    if (!empty($stockIssues)) {
                        $error = implode("<br>", $stockIssues);
                    } else {
                        // Check live user balance in database rather than relying on session cache
                        $stmtUser = $pdo->prepare("SELECT solde FROM users WHERE id = ?");
                        $stmtUser->execute([$userId]);
                        $userSolde = (float) $stmtUser->fetchColumn();

                        if ($userSolde < $totalAmount) {
                            $error = "Insufficient balance! Total order: " . number_format($totalAmount, 2, ',', ' ') . " € — Available balance: " . number_format($userSolde, 2, ',', ' ') . " €.";
                        } else {
                            // Atomic database transaction guarantees that balance deduction, inventory reduction,
                            // invoice persistence, and cart clearance succeed together or fail without partial side-effects.
                            $pdo->beginTransaction();

                            // 1. Deduct total cost from user balance
                            $newSolde = $userSolde - $totalAmount;
                            $stmtUpdateSolde = $pdo->prepare("UPDATE users SET solde = ? WHERE id = ?");
                            $stmtUpdateSolde->execute([$newSolde, $userId]);
                            $_SESSION['solde'] = $newSolde;

                            // 2. Decrement physical inventory
                            $stmtDecStock = $pdo->prepare("UPDATE stock SET nombre = nombre - ? WHERE article_id = ?");
                            foreach ($cartItems as $item) {
                                $stmtDecStock->execute([$item['quantite'], $item['article_id']]);
                            }

                            // 3. Record immutable sales invoice for bookkeeping
                            $stmtInvoice = $pdo->prepare("
                                INSERT INTO invoice (user_id, montant, adresse_facturation, ville, cp) 
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmtInvoice->execute([$userId, $totalAmount, $adresse, $ville, $cp]);
                            $invoiceId = $pdo->lastInsertId();

                            // 4. Empty active cart upon successful payment
                            $stmtClearCart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                            $stmtClearCart->execute([$userId]);

                            $pdo->commit();

                            $_SESSION['flash_success'] = "Order #$invoiceId confirmed! Paid: " . number_format($totalAmount, 2, ',', ' ') . " €. New balance: " . number_format($newSolde, 2, ',', ' ') . " €.";
                            header("Location: /backend/cart.php");
                            exit;
                        }
                    }
                }
            } catch (Exception $e) {
                // Roll back any partial database mutations to prevent data inconsistency
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Checkout transaction failed for user #$userId: " . $e->getMessage());
                $error = "An error occurred while processing your order. No charges were made.";
            }
        }
    }
}

// Fetch active cart items for display
try {
    $stmtCart = $pdo->prepare("
        SELECT cart.id AS cart_id, cart.quantite, article.id AS article_id, article.nom, article.prix, article.image, COALESCE(stock.nombre, 0) AS stock_qty
        FROM cart
        JOIN article ON cart.article_id = article.id
        LEFT JOIN stock ON stock.article_id = article.id
        WHERE cart.user_id = ?
    ");
    $stmtCart->execute([$userId]);
    $items = $stmtCart->fetchAll(PDO::FETCH_ASSOC);

    $totalGlobal = 0;
    foreach ($items as $item) {
        $totalGlobal += $item['prix'] * $item['quantite'];
    }

    // Refresh balance in session from database to keep header badge in sync
    $stmtSolde = $pdo->prepare("SELECT solde FROM users WHERE id = ?");
    $stmtSolde->execute([$userId]);
    $currentSolde = (float) $stmtSolde->fetchColumn();
    $_SESSION['solde'] = $currentSolde;

} catch (PDOException $e) {
    error_log("Failed to load cart for user #$userId: " . $e->getMessage());
    die("An error occurred while loading your cart.");
}

require_once __DIR__ . '/../../frontend/pages/cart.php';
