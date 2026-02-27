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
    Flujo correcto:
    1. Buscar usuario en activeuser
    2. Validar que piso sea numérico
    3. Obtener coordenadas desde ubicacion
    4. Obtener nodo desde nodos (ubicacion::int)
*/

$sqlUser = "
    SELECT 
        idu,
        nomuser,
        piso,
        ubimapa2 AS ubicacion,
        switch,
        puerto
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

/* Validar piso numérico */
if (!preg_match('/^[0-9]+$/', $infoUser["piso"])) {
    echo json_encode([
        "status" => "error",
        "message" => "El usuario tiene un piso no válido (ND)"
    ]);
    exit;
}

$piso = intval($infoUser["piso"]);
$ubicacion = intval($infoUser["ubicacion"]);

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
    SELECT idnodo
    FROM nodos
    WHERE piso = :piso AND ubicacion::int = :ubicacion
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
        "nodo" => $nodo["idnodo"] ?? null,
        "switch" => $infoUser["switch"] ?? null,
        "puerto" => $infoUser["puerto"] ?? null
    ]
]);
exit;
