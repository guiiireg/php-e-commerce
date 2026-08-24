<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier d'Achat — PHP E-Commerce</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <!-- Inclusion de l'en-tête de navigation -->
    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="main-content container">

        <h1>🛒 Votre Panier d'Achat</h1>

        <!-- Messages d'alerte et notifications -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= $error // Peut contenir du HTML contrôlé ?>
            </div>
        <?php endif; ?>

        <?php if (count($items) > 0): ?>
            <div class="cart-layout">
                <div class="cart-table-wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Quantité</th>
                                <th>Sous-total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="product-cell">
                                        <img 
                                            src="/frontend/assets/img/<?= htmlspecialchars($item['image']) ?>" 
                                            alt="<?= htmlspecialchars($item['nom']) ?>"
                                            class="cart-thumb"
                                            onerror="this.src='/frontend/assets/img/default.jpg';"
                                        >
                                        <div>
                                            <strong><a href="/backend/detail.php?id=<?= $item['article_id'] ?>"><?= htmlspecialchars($item['nom']) ?></a></strong>
                                            <?php if ($item['quantite'] > $item['stock_qty']): ?>
                                                <div class="text-danger-sm">⚠️ Stock insuffisant (Max: <?= $item['stock_qty'] ?>)</div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?= number_format($item['prix'], 2, ',', ' ') ?> €</td>
                                    <td>
                                        <form method="POST" action="/backend/cart.php" class="inline-qty-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="update_qty">
                                            <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                            <input 
                                                type="number" 
                                                name="quantite" 
                                                value="<?= $item['quantite'] ?>" 
                                                min="1" 
                                                max="<?= $item['stock_qty'] ?>" 
                                                onchange="this.form.submit()" 
                                                class="qty-input"
                                            >
                                        </form>
                                    </td>
                                    <td class="font-bold">
                                        <?= number_format($item['prix'] * $item['quantite'], 2, ',', ' ') ?> €
                                    </td>
                                    <td>
                                        <form method="POST" action="/backend/cart.php" style="display:inline-block;" onsubmit="return confirm('Retirer cet article du panier ?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="cart_id" value="<?= (int)$item['cart_id'] ?>">
                                            <button type="submit" class="btn btn-delete btn-sm">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="cart-summary-box">
                    <h3>Récapitulatif de la commande</h3>

                    <div class="summary-line">
                        <span>Solde disponible :</span>
                        <strong class="text-success"><?= number_format($currentSolde, 2, ',', ' ') ?> €</strong>
                    </div>

                    <div class="summary-line total-line">
                        <span>Total de la commande :</span>
                        <strong class="total-price"><?= number_format($totalGlobal, 2, ',', ' ') ?> €</strong>
                    </div>

                    <?php if ($currentSolde < $totalGlobal): ?>
                        <div class="alert alert-warning-sm">
                            ⚠️ Votre solde est insuffisant pour valider cette commande.
                        </div>
                    <?php endif; ?>

                    <hr>

                    <h4>Adresse de livraison / Facturation</h4>
                    <form method="POST" action="/backend/cart.php" class="checkout-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="checkout">

                        <div class="form-group">
                            <label for="adresse_facturation">Adresse :</label>
                            <input type="text" id="adresse_facturation" name="adresse_facturation" placeholder="ex: 12 Rue des Fleurs" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="ville">Ville :</label>
                                <input type="text" id="ville" name="ville" placeholder="ex: Paris" required>
                            </div>

                            <div class="form-group">
                                <label for="cp">Code Postal :</label>
                                <input type="text" id="cp" name="cp" placeholder="ex: 75001" required>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            class="btn btn-success btn-block btn-large"
                            <?= $currentSolde < $totalGlobal ? 'disabled title="Solde insuffisant"' : '' ?>
                        >
                            💳 Valider et Payer la commande
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>Votre panier est actuellement vide.</p>
                <a href="/backend/home.php" class="btn btn-primary" style="margin-top: 15px;">Parcourir le catalogue</a>
            </div>
        <?php endif; ?>

    </main>

    <!-- Inclusion du pied de page -->
    <?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
