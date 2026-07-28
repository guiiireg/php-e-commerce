<?php
/**
 * SCRIPT UTILITAIRE DE DÉVELOPPEMENT : SIMULATION / INITIALISATION SESSION ADMIN
 * - Force une session d'administrateur pour faciliter le développement et les tests.
 * - Ne doit pas être déployé en production.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forçage des attributs de session administrateur
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'SuperTester';
$_SESSION['email'] = 'admin@example.com';
$_SESSION['solde'] = 500.00;

echo "<div style='font-family: sans-serif; padding: 20px; text-align: center;'>";
echo "<h2>✅ Mode Admin activé pour le développement !</h2>";
echo "<p>Session initialisée avec le rôle <strong>admin</strong> et un solde de 500,00 €.</p>";
echo "<p><a href='/backend/admin.php' style='display:inline-block; padding:10px 20px; background:#2563eb; color:white; text-decoration:none; border-radius:5px;'>Accéder au tableau de bord Admin</a></p>";
echo "<p><a href='/backend/home.php'>Aller à l'accueil</a></p>";
echo "</div>";