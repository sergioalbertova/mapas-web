<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
require "db.php";

$usuario = isset($_GET["usuario"]) ? trim($_GET["usuario"]) : "";

if ($usuario === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Debe ingresar un usuario"
    ]);
    exit;
}

/*
    Solución definitiva:
    - Convertir piso y ubimapa2 SIEMPRE a texto
    - Validar con regex sobre TEXTO
    - Comparar nodos como TEXTO
    - Nunca castear a entero
*/

$sql = "
    SELECT 
        a.idu,
        a.nomuser,
        a.piso,
        a.ubimapa2 AS ubicacion,
        n.idnodo AS nodo
    FROM activeuser a
    LEFT JOIN nodos n
        ON (a.piso)::text ~ '^[0-9]+$'
       AND (a.ubimapa2)::text ~ '^[0-9]+$'
       AND (n.piso)::text = (a.piso)::text
       AND (n.ubicacion)::text = (a.ubimapa2)::text
    WHERE a.nomuser ILIKE :usuario
    ORDER BY a.nomuser
";

$stmt = $pdo->prepare($sql);
$stmt->execute(["usuario" => "%$usuario%"]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$usuarios) {
    echo json_encode([
        "status" => "error",
        "message" => "No se encontraron usuarios"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "data" => $usuarios
]);
exit;
