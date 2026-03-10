<?php
require "db.php";

$stmt = $pdo->query("SELECT id, clave FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $u) {
    $hash = password_hash($u['clave'], PASSWORD_DEFAULT);

    $update = $pdo->prepare("UPDATE usuarios SET clave = :c WHERE id = :id");
    $update->execute([
        'c' => $hash,
        'id' => $u['id']
    ]);
}

echo "Contraseñas convertidas correctamente.";
