<?php
require "session_config.php";
require "db.php";

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=no_session");
    exit;
}

// Validar campos obligatorios
if (!isset($_POST['tituloincidente']) || trim($_POST['tituloincidente']) === "") {
    die("Error: Falta el título del incidente.");
}

// Recibir datos
$tituloincidente      = $_POST['tituloincidente'];
$descripcion          = $_POST['descripcion'] ?? null;
$prioridad            = $_POST['prioridad'] ?? 'Alta';
$impacto              = $_POST['impacto'] ?? 'Alto';
$urgencia             = $_POST['urgencia'] ?? 'Alta';
$categoria            = $_POST['categoria'] ?? null;
$subcategoria         = $_POST['subcategoria'] ?? null;
$tiempo_estimado      = $_POST['tiempo_estimado'] !== "" ? intval($_POST['tiempo_estimado']) : null;
$requiere_aprobacion  = isset($_POST['requiere_aprobacion']) ? true : false;
$notas_internas       = $_POST['notas_internas'] ?? null;
$activo               = isset($_POST['activo']) ? true : false;
$orden                = $_POST['orden'] !== "" ? intval($_POST['orden']) : 0;
$solucion_propuesta   = $_POST['solucion_propuesta'] ?? null;

// Insertar en la tabla catapoyo
$sql = "INSERT INTO catapoyo
(
    tituloincidente,
    descripcion,
    prioridad,
    impacto,
    urgencia,
    categoria,
    subcategoria,
    tiempo_estimado,
    requiere_aprobacion,
    notas_internas,
    activo,
    orden,
    solucion_propuesta
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $tituloincidente,
    $descripcion,
    $prioridad,
    $impacto,
    $urgencia,
    $categoria,
    $subcategoria,
    $tiempo_estimado,
    $requiere_aprobacion,
    $notas_internas,
    $activo,
    $orden,
    $solucion_propuesta
]);

// Redirigir con mensaje
header("Location: itil_catalogo.php?msg=ok");
exit;
?>
