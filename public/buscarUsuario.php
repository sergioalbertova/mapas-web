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
    IMPORTANTE:
    - activeuser.nomuser = nombre del usuario
    - activeuser.ubimapa2 = ubicación (texto → convertir a entero)
    - activeuser.piso = piso
    - nodos.ubicacion = ubicación (texto)
    - nodos.NumeroNodo = nodo
*/

$sql = "
    SELECT 
        a.nomuser,
        a.piso,
        a.ubimapa2::int AS ubicacion,
        n.\"NumeroNodo\" AS nodo
    FROM activeuser a
    LEFT JOIN nodos n 
        ON n.piso = a.piso 
        AND n.ubicacion::int = a.ubimapa2::int
    WHERE LOWER(a.nomuser) LIKE LOWER(:usuario)
    ORDER BY a.piso, a.ubimapa2::int
";

$stmt = $pdo->prepare($sql);
$stmt->execute(["usuario" => "%$usuario%"]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$data) {
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
