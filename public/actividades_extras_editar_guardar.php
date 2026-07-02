<?php
date_default_timezone_set('America/Mexico_City');

require "auth.php";
require "db.php";

// ✅ Validar ID
if (!isset($_POST['idextra'])) {
    header("Location: actividades_extras.php");
    exit;
}

$idextra          = $_POST['idextra'];
$idactividad      = $_POST['idactividad'] ?? null;
$usuario_afectado = trim($_POST['usuario_afectado'] ?? "");
$equipo           = trim($_POST['equipo'] ?? "");
$comentarios      = trim($_POST['comentarios'] ?? "");
$estatus          = $_POST['estatus'] ?? "en proceso";

$evidencia = $_POST['evidencia_actual'] ?? "";

// Si se seleccionó una nueva imagen
if (
    isset($_FILES['evidencia']) &&
    $_FILES['evidencia']['error'] === 0
) {

    $cloud_name = "u6byivhv";
    $upload_preset = "evidencias";

    $archivo = new CURLFile($_FILES['evidencia']['tmp_name']);

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.cloudinary.com/v1_1/$cloud_name/image/upload",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => [
            'file' => $archivo,
            'upload_preset' => $upload_preset
        ]
    ]);

    $respuesta = curl_exec($ch);
    curl_close($ch);

    $resultado = json_decode($respuesta, true);

    if (isset($resultado['secure_url'])) {
        $evidencia = $resultado['secure_url'];
    }
}

// ✅ limpiar texto
if ($usuario_afectado === "") {
    $usuario_afectado = null;
}

// ✅ validar
if (!$idactividad) {
    header("Location: actividades_extras_editar.php?id=$idextra&error=actividad");
    exit;
}

// ===================================================
// ✅ OBTENER VALORES ACTUALES (muy importante)
// ===================================================

$stmt = $pdo->prepare("
    SELECT fecha_inicio, fecha_fin 
    FROM actividades_extras 
    WHERE idextra = ?
");
$stmt->execute([$idextra]);

$actual = $stmt->fetch(PDO::FETCH_ASSOC);

$fecha_inicio = $actual['fecha_inicio'];
$fecha_fin    = $actual['fecha_fin'];

// ===================================================
// ✅ LÓGICA INTELIGENTE DE TIEMPO
// ===================================================

// 🔥 Si se marca como completado y no tiene fin → asignar
if ($estatus === "completado" && !$fecha_fin) {
    $fecha_fin = date("Y-m-d H:i:s");
}

// 🔥 Si lo regresan a "en proceso" → quitar fin
if ($estatus === "en proceso") {
    $fecha_fin = null;
}

// ===================================================
// ✅ ACTUALIZAR
// ===================================================

$stmt = $pdo->prepare("
    UPDATE actividades_extras
    SET idactividad = ?, 
        usuario_afectado = ?, 
        equipo = ?, 
        comentarios = ?, 
        evidencia = ?, 
        estatus = ?, 
        fecha_inicio = ?, 
        fecha_fin = ?
    WHERE idextra = ?
");

$stmt->execute([
    $idactividad,
    $usuario_afectado,
    $equipo,
    $comentarios,
    $evidencia,
    $estatus,
    $fecha_inicio, // ✅ nunca se pierde
    $fecha_fin,    // ✅ control inteligente
    $idextra
]);

// ✅ redirigir
header("Location: actividades_extras.php?edit=1");
exit;