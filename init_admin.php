<?php
// init_admin.php
session_start();

// On force les variables de session comme si on s'était connecté
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'SuperTester';

echo "Mode Admin activé ! <a href='admin.php'>Aller au tableau de bord</a>";