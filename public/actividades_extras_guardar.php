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
$evidencia = "";

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
