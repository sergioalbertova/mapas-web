<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
require "db.php";

$nodo = isset($_GET["nodo"]) ? intval($_GET["nodo"]) : 0;
$piso = isset($_GET["piso"]) ? intval($_GET["piso"]) : 0;
$ubic = isset($_GET["ubicacion"]) ? intval($_GET["ubicacion"]) : 0;

/*
    MODO 1: BÚSQUEDA SEGURA (piso + ubicación)
    Este modo se usa cuando haces clic en una fila del dashboard.
*/
if ($piso > 0 && $ubic > 0) {

    $sqlNodo = "
        SELECT 
            \"idnodo\",
            piso,
            ubicacion,
            \"switchnombre\",
            \"switchpuerto\",
            \"NumeroNodo\"
        FROM nodos
        WHERE piso = :piso AND ubicacion::int = :ubic
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sqlNodo);
    $stmt->execute(["piso" => $piso, "ubic" => $ubic]);
    $infoNodo = $stmt->fetch(PDO::FETCH_ASSOC);

} else {

    /*
        MODO 2: BÚSQUEDA POR NÚMERO DE NODO
        (solo si no se envió piso + ubicación)
    */
    if ($nodo <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Nodo inválido"
        ]);
        exit;
    }

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
}

if (!$infoNodo) {
    echo json_encode([
        "status" => "error",
        "message" => "Nodo no encontrado"
    ]);
    exit;
}

$piso = $infoNodo["piso"];
$ubicacion = intval($infoNodo["ubicacion"]);

/* Coordenadas */
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

/* Usuario asignado */
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
        "nodo"      => $infoNodo["NumeroNodo"],
        "piso"      => $piso,
        "ubicacion" => $ubicacion,
        "switch"    => $infoNodo["switchnombre"],
        "puerto"    => $infoNodo["switchpuerto"],
        "cx_rel"    => $coord["cx_rel"] ?? null,
        "cy_rel"    => $coord["cy_rel"] ?? null,
        "usuario"   => $user["nomuser"] ?? null
    ]
]);
exit;
