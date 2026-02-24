<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
</head>

<body>
    <h1>Connexion</h1>

    <?php if (isset($error)): ?>
        <div style="padding: 10px; background-color: #f44336; color: white; margin: 10px 0;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="/backend/auth/login.php" method="POST">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Se connecter</button>
    </form>

    <p>Pas encore de compte ? <a href="/backend/auth/register.php">S'inscrire</a></p>
</body>

</html>
