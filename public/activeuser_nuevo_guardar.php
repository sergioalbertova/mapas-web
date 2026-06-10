<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

// Convertir vacío a NULL para evitar error en INTEGER
$ubimapa2 = $_POST['ubimapa2'];
$ubimapa2 = ($ubimapa2 === "" ? null : $ubimapa2);

$stmt = $pdo->prepare("
    INSERT INTO activeuser (nomuser, ubicacion, hor1, piso, ubimapa2)
    VALUES (:nomuser, :ubicacion, :hor1, :piso, :ubimapa2)
");

$stmt->execute([
    ':nomuser'   => $_POST['nomuser']   ?? null,
    ':ubicacion' => $_POST['ubicacion'] ?? null,
    ':hor1'      => $_POST['hor']       ?? null,
    ':piso'      => $_POST['piso']      ?? null,
    ':ubimapa2'  => $ubimapa2
]);

header("Location: activeuser_admin.php?msg=nuevo_ok");
exit;
