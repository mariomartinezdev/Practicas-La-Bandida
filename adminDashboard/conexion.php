<?php
$host = "sql209.byetcluster.com"; // El servidor de tu phpMyAdmin en ByetHost
$db   = "if0_42105450_bandida";  // nombre de base de datos del hosting
$user = "if0_42105450";          // usuario de la base de datos
$pass = "h2Kp08FW9qnQec";     // la contraseña real de tu cuenta de hosting
$charset = "utf8mb4";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>