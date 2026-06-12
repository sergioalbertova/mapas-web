<?php
require "db.php";

$piso = $_GET['piso'];

$stmt = $pdo->prepare("SELECT idubicacion, ubicacion FROM ubicacion WHERE piso = ? order by ubicacion");
$stmt->execute([$piso]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
