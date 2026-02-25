<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "db.php";

$input = json_decode(file_get_contents("php://input"), true);

$usuario = $input["usuario"] ?? "";
$clave = $input["clave"] ?? "";

if ($usuario === "" || $clave === "") {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND clave = :clave");
    $stmt->bindParam(":usuario", $usuario);
    $stmt->bindParam(":clave", $clave);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Login correcto"]);
    } else {
        echo json_encode(["success" => false, "message" => "Usuario o clave incorrectos"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error en el servidor"]);
}
