<?php
/**
 * Administrator Deletion Processing Endpoint
 *
 * Handles protected POST-based removal of user accounts and catalog articles.
 */

require_once __DIR__ . "/config/config.php";

// Strict Role Guard: Non-admins cannot invoke deletion operations
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// Destructive state-changing operations require HTTP POST and a valid CSRF token.
// GET requests are unsafe because browsers pre-fetch links and external sites can embed them in <img> tags.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token()) {
    $_SESSION['error'] = "Unauthorized request or expired CSRF security token.";
    header('Location: /backend/admin.php');
    exit();
}

$id = (int)($_POST['id'] ?? 0);
$type = $_POST['type'] ?? '';

if ($id <= 0) {
    $_SESSION['error'] = "Invalid identifier.";
    header('Location: /backend/admin.php');
    exit();
}

try {
    if ($type === 'user') {
        // Self-lockout protection: Prevent the logged-in administrator from accidentally deleting their own active account
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own active administrator account.";
        } else {
            // Foreign key CASCADE constraints in database.sql automatically purge associated carts and orders
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "User #$id has been successfully deleted.";
        }
    } elseif ($type === 'article') {
        // Foreign key CASCADE constraints clean up corresponding inventory stock rows
        $stmt = $pdo->prepare("DELETE FROM article WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Article #$id has been successfully deleted.";
    } else {
        $_SESSION['error'] = "Invalid entity type specified.";
    }
} catch (PDOException $e) {
    error_log("Admin deletion error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while deleting the requested record.";
}

header('Location: /backend/admin.php');
exit();