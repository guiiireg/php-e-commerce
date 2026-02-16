
-- Modification quantité panier
UPDATE cart 
SET quantite = 5 WHERE id = 1;

-- Suppression test
DELETE FROM cart WHERE id = 10;

-- jointure pour afficher les articles du panier avec leur prix et le sous-total
SELECT article.nom, article.prix, cart.quantite, (article.prix * cart.quantite) AS sous_total 
FROM cart JOIN article ON cart.article_id = article.id;


-- Affichage du total global du panier
SELECT 
    SUM(article.prix * cart.quantite) AS total_global
FROM cart
JOIN article
    ON cart.article_id = article.id;
