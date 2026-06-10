<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

$idu = $_POST['idu'] ?? null;

// Manejo de enteros vacíos
$ubimapa2 = $_POST['ubimapa2'] ?? null;
$ubimapa2 = ($ubimapa2 === "" ? null : $ubimapa2);

$stmt = $pdo->prepare("
    UPDATE activeuser SET
        nomuser   = :nomuser,
        ubicacion = :ubicacion,
        hor1      = :hor1,
        hor2      = :hor2,
        piso      = :piso,
        ubimapa2  = :ubimapa2
    WHERE idu = :idu
");

$stmt->execute([
    ':nomuser'   => $_POST['nomuser']   ?? null,
    ':ubicacion' => $_POST['ubicacion'] ?? null,
    ':hor1'      => $_POST['hor']       ?? null,
    ':hor2'      => $_POST['monitor']   ?? null,
    ':piso'      => $_POST['piso']      ?? null,
    ':ubimapa2'  => $ubimapa2,
    ':idu'       => $idu
]);

header("Location: activeuser_admin.php?msg=ok");
exit;
