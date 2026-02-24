<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accueil - Nos Articles</title>
        <link rel="stylesheet" href="/frontend/assets/css/home.css">
    </head>
    <body>
        <h1 style="margin-bottom: 20px">Nos Articles</h1>
        <form class="search-form" method="GET" action="/backend/home.php">
            <input
                type="text"
                name="search"
                placeholder="Rechercher un article..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <select name="sort">
                <option value="" <?= $sort === '' ? 'selected' : '' ?>>
                    Plus récents
                </option>

                <option value="prix_asc" <?= $sort === 'prix_asc' ? 'selected' : '' ?>>
                    Prix croissant
                </option>

                <option value="prix_desc" <?= $sort === 'prix_desc' ? 'selected' : '' ?>>
                    Prix décroissant
                </option>
            </select>
            <button type="submit">Rechercher</button>
        </form>

        <?php if (count($articles) > 0): ?>
            <section class="articles-grid">
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <img
                            src="uploads/<?= htmlspecialchars($article['image']) ?>"
                            alt="<?= htmlspecialchars($article['nom']) ?>"
                        >
                        <div class="content">
                            <h3><?= htmlspecialchars($article['nom']) ?></h3>
                            <p class="prix">
                                <?= number_format($article['prix'], 2, ',', ' ') ?> €
                            </p>
                            <p class="date">
                                Publié le <?= date('d/m/Y à H:i', strtotime($article['date_publication'])) ?>
                            </p>
                            <a href="detail.php?id=<?= (int)$article['id'] ?>">
                                Voir le détail
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <article class="no-results">
                <p>Aucun article trouvé.</p>
                <?php if (!empty($search)): ?>
                    <p style="margin-top: 10px;">
                        <a href="/backend/home.php">Voir tous les articles</a>
                    </p>
                <?php endif; ?>
            </article>
        <?php endif; ?>
    </body>
</html>
