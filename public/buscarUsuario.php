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
    - activeuser.piso puede ser "ND", NULL, texto, etc.
    - nodos.piso sí es entero
    - Por lo tanto:
        * NO convertimos a.piso a entero
        * Convertimos n.piso a texto para comparar
*/

$sql = "
    SELECT 
        a.nomuser,
        a.piso AS piso,              -- SIN CAST
        a.ubimapa2 AS ubicacion,     -- SIN CAST
        n.\"NumeroNodo\" AS nodo
    FROM activeuser a
    LEFT JOIN nodos n 
        ON n.piso::text = a.piso     -- COMPARACIÓN TEXTO-TEXTO
        AND n.ubicacion = a.ubimapa2 -- TEXTO-TEXTO
    WHERE LOWER(a.nomuser) LIKE LOWER(:usuario)
    ORDER BY a.piso, a.ubimapa2
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
