<?php
/**
 * User Logout & Session Teardown Controller
 *
 * Fully invalidates both server-side session state and client-side cookie identifiers.
 */

require_once __DIR__ . '/../config/config.php';

// 1. Wipe in-memory session variables
$_SESSION = array();

// 2. Invalidate client-side session cookie by setting an expired timestamp
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

// 3. Destroy underlying server-side session storage
session_destroy();

// 4. Start a clean ephemeral session to pass a confirmation flash message to the homepage
session_start();
$_SESSION['flash_message'] = "You have been logged out successfully.";
header("Location: /backend/home.php");
exit;
