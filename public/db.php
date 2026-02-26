<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "ep-cold-dawn-aitfe8k8-pooler.c-4.us-east-1.aws.neon.tech";
$dbname = "neondb";
$user = "neondb_owner";
$pass = "npg_uZAv9VetBzl4";

try {
    $pdo = new PDO(
        "pgsql:host=$host;dbname=$dbname;sslmode=require",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
