<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
</head>

<body>
    <h1>Inscription</h1>

    <?php if (isset($error)): ?>
        <div style="padding: 10px; background-color: #f44336; color: white; margin: 10px 0;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/backend/auth/register.php">
        <label>Nom d'utilisateur</label>
        <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label>Mot de passe</label>
        <input type="password" name="password" required>

        <label>Confirmer le mot de passe</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="/backend/auth/login.php">Se connecter</a></p>
</body>

</html>
