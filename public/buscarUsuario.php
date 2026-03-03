<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
require "db.php";

$usuario = isset($_GET["usuario"]) ? trim($_GET["usuario"]) : "";

if ($usuario === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Usuario vacío"
    ]);
    exit;
}

/*
    NOTAS IMPORTANTES:
    - ubimapa2 es TEXTO (puede ser "ND", "45", "A1", etc.)
    - nodos.ubicacion también es TEXTO
    - NO se convierte nada a entero
    - El JOIN se hace por TEXTO EXACTO
*/

$sql = "
    SELECT 
        a.nomuser,
        a.piso::int AS piso,
        a.ubimapa2 AS ubicacion,
        n.\"NumeroNodo\" AS nodo
    FROM activeuser a
    LEFT JOIN nodos n 
        ON n.piso::int = a.piso::int
        AND n.ubicacion = a.ubimapa2
    WHERE LOWER(a.nomuser) LIKE LOWER(:usuario)
    ORDER BY a.piso::int, a.ubimapa2
";

$stmt = $pdo->prepare($sql);
$stmt->execute(["usuario" => "%$usuario%"]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$data || count($data) === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se encontraron usuarios"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);
exit;
