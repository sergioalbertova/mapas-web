<?php
session_start();
require "db.php";

header('Content-Type: application/json; charset=utf-8');

$usuario = $_POST['usuario'] ?? '';
$clave   = $_POST['clave'] ?? '';

if ($usuario === '' || $clave === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Usuario y contraseña son obligatorios"
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, usuario, clave FROM usuarios WHERE usuario = :u LIMIT 1");
    $stmt->execute(['u' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "status" => "error",
            "message" => "Usuario no encontrado"
        ]);
        exit;
    }

    if (!password_verify($clave, $user['clave'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Contraseña incorrecta"
        ]);
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['usuario'] = $user['usuario'];

    echo json_encode([
        "status" => "success"
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error interno del servidor"
    ]);
}
