<?php
require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$idu            = $data['idu'] ?? null;
$usuario_nombre = $data['usuario_nombre'] ?? null;
$piso           = $data['piso'] ?? null;
$notas          = $data['notas'] ?? null;

$fondo = $data['fondo'] ?? false;
$correo = $data['correo'] ?? false;
$teams = $data['teams'] ?? false;

$stmt = $pdo->prepare("
    INSERT INTO checklist_revision (idu, usuario_nombre, piso, notas, fondo, correo, teams)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $idu,
    $usuario_nombre,
    $piso,
    $notas,
    $fondo,
    $correo,
    $teams
]);

echo "OK";
