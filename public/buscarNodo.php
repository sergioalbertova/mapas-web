<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
require "db.php";

$piso = isset($_GET["piso"]) ? intval($_GET["piso"]) : 0;
$nodo = isset($_GET["nodo"]) ? intval($_GET["nodo"]) : 0;
$ubic = isset($_GET["ubicacion"]) ? intval($_GET["ubicacion"]) : 0;

$infoNodo = null;

/* ============================================================
   1) MODO SEGURO: BUSCAR POR PISO + NODO (clave única real)
   ============================================================ */
if ($piso > 0 && $nodo > 0) {

    $sqlNodo = "
        SELECT 
            \"idnodo\",
            piso,
            ubicacion,
            \"switchnombre\",
            \"switchpuerto\",
            \"NumeroNodo\"
        FROM nodos
        WHERE piso = :piso AND \"NumeroNodo\" = :nodo
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sqlNodo);
    $stmt->execute(["piso" => $piso, "nodo" => $nodo]);
    $infoNodo = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ============================================================
   2) MODO ALTERNATIVO: BUSCAR POR PISO + UBICACIÓN
   ============================================================ */
if (!$infoNodo && $piso > 0 && $ubic > 0) {

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
}

/* ============================================================
   3) MODO LEGACY: BUSCAR SOLO POR NODO (NO RECOMENDADO)
   ============================================================ */
if (!$infoNodo && $nodo > 0) {

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

/* ============================================================
   VALIDACIÓN
   ============================================================ */
if (!$infoNodo) {
    echo json_encode([
        "status" => "error",
        "message" => "Nodo no encontrado"
    ]);
    exit;
}

$piso = $infoNodo["piso"];
$ubicacion = intval($infoNodo["ubicacion"]);

/* ============================================================
   COORDENADAS
   ============================================================ */
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

/* ============================================================
   USUARIO
   ============================================================ */
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

/* ============================================================
   RESPUESTA
   ============================================================ */
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
