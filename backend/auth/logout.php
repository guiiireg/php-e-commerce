<?php
/**
 * TRAITEMENT DE LA DÉCONNEXION
 * - Détruit la session active de l'utilisateur.
 * - Redirige vers la page de connexion.
 */

require_once __DIR__ . '/../config/config.php';

// Réinitialisation du tableau de session
$_SESSION = array();

// Destruction du cookie de session côté client s'il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruction effective de la session côté serveur
session_destroy();

// Redirection vers la page d'accueil avec message explicatif
session_start();
$_SESSION['flash_message'] = "Vous avez été déconnecté avec succès.";
header("Location: /backend/home.php");
exit;
