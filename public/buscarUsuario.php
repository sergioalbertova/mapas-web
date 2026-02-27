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
    Ahora devolvemos TODOS los usuarios que coincidan
*/

$sqlUser = "
    SELECT 
        idu,
        nomuser,
        piso,
        ubimapa2 AS ubicacion
    FROM activeuser
    WHERE nomuser ILIKE :usuario
    ORDER BY nomuser
";

$stmt = $pdo->prepare($sqlUser);
$stmt->execute(["usuario" => "%$usuario%"]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$usuarios) {
    echo json_encode([
        "status" => "error",
        "message" => "No se encontraron usuarios"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "data" => $usuarios
]);
exit;
