<?php
require "db.php";

$id = $_POST['idubicacion'];
$xm = $_POST['xm'];
$ym = $_POST['ym'];

$stmt = $pdo->prepare("UPDATE ubicacion SET xm = ?, ym = ? WHERE idubicacion = ?");
$stmt->execute([$xm, $ym, $id]);

echo "Coordenadas guardadas correctamente";
