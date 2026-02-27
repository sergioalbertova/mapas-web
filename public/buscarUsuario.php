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
    Flujo:
    1. Buscar usuario en activeusers
    2. Obtener piso y ubimapa2 (ubicación)
    3. Obtener coordenadas relativas desde ubicacion
    4. Buscar nodo asignado en nodos (piso + ubicacion)
*/

$sqlUser = "
    SELECT 
        idu,
        nomuser,
        piso,
        ubimapa2 AS ubicacion
    FROM activeuser
    WHERE nomuser ILIKE :usuario
    LIMIT 1
";

$stmt = $pdo->prepare($sqlUser);
$stmt->execute(["usuario" => "%$usuario%"]);
$infoUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$infoUser) {
    echo json_encode([
        "status" => "error",
        "message" => "Usuario no encontrado"
    ]);
    exit;
}

$piso = $infoUser["piso"];
$ubicacion = $infoUser["ubicacion"];

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

/* 3. Buscar nodo asignado */
$sqlNodo = "
    SELECT NumeroNodo, switchnombre, switchpuerto
    FROM nodos
    WHERE piso = :piso AND ubicacion = :ubicacion
    LIMIT 1
";

$stmt = $pdo->prepare($sqlNodo);
$stmt->execute([
    "piso" => $piso,
    "ubicacion" => $ubicacion
]);
$nodo = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "success",
    "data" => [
        "usuario" => $infoUser["nomuser"],
        "piso" => $piso,
        "ubicacion" => $ubicacion,
        "cx_rel" => $coord["cx_rel"],
        "cy_rel" => $coord["cy_rel"],
        "nodo" => $nodo["NumeroNodo"] ?? null,
        "switch" => $nodo["switchnombre"] ?? null,
        "puerto" => $nodo["switchpuerto"] ?? null
    ]
]);
exit;
