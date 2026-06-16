<?php
require "session_config.php";
require "db.php";

// Validar ID
if (!isset($_POST['idextra'])) {
    header("Location: actividades_extras.php");
    exit;
}

$idextra          = $_POST['idextra'];
$idactividad      = $_POST['idactividad'] ?? null;
$usuario_afectado = trim($_POST['usuario_afectado'] ?? "");
$equipo           = trim($_POST['equipo'] ?? "");
$comentarios      = trim($_POST['comentarios'] ?? "");
$evidencia        = trim($_POST['evidencia'] ?? "");
$estatus          = $_POST['estatus'] ?? "completado";

// Usuario afectado puede quedar vacío
if ($usuario_afectado === "") {
    $usuario_afectado = null;
}

// Validación mínima
if (!$idactividad) {
    header("Location: actividades_extras_editar.php?id=$idextra&error=actividad");
    exit;
}

// Actualizar registro
$stmt = $pdo->prepare("
    UPDATE actividades_extras
    SET idactividad = ?, 
        usuario_afectado = ?, 
        equipo = ?, 
        comentarios = ?, 
        evidencia = ?, 
        estatus = ?
    WHERE idextra = ?
");

$stmt->execute([
    $idactividad,
    $usuario_afectado,
    $equipo,
    $comentarios,
    $evidencia,
    $estatus,
    $idextra
]);

// Redirigir al listado
header("Location: actividades_extras.php?edit=1");
exit;
