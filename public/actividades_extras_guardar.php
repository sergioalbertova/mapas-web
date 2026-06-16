<?php
require "session_config.php";
require "db.php";

// Validar que venga el ingeniero
if (!isset($_POST['idingeniero'])) {
    header("Location: actividades_extras.php");
    exit;
}

$idingeniero       = $_POST['idingeniero'];
$idactividad       = $_POST['idactividad'] ?? null;
$usuario_afectado  = trim($_POST['usuario_afectado'] ?? "");
$equipo            = trim($_POST['equipo'] ?? "");
$comentarios       = trim($_POST['comentarios'] ?? "");
$evidencia         = trim($_POST['evidencia'] ?? "");
$estatus           = $_POST['estatus'] ?? "completado";

// Usuario afectado puede quedar vacío
if ($usuario_afectado === "") {
    $usuario_afectado = null;
}

// Validación mínima
if (!$idactividad) {
    header("Location: actividades_extras_nuevo.php?error=actividad");
    exit;
}

// Insertar en BD
$stmt = $pdo->prepare("
    INSERT INTO actividades_extras 
    (idingeniero, idactividad, usuario_afectado, equipo, comentarios, evidencia, estatus)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $idingeniero,
    $idactividad,
    $usuario_afectado,
    $equipo,
    $comentarios,
    $evidencia,
    $estatus
]);

// Redirigir al listado
header("Location: actividades_extras.php?ok=1");
exit;
