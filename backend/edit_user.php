<?php
/**
 * Administrator User Modification Controller
 *
 * Allows administrators to adjust member roles, usernames, contact emails,
 * and virtual bank balances.
 */

require_once __DIR__ . '/config/config.php';

// Role Guard: Ensure non-admin users cannot alter account permissions or balances
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Access denied.";
    header('Location: /backend/auth/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /backend/admin.php');
    exit();
}

$error = null;

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        header('Location: /backend/admin.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Failed to load user #$id for editing: " . $e->getMessage());
    die("An error occurred while fetching user information.");
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = "Form session expired. Please refresh the page and try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $solde = (float)($_POST['solde'] ?? 0);

        if (empty($username) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please provide a valid username and email address.";
        } elseif (!in_array($role, ['user', 'admin'])) {
            // Whitelist permitted roles to avoid unintended permission assignment
            $error = "Invalid role selected.";
        } else {
            try {
                // Check email uniqueness excluding the current user ID to allow preserving the existing email
                $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmtCheck->execute([$email, $id]);
                if ($stmtCheck->fetch()) {
                    $error = "This email address is already assigned to another account.";
                } else {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE users 
                        SET username = ?, email = ?, role = ?, solde = ? 
                        WHERE id = ?
                    ");
                    $stmtUpdate->execute([$username, $email, $role, max(0, $solde), $id]);

                    // If the administrator is modifying their own account, synchronize active session
                    // variables immediately to avoid UI state discrepancies (e.g. balance badge in header)
                    if ($id === (int)$_SESSION['user_id']) {
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = $role;
                        $_SESSION['solde'] = max(0, $solde);
                    }

                    $_SESSION['success'] = "User #$id updated successfully!";
                    header('Location: /backend/admin.php');
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Failed to update user #$id: " . $e->getMessage());
                $error = "An error occurred while updating the user profile.";
            }
        }
    }
}

require_once __DIR__ . '/../frontend/pages/edit_user.php';
