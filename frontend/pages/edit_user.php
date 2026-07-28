<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Utilisateur #<?= $user['id'] ?> — Admin</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="main-content container">

        <div class="breadcrumb" style="margin-bottom: 20px;">
            <a href="/backend/admin.php">&larr; Retour au tableau de bord Admin</a>
        </div>

        <h1>👤 Modifier l'Utilisateur #<?= $user['id'] ?></h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-container">
            <form method="POST" action="/backend/edit_user.php?id=<?= $user['id'] ?>" class="admin-form">
                
                <div class="form-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input type="text" id="username" name="username" required value="<?= htmlspecialchars($user['username']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Adresse Email :</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role">Rôle :</label>
                        <select id="role" name="role" required>
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur (user)</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur (admin)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="solde">Solde du compte (€) :</label>
                        <input type="number" step="0.01" id="solde" name="solde" required value="<?= htmlspecialchars($user['solde']) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-large">Enregistrer les modifications</button>
            </form>
        </div>

    </main>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
