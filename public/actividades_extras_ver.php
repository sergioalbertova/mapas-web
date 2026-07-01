<?php
require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

if (!isset($_GET['id'])) {
    header("Location: actividades_extras.php");
    exit;
}

$idextra = $_GET['id'];

// ✅ Obtener datos completos
$stmt = $pdo->prepare("
    SELECT ae.*, 
           u.nombre AS ingeniero,
           ca.actividad,

           EXTRACT(EPOCH FROM (ae.fecha_fin - ae.fecha_inicio))/60 AS duracion_min
           
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

// ✅ Formatos
$inicio = $extra['fecha_inicio'] ? date("Y-m-d H:i:s", strtotime($extra['fecha_inicio'])) : "-";
$fin = $extra['fecha_fin'] ? date("Y-m-d H:i:s", strtotime($extra['fecha_fin'])) : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ver Actividad Extra</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

/* ✅ VARIABLES PARA DARK MODE */
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
    --border: rgba(255,255,255,0.15);
}

/* BASE */
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

/* TITULOS */
h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    opacity: 0.7;
    margin-bottom: 30px;
}

/* CARD */
.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    max-width: 700px;
    margin: auto;
    border: 1px solid var(--border);
}

/* CAMPOS */
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

/* BADGES */
.badge {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    white-space: nowrap;
}

.en-proceso {
    background: #f59e0b;
    color: white;
}

.completo {
    background: #10b981;
    color: white;
}

/* BOTÓN */
.btn-volver {
    display: inline-block;
    margin-top: 25px;
    padding: 12px 18px;
    background: #00AEEF;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}

</style>
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Detalle de Actividad</h2>
<div class="subtitle">Información completa de la actividad</div>

<div class="form-card">

    <div class="label">Inicio</div>
    <div class="valor"><?= $inicio ?></div>

    <div class="label">Fin</div>
    <div class="valor">
        <?php if ($fin): ?>
            <?= $fin ?>
        <?php else: ?>
            <span class="badge en-proceso">En proceso</span>
        <?php endif; ?>
    </div>

    <div class="label">Duración</div>
    <div class="valor">
        <?php
        if ($extra['fecha_fin']) {
            echo "⏱ " . round($extra['duracion_min'],1) . " min";
        } else {
            echo "⏳ En curso";
        }
        ?>
    </div>

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

<div class="valor">

<?php if (!empty($extra['evidencia'])): ?>

    <a href="<?= htmlspecialchars($extra['evidencia']) ?>"
       target="_blank">

        <img
            src="<?= htmlspecialchars($extra['evidencia']) ?>"
            alt="Evidencia"
            style="
                max-width:100%;
                max-height:400px;
                border-radius:8px;
                cursor:pointer;
            ">
    </a>

    <br><br>

    <a href="<?= htmlspecialchars($extra['evidencia']) ?>"
       target="_blank">
       Abrir imagen completa
    </a>

<?php else: ?>

    Sin evidencia adjunta

<?php endif; ?>

</div>

    <div class="label">Estatus</div>
    <div class="valor">
        <?php if ($extra['estatus'] === 'completado'): ?>
            <span class="badge completo">Completado</span>
        <?php else: ?>
            <span class="badge en-proceso">En proceso</span>
        <?php endif; ?>
    </div>

    <a href="actividades_extras.php" class="btn-volver">Volver</a>

</div>

</div>

<script src="theme.js"></script>

</body>
</html>