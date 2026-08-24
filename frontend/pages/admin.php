<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration — Tableau de bord</title>
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
    <link rel="stylesheet" href="/frontend/assets/css/home.css">
</head>
<body>

    <!-- Inclusion de l'en-tête de navigation -->
    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <main class="main-content container">

        <div class="admin-header">
            <h1>⚙️ Tableau de bord Administrateur</h1>
            <p>Bienvenue, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong>. Vous avez accès à la gestion globale de la plateforme.</p>
        </div>

        <!-- Messages de confirmation ou d'erreur -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- SECTION 1 : GESTION DES MEMBRES -->
        <section class="admin-section">
            <h2>👥 Gestion des Utilisateurs (<?= count($users) ?>)</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Solde</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($user['id']) ?></td>
                                <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= number_format($user['solde'], 2, ',', ' ') ?> €</td>
                                <td>
                                    <span class="role-badge <?= $user['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                        <?= htmlspecialchars($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/backend/edit_user.php?id=<?= $user['id'] ?>" class="btn btn-edit btn-sm">Modifier</a>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <form method="POST" action="/backend/admin_delete.php" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="type" value="user">
                                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                                            <button type="submit" class="btn btn-delete btn-sm">Supprimer</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted-sm">(Compte actif)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION 2 : GESTION DES ARTICLES ET STOCKS -->
        <section class="admin-section">
            <div class="section-title-action">
                <h2>📦 Gestion du Catalogue &amp; Stocks (<?= count($articles) ?>)</h2>
                <a href="/backend/add_article.php" class="btn btn-success">➕ Créer un nouvel article</a>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Titre</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Auteur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($article['id']) ?></td>
                                <td>
                                    <img 
                                        src="/frontend/assets/img/<?= htmlspecialchars($article['image']) ?>" 
                                        alt="<?= htmlspecialchars($article['nom']) ?>"
                                        class="table-thumb"
                                        onerror="this.src='/frontend/assets/img/default.jpg';"
                                    >
                                </td>
                                <td><strong><?= htmlspecialchars($article['nom'] ?? 'Sans nom') ?></strong></td>
                                <td><?= number_format($article['prix'], 2, ',', ' ') ?> €</td>
                                <td>
                                    <?php if ($article['stock_qty'] > 0): ?>
                                        <span class="stock-badge in-stock"><?= (int)$article['stock_qty'] ?> unités</span>
                                    <?php else: ?>
                                        <span class="stock-badge out-of-stock">Rupture</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($article['auteur_name'] ?? 'Inconnu') ?></td>
                                <td>
                                    <a href="/backend/edit_article.php?id=<?= $article['id'] ?>" class="btn btn-edit btn-sm">Modifier</a>
                                    <form method="POST" action="/backend/admin_delete.php" style="display:inline-block;" onsubmit="return confirm('Supprimer cet article définitivement ?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="type" value="article">
                                        <input type="hidden" name="id" value="<?= (int)$article['id'] ?>">
                                        <button type="submit" class="btn btn-delete btn-sm">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECTION 3 : HISTORIQUE DES COMMANDES / FACTURES -->
        <section class="admin-section">
            <h2>📜 Historique des Factures (<?= count($invoices) ?>)</h2>
            <?php if (count($invoices) > 0): ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Facture #</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Adresse de facturation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><strong>#<?= $inv['id'] ?></strong></td>
                                    <td><?= htmlspecialchars($inv['client_name']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($inv['date'])) ?></td>
                                    <td class="font-bold text-success"><?= number_format($inv['montant'], 2, ',', ' ') ?> €</td>
                                    <td>
                                        <?= htmlspecialchars($inv['adresse_facturation']) ?>, 
                                        <?= htmlspecialchars($inv['cp']) ?> <?= htmlspecialchars($inv['ville']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Aucune commande n'a été passée pour le moment.</p>
            <?php endif; ?>
        </section>

    </main>

    <!-- Inclusion du pied de page -->
    <?php require_once __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
