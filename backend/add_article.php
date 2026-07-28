<?php
/**
 * CONTRÔLEUR D'AJOUT D'UN NOUVEL ARTICLE (ADMIN)
 * - Accessible uniquement aux utilisateurs disposant du rôle 'admin'.
 * - Reçoit les données du nouveau produit (nom, description, prix, stock, nom d'image).
 * - Insère le produit dans `article` et son stock initial dans `stock`.
 */

require_once __DIR__ . '/config/config.php';

// Contrôle des droits d'administration
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Accès refusé.";
    header('Location: /backend/auth/login.php');
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = (float)($_POST['prix'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $image = trim($_POST['image'] ?? 'default.jpg');

    if (empty($nom) || empty($description) || $prix <= 0) {
        $error = "Veuillez saisir un nom, une description et un prix valide (> 0).";
    } else {
        try {
            $pdo->beginTransaction();

            // Insertion dans la table article
            $stmtArt = $pdo->prepare("
                INSERT INTO article (nom, description, prix, auteur_id, image) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtArt->execute([$nom, $description, $prix, $_SESSION['user_id'], empty($image) ? 'default.jpg' : $image]);
            $articleId = $pdo->lastInsertId();

            // Insertion dans la table stock
            $stmtStock = $pdo->prepare("INSERT INTO stock (article_id, nombre) VALUES (?, ?)");
            $stmtStock->execute([$articleId, max(0, $stock)]);

            $pdo->commit();

            $_SESSION['success'] = "Article '" . htmlspecialchars($nom) . "' créé avec succès !";
            header('Location: /backend/admin.php');
            exit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Erreur lors de la création de l'article : " . htmlspecialchars($e->getMessage());
        }
    }
}

require_once __DIR__ . '/../frontend/pages/add_article.php';
