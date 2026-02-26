<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "ep-xxxxx.us-east-2.aws.neon.tech";
$dbname = "neon_db";
$user = "neon_user";
$pass = "npg_uZAv9VetBzl4";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
