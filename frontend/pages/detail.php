<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['nom']) ?> — Fiche Produit</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <!-- Inclusion de l'en-tête de navigation -->
    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="main-content container">

        <div class="breadcrumb" style="margin-bottom: 20px;">
            <a href="/backend/home.php">&larr; Retour au catalogue</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="product-detail-layout">
            <div class="product-detail-image">
                <img 
                    src="/frontend/assets/img/<?= htmlspecialchars($article['image']) ?>" 
                    alt="<?= htmlspecialchars($article['nom']) ?>"
                    onerror="this.src='/frontend/assets/img/default.jpg';"
                >
            </div>

            <div class="product-detail-info">
                <h1><?= htmlspecialchars($article['nom']) ?></h1>

                <p class="product-author">
                    Mis en ligne par <strong><?= htmlspecialchars($article['auteur_name'] ?? 'Administrateur') ?></strong> 
                    le <?= date('d/m/Y à H:i', strtotime($article['date_publication'])) ?>
                </p>

                <div class="product-price">
                    <?= number_format($article['prix'], 2, ',', ' ') ?> €
                </div>

                <div class="stock-status">
                    <?php if ($article['stock_qty'] > 0): ?>
                        <span class="stock-badge in-stock">
                            ✔ En stock (<?= (int)$article['stock_qty'] ?> unités disponibles)
                        </span>
                    <?php else: ?>
                        <span class="stock-badge out-of-stock">
                            ✖ Rupture de stock
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-description-box">
                    <h3>Description du produit</h3>
                    <p><?= nl2br(htmlspecialchars($article['description'])) ?></p>
                </div>

                <!-- Formulaire d'ajout au panier -->
                <?php if ($article['stock_qty'] > 0): ?>
                    <form method="POST" action="/backend/detail.php?id=<?= (int)$article['id'] ?>" class="add-to-cart-form">
                        <input type="hidden" name="action" value="add_to_cart">
                        
                        <div class="quantity-picker">
                            <label for="quantite">Quantité :</label>
                            <input 
                                type="number" 
                                id="quantite" 
                                name="quantite" 
                                value="1" 
                                min="1" 
                                max="<?= (int)$article['stock_qty'] ?>" 
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-success btn-large">
                            🛒 Ajouter au panier
                        </button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-disabled btn-large" disabled>Article indisponible</button>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Inclusion du pied de page -->
    <?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
