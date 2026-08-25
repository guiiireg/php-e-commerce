-- ==============================================================================
-- USEFUL SQL QUERIES & DATA MANIPULATION EXAMPLES
-- ==============================================================================

-- 1. Update quantity of an article inside a user's shopping cart
UPDATE cart 
SET quantite = 5 
WHERE id = 1;

-- 2. Remove a specific cart entry
DELETE FROM cart 
WHERE id = 10;

-- 3. Join cart with article table to display unit prices and item subtotals
SELECT 
    article.nom, 
    article.prix, 
    cart.quantite, 
    (article.prix * cart.quantite) AS sous_total 
FROM cart 
JOIN article ON cart.article_id = article.id
WHERE cart.user_id = 1;

-- 4. Calculate total cart order cost for a given user
SELECT 
    SUM(article.prix * cart.quantite) AS total_global
FROM cart
JOIN article ON cart.article_id = article.id
WHERE cart.user_id = 1;

-- 5. Query to verify stock availability before checkout completion
SELECT 
    article.id, 
    article.nom, 
    cart.quantite AS quantite_demandee, 
    stock.nombre AS stock_disponible
FROM cart
JOIN article ON cart.article_id = article.id
LEFT JOIN stock ON stock.article_id = article.id
WHERE cart.user_id = 1;

