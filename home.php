<?php

require_once 'config.php';

// Get search and sort parameters from the query string
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
// Trim the search term to remove extra whitespace
$search = trim($search);

// Build the SQL query with optional search and sorting
$sql = "SELECT * FROM Article";
$params = [];

// Add a WHERE clause if a search term is provided
if (!empty($search)) {
    $sql .= " WHERE nom LIKE :search";
    $params[':search'] = '%' . $search . '%';
}

// Determine the ORDER BY clause based on the sort parameter
switch ($sort) {
    case 'prix_asc':
        $orderBy = "pris ASC";
        break;
    case 'prix_desc':
        $orderBy = "pris DESC";
        break;
    default:
        $orderBy = "date_publication DESC";
        break;
}
// Append the ORDER BY clause to the SQL query
$sql .= " ORDER BY " . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accueil - Nos Articles</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h1 style="margin-bottom: 20px">Nos Articles</h1>
        <form class="search-form" method="GET" action="home.php">
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

        <?php 
        if (count($articles) > 0):
        ?>
            <section class="articles-grid">
                <?php
                foreach ($articles as $article):
                ?>
                    <article class="article-card">
                        <img
                            src="uploads/<?= htmlspecialchars($article['image']) ?>"
                            alt="<?= htmlspecialchars($article['nom']) ?>"
                        >
                        <div class="content">
                            <h3><?= htmlspecialchars($articles['nom']) ?></h3>
                            <p class="prix">
                                <?= number_format($articles['prix'], 2, ',', ' ') ?> €
                            </p>
                            <p class="date">
                                Publié le <?= date('d/m/Y à H:i', strtotime($articles['date_publication'])) ?>
                            </p>
                            <a href="detail.php?id=<?= (int)$article['id'] ?>">
                                Voir le détail
                            </a>
                        </div>                        
                    </article>
                    <?php
                endforeach;
                ?>
            </section>
        <?php
        else:
        ?>
            <article class="no-results">
                <p>Aucun article trouvé.</p>
                <?php
                if (!empty($search)):
                ?>
                    <p style="margin-top: 10px;">
                        <a href="home.php">Voir tous les articles</a>
                    </p>
                <?php endif; ?>
            </article>
        <?php
        endif;
        ?>
    </body>
</html>