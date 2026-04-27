<?php
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$idu     = $data['idu']     ?? null;
$nomuser = $data['nomuser'] ?? null;
$piso    = $data['piso']    ?? null;
$notas   = $data['notas']   ?? null;

if (!$idu || !$nomuser || !$piso) {
    http_response_code(400);
    echo "Datos incompletos";
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO checklist_revision (idu, nomuser, piso, notas)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$idu, $nomuser, $piso, $notas]);

echo "OK";
