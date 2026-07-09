<?php

require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

if (!isset($_GET['id'])) {

    header("Location: respaldos_usuarios.php");
    exit;

}

$idrespaldo = $_GET['id'];

/* ==========================
   RESPALDO ACTUAL
========================== */

$stmt = $pdo->prepare("
    SELECT *
    FROM respaldos_usuarios
    WHERE idrespaldo = ?
");

$stmt->execute([$idrespaldo]);

$respaldo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$respaldo) {

    header("Location: respaldos_usuarios.php");
    exit;

}

/* ==========================
   DISCOS
========================== */

$stmt = $pdo->query("
    SELECT
        d.iddisco,
        d.nombre,
        d.tamano_total_gb,

        COALESCE(
            SUM(r.tamano_gb),
            0
        ) AS utilizado

    FROM discos_respaldo d

    LEFT JOIN respaldos_usuarios r
        ON r.iddisco = d.iddisco

    GROUP BY
        d.iddisco,
        d.nombre,
        d.tamano_total_gb

    ORDER BY d.nombre
");

$discos = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Detalle del Respaldo</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --border: #ddd;
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --border: rgba(255,255,255,.15);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    max-width: 700px;
    margin: auto;
    border: 1px solid var(--border);
}

h2 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    opacity: .7;
    margin-bottom: 30px;
}

.label {
    font-weight: 600;
    margin-top: 15px;
}

.valor {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-top: 5px;
}

.btn-volver {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 18px;
    background: #00AEEF;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}

</style>

</head>

<body>


<form action="respaldos_usuarios_editar_guardar.php" method="POST">
   
<input type="hidden" name="idrespaldo" value="<?= $respaldo['idrespaldo'] ?>">

    <label>Usuario</label>

    <input
        type="text"
        id="buscar_usuario"
        value="<?= htmlspecialchars($respaldo['usuario']) ?>">

    <input
        type="hidden"
        name="usuario"
        id="usuario"
        value="<?= htmlspecialchars($respaldo['usuario']) ?>">

    <div id="resultados_usuarios"></div>

    <label>Disco</label>

    <select>
        name="iddisco"
        id="iddisco"
        required>

        <?php foreach($discos as $d): ?>

            <?php
            $disponible =
                $d['tamano_total_gb']
                - $d['utilizado'];
            ?>

            <option
                value="<?= $d['iddisco'] ?>"
                data-total="<?= $d['tamano_total_gb'] ?>"
                data-utilizado="<?= $d['utilizado'] ?>"
                data-disponible="<?= $disponible ?>"
                <?= $d['iddisco'] == $respaldo['iddisco'] ? 'selected' : '' ?>>

                <?= htmlspecialchars($d['nombre']) ?>

            </option>

        <?php endforeach; ?>

    </select>

    <div
        id="infoDisco"
        class="info-disco">
    </div>

    <label>Tamaño del respaldo (GB)</label>

    <input
        type="number"
        step="0.01"
        min="0"
        name="tamano_gb"
        value="<?= $respaldo['tamano_gb'] ?>"
        required>

    <label>Observaciones</label>

    <textarea
        name="observaciones"><?= htmlspecialchars($respaldo['observaciones']) ?></textarea>

    <button class="btn">
        Guardar cambios
    </button>

    <br><br>

    "
        class="btn-volver">

        ← Volver

    </a>

</form>

<script src="theme.js"></script>

</body>

</html>