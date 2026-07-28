<?php
/**
 * GUARD D'AUTHENTIFICATION
 * Fichier utilitaire de protection : vérifie si l'utilisateur possède une session active.
 * S'il n'est pas connecté, il est automatiquement redirigé vers le formulaire de connexion.
 */

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: /backend/auth/login.php");
    exit;
}

// Redirection vers la page d'accueil si déjà connecté
header("Location: /backend/home.php");
exit;