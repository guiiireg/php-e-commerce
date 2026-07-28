-- ==============================================================================
-- REQUÊTES SQL UTILES & EXEMPLES DE MANIPULATION
-- ==============================================================================

-- 1. Modification de la quantité d'un article dans le panier d'un utilisateur
UPDATE cart 
SET quantite = 5 
WHERE id = 1;

-- 2. Suppression d'une entrée spécifique du panier
DELETE FROM cart 
WHERE id = 10;

-- 3. Jointure pour afficher les articles du panier avec leur prix unitaire et le sous-total par article
SELECT 
    article.nom, 
    article.prix, 
    cart.quantite, 
    (article.prix * cart.quantite) AS sous_total 
FROM cart 
JOIN article ON cart.article_id = article.id
WHERE cart.user_id = 1;

-- 4. Calcul du montant total global du panier pour un utilisateur donné
SELECT 
    SUM(article.prix * cart.quantite) AS total_global
FROM cart
JOIN article ON cart.article_id = article.id
WHERE cart.user_id = 1;

-- 5. Requête de vérification du stock disponible avant validation de commande
SELECT 
    article.id, 
    article.nom, 
    cart.quantite AS quantite_demandee, 
    stock.nombre AS stock_disponible
FROM cart
JOIN article ON cart.article_id = article.id
LEFT JOIN stock ON stock.article_id = article.id
WHERE cart.user_id = 1;
