<?php

require_once __DIR__ . "/config/config.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    $stmtUsers = $pdo->query("SELECT * FROM User ORDER BY id DESC");
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    $stmtArticles = $pdo->query("SELECT * FROM Article ORDER BY id DESC");
    $articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}

require_once __DIR__ . '/../frontend/pages/admin.php';