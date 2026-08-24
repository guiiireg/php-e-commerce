<?php
/**
 * Administrator Product Creation Controller
 *
 * Validates new article inputs, sanitizes asset references,
 * and initializes inventory records atomically.
 */

require_once __DIR__ . '/config/config.php';

// Role Guard: Restrict product creation privileges strictly to administrators
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_error'] = "Access denied.";
    header('Location: /backend/auth/login.php');
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $error = "Form session expired. Please refresh the page and try again.";
    } else {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = (float)($_POST['prix'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $rawImage = trim($_POST['image'] ?? 'default.jpg');

        // Path Traversal Mitigation: Strip relative directory segments (e.g., ../../) to ensure
        // images resolve only within the intended /frontend/assets/img/ directory.
        $image = basename($rawImage);
        if (empty($image)) {
            $image = 'default.jpg';
        }

        // Business rule: Enforce positive prices to prevent free or negative billing bugs
        if (empty($nom) || empty($description) || $prix <= 0) {
            $error = "Please provide a valid title, description, and a price greater than 0.";
        } else {
            try {
                // Atomic transaction ensures an article record is never orphaned without its stock entry
                $pdo->beginTransaction();

                // Persist article metadata linked to the creator's user_id
                $stmtArt = $pdo->prepare("
                    INSERT INTO article (nom, description, prix, auteur_id, image) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmtArt->execute([$nom, $description, $prix, (int)$_SESSION['user_id'], $image]);
                $articleId = $pdo->lastInsertId();

                // Initialize stock counter with zero-floor clamp
                $stmtStock = $pdo->prepare("INSERT INTO stock (article_id, nombre) VALUES (?, ?)");
                $stmtStock->execute([$articleId, max(0, $stock)]);

                $pdo->commit();

                $_SESSION['success'] = "Article '" . htmlspecialchars($nom) . "' created successfully!";
                header('Location: /backend/admin.php');
                exit();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Failed to create article: " . $e->getMessage());
                $error = "An error occurred while creating the article.";
            }
        }
    }
}

require_once __DIR__ . '/../frontend/pages/add_article.php';
