<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($username)) {
        $error = "Le nom d'utilisateur est obligatoire";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide";
    } elseif (strlen($password) < 12) {
        $error = "Le mot de passe doit contenir au moins 12 caractères";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM User WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email déjà utilisé";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO User (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed]);

            header("Location: /backend/auth/login.php");
            exit;
        }
    }
}

require_once __DIR__ . '/../../frontend/pages/auth/register.php';http://localhost:8080/backend/admin.php