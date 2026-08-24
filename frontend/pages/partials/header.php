<?php
/**
 * Global Navigation Header Component
 *
 * Renders the top navigation bar, active user authentication badge,
 * live balance counter, and shopping cart item quantity.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Compute aggregate cart quantity dynamically for the navigation badge
$cartCount = 0;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $stmtCartCount = $pdo->prepare("SELECT SUM(quantite) AS total FROM cart WHERE user_id = ?");
        $stmtCartCount->execute([$_SESSION['user_id']]);
        $cartCount = (int) ($stmtCartCount->fetchColumn() ?? 0);
    } catch (Exception $e) {
        // Fallback to 0 badge count if database is temporarily unavailable during header rendering
        $cartCount = 0;
    }
}
?>
<div class="demo-notice-banner" style="background:#1e293b; color:#cbd5e1; text-align:center; padding:6px 12px; font-size:0.85rem; border-bottom:1px solid #334155;">
    ℹ️ <strong>Projet étudiant / Démonstration</strong> — Aucun paiement réel ni livraison physique.
</div>
<header class="main-header">
    <div class="header-container">
        <a href="/backend/home.php" class="brand-logo">
            🛍️ <span>PHP E-Commerce</span>
        </a>

        <nav class="main-nav">
            <a href="/backend/home.php" class="nav-link">Accueil</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/backend/cart.php" class="nav-link cart-link">
                    🛒 Panier 
                    <?php if ($cartCount > 0): ?>
                        <span class="badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/backend/admin.php" class="nav-link admin-link">⚙️ Admin</a>
                <?php endif; ?>

                <div class="user-info">
                    <span class="username">👤 <?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur') ?></span>
                    <?php if (isset($_SESSION['solde'])): ?>
                        <span class="user-solde">💳 <?= number_format((float)$_SESSION['solde'], 2, ',', ' ') ?> €</span>
                    <?php endif; ?>
                    <a href="/backend/auth/logout.php" class="btn-logout">Déconnexion</a>
                </div>
            <?php else: ?>
                <div class="auth-links">
                    <a href="/backend/auth/login.php" class="btn-login">Connexion</a>
                    <a href="/backend/auth/register.php" class="btn-register">Inscription</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
