<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
require "db.php";

$piso = isset($_GET["piso"]) ? intval($_GET["piso"]) : 0;

if ($piso <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Piso inválido"
    ]);
    exit;
}

/*
    Tabla ubicacion: piso, ubicacion, cx_rel, cy_rel
    Tabla nodos: piso, ubicacion, NumeroNodo
    Tabla activeusers: piso, ubimapa2, nomuser
*/

$sql = "
    SELECT 
        u.ubicacion,
        u.piso,
        u.cx_rel,
        u.cy_rel,
        n.NumeroNodo AS nodo,
        a.nomuser AS usuario
    FROM ubicacion u
    LEFT JOIN nodos n
        ON n.piso = u.piso
       AND n.ubicacion = u.ubicacion
    LEFT JOIN activeusers a
        ON a.piso = u.piso
       AND a.ubimapa2 = u.ubicacion
    WHERE u.piso = :piso
    ORDER BY u.ubicacion
";

$stmt = $pdo->prepare($sql);
$stmt->execute(["piso" => $piso]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "success",
    "data" => $rows
]);
exit;

