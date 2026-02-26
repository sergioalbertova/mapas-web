<?php
session_start();
require "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($email === "" || $password === "") {
        echo json_encode(["status" => "error", "message" => "Todos los campos son obligatorios"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
        exit;
    }

    if (!password_verify($password, $user["password"])) {
        echo json_encode(["status" => "error", "message" => "Contraseña incorrecta"]);
        exit;
    }

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_name"] = $user["nombre"];

    echo json_encode(["status" => "success"]);
}
