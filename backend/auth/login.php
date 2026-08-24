<?php
/**
 * User Authentication & Login Controller
 *
 * Validates user credentials using BCrypt password verification,
 * regenerates session identifiers to prevent session fixation,
 * and initializes authorized session state.
 */

require_once __DIR__ . '/../config/config.php';

// Bypass login page if already authenticated to avoid redundant logins
if (isset($_SESSION["user_id"])) {
    header("Location: /backend/home.php");
    exit;
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!verify_csrf_token()) {
        $error = "Form session expired. Please refresh the page and try again.";
    } else {
        $email = trim($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify password using native constant-time BCrypt verification
            if ($user && password_verify($password, $user["password"])) {
                // Session Fixation Countermeasure: Invalidate the previous unauthenticated session ID
                // and issue a fresh cryptographically strong ID upon privilege elevation
                session_regenerate_id(true);

                // Populate authorized session variables
                $_SESSION["user_id"] = (int) $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["solde"] = (float) $user["solde"];

                header("Location: /backend/home.php");
                exit;
            } else {
                // Return a generic error message to prevent account enumeration attacks
                // (i.e. attackers should not be able to guess which emails are registered based on differing error messages)
                $error = "Invalid credentials (email or password incorrect).";
            }
        }
    }
}

require_once __DIR__ . '/../../frontend/pages/auth/login.php';
