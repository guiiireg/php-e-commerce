<?php
/**
 * CONTRÔLEUR DE MODIFICATION D'UN UTILISATEUR (ADMIN)
 * - Permet aux administrateurs de modifier le nom d'utilisateur, l'email, le rôle et le solde d'un compte.
 */

require_once __DIR__ . '/config/config.php';

// Contrôle des droits d'administration
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Accès refusé.";
    header('Location: /backend/auth/login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /backend/admin.php');
    exit();
}

$error = null;

// Chargement des informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "Utilisateur introuvable.";
        header('Location: /backend/admin.php');
        exit();
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération : " . htmlspecialchars($e->getMessage()));
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $solde = (float)($_POST['solde'] ?? 0);

    if (empty($username) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez saisir un nom d'utilisateur et un email valide.";
    } elseif (!in_array($role, ['user', 'admin'])) {
        $error = "Rôle invalide.";
    } else {
        try {
            // Vérification que l'email n'est pas utilisé par un autre utilisateur
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmtCheck->execute([$email, $id]);
            if ($stmtCheck->fetch()) {
                $error = "Cet email est déjà attribué à un autre compte.";
            } else {
                $stmtUpdate = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, role = ?, solde = ? 
                    WHERE id = ?
                ");
                $stmtUpdate->execute([$username, $email, $role, max(0, $solde), $id]);

                // Mettre à jour la session si l'administrateur modifie son propre solde
                if ($id === $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['role'] = $role;
                    $_SESSION['solde'] = max(0, $solde);
                }

                $_SESSION['success'] = "Utilisateur #$id mis à jour avec succès !";
                header('Location: /backend/admin.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur de mise à jour : " . htmlspecialchars($e->getMessage());
        }
    }
}

require_once __DIR__ . '/../frontend/pages/edit_user.php';
