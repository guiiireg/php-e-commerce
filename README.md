# 🛍️ PHP E-Commerce — Application E-Commerce Native PHP & MySQL

Bienvenue sur le projet **PHP E-Commerce**. Il s'agit d'une application e-commerce complète, développée en **PHP natif** (sans framework) et **MySQL/PDO**, respectant une architecture propre et modulable avec une séparation claire entre la logique métier (**backend**) et l'interface utilisateur (**frontend**).

---

## 📋 Sommaire

- [Présentation du projet](#-présentation-du-projet)
- [Fonctionnalités principales](#-fonctionnalités-principales)
- [Architecture & Structure du projet](#-architecture--structure-du-projet)
- [Prérequis système](#-prérequis-système)
- [Installation & Configuration](#-installation--configuration)
  - [1. Obtenir les sources](#1-obtenir-les-sources)
  - [2. Importer la base de données](#2-importer-la-base-de-données)
  - [3. Configurer les identifiants PDO](#3-configurer-les-identifiants-pdo)
  - [4. Lancer le serveur local](#4-lancer-le-serveur-local)
- [Schéma de la base de données](#-schéma-de-la-base-de-données)
- [Cartographie des Routes & Fichiers](#-cartographie-des-routes--fichiers)
- [Comptes de test & Rôles](#-comptes-de-test--rôles)
- [Documentation & Zones de Commentaires](#-documentation--zones-de-commentaires)

---

## 🌟 Présentation du projet

Ce projet constitue une plateforme e-commerce clé en main incluant :
- Un catalogue de produits avec recherche dynamique par mot-clé et système de tri.
- Un système complet d'authentification utilisateur avec chiffrement sécurisé des mots de passe.
- Un panier d'achat persistant par utilisateur avec ajustement des quantités et calcul du sous-total/total.
- Un mécanisme de commande gérant le solde bancaire virtuel de l'utilisateur, la décrémentation des stocks en temps réel et la génération de factures.
- Un tableau de bord d'administration sécurisé permettant la modération des membres, la création et l'édition d'articles, la mise à jour des stocks et la consultation de l'historique des factures.

---

## 🔥 Fonctionnalités principales

### 🛒 Côté Client / Utilisateur
1. **Navigation & Catalogue** :
   - Affichage des articles sous forme de cartes modernes.
   - Badge de disponibilité du stock en temps réel (*En stock* avec quantité restante / *Rupture de stock*).
   - Barre de recherche par mot-clé (nom ou description).
   - Tri dynamique : plus récents, prix croissant, prix décroissant.
2. **Page Fiche Produit (`detail.php`)** :
   - Vue détaillée avec auteur du produit, date de mise en ligne, description complète et tarif.
   - Sélecteur de quantité borné par la limite du stock disponible.
   - Bouton d'ajout immédiat au panier.
3. **Panier & Prise de Commande (`cart.php`)** :
   - Affichage sous forme de tableau récapitulatif.
   - Modification en direct des quantités et suppression d'articles.
   - Vérification automatique de la solvabilité du client (solde utilisateur vs total de la commande).
   - Saisie de l'adresse de facturation/livraison.
   - Validation de la commande via transaction SQL sécurisée (`beginTransaction` / `commit` / `rollBack`) qui :
     - Déduit le montant total du solde utilisateur.
     - Décrémente le stock de chaque article.
     - Génère une entrée de facture (`invoice`).
     - Vide le panier de l'utilisateur.
4. **Authentification (`auth/`)** :
   - Inscription avec validation du format de l'email, mot de passe fort (12+ caractères) et solde de bienvenue initial crédité (100 €).
   - Connexion via hash de mot de passe BCrypt (`password_verify`).
   - Déconnexion sécurisée réinitialisant la session PHP et les cookies associés.

### ⚙️ Côté Administrateur (`admin.php`)
1. **Gestion des Utilisateurs** :
   - Liste de tous les comptes enregistrés.
   - Édition des informations membres : nom d'utilisateur, email, attribution du rôle (`user` ou `admin`), rechargement du solde du compte (`edit_user.php`).
   - Suppression sécurisée de comptes (avec protection contre l'auto-suppression de l'admin connecté).
2. **Gestion des Articles & Stocks** :
   - Création de nouveaux produits avec attribution du stock initial (`add_article.php`).
   - Édition complète des fiches produits : nom, description, prix, image et niveau du stock (`edit_article.php`).
   - Suppression définitive d'articles (`admin_delete.php`).
3. **Historique des Ventes** :
   - Tableau de bord des factures enregistrées avec nom du client, date de la transaction, montant et adresse de facturation.

---

## 📐 Architecture & Structure du projet

```
php-e-commerce/
├── index.php                       # Point d'entrée racine (redirection vers backend/home.php)
├── CODEOWNERS                      # Fichier de propriétaires de code
├── README.md                       # Documentation principale du projet
├── backend/                        # LOGIQUE SVEUR & CONTRÔLEURS
│   ├── home.php                    # Contrôleur d'accueil (catalogue, recherche & tri)
│   ├── detail.php                  # Contrôleur de fiche produit & ajout panier
│   ├── cart.php                    # Contrôleur du panier et paiement de commande
│   ├── admin.php                   # Contrôleur du tableau de bord administrateur
│   ├── admin_delete.php            # Traitement de suppression (membres / articles)
│   ├── add_article.php             # Contrôleur de création d'article
│   ├── edit_article.php            # Contrôleur d'édition d'article & stock
│   ├── edit_user.php               # Contrôleur d'édition d'utilisateur & solde
│   ├── init_admin.php              # Script utilitaire de dev (activation session admin)
│   ├── auth/
│   │   ├── index.php               # Guard d'authentification
│   │   ├── login.php               # Traitement du formulaire de connexion
│   │   ├── register.php            # Traitement du formulaire d'inscription
│   │   ├── logout.php              # Traitement de la déconnexion
│   │   └── pages/                  # Templates HTML de secours (login / register)
│   └── config/
│       └── config.php              # Connexion PDO MySQL & initialisation des sessions
├── frontend/                       # VUES & INTERFACE UTILISATEUR
│   ├── index.php                   # Redirection frontend vers backend/home.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css           # Feuille de style globale (thème, boutons, tables)
│   │   │   └── home.css            # Feuille de style spécifique (grilles, cartes, panier)
│   │   └── img/
│   │       └── default.jpg         # Image par défaut des articles
│   └── pages/
│       ├── home.php                # Vue de la page d'accueil
│       ├── detail.php              # Vue de la fiche produit
│       ├── cart.php                # Vue du panier d'achat
│       ├── admin.php               # Vue du tableau de bord administrateur
│       ├── add_article.php         # Vue de création d'un article
│       ├── edit_article.php        # Vue d'édition d'un article
│       ├── edit_user.php           # Vue d'édition d'un utilisateur
│       ├── partials/
│       │   ├── header.php          # Barre de navigation partagée avec solde & panier
│       │   └── footer.php          # Pied de page partagé
│       └── auth/
│           ├── login.php           # Vue du formulaire de connexion
│           └── register.php        # Vue du formulaire d'inscription
└── sql/
    ├── database.sql                # Script d'initialisation de la BDD et tables
    └── request.sql                 # Requêtes SQL de démonstration et utilitaires
```

---

## 🛠️ Prérequis système

- **PHP** : version 8.0 ou supérieure (avec extensions `pdo_mysql` et `mbstring`).
- **MySQL / MariaDB** : version 5.7+ ou MariaDB 10.3+.
- **Navigateur Web** : n'importe quel navigateur moderne (Chrome, Firefox, Edge, Safari).

---

## 🚀 Installation & Configuration

### 1. Obtenir les sources
```bash
git clone https://github.com/guiiireg/php-e-commerce.git
cd php-e-commerce
```

### 2. Importer la base de données
Exécutez le script SQL pour créer la base `php_exam`, la structure des tables et charger les données de démonstration :

```bash
mysql -u root -p < sql/database.sql
```

Si vous préférez créer l'utilisateur MySQL dédié avec les droits appropriés :
```sql
CREATE USER IF NOT EXISTS 'php_user'@'localhost' IDENTIFIED BY 'root123';
GRANT ALL PRIVILEGES ON php_exam.* TO 'php_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configurer les identifiants PDO
Le fichier `backend/config/config.php` contient les identifiants de connexion. Modifiez-les si nécessaire :
```php
$host = 'localhost';
$dbname = 'php_exam';
$username = 'php_user'; // Votre identifiant MySQL
$password = 'root123';  // Votre mot de passe MySQL
```

### 4. Lancer le serveur local
Exécutez la commande suivante depuis la racine du projet :
```bash
php -S localhost:8080
```
Accédez ensuite à l'application dans votre navigateur : **[http://localhost:8080](http://localhost:8080)**.

---

## 🗄️ Schéma de la base de données

```
  +-------------------------------------------------------------+
  |                            users                            |
  +-------------------------------------------------------------+
  | id (PK), username, email, password, solde, photo, role      |
  +-------------------------------------------------------------+
         |                                           |
         | (1:N)                                     | (1:N)
         v                                           v
  +-----------------------+                   +------------------+
  |        article        |                   |     invoice      |
  +-----------------------+                   +------------------+
  | id (PK), nom, prix,   |                   | id (PK), user_id,|
  | description, image,   |                   | date, montant,   |
  | date_pub, auteur_id   |                   | adresse, ville,cp|
  +-----------------------+                   +------------------+
    |                 |
    | (1:1)           | (1:N)
    v                 v
  +-----------+     +-------------------+
  |   stock   |     |       cart        |
  +-----------+     +-------------------+
  | id (PK),  |     | id (PK), user_id, |
  | article_id|     | article_id,       |
  | nombre    |     | quantite          |
  +-----------+     +-------------------+
```

- **`users`** : Comptes utilisateurs, hash de mot de passe, rôle (`user`/`admin`) et solde bancaire virtuel (`solde`).
- **`article`** : Produits mis en vente (nom, description, prix, auteur, image).
- **`stock`** : Quantité disponible en stock associée à chaque article.
- **`cart`** : Articles et quantités enregistrés dans le panier d'un utilisateur.
- **`invoice`** : Factures émises lors de la validation d'une commande.

---

## 🔑 Comptes de test & Rôles

Le fichier `sql/database.sql` pré-remplit la base de données avec des comptes de test :

| Role | Identifiant / Email | Mot de passe | Solde par défaut |
|------|--------------------|--------------|------------------|
| **Admin** | `admin@example.com` | `Admin123456!` | 500.00 € |
| **User** | `jean@example.com` | `Admin123456!` | 250.00 € |

> 💡 **Mode Rapide Admin (Développement)** :  
> Vous pouvez forcer une session administrateur sans saisir d'identifiant en visitant l'URL :  
> `http://localhost:8080/backend/init_admin.php`

---

## 📚 Documentation & Zones de Commentaires

L'ensemble du code a été documenté avec des zones de commentaires claires et didactiques en français afin d'en faciliter la lecture et la compréhension par tout développeur :
- **En-têtes de fichiers (Docblocks)** : Présentation du rôle du fichier et de son contexte d'exécution.
- **Gestion des transactions SQL** : Explication du fonctionnement des requêtes préparées PDO, des jointures (`LEFT JOIN`) et du contrôle des transactions (`beginTransaction` / `commit` / `rollBack`).
- **Sécurité** : Explications détaillées du chiffrement des mots de passe (`password_hash`, `password_verify`), de la prévention contre les failles XSS (`htmlspecialchars`) et des injections SQL.
