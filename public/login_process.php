<?php
session_start();
require "db.php";

// ==========================
// ✅ VALIDACIÓN DE DATOS
// ==========================
if (!isset($_POST['usuario'], $_POST['clave'])) {
    header("Location: login.php?error=1");
    exit;
}

$usuario = trim($_POST['usuario']);
$clave   = trim($_POST['clave']);

// ==========================
// ✅ BUSCAR USUARIO
// ==========================
$stmt = $pdo->prepare("
    SELECT id, usuario, clave, rol, nombre
    FROM usuarios 
    WHERE LOWER(usuario) = LOWER(:u)
");

$stmt->execute([':u' => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ==========================
// ✅ VALIDAR CREDENCIALES
// ==========================
if (!$user || !password_verify($clave, $user['clave'])) {
    header("Location: login.php?error=1");
    exit;
}

// ==========================
// ✅ CREAR SESIÓN NORMAL
// ==========================
$_SESSION['user_id'] = $user['id'];
$_SESSION['rol']     = $user['rol'];
$_SESSION['nombre']  = $user['nombre'] ?? null;
$_SESSION['ultimo_movimiento'] = time();

// ==========================
// ✅ NUEVO SISTEMA PRO (user_sessions)
// ==========================

// Token de sesión único (por dispositivo)
$sessionToken = bin2hex(random_bytes(32));

// Datos del cliente
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Expiración (7 días)
$fechaExpira = date('Y-m-d H:i:s', time() + (86400 * 7));

// Guardar en user_sessions
$stmt = $pdo->prepare("
    INSERT INTO user_sessions 
    (user_id, token, ip, user_agent, fecha_expira)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $user['id'],
    $sessionToken,
    $ip,
    $userAgent,
    $fechaExpira
]);

// ==========================
// ✅ REMEMBER ME
// ==========================
if (!empty($_POST['remember'])) {

    // 👉 Guardamos también en usuarios (compatibilidad)
    $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
    $stmt->execute([$sessionToken, $user['id']]);

    // 👉 Cookie (7 días)
    setcookie(
        "remember_token",
        $sessionToken,
        time() + (86400 * 7),
        "/",
        "",
        false, // true si usas HTTPS
        true   // HttpOnly
    );
}

// ==========================
// ✅ REDIRECCIÓN FINAL
// ==========================
header("Location: index.php");
exit;
?>
``