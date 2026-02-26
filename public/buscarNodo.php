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
    Flujo:
    1. Buscar nodo en tabla nodos
    2. Obtener piso, ubicacion, switch, puerto
    3. Buscar coordenadas relativas en tabla ubicacion
    4. Buscar usuario asignado en activeusers (piso + ubimapa2)
*/

$sqlNodo = "
    SELECT 
        IdNodo,
        piso,
        ubicacion,
        SwitchNombre,
        SwitchPuerto,
        NumeroNodo
    FROM nodos
    WHERE NumeroNodo = :nodo
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
$ubicacion = $infoNodo["ubicacion"];

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
    WHERE piso = :piso AND ubimapa2 = :ubicacion
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
        "switch" => $infoNodo["SwitchNombre"],
        "puerto" => $infoNodo["SwitchPuerto"],
        "cx_rel" => $coord["cx_rel"],
        "cy_rel" => $coord["cy_rel"],
        "usuario" => $user["nomuser"] ?? null
    ]
]);
exit;
