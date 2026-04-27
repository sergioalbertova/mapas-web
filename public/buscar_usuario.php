<?php
require "db.php";

$q = $_GET['q'] ?? '';

$stmt = $pdo->prepare("
    SELECT idu, nomuser 
    FROM activeuser 
    WHERE nomuser ILIKE ?
    ORDER BY nomuser
");
$stmt->execute(["%$q%"]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
