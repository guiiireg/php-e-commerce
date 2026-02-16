<?php

require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    $stmtUsers = $pdo->query("SELECT * FROM User ORDER BY id DESC");
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    $stmtArticles = $pdo->query("SELECT * FROM Article ORDER BY id DESC");
    $articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Modération</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .btn {
            padding: 5px 10px;
            text-decoration: none;
            color: white;
            border-radius: 4px;
        }

        .btn-edit {
            background-color: #4CAF50;
        }

        .btn-delete {
            background-color: #f44336;
        }
    </style>
</head>

<body>
    <h1>Tableau de bord Admin</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>.</p>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="padding: 10px; background-color: #4CAF50; color: white; margin: 10px 0;">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="padding: 10px; background-color: #f44336; color: white; margin: 10px 0;">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <h2>Gestion des Membres</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-edit">Modifier</a>
                        <a href="admin_delete.php?type=user&id=<?= $user['id'] ?>" class="btn btn-delete"
                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                            Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Gestion des Articles</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Prix</th>
                <th>Auteur</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <td><?= htmlspecialchars($article['id']) ?></td>
                    <td><?= htmlspecialchars($article['nom'] ?? 'Sans nom') ?></td>
                    <td><?= htmlspecialchars($article['prix']) ?> €</td>
                    <td><?= htmlspecialchars($article['auteur_id']) ?></td>
                    <td>
                        <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-edit">Modifier</a>

                        <a href="admin_delete.php?type=article&id=<?= $article['id'] ?>" class="btn btn-delete"
                            onclick="return confirm('Supprimer cet article définitivement ?');">
                            Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>