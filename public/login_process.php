<?php
session_start();
require "db.php";

// Validar que vengan datos
if (!isset($_POST['usuario'], $_POST['clave'])) {
    header("Location: login.php?error=1");
    exit;
}

$usuario = trim($_POST['usuario']);
$clave   = trim($_POST['clave']);

// Buscar usuario
$stmt = $pdo->prepare("SELECT id, usuario, clave FROM usuarios WHERE usuario = ?");

$stmt = $pdo->prepare("
    SELECT id, usuario, clave, rol
    FROM usuarios 
    WHERE LOWER(usuario) = LOWER(:u)
");
$stmt->execute([':u' => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Validar usuario y contraseña
if (!$user || !password_verify($clave, $user['clave'])) {
    header("Location: login.php?error=1");
    exit;
}

// Crear sesión
$_SESSION['user_id'] = $user['id'];
$_SESSION['rol'] = $user['rol'];


// ===============================
//  REMEMBER ME (si está marcado)
// ===============================
if (!empty($_POST['remember'])) {

    // Token seguro
    $token = bin2hex(random_bytes(32));

    // Guardarlo en BD
    $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
    $stmt->execute([$token, $user['id']]);

    // Guardarlo en cookie (30 días)
    setcookie(
        "remember_token",
        $token,
        time() + (86400 * 30),
        "/",
        "",
        false,   // HTTPS true si tienes SSL
        true     // HttpOnly
    );
}

// Redirigir al panel
header("Location: index.php");
exit;
?>
