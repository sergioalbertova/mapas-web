<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    exit;
}

$usuario = trim($_POST["usuario"] ?? "");
$clave = trim($_POST["clave"] ?? "");

if ($usuario === "" || $clave === "") {
    echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, nombre, clave FROM usuarios WHERE usuario = :usuario LIMIT 1");
$stmt->execute(["usuario" => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit;
}

if ($clave !== $user["clave"]) {
    echo json_encode(["status" => "error", "message" => "Contraseña incorrecta"]);
    exit;
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["nombre"];

echo json_encode(["status" => "success"]);
exit;
