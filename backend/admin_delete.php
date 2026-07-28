<?php
/**
 * TRAITEMENT DE LA SUPPRESSION D'UTILISATEUR OU D'ARTICLE (ADMIN)
 * - Vérifie que l'utilisateur en session possède le rôle 'admin'.
 * - Reçoit l'identifiant (id) et le type de ressource à supprimer ('user' ou 'article').
 * - Supprime l'entrée en BDD via une requête préparée.
 */

require_once __DIR__ . "/config/config.php";

// Contrôle d'accès strict
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé.");
}

// Vérification de la présence des paramètres requis
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    $_SESSION['error'] = "Paramètres de suppression manquants.";
    header('Location: /backend/admin.php');
    exit();
}

$id = (int) $_GET['id'];
$type = $_GET['type'];

if ($id <= 0) {
    $_SESSION['error'] = "Identifiant invalide.";
    header('Location: /backend/admin.php');
    exit();
}

try {
    if ($type === 'user') {
        // Empêcher la suppression du compte admin actuellement connecté
        if ($id === $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte connecté.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "L'utilisateur #$id a été supprimé avec succès.";
        }
    } elseif ($type === 'article') {
        $stmt = $pdo->prepare("DELETE FROM article WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "L'article #$id a été supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Type d'élément invalide.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur SQL lors de la suppression : " . htmlspecialchars($e->getMessage());
}

header('Location: /backend/admin.php');
exit();