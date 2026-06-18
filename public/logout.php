<?php
session_start();
require "db.php";

// ✅ Si existe cookie → eliminar sesión en user_sessions
if (isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    // 🔥 borrar sesión específica (este dispositivo)
    $stmt = $pdo->prepare("
        DELETE FROM user_sessions 
        WHERE token = ?
    ");
    $stmt->execute([$token]);
}

// ✅ Fallback: limpiar campo antiguo (opcional pero seguro)
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        UPDATE usuarios 
        SET remember_token = NULL 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
}

// ✅ eliminar cookie
setcookie("remember_token", "", time() - 3600, "/");

// ✅ destruir sesión PHP
session_unset();
session_destroy();

// ✅ redirigir
header("Location: login.php");
exit;
?>
