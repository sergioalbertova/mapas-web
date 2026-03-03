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
    - NO convertimos ubimapa2 a entero dentro del CASE.
    - Primero lo convertimos a texto SIEMPRE.
    - Luego verificamos si es numérico usando regexp.
    - Si es numérico → lo convertimos a entero.
    - Si NO es numérico → devolvemos NULL.
*/

$sql = "
    WITH datos AS (
        SELECT 
            a.nomuser,
            a.piso::int AS piso,
            (a.ubimapa2)::text AS ubi_texto
        FROM activeuser a
        WHERE LOWER(a.nomuser) LIKE LOWER(:usuario)
    )
    SELECT 
        d.nomuser,
        d.piso,
        CASE 
            WHEN d.ubi_texto ~ '^[0-9]+$' THEN d.ubi_texto::int
            ELSE NULL
        END AS ubicacion,
        n.\"NumeroNodo\" AS nodo
    FROM datos d
    LEFT JOIN nodos n 
        ON n.piso::int = d.piso
        AND n.ubicacion::int = 
            CASE 
                WHEN d.ubi_texto ~ '^[0-9]+$' THEN d.ubi_texto::int
                ELSE -9999
            END
    ORDER BY d.piso, ubicacion NULLS LAST
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
