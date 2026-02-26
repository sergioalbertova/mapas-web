<?php
require "db.php";

$dimensiones = [
    1 => ["w" => 2325, "h" => 1008],
    2 => ["w" => 1920, "h" => 996],
    3 => ["w" => 1920, "h" => 876],
    4 => ["w" => 1920, "h" => 890],
    5 => ["w" => 1920, "h" => 723]
];

$ubicaciones = $pdo->query("SELECT idubicacion, piso, cx, cy FROM ubicacion")->fetchAll(PDO::FETCH_ASSOC);

foreach ($ubicaciones as $u) {
    $piso = $u["piso"];
    $w = $dimensiones[$piso]["w"];
    $h = $dimensiones[$piso]["h"];

    $cx_rel = $u["cx"] / $w;
    $cy_rel = $u["cy"] / $h;

    $stmt = $pdo->prepare("UPDATE ubicacion SET cx_rel = :cxr, cy_rel = :cyr WHERE idubicacion = :id");
    $stmt->execute([
        "cxr" => $cx_rel,
        "cyr" => $cy_rel,
        "id" => $u["idubicacion"]
    ]);
}

echo "Coordenadas relativas generadas correctamente.";
