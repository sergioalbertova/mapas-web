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
    - ubimapa2 puede contener texto no numérico.
    - Usamos regexp para validar si es número.
    - No usamos unaccent() para evitar errores.
*/

$sql = "
    SELECT 
        a.nomuser,
        a.piso,
        CASE 
            WHEN a.ubimapa2 ~ '^[0-9]+$' THEN a.ubimapa2::int
            ELSE NULL
        END AS ubicacion,
        n.\"NumeroNodo\" AS nodo
    FROM activeuser a
    LEFT JOIN nodos n 
        ON n.piso = a.piso 
        AND n.ubicacion::int = 
            CASE 
                WHEN a.ubimapa2 ~ '^[0-9]+$' THEN a.ubimapa2::int
                ELSE -1
            END
    WHERE LOWER(a.nomuser) LIKE LOWER(:usuario)
    ORDER BY a.piso, ubicacion
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
