# 🛍️ PHP E-Commerce — Native PHP & MySQL E-Commerce Web Application

> **Note pour les recruteurs / RH :** *PHP E-Commerce est une application complète de commerce électronique développée en PHP 8 natif (sans framework) et MySQL/PDO, implémentant une architecture modulaire MVC, la gestion des sessions/CSRF, des transactions bancaires ACID virtuelles et un espace d'administration sécurisé.*

[![Live Demo](https://img.shields.io/badge/Live%20Demo-guireg.alwaysdata.net-2563eb?style=for-the-badge&logo=google-chrome&logoColor=white)](https://guireg.alwaysdata.net)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)

> 🌐 **Live Demo Available:** **[https://guireg.alwaysdata.net](https://guireg.alwaysdata.net)**

Welcome to the **PHP E-Commerce** repository. This is a full-featured e-commerce web platform built with **native PHP** (no heavy third-party frameworks) and **MySQL/PDO**, adhering to clean code principles, modular architecture, and strict separation between server-side domain logic (**backend**) and template presentation (**frontend**).

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Architecture & Directory Structure](#-architecture--directory-structure)
- [System Requirements](#-system-requirements)
- [Installation & Setup](#-installation--setup)
  - [1. Clone the Repository](#1-clone-the-repository)
  - [2. Import the Database](#2-import-the-database)
  - [3. Configure PDO Credentials](#3-configure-pdo-credentials)
  - [4. Start the Local Server](#4-start-the-local-server)
- [Database Schema](#-database-schema)
- [Route & File Mapping](#-route--file-mapping)
- [Demo Test Accounts & Roles](#-demo-test-accounts--roles)
- [Security & Engineering Standards](#-security--engineering-standards)

---

## 🌟 Overview

This project delivers a complete turnkey e-commerce solution including:
- Dynamic product catalog with keyword search and price/date sorting.
- Robust user authentication and session management with BCrypt password hashing.
- Persistent user shopping carts with live quantity adjustment and automatic subtotal calculation.
- Transactional checkout engine managing virtual customer bank balances, atomic inventory deduction, and invoice generation.
- Role-Based Access Control (RBAC) administrative dashboard for member moderation, product publishing, inventory stock updates, and sales reporting.

---

## 🔥 Key Features

### 🛒 Customer & Storefront
1. **Catalog & Discovery** :
   - Modern grid layout with responsive product cards.
   - Real-time stock status badge (*In Stock* with available units / *Out of Stock*).
   - Multi-column keyword search (filtering across product title and description).
   - Dynamic sorting: newest first, price ascending, price descending.
2. **Product Details Page (`detail.php`)** :
   - Dedicated specification view with publisher info, release timestamp, full description, and unit price.
   - Dynamic quantity selector bounded by real-time inventory limits.
   - One-click cart addition with automatic quantity merging.
3. **Cart & Checkout Engine (`cart.php`)** :
   - Interactive tabular cart overview with live subtotals and global order sum.
   - Real-time item deletion and quantity modification.
   - Automated client solvency check (user balance vs total checkout sum).
   - Billing and shipping address collection.
   - Secure atomic SQL transaction (`beginTransaction` / `commit` / `rollBack`) executing:
     - Order total deduction from user virtual funds.
     - Decrementing stock for each purchased item.
     - Recording immutable sales invoice (`invoice`).
     - Clearing active shopping cart rows.
4. **Authentication & Security (`auth/`)** :
   - Member registration with email format validation, strong password enforcement (12+ characters), and a credited starting balance (€100.00).
   - Secure login using constant-time BCrypt verification (`password_verify`).
   - Session fixation countermeasure with `session_regenerate_id(true)` upon successful authentication.
   - Comprehensive CSRF token validation on all state-mutating POST forms.

### ⚙️ Administrator Dashboard (`admin.php`)
1. **User Account Moderation** :
   - Complete list of registered users.
   - Member profile editor: update username, email, role assignment (`user` vs `admin`), and virtual balance top-up (`edit_user.php`).
   - Secure deletion of user accounts with self-deletion protection for the active administrator.
2. **Catalog & Inventory Management** :
   - Create new products with initial stock allocations (`add_article.php`).
   - Complete article editor: title, description, price, thumbnail image, and inventory quantities (`edit_article.php`).
   - Permanent removal of products with cascading foreign key cleanup (`admin_delete.php`).
3. **Sales & Invoice Bookkeeping** :
   - Historical invoices ledger displaying customer name, order timestamp, amount, and billing address.

---

## 📐 Architecture & Directory Structure

```text
php-e-commerce/
├── index.php                       # Root entry point (forwards to backend/home.php)
├── CODEOWNERS                      # Repository code ownership file
├── README.md                       # Main project documentation
├── backend/                        # SERVER LOGIC & CONTROLLERS
│   ├── home.php                    # Catalog discovery, search & sorting controller
│   ├── detail.php                  # Product detail & add-to-cart controller
│   ├── cart.php                    # Shopping cart & checkout transaction controller
│   ├── admin.php                   # Administrative dashboard overview controller
│   ├── admin_delete.php            # Administrative deletion endpoint (users & articles)
│   ├── add_article.php             # Product creation controller
│   ├── edit_article.php            # Product & inventory update controller
│   ├── edit_user.php               # User account & balance update controller
│   ├── auth/
│   │   ├── index.php               # Auth directory guard
│   │   ├── login.php               # Login credential processor
│   │   ├── register.php            # Member registration processor
│   │   ├── logout.php              # Session teardown & cookie invalidation
│   │   └── pages/                  # Static fallback HTML templates
│   └── config/
│       └── config.php              # PDO MySQL connection, session hardening & CSRF helpers
├── frontend/                       # VIEW LAYER & USER INTERFACE
│   ├── index.php                   # Frontend directory fallback
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css           # Global typography, layout, buttons & tables
│   │   │   └── home.css            # Storefront grid, cards, and cart styling
│   │   └── img/
│   │       └── default.jpg         # Default product placeholder asset
│   └── pages/
│       ├── home.php                # Storefront catalog view template
│       ├── detail.php              # Product detail view template
│       ├── cart.php                # Shopping cart & checkout view template
│       ├── admin.php               # Administrative dashboard view template
│       ├── add_article.php         # Product creation view template
│       ├── edit_article.php        # Product edit view template
│       ├── edit_user.php           # User edit view template
│       ├── partials/
│       │   ├── header.php          # Shared navigation bar with live balance & cart badge
│       │   └── footer.php          # Shared global footer
│       └── auth/
│           ├── login.php           # Login form view template
│           └── register.php        # Registration form view template
└── sql/
    ├── database.sql                # Complete database schema and seed data
    └── request.sql                 # Reference SQL queries and data manipulation examples
```

---

## 🛠️ System Requirements

- **PHP** : `8.0` or higher (with `pdo_mysql` and `mbstring` extensions enabled).
- **MySQL / MariaDB** : `5.7+` or MariaDB `10.3+`.
- **Web Browser** : Any modern standards-compliant browser (Chrome, Firefox, Edge, Safari).

---

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/guiiireg/php-e-commerce.git
cd php-e-commerce
```

### 2. Import the Database
Execute the SQL script to create the `php_exam` database, initialize table structures, and seed initial test data:

```bash
mysql -u root -p < sql/database.sql
```

Alternatively, create a dedicated MySQL user:
```sql
CREATE USER IF NOT EXISTS 'php_user'@'localhost' IDENTIFIED BY 'root123';
GRANT ALL PRIVILEGES ON php_exam.* TO 'php_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configure PDO Credentials
Adjust connection parameters in `backend/config/config.php` (or provide environment variables):
```php
$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$dbname = env('DB_NAME', 'php_exam');
$username = env('DB_USER', 'php_user');
$password = env('DB_PASS', 'root123');
```

### 4. Start the Local Server
Launch PHP's built-in development server from the repository root:
```bash
php -S localhost:8080
```
Open your browser at **[http://localhost:8080](http://localhost:8080)**.

---

## 🗄️ Database Schema

```text
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

- **`users`** : User accounts, BCrypt password hashes, role assignments (`user`/`admin`), and virtual bank balances (`solde`).
- **`article`** : Product catalog listings (name, description, price, author reference, image asset).
- **`stock`** : Real-time available inventory quantity linked 1:1 with each article.
- **`cart`** : User active shopping cart items and quantities.
- **`invoice`** : Immutable customer invoices generated upon checkout completion.

---

## 🔑 Demo Test Accounts & Roles

Pre-configured demo credentials from `sql/database.sql`:

| Role | Username / Email | Password | Default Balance |
|------|------------------|----------|-----------------|
| **Admin** | `admin@example.com` | `Admin123456!` | 500.00 € |
| **User** | `jean@example.com` | `Admin123456!` | 250.00 € |

---

## 🛡️ Security & Engineering Standards

- **SQL Injection Prevention:** 100% prepared statements with native parameters (`PDO::ATTR_EMULATE_PREPARES => false`).
- **XSS Mitigation:** Universal output encoding using `htmlspecialchars()` across all presentation templates.
- **CSRF Protection:** Cryptographically secure per-session tokens (`random_bytes(32)`) validated with constant-time `hash_equals()`.
- **Session Hardening:** `HttpOnly`, `SameSite=Lax`, and HTTPS-aware `Secure` flags configured on session cookies.
- **Path Traversal Protection:** Image file references are sanitized using `basename()` to prevent directory traversal.
