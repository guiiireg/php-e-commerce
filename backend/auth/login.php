<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["role"] = $user["role"];

        header("Location: /backend/auth/index.php");
        exit;

    } else {
        $error = "Identifiants incorrects";
    }
}

require_once __DIR__ . '/../../frontend/pages/auth/login.php';

