<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    die("Error: No hay sesión activa.");
}

$tecnico_id = intval($_SESSION['user_id']); // técnico logueado

// Validar campos obligatorios
$requeridos = [
    'usuario_final_id',
    'ubicacion_detalle',
    'activo_inventario',
    'descripcion',
    'prioridad',
    'impacto',
    'urgencia',
    'titulo_select'
];

foreach ($requeridos as $campo) {
    if (!isset($_POST[$campo]) || trim($_POST[$campo]) === "") {
        die("Error: Falta el campo $campo");
    }
}

// Recibir datos
$usuario_final_id   = $_POST['usuario_final_id'];   // usuario afectado
$ubicacion_detalle  = $_POST['ubicacion_detalle'];
$activo_inventario  = $_POST['activo_inventario'];
$titulo             = $_POST['titulo_select'];      // viene del combo
$descripcion        = $_POST['descripcion'];
$prioridad          = $_POST['prioridad'];
$impacto            = $_POST['impacto'];
$urgencia           = $_POST['urgencia'];

// Insertar incidente
$sql = "INSERT INTO itil_incidentes 
(
    titulo,
    descripcion,
    prioridad,
    impacto,
    urgencia,
    usuario_reporta,
    tecnico_asignado,
    usuario_final_id,
    activo_inventario,
    ubicacion_detalle,
    fecha_reporte
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $titulo,
    $descripcion,
    $prioridad,
    $impacto,
    $urgencia,
    $usuario_final_id,  // usuario que reporta = usuario afectado
    $tecnico_id,        // técnico asignado = usuario logueado
    $usuario_final_id,
    $activo_inventario,
    $ubicacion_detalle
]);

// Redirigir con mensaje
header("Location: itil_incidentes.php?msg=ok");
exit;
?>
