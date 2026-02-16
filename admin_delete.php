<?php

require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé.");
}

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    $_SESSION['error'] = "Paramètres manquants.";
    header('Location: admin.php');
    exit();
}

$id = (int) $_GET['id'];
$type = $_GET['type'];

if ($id <= 0) {
    $_SESSION['error'] = "ID invalide.";
    header('Location: admin.php');
    exit();
}

try {
    if ($type === 'user') {
        $stmt = $pdo->prepare("DELETE FROM User WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Utilisateur supprimé avec succès.";
    } elseif ($type === 'article') {
        $stmt = $pdo->prepare("DELETE FROM Article WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Article supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Type invalide.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header('Location: admin.php');
exit();