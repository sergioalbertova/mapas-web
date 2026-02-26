<?php
$host = "TU_HOST";
$dbname = "TU_DB";
$user = "TU_USUARIO";
$pass = "TU_PASSWORD";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
