<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Article — Administration</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="main-content container">

        <div class="breadcrumb" style="margin-bottom: 20px;">
            <a href="/backend/admin.php">&larr; Retour au tableau de bord Admin</a>
        </div>

        <h1>➕ Ajouter un Nouvel Article</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-container">
            <form method="POST" action="/backend/add_article.php" class="admin-form">
                
                <div class="form-group">
                    <label for="nom">Nom de l'article :</label>
                    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prix">Prix (€) :</label>
                        <input type="number" step="0.01" id="prix" name="prix" required value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="stock">Quantité en stock :</label>
                        <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '10') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Nom de l'image (dans assets/img/) :</label>
                    <input type="text" id="image" name="image" placeholder="ex: default.jpg" value="<?= htmlspecialchars($_POST['image'] ?? 'default.jpg') ?>">
                </div>

                <button type="submit" class="btn btn-success btn-large">Publier l'article</button>
            </form>
        </div>

    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
