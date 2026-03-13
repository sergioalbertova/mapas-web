<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "ID no recibido"]);
    exit;
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT 
        nomuser,
        ubicacion,
        piso,
        ubimapa2,
        hor1,
        observaciones
    FROM activeuser
    WHERE idu = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo json_encode(["error" => "Usuario no encontrado"]);
    exit;
}

echo json_encode($data);
?>
