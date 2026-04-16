<?php
require "db.php"; // tu conexión PDO

// Ruta del CSV (debe estar en la misma carpeta que este archivo)
$csvFile = "activeuserabril.csv";

// 1. Cargar usuarios existentes
$stmt = $pdo->query("SELECT nomuser FROM activeuser");
$existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
$existentes = array_map('trim', $existentes);

// 2. Preparar inserción
$insert = $pdo->prepare("
    INSERT INTO activeuser (nomuser, ubicacion, hor1)
    VALUES (?, ?, ?)
");

$nuevos = 0;

if (($handle = fopen($csvFile, "r")) !== false) {

    fgetcsv($handle); // Saltar encabezado

    while (($row = fgetcsv($handle)) !== false) {

        $nomuser   = trim($row[0]);
        $ubicacion = trim($row[1]);
        $hor1      = trim($row[2]);

        // Si no existe → insertar
        if (!in_array($nomuser, $existentes)) {
            $insert->execute([$nomuser, $ubicacion, $hor1]);
            $nuevos++;
        }
    }

    fclose($handle);
}

echo "<h2>Importación completada</h2>";
echo "<p>Nuevos usuarios insertados: <strong>$nuevos</strong></p>";
?>
