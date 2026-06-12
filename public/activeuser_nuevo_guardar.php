<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

$ubimapa2 = $_POST['ubimapa2'] ?? null;
$ubimapa2 = ($ubimapa2 === "" ? null : $ubimapa2);

$stmt = $pdo->prepare("
    INSERT INTO activeuser (nomuser, ubicacion, hor1, hor2, piso, ubimapa2, xm, ym)
VALUES (:nomuser, :ubicacion, :hor1, :hor2, :piso, :ubimapa2, :xm, :ym)
");

$stmt->execute([
    ':nomuser'   => $_POST['nomuser']   ?? null,
    ':ubicacion' => $_POST['ubicacion'] ?? null,
    ':hor1'      => $_POST['hor']       ?? null,
    ':hor2'      => $_POST['monitor']   ?? null,
    ':piso'      => $_POST['piso']      ?? null,
    ':ubimapa2'  => $ubimapa2,
    ':xm' => ($_POST['xm'] === "" ? null : $_POST['xm']),
    ':ym' => ($_POST['ym'] === "" ? null : $_POST['ym'])

]);

header("Location: activeuser_admin.php?msg=nuevo_ok");
exit;
