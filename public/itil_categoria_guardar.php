<?php
require "session_config.php";
require "db.php";

if (!isset($_POST['nombre']) || trim($_POST['nombre']) === "") {
    die("Nombre requerido");
}

$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'] ?? null;

try {
    $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
    $stmt->execute([$nombre, $descripcion]);
    echo "OK";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
