<?php
session_start();
require "db.php";

// Si ya hay sesión → continuar
if (isset($_SESSION['user_id'])) {
    return;
}

// Si no hay cookie → no hacer nada
if (!isset($_COOKIE['remember_token'])) {
    return;
}

$token = $_COOKIE['remember_token'];

// Buscar usuario con ese token
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE remember_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {

    // Crear sesión automáticamente
    $_SESSION['user_id'] = $user['id'];

    // Regenerar token para seguridad
    $newToken = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
    $stmt->execute([$newToken, $user['id']]);

    // Actualizar cookie
    setcookie(
        "remember_token",
        $newToken,
        time() + (86400 * 30),
        "/",
        "",
        false,
        true
    );
}
?>
