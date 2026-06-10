<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

$idu = $_POST['idu'];

$stmt = $pdo->prepare("
    UPDATE activeuser SET
        nomuser = :nomuser,
        ubicacion = :ubicacion,
        hor = :hor,
        piso = :piso,
        ubimapa2 = :ubimapa2
    WHERE idu = :idu
");

$stmt->execute([
    ':nomuser' => $_POST['nomuser'],
    ':ubicacion' => $_POST['ubicacion'],
    ':hor' => $_POST['hor'],
    ':piso' => $_POST['piso'],
    ':ubimapa2' => $_POST['ubimapa2'],
    ':idu' => $idu
]);

header("Location: activeuser_admin.php?msg=ok");
exit;
