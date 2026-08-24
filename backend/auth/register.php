<?php
/**
 * User Registration Controller
 *
 * Validates new member credentials, enforces robust password policies,
 * generates cryptographic password hashes, and seeds initial virtual balances.
 */

require_once __DIR__ . '/../config/config.php';

// Bypass registration if already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: /backend/home.php");
    exit;
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token()) {
        $error = "Form session expired. Please refresh the page and try again.";
    } else {
        $username = trim($_POST["username"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';
        $confirm_password = $_POST["confirm_password"] ?? '';

        // Enforce a minimum 12-character password policy to resist brute-force and dictionary attacks
        if (empty($username)) {
            $error = "Username is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "The email address entered is invalid.";
        } elseif (strlen($password) < 12) {
            $error = "Password must contain at least 12 characters.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check uniqueness across both username and email to prevent account collisions
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);

            if ($stmt->fetch()) {
                $error = "This email or username is already registered.";
            } else {
                // PASSWORD_DEFAULT ensures automatic salting and keeps up with modern PHP hashing standards (BCrypt / Argon2id)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $defaultSolde = 100.00; // Seed demo accounts with initial starting funds

                // Hardcode role to 'user' to prevent privilege escalation during public self-registration
                $insertStmt = $pdo->prepare(
                    "INSERT INTO users (username, email, password, solde, role) VALUES (?, ?, ?, ?, 'user')"
                );
                $insertStmt->execute([$username, $email, $hashedPassword, $defaultSolde]);

                $_SESSION['success_register'] = "Registration successful! You can now log in.";
                header("Location: /backend/auth/login.php");
                exit;
            }
        }
    }
}

require_once __DIR__ . '/../../frontend/pages/auth/register.php';