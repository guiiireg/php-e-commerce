<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil — Catalogue Produit PHP E-Commerce</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <!-- Inclusion de l'en-tête de navigation -->
    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="main-content container">
        
        <!-- Affichage des messages flash (ex: déconnexion) -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="catalog-header">
            <h1>Notre Catalogue Produit</h1>
            <p class="subtitle">Découvrez nos derniers articles disponibles à la vente.</p>
        </div>

        <!-- Formulaire de recherche et de tri -->
        <form class="search-form" method="GET" action="/backend/home.php">
            <div class="form-group search-input-group">
                <input
                    type="text"
                    name="search"
                    placeholder="Rechercher un article..."
                    value="<?= htmlspecialchars($search) ?>"
                >
            </div>

            <div class="form-group">
                <select name="sort">
                    <option value="" <?= $sort === '' ? 'selected' : '' ?>>Plus récents</option>
                    <option value="prix_asc" <?= $sort === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="prix_desc" <?= $sort === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Rechercher</button>

            <?php if (!empty($search) || !empty($sort)): ?>
                <a href="/backend/home.php" class="btn btn-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <!-- Grille d'affichage des articles -->
        <?php if (count($articles) > 0): ?>
            <section class="articles-grid">
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <div class="card-image-wrapper">
                            <img
                                src="/frontend/assets/img/<?= htmlspecialchars($article['image']) ?>"
                                alt="<?= htmlspecialchars($article['nom']) ?>"
                                onerror="this.src='/frontend/assets/img/default.jpg';"
                            >
                            <?php if ($article['stock_qty'] > 0): ?>
                                <span class="stock-badge in-stock">En stock (<?= (int)$article['stock_qty'] ?>)</span>
                            <?php else: ?>
                                <span class="stock-badge out-of-stock">Rupture de stock</span>
                            <?php endif; ?>
                        </div>

                        <div class="content">
                            <h3><?= htmlspecialchars($article['nom']) ?></h3>
                            <p class="description">
                                <?= htmlspecialchars(mb_strimwidth($article['description'], 0, 100, "...")) ?>
                            </p>
                            <div class="card-footer-info">
                                <p class="prix"><?= number_format($article['prix'], 2, ',', ' ') ?> €</p>
                                <a href="/backend/detail.php?id=<?= (int)$article['id'] ?>" class="btn btn-detail">
                                    Voir le produit
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <div class="no-results">
                <p>Aucun article ne correspond à votre recherche.</p>
                <?php if (!empty($search)): ?>
                    <a href="/backend/home.php" class="btn btn-primary" style="margin-top: 15px;">Voir tous les articles</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- Inclusion du pied de page -->
    <?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
