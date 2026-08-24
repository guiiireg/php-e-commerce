<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — PHP E-Commerce</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <!-- En-tête de navigation -->
    <?php require_once __DIR__ . '/../partials/header.php'; ?>

    <main class="main-content container flex-center">

        <div class="auth-card">
            <h1>📝 Créer un Compte</h1>
            <p class="auth-subtitle">Inscrivez-vous pour profiter d'un solde de bienvenue et effectuer vos achats.</p>

            <!-- Message d'erreur d'inscription -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/backend/auth/register.php" class="auth-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="username">Nom d'utilisateur :</label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        placeholder="ex: JeanDupont"
                        required 
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Adresse Email :</label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        placeholder="exemple@domaine.com"
                        required 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe (12 caractères min.) :</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="••••••••••••"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe :</label>
                    <input 
                        type="password" 
                        id="confirm_password"
                        name="confirm_password" 
                        placeholder="••••••••••••"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-success btn-block btn-large">S'inscrire</button>
            </form>

            <div class="auth-footer">
                <p>Déjà un compte ? <a href="/backend/auth/login.php">Se connecter</a></p>
            </div>
        </div>

    </main>

    <!-- Pied de page -->
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
