<?php
/**
 * CONTRÔLEUR DE GESTION DU PANIER ET DU PROCESSUS DE COMMANDE
 * - Récupère la liste des articles ajoutés au panier par l'utilisateur connecté.
 * - Calcule les sous-totaux par ligne et le montant total global.
 * - Permet la modification de quantité et la suppression d'éléments du panier.
 * - Traite la validation de la commande avec vérification du solde, décrémentation des stocks et génération de facture.
 */

require_once __DIR__ . '/config/config.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = "Veuillez vous connecter pour accéder à votre panier.";
    header("Location: /backend/auth/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$error = null;
$success = null;

// GESTION DES ACTIONS SUR LE PANIER (Suppression / Mise à jour)
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $cartId = (int)($_GET['id'] ?? 0);
    if ($cartId > 0) {
        $stmtDel = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmtDel->execute([$cartId, $userId]);
        $_SESSION['flash_success'] = "Article retiré du panier.";
    }
    header("Location: /backend/cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $cartId = (int)($_POST['cart_id'] ?? 0);
    $newQty = (int)($_POST['quantite'] ?? 1);

    if ($cartId > 0 && $newQty > 0) {
        $stmtUp = $pdo->prepare("UPDATE cart SET quantite = ? WHERE id = ? AND user_id = ?");
        $stmtUp->execute([$newQty, $cartId, $userId]);
        $_SESSION['flash_success'] = "Quantité mise à jour.";
    }
    header("Location: /backend/cart.php");
    exit;
}

// TRAITEMENT DU PAIEMENT / VALDIATION DE LA COMMANDE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $adresse = trim($_POST['adresse_facturation'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $cp = trim($_POST['cp'] ?? '');

    if (empty($adresse) || empty($ville) || empty($cp)) {
        $error = "Veuillez renseigner tous les champs de l'adresse de facturation.";
    } else {
        try {
            // Récupération des éléments du panier avec stock et prix actuels
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
                $error = "Votre panier est vide.";
            } else {
                // Calcul du montant total
                $totalAmount = 0;
                $stockIssues = [];

                foreach ($cartItems as $item) {
                    $totalAmount += $item['prix'] * $item['quantite'];
                    if ($item['quantite'] > $item['stock_qty']) {
                        $stockIssues[] = "L'article '" . $item['nom'] . "' dispose seulement de " . $item['stock_qty'] . " unités en stock (demandé: " . $item['quantite'] . ").";
                    }
                }

                if (!empty($stockIssues)) {
                    $error = implode("<br>", $stockIssues);
                } else {
                    // Vérification du solde de l'utilisateur
                    $stmtUser = $pdo->prepare("SELECT solde FROM users WHERE id = ?");
                    $stmtUser->execute([$userId]);
                    $userSolde = (float) $stmtUser->fetchColumn();

                    if ($userSolde < $totalAmount) {
                        $error = "Solde insuffisant ! Montant total de la commande : " . number_format($totalAmount, 2, ',', ' ') . " € — Votre solde actuel : " . number_format($userSolde, 2, ',', ' ') . " €.";
                    } else {
                        // DEBUT DE TRANSACTION BASE DE DONNÉES
                        $pdo->beginTransaction();

                        // 1. Déduction du solde utilisateur
                        $newSolde = $userSolde - $totalAmount;
                        $stmtUpdateSolde = $pdo->prepare("UPDATE users SET solde = ? WHERE id = ?");
                        $stmtUpdateSolde->execute([$newSolde, $userId]);
                        $_SESSION['solde'] = $newSolde;

                        // 2. Décrémentation du stock pour chaque article
                        $stmtDecStock = $pdo->prepare("UPDATE stock SET nombre = nombre - ? WHERE article_id = ?");
                        foreach ($cartItems as $item) {
                            $stmtDecStock->execute([$item['quantite'], $item['article_id']]);
                        }

                        // 3. Création de la facture
                        $stmtInvoice = $pdo->prepare("
                            INSERT INTO invoice (user_id, montant, adresse_facturation, ville, cp) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmtInvoice->execute([$userId, $totalAmount, $adresse, $ville, $cp]);
                        $invoiceId = $pdo->lastInsertId();

                        // 4. Vidage du panier
                        $stmtClearCart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                        $stmtClearCart->execute([$userId]);

                        // Confirmation de la transaction
                        $pdo->commit();

                        $_SESSION['flash_success'] = "Commande n°$invoiceId validée avec succès ! Montant payé : " . number_format($totalAmount, 2, ',', ' ') . " €. Votre nouveau solde est de " . number_format($newSolde, 2, ',', ' ') . " €.";
                        header("Location: /backend/cart.php");
                        exit;
                    }
                }
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Une erreur est survenue lors du traitement de la commande : " . htmlspecialchars($e->getMessage());
        }
    }
}

// RÉCUPÉRATION DU PANIER EN VUE DE L'AFFICHAGE
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

    // Calcul du total global
    $totalGlobal = 0;
    foreach ($items as $item) {
        $totalGlobal += $item['prix'] * $item['quantite'];
    }

    // Récupération du solde utilisateur actuel en BDD
    $stmtSolde = $pdo->prepare("SELECT solde FROM users WHERE id = ?");
    $stmtSolde->execute([$userId]);
    $currentSolde = (float) $stmtSolde->fetchColumn();
    $_SESSION['solde'] = $currentSolde;

} catch (PDOException $e) {
    die("Erreur lors du chargement du panier : " . htmlspecialchars($e->getMessage()));
}

// Inclusions de la vue HTML du panier
require_once __DIR__ . '/../../frontend/pages/cart.php';
