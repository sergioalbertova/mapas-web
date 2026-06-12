<?php
require "db.php";

$piso = $_GET['piso'];

$stmt = $pdo->prepare("SELECT idubicacion, ubicacion FROM ubicacion WHERE piso = ?");
$stmt->execute([$piso]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
