<?php
require "db.php";

$q = $_GET['q'] ?? '';

$sql = "
    SELECT *
    FROM activeuser
    WHERE 
        nomuser ILIKE ? OR
        ubicacion ILIKE ? OR
        hor1 ILIKE ? OR
        hor2 ILIKE ?
    ORDER BY nomuser ASC
";

$stmt = $pdo->prepare($sql);

$param = "%$q%";

$stmt->execute([$param, $param, $param, $param]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);
