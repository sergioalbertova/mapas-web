<?php

require "auth.php";
require "db.php";

// Validar datos
$nombre = trim($_POST['nombre'] ?? '');
$tamano_total_gb = $_POST['tamano_total_gb'] ?? '';
$observaciones = trim($_POST['observaciones'] ?? '');

// Validaciones
if ($nombre === '' || $tamano_total_gb === '') {
    header("Location: discos_respaldo_nuevo.php?error=1");
    exit;
}

// Insertar
$stmt = $pdo->prepare("
    INSERT INTO discos_respaldo
    (
        nombre,
        tamano_total_gb,
        observaciones
    )
    VALUES (?, ?, ?)
");

$stmt->execute([
    $nombre,
    $tamano_total_gb,
    $observaciones
]);

// Regresar
header("Location: discos_respaldo.php?ok=1");
exit;