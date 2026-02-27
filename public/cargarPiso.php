<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

require "db.php";

if (!isset($_GET["idpiso"])) {
    echo json_encode([
        "status" => "error",
        "message" => "idpiso requerido"
    ]);
    exit;
}

$idpiso = intval($_GET["idpiso"]);

$stmt = $pdo->prepare("SELECT imagenmapa FROM pisos WHERE idpiso = :idpiso LIMIT 1");
$stmt->execute(["idpiso" => $idpiso]);
$piso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$piso) {
    echo json_encode([
        "status" => "error",
        "message" => "Piso no encontrado"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "imagen" => $piso["imagenmapa"]
]);
exit;
