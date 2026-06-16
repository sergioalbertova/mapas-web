<?php
require "session_config.php";
require "db.php";

if (!isset($_GET['id'])) {
    header("Location: actividades_extras.php");
    exit;
}

$idextra = $_GET['id'];

// Obtener datos completos
$stmt = $pdo->prepare("
    SELECT ae.*, 
           u.nombre AS ingeniero,
           ca.actividad
    FROM actividades_extras ae
    JOIN usuarios u ON u.id = ae.idingeniero
    JOIN catalogo_actividades ca ON ca.idactividad = ae.idactividad
    WHERE ae.idextra = ?
");
$stmt->execute([$idextra]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$extra) {
    header("Location: actividades_extras.php");
    exit;
}

// Formatear fecha
$fecha = date("Y-m-d H:i:s", strtotime($extra['fecha']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ver Actividad Extra</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --shadow: rgba(0,0,0,0.45);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* MAIN */
.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 8px;
    font-weight: 600;
}

.subtitle {
    text-align: center;
    opacity: 0.7;
    margin-bottom: 40px;
    font-size: 15px;
}

/* CARD */
.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px var(--shadow);
    max-width: 650px;
    margin: auto;
    box-sizing: border-box;
}

.label {
    font-weight: 700;
    margin-top: 15px;
}

.valor {
    background: var(--card-bg);
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-top: 5px;
    margin-bottom: 15px;
}

/* BOTÓN VOLVER */
.btn-volver {
    margin-top: 25px;
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
}

</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Detalle de Actividad</h2>
<div class="subtitle">Información completa de la actividad registrada</div>

<div class="form-card">

    <div class="label">Fecha</div>
    <div class="valor"><?= $fecha ?></div>

    <div class="label">Ingeniero</div>
    <div class="valor"><?= htmlspecialchars($extra['ingeniero']) ?></div>

    <div class="label">Actividad</div>
    <div class="valor"><?= htmlspecialchars($extra['actividad']) ?></div>

    <div class="label">Usuario afectado</div>
    <div class="valor"><?= htmlspecialchars($extra['usuario_afectado'] ?? "—") ?></div>

    <div class="label">Equipo</div>
    <div class="valor"><?= htmlspecialchars($extra['equipo'] ?? "—") ?></div>

    <div class="label">Comentarios</div>
    <div class="valor"><?= nl2br(htmlspecialchars($extra['comentarios'] ?? "—")) ?></div>

    <div class="label">Evidencia</div>
    <div class="valor"><?= htmlspecialchars($extra['evidencia'] ?? "—") ?></div>

    <div class="label">Estatus</div>
    <div class="valor"><?= htmlspecialchars($extra['estatus']) ?></div>

    <a href="actividades_extras.php" class="btn-volver">Volver</a>

</div>

</div>

<script src="theme.js"></script>

</body>
</html>
