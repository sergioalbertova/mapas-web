<?php

require "auth.php";
require "db.php";

$q = trim($_GET['q'] ?? '');

if ($q === '') {

    echo json_encode([]);
    exit;

}

$stmt = $pdo->prepare("
    SELECT DISTINCT usuario

    FROM respaldos_usuarios

    WHERE usuario ILIKE ?

    ORDER BY usuario

    LIMIT 15
");

$stmt->execute([
    "%{$q}%"
]);

$resultados =
    $stmt->fetchAll(
        PDO::FETCH_COLUMN
    );

header('Content-Type: application/json');

echo json_encode(
    $resultados
);