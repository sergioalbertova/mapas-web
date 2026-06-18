<?php

// ==============================
// ✅ CONFIGURACIÓN DE SESIÓN
// ==============================

$lifetime = 8 * 60 * 60;

session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

ini_set('session.gc_maxlifetime', $lifetime);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "db.php";

// ==============================
// ✅ SI YA HAY SESIÓN VÁLIDA
// ==============================

if (isset($_SESSION['user_id']) && isset($_SESSION['nombre'])) {
    return;
}

// ==============================
// ✅ SI NO HAY COOKIE
// ==============================

if (!isset($_COOKIE['remember_token'])) {
    return;
}

$token = $_COOKIE['remember_token'];

// ======================================================
// ✅ 1. BUSCAR EN user_sessions (NUEVO SISTEMA)
// ======================================================

$stmt = $pdo->prepare("
    SELECT us.*, u.nombre, u.rol, u.tema
    FROM user_sessions us
    JOIN usuarios u ON u.id = us.user_id
    WHERE us.token = ?
    AND us.fecha_expira > NOW()
");

$stmt->execute([$token]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['nombre']  = $user['nombre'];
    $_SESSION['rol']     = $user['rol'] ?? null;
    $_SESSION['tema']    = $user['tema'] ?? null;

    $_SESSION['ultimo_movimiento'] = time();

    return;
}

// ======================================================
// ✅ 2. FALLBACK → MÉTODO ANTIGUO (usuarios)
// ======================================================

$stmt = $pdo->prepare("
    SELECT id, nombre, rol, tema
    FROM usuarios
    WHERE remember_token = ?
");

$stmt->execute([$token]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nombre']  = $user['nombre'];
    $_SESSION['rol']     = $user['rol'] ?? null;
    $_SESSION['tema']    = $user['tema'] ?? null;

    $_SESSION['ultimo_movimiento'] = time();

    return;
}
?>