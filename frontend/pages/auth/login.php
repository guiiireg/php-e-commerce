<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — PHP E-Commerce</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <!-- En-tête de navigation -->
    <?php require_once __DIR__ . '/../partials/header.php'; ?>

    <main class="main-content container flex-center">

        <div class="auth-card">
            <h1>🔐 Connexion</h1>
            <p class="auth-subtitle">Accédez à votre compte pour gérer vos achats et votre panier.</p>

            <!-- Message d'erreur de connexion -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_register'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success_register']) ?>
                </div>
                <?php unset($_SESSION['success_register']); ?>
            <?php endif; ?>

            <form action="/backend/auth/login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Adresse Email :</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        placeholder="exemple@domaine.com"
                        required 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="••••••••••••"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-large">Se connecter</button>
            </form>

            <div class="auth-footer">
                <p>Pas encore de compte ? <a href="/backend/auth/register.php">Créer un compte gratuitement</a></p>
            </div>
        </div>

    </main>

    <!-- Pied de page -->
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
