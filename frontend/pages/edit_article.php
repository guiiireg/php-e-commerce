<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Article #<?= $article['id'] ?> — Admin</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="main-content container">

        <div class="breadcrumb" style="margin-bottom: 20px;">
            <a href="/backend/admin.php">&larr; Retour au tableau de bord Admin</a>
        </div>

        <h1>✏️ Modifier l'Article #<?= $article['id'] ?></h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-container">
            <form method="POST" action="/backend/edit_article.php?id=<?= $article['id'] ?>" class="admin-form">
                
                <div class="form-group">
                    <label for="nom">Nom de l'article :</label>
                    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($article['nom']) ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($article['description']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prix">Prix (€) :</label>
                        <input type="number" step="0.01" id="prix" name="prix" required value="<?= htmlspecialchars($article['prix']) ?>">
                    </div>

                    <div class="form-group">
                        <label for="stock">Quantité en stock :</label>
                        <input type="number" id="stock" name="stock" min="0" required value="<?= (int)$article['stock_qty'] ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Nom du fichier image :</label>
                    <input type="text" id="image" name="image" value="<?= htmlspecialchars($article['image']) ?>">
                </div>

                <button type="submit" class="btn btn-success btn-large">Enregistrer les modifications</button>
            </form>
        </div>

    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
