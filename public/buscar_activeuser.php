<?php
require "db.php";

$q = $_GET['q'] ?? '';

$stmt = $pdo->prepare("
    SELECT idu, nomuser, ubicacion
    FROM activeuser
    WHERE nomuser ILIKE ?
    ORDER BY nomuser
    LIMIT 20
");
$stmt->execute(["%$q%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
