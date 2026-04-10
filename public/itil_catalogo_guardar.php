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

$tituloincidente      = $_POST['tituloincidente'];
$descripcion          = $_POST['descripcion'] ?? null;
$prioridad            = $_POST['prioridad'] ?? 'Alta';
$impacto              = $_POST['impacto'] ?? 'Alto';
$urgencia             = $_POST['urgencia'] ?? 'Alta';
$categoria            = $_POST['categoria'] ?? null;
$notas_internas       = $_POST['notas_internas'] ?? null;
$activo               = isset($_POST['activo']) ? true : false;
$solucion_propuesta   = $_POST['solucion_propuesta'] ?? null;

$sql = "INSERT INTO catapoyo
(
    tituloincidente,
    descripcion,
    prioridad,
    impacto,
    urgencia,
    categoria,
    notas_internas,
    activo,
    solucion_propuesta
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $tituloincidente,
    $descripcion,
    $prioridad,
    $impacto,
    $urgencia,
    $categoria,
    $notas_internas,
    $activo,
    $solucion_propuesta
]);

header("Location: itil_catalogo.php?msg=ok");
exit;
?>
