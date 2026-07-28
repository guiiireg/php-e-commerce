<?php
/**
 * TRAITEMENT DE LA CONNEXION UTILISATEUR
 * - Vérifie la méthode HTTP POST.
 * - Récupère et valide les identifiants en base de données.
 * - Initialise la session utilisateur en cas de succès.
 */

require_once __DIR__ . '/../config/config.php';

// Si l'utilisateur est déjà connecté, redirection directe vers la page d'accueil
if (isset($_SESSION["user_id"])) {
    header("Location: /backend/home.php");
    exit;
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Nettoyage et récupération des saisies utilisateur
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Recherche de l'utilisateur par son adresse email dans la table `users`
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérification de l'existence du compte et du mot de passe hashé
        if ($user && password_verify($password, $user["password"])) {
            // Démarrage et initialisation de la session utilisateur
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["solde"] = (float) $user["solde"];

            // Redirection vers la page d'accueil
            header("Location: /backend/home.php");
            exit;
        } else {
            $error = "Identifiants incorrects (email ou mot de passe invalide).";
        }
    }
}

// Inclusions de la vue HTML de connexion
require_once __DIR__ . '/../../frontend/pages/auth/login.php';
