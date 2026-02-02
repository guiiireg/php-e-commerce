<?php

$db_host = 'localhost';
$db_name = 'php_exam';
$db_user = 'root';
$db_pass = '';

try {
	$pdo = new PDO("mysql:host=$db_host;db_name=$db_name;charset=utf8", $db_user, $db_pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Error while connecting to database : " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}