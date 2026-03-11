<?php
require "db.php";

$q = $_GET['q'] ?? '';

$sql = $pdo->prepare("
    SELECT *
    FROM IncidentesTI
    WHERE 
        categoria LIKE ? OR
        subcategoria LIKE ? OR
        Desbreve LIKE ? OR
        Tema LIKE ? OR
        Descdetallada LIKE ? OR
        Solucion LIKE ?
");
$like = "%$q%";
$sql->execute([$like,$like,$like,$like,$like,$like]);

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
