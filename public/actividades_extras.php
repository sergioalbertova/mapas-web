<?php
require "auth.php";
require "db.php";

$id = $_SESSION['user_id'];

// Obtener actividades
$stmt = $pdo->query("
    SELECT 
        ae.idextra,
        ae.fecha,
        ae.fecha_inicio,
        ae.fecha_fin,
        ae.estatus,
        ae.equipo,
        ae.usuario_afectado,

        u.nombre AS ingeniero,
        ca.actividad,

        EXTRACT(EPOCH FROM (ae.fecha_fin - ae.fecha_inicio))/60 AS duracion_min

    FROM actividades_extras ae
    JOIN usuarios u ON u.id = ae.idingeniero
    JOIN catalogo_actividades ca ON ca.idactividad = ae.idactividad
    ORDER BY ae.fecha DESC
");

$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

function safe($v) {
    return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Actividades Extras</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    display: flex;
}

/* MAIN */
.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

/* TABLA */
.tabla {
    width: 100%;
    border-collapse: collapse;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

.tabla th {
    background: linear-gradient(135deg, #00AEEF, #0284c7);
    color: white;
    padding: 14px;
    text-align: left;
    font-size: 14px;
}

.tabla td {
    padding: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    vertical-align: middle;
}

/* HOVER */
.tabla tr:hover {
    background: rgba(255,255,255,0.03);
}

/* COLUMNAS CONTROLADAS */
.tabla td:nth-child(1),
.tabla td:nth-child(2),
.tabla td:nth-child(3) {
    width: 150px;
}

.tabla td:last-child {
    width: 160px;
    white-space: nowrap;
}

/* BADGES */
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
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

/* BOTONES */
.acciones {
    display: flex;
    gap: 6px;
}

.btn {
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 12px;
    text-decoration: none;
    color: white;
}

.ver {
    background: #2563eb;
}

.editar {
    background: #059669;
}

.btn:hover {
    opacity: 0.85;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Actividades Extras</h2>

actividades_extras_nuevo.php" class="btn-nuevo">
    + Nueva actividad
</a>

<table class="tabla">

<tr>
    <th>Inicio</th>
    <th>Fin</th>
    <th>Duración</th>
    <th>Ingeniero</th>
    <th>Actividad</th>
    <th>Usuario</th>
    <th>Equipo</th>
    <th>Estatus</th>
    <th>Acciones</th>
</tr>

<?php foreach ($lista as $row): ?>
<tr>

<td>
    <?= $row['fecha_inicio'] ? substr($row['fecha_inicio'],0,19) : '-' ?>
</td>

<td>
    <?php if ($row['fecha_fin']): ?>
        <?= substr($row['fecha_fin'],0,19) ?>
    <?php else: ?>
        <span class="badge en-proceso">En proceso</span>
    <?php endif; ?>
</td>

<td>
<?php
if ($row['fecha_fin']) {
    echo "⏱ " . round($row['duracion_min'],1) . " min";
} else {
    echo '<span style="opacity:0.6">⏳</span>';
}
?>
</td>

<td><?= safe($row['ingeniero']) ?></td>
<td><?= safe($row['actividad']) ?></td>
<td><?= safe($row['usuario_afectado']) ?></td>
<td><?= safe($row['equipo']) ?></td>

<td>
<?php if ($row['estatus'] === 'completado'): ?>
    <span class="badge completo">Completado</span>
<?php else: ?>
    <span class="badge en-proceso">En proceso</span>
<?php endif; ?>
</td>

<td class="acciones">
    actividades_extras_ver.php?id=<?= $row['idextra'] ?>" class="btn ver">Ver</a>
    actividades_extras_editar.php?id=<?= $row['idextra'] ?>" class="btn editar">Editar</a>
</td>

</tr>
<?php endforeach; ?>

</table>

</div>

<script src="theme.js"></script>

</body>
</html>
