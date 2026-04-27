<?php
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$idu             = $data['idu'] ?? null;
$usuario_nombre  = $data['usuario_nombre'] ?? null;
$piso            = $data['piso'] ?? null;
$notas           = $data['notas'] ?? null;

if (!$idu || !$usuario_nombre || !$piso) {
    http_response_code(400);
    echo "Datos incompletos";
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO checklist_revision (idu, usuario_nombre, piso, notas)
    VALUES (?, ?, ?, ?)
");

$stmt->execute([$idu, $usuario_nombre, $piso, $notas]);

echo "OK";
