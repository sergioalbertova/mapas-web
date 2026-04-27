<?php
require "db.php";

$q = $_GET['q'] ?? '';
$q = trim($q);

if ($q === '') {
    echo json_encode([]);
    return;
}

$stmt = $pdo->prepare("
    SELECT idu, nomuser
    FROM activeuser
    WHERE nomuser ILIKE ?
    ORDER BY nomuser
    LIMIT 20
");
$stmt->execute(["%$q%"]);

$result = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $result[] = [
        "idu" => $row["idu"],
        "usuario_nombre" => $row["nomuser"]
    ];
}

echo json_encode($result);
