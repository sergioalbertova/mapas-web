<?php
require "auth.php";
require "db.php";

date_default_timezone_set('America/Mexico_City');

// ✅ Validar ingeniero
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
$estatus           = $_POST['estatus'] ?? "en proceso";

// ✅ NUEVOS CAMPOS
$fecha_inicio = $_POST['fecha_inicio'] ?? date("Y-m-d H:i:s");
$fecha_fin    = ($estatus === "completado") ? date("Y-m-d H:i:s") : null;

// limpiar usuario
if ($usuario_afectado === "") {
    $usuario_afectado = null;
}

// validar actividad
if (!$idactividad) {
    header("Location: actividades_extras_nuevo.php?error=actividad");
    exit;
}

// ✅ INSERT CORRECTO CON NUEVOS CAMPOS
$stmt = $pdo->prepare("
    INSERT INTO actividades_extras 
    (fecha, fecha_inicio, fecha_fin, idingeniero, idactividad, usuario_afectado, equipo, comentarios, evidencia, estatus)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    date("Y-m-d H:i:s"), // fecha general
    $fecha_inicio,
    $fecha_fin,
    $idingeniero,
    $idactividad,
    $usuario_afectado,
    $equipo,
    $comentarios,
    $evidencia,
    $estatus
]);

header("Location: actividades_extras.php?ok=1");
exit;
