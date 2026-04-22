<?php
require "session_config.php";
require "db.php";

$id        = intval($_POST['id']);
$apoyo     = $_POST['apoyo'];
$categoria = $_POST['categoria'] ?: null;
$prioridad = $_POST['prioridad'];
$activo    = intval($_POST['activo']);

$sql = "UPDATE itil_catalogo 
        SET apoyo = :apoyo,
            categoria = :categoria,
            prioridad = :prioridad,
            activo = :activo
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':apoyo'     => $apoyo,
    ':categoria' => $categoria,
    ':prioridad' => $prioridad,
    ':activo'    => $activo,
    ':id'        => $id
]);

header("Location: itil_catalogo.php?msg=ok");
exit;
