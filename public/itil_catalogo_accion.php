<?php
require "session_config.php";
require "db.php";

/* Validar que venga el ID */
if (!isset($_POST['idapoyo'])) {
    die("ID no recibido");
}

$idapoyo = intval($_POST['idapoyo']);

/* Recibir datos del formulario */
$tituloincidente     = $_POST['tituloincidente'] ?? '';
$descripcion         = $_POST['descripcion'] ?? '';
$categoria           = $_POST['categoria'] ?: null; // puede ser null
$subcategoria        = $_POST['subcategoria'] ?? '';
$prioridad           = $_POST['prioridad'] ?? 'Alta';
$impacto             = $_POST['impacto'] ?? 'Alto';
$urgencia            = $_POST['urgencia'] ?? 'Alta';
$tiempo_estimado     = $_POST['tiempo_estimado'] !== '' ? intval($_POST['tiempo_estimado']) : null;
$requiere_aprobacion = isset($_POST['requiere_aprobacion']) ? intval($_POST['requiere_aprobacion']) : 0;
$notas_internas      = $_POST['notas_internas'] ?? '';
$solucion_propuesta  = $_POST['solucion_propuesta'] ?? '';
$activo              = isset($_POST['activo']) ? intval($_POST['activo']) : 1;

/* Actualizar registro */
$sql = "
    UPDATE catapoyo SET
        tituloincidente     = :tituloincidente,
        descripcion         = :descripcion,
        categoria           = :categoria,
        subcategoria        = :subcategoria,
        prioridad           = :prioridad,
        impacto             = :impacto,
        urgencia            = :urgencia,
        tiempo_estimado     = :tiempo_estimado,
        requiere_aprobacion = :requiere_aprobacion,
        notas_internas      = :notas_internas,
        solucion_propuesta  = :solucion_propuesta,
        activo              = :activo
    WHERE idapoyo = :idapoyo
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':tituloincidente'     => $tituloincidente,
    ':descripcion'         => $descripcion,
    ':categoria'           => $categoria,
    ':subcategoria'        => $subcategoria,
    ':prioridad'           => $prioridad,
    ':impacto'             => $impacto,
    ':urgencia'            => $urgencia,
    ':tiempo_estimado'     => $tiempo_estimado,
    ':requiere_aprobacion' => $requiere_aprobacion,
    ':notas_internas'      => $notas_internas,
    ':solucion_propuesta'  => $solucion_propuesta,
    ':activo'              => $activo,
    ':idapoyo'             => $idapoyo
]);

/* Redirigir con mensaje */
header("Location: itil_catalogo.php?msg=edit_ok");
exit;
