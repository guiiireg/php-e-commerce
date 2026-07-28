<?php
/**
 * TRAITEMENT DE L'INSCRIPTION UTILISATEUR
 * - Validation des données saisies (format email, longueur du mot de passe, confirmation).
 * - Vérification de l'unicité de l'email et du nom d'utilisateur.
 * - Hashage sécurisé du mot de passe avec l'algorithme par défaut (BCrypt/Argon2).
 * - Enregistrement dans la table `users`.
 */

require_once __DIR__ . '/../config/config.php';

// Redirection si déjà connecté
if (isset($_SESSION["user_id"])) {
    header("Location: /backend/home.php");
    exit;
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';

    // Validation des critères du formulaire
    if (empty($username)) {
        $error = "Le nom d'utilisateur est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email saisie n'est pas valide.";
    } elseif (strlen($password) < 12) {
        $error = "Le mot de passe doit contenir au moins 12 caractères.";
    } elseif ($password !== $confirm_password) {
        $error = "Les deux mots de passe ne correspondent pas.";
    } else {
        // Vérification si l'email ou le username existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);

        if ($stmt->fetch()) {
            $error = "Cet email ou nom d'utilisateur est déjà utilisé.";
        } else {
            // Hashage sécurisé du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $defaultSolde = 100.00; // Solde de bienvenue crédité lors de la création

            // Insertion du nouvel utilisateur en base de données
            $insertStmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, solde, role) VALUES (?, ?, ?, ?, 'user')"
            );
            $insertStmt->execute([$username, $email, $hashedPassword, $defaultSolde]);

            $_SESSION['success_register'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            header("Location: /backend/auth/login.php");
            exit;
        }
    }
}

// Inclusions de la vue HTML d'inscription
require_once __DIR__ . '/../../frontend/pages/auth/register.php';