# PHP E-Commerce

Application e-commerce développée en **PHP natif**, structurée en deux couches distinctes : un **backend** (logique métier, authentification, administration) et un **frontend** (interface utilisateur, assets).

---

## Table des matières

- [PHP E-Commerce](#php-e-commerce)
  - [Table des matières](#table-des-matières)
  - [Architecture du projet](#architecture-du-projet)
    - [Principes d'architecture](#principes-darchitecture)
  - [Prérequis](#prérequis)
  - [Installation \& Déploiement](#installation--déploiement)
    - [1. Cloner le dépôt](#1-cloner-le-dépôt)
    - [2. Importer la base de données](#2-importer-la-base-de-données)
    - [3. Configurer la connexion à la base de données](#3-configurer-la-connexion-à-la-base-de-données)
    - [4. Lancer le serveur de développement](#4-lancer-le-serveur-de-développement)
  - [Base de données](#base-de-données)
    - [Schéma relationnel](#schéma-relationnel)
  - [Routes principales](#routes-principales)
  - [Comptes \& Rôles](#comptes--rôles)
    - [Créer un compte admin (développement)](#créer-un-compte-admin-développement)
    - [Règles d'inscription](#règles-dinscription)

---

## Architecture du projet

```
php-e-commerce/
├── backend/                    # Logique serveur
│   ├── admin.php               # Tableau de bord admin (users + articles)
│   ├── admin_delete.php        # Suppression d'utilisateurs / articles (admin)
│   ├── home.php                # Récupération des articles (recherche, tri)
│   ├── init_admin.php          # Script utilitaire pour forcer une session admin
│   ├── auth/
│   │   ├── index.php           # Guard d'authentification (redirection si non connecté)
│   │   ├── login.php           # Traitement du formulaire de connexion
│   │   ├── register.php        # Traitement du formulaire d'inscription
│   │   └── pages/
│   │       ├── login.html      # Template HTML de connexion
│   │       └── register.html   # Template HTML d'inscription
│   └── config/
│       └── config.php          # Connexion PDO à MySQL + démarrage de session
├── frontend/                   # Interface utilisateur
│   ├── index.php               # Point d'entrée frontend
│   ├── assets/
│   │   ├── css/
│   │   │   ├── home.css        # Styles de la page d'accueil
│   │   │   └── style.css       # Styles globaux
│   │   └── img/                # Images statiques
│   └── pages/
│       ├── admin.php           # Vue admin (rendu HTML)
│       ├── home.php            # Vue accueil / catalogue articles
│       └── auth/
│           ├── login.php       # Vue formulaire de connexion
│           └── register.php    # Vue formulaire d'inscription
├── sql/
│   ├── database.sql            # Script de création de la BDD et des tables
│   └── request.sql             # Requêtes SQL utilitaires (panier, totaux)
├── CODEOWNERS
└── README.md
```

### Principes d'architecture

- **Séparation backend / frontend** : la logique métier (requêtes SQL, contrôle d'accès, traitement des formulaires) est dans `backend/`. Les vues HTML/PHP sont dans `frontend/pages/`.
- **Routage par fichier** : chaque URL correspond directement à un fichier PHP (pas de framework, pas de routeur).
- **Authentification par session** : les sessions PHP (`$_SESSION`) gèrent l'état de connexion et le rôle de l'utilisateur.
- **Base de données MySQL** : accès via PDO avec requêtes préparées.

---

## Prérequis

| Outil | Version minimale |
|-------|-----------------|
| **PHP** | 8.0+ |
| **MySQL** | 5.7+ / MariaDB 10.3+ |
| **Git** | 2.x |

> **Note** : Aucun gestionnaire de dépendances (Composer) n'est requis. Le projet fonctionne en PHP natif.

Vérifiez vos versions installées :

```bash
php -v
mysql --version
```

---

## Installation & Déploiement

### 1. Cloner le dépôt

```bash
git clone <url-du-depot>
cd php-e-commerce
```

### 2. Importer la base de données

Connectez-vous à votre serveur MySQL et exécutez le script de création :

```bash
mysql -u root -p < sql/database.sql
```

Ce script effectue les opérations suivantes :

- Création de la base de données `php_exam`
- Création des tables :
  - **users** — Utilisateurs (username, email, password hashé, solde, photo, rôle)
  - **Article** — Articles en vente (nom, description, prix, image, auteur)
  - **Stock** — Gestion du stock par article
  - **Cart** — Panier d'achat par utilisateur
  - **Invoice** — Factures générées après achat

### 3. Configurer la connexion à la base de données

Ouvrez le fichier `backend/config/config.php` et adaptez les identifiants de connexion à votre environnement local :

```php
$host = 'localhost';
$dbname = 'php_exam';
$username = 'php_user';    // ← Remplacez par votre utilisateur MySQL
$password = 'root123';     // ← Remplacez par votre mot de passe MySQL
```

> **Important** : Assurez-vous que l'utilisateur MySQL configuré a les droits sur la base `php_exam`. Si besoin, créez l'utilisateur :
>
> ```sql
> CREATE USER 'php_user'@'localhost' IDENTIFIED BY 'root123';
> GRANT ALL PRIVILEGES ON php_exam.* TO 'php_user'@'localhost';
> FLUSH PRIVILEGES;
> ```

### 4. Lancer le serveur de développement

Depuis la **racine du projet**, démarrez le serveur intégré de PHP :

```bash
php -S localhost:8080
```

L'application est maintenant accessible à l'adresse :

**[http://localhost:8080](http://localhost:8080)**

---

## Base de données

### Schéma relationnel

```
users (id, username, password, email, solde, photo, role)
  │
  ├──< Article (id, nom, description, prix, date_publication, auteur_id, image)
  │       │
  │       ├──< Stock (id, article_id, nombre)
  │       │
  │       └──< Cart (id, user_id, article_id, quantite)
  │
  └──< Invoice (id, user_id, date, montant, adresse_facturation, ville, cp)
```

- Les clés étrangères utilisent `ON DELETE CASCADE` : la suppression d'un utilisateur ou d'un article supprime les données liées.

---

## Routes principales

| URL | Méthode | Description |
|-----|---------|-------------|
| `/backend/home.php` | GET | Page d'accueil — liste des articles (recherche & tri) |
| `/backend/auth/login.php` | GET / POST | Connexion utilisateur |
| `/backend/auth/register.php` | GET / POST | Inscription utilisateur |
| `/backend/admin.php` | GET | Tableau de bord admin (liste users + articles) |
| `/backend/admin_delete.php?type=user&id=X` | GET | Suppression d'un utilisateur (admin) |
| `/backend/admin_delete.php?type=article&id=X` | GET | Suppression d'un article (admin) |
| `/backend/init_admin.php` | GET | **Dev only** — Force une session admin pour les tests |

---

## Comptes & Rôles

L'application gère deux rôles :

- **`user`** (par défaut) — Accès au catalogue, panier et achat
- **`admin`** — Accès au tableau de bord d'administration (gestion des utilisateurs et articles)

### Créer un compte admin (développement)

Option 1 — Via le script utilitaire :

```
http://localhost:8080/backend/init_admin.php
```

Option 2 — Directement en base de données :

```sql
UPDATE users SET role = 'admin' WHERE email = 'votre@email.com';
```

### Règles d'inscription

- Nom d'utilisateur obligatoire
- Email valide et unique
- Mot de passe de **12 caractères minimum**
- Confirmation du mot de passe obligatoire

---
