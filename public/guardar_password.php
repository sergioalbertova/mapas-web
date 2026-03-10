<?php
session_start();
require "db.php";

$id = $_SESSION['user_id'];

$actual    = $_POST['actual'];
$nueva     = $_POST['nueva'];
$confirmar = $_POST['confirmar'];

if ($nueva !== $confirmar) {
    die("Las contraseñas nuevas no coinciden");
}

$stmt = $pdo->prepare("SELECT clave FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!password_verify($actual, $user['clave'])) {
    die("La contraseña actual es incorrecta");
}

$nuevaHash = password_hash($nueva, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE usuarios SET clave = :c WHERE id = :id");
$stmt->execute(['c' => $nuevaHash, 'id' => $id]);

echo "Contraseña actualizada correctamente";
