<?php
session_start();

$host = 'localhost';
$dbname = 'php_exam';
$username = 'php_user';
$password = 'votre_mot_de_passe';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error while connecting to database : " . $e->getMessage());
}