<?php
session_start();
require "db.php";

$id = $_SESSION['user_id'];

$actual    = $_POST['actual'];
$nueva     = $_POST['nueva'];
$confirmar = $_POST['confirmar'];

if ($nueva !== $confirmar) {
    echo "<script>alert('Las contraseñas nuevas no coinciden'); window.history.back();</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT clave FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!password_verify($actual, $user['clave'])) {
    echo "<script>alert('La contraseña actual es incorrecta'); window.history.back();</script>";
    exit;
}

$nuevaHash = password_hash($nueva, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE usuarios SET clave = :c WHERE id = :id");
$stmt->execute(['c' => $nuevaHash, 'id' => $id]);

echo "<script>
alert('Contraseña actualizada correctamente. Inicia sesión nuevamente.');
window.location.href = 'login.php';
</script>";
