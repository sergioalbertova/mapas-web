<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
require "db.php";

$nodo = isset($_GET["nodo"]) ? intval($_GET["nodo"]) : 0;

if ($nodo <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Nodo inválido"
    ]);
    exit;
}

/*
    IMPORTANTE:
    - nodos.ubicacion es TEXT → se debe castear a entero
    - ubicacion.ubicacion es INTEGER
    - activeuser.ubimapa2 es TEXT → se debe castear a entero
*/

$sqlNodo = "
    SELECT 
        \"idnodo\",
        piso,
        ubicacion,
        \"switchnombre\",
        \"switchpuerto\",
        \"NumeroNodo\"
    FROM nodos
    WHERE \"NumeroNodo\" = :nodo
    LIMIT 1
";

$stmt = $pdo->prepare($sqlNodo);
$stmt->execute(["nodo" => $nodo]);
$infoNodo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$infoNodo) {
    echo json_encode([
        "status" => "error",
        "message" => "Nodo no encontrado"
    ]);
    exit;
}

$piso = $infoNodo["piso"];
$ubicacion = intval($infoNodo["ubicacion"]); // ← CAST A ENTERO

/* 2. Coordenadas relativas */
$sqlCoord = "
    SELECT cx_rel, cy_rel
    FROM ubicacion
    WHERE piso = :piso AND ubicacion = :ubicacion
    LIMIT 1
";

$stmt = $pdo->prepare($sqlCoord);
$stmt->execute([
    "piso" => $piso,
    "ubicacion" => $ubicacion
]);
$coord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coord) {
    echo json_encode([
        "status" => "error",
        "message" => "No hay coordenadas para esta ubicación"
    ]);
    exit;
}

/* 3. Usuario asignado */
$sqlUser = "
    SELECT nomuser
    FROM activeuser
    WHERE piso = :piso AND ubimapa2::int = :ubicacion
    LIMIT 1
";

$stmt = $pdo->prepare($sqlUser);
$stmt->execute([
    "piso" => $piso,
    "ubicacion" => $ubicacion
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "success",
    "data" => [
        "nodo" => $infoNodo["NumeroNodo"],
        "piso" => $piso,
        "ubicacion" => $ubicacion,
        "switch" => $infoNodo["switchnombre"],
        "puerto" => $infoNodo["switchpuerto"],
        "cx_rel" => $coord["cx_rel"],
        "cy_rel" => $coord["cy_rel"],
        "usuario" => $user["nomuser"] ?? null
    ]
]);
exit;
