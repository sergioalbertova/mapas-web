<?php
require "db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Pisos</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        #imgMapa { max-width: 600px; border: 1px solid #ccc; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>

<h2>Dashboard de Pisos</h2>

<!-- SELECT DE PISOS -->
<label for="selectPiso">Seleccionar piso:</label>
<select id="selectPiso">
    <option value="">Seleccione un piso</option>
    <?php
    $pisos = $pdo->query("SELECT idpiso, nombrepiso FROM pisos ORDER BY idpiso")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pisos as $p) {
        echo "<option value='{$p['idpiso']}'>Piso {$p['nombrepiso']}</option>";
    }
    ?>
</select>

<!-- MAPA -->
<img id="imgMapa" src="" alt="Mapa del piso">

<!-- TABLA DE UBICACIONES -->
<table id="tablaUbicaciones">
    <thead>
