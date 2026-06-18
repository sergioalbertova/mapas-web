<?php
require "auth.php";
require "db.php";

$id = $_SESSION['user_id'];

// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";

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

:root {
    --bg: #F4F7FA;
    --text: #1F2933;

    --card-bg: #FFFFFF;
    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
    --border: rgba(0,0,0,0.1);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;

    --card-bg: #1f2937;
    --border: rgba(255,255,255,0.1);
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

/* TITULO */
.main h2 {
    text-align: center;
    margin-bottom: 8px;
}

.subtitle {
    text-align: center;
    opacity: 0.7;
    margin-bottom: 30px;
}

/* TABLA */
.tabla {
    width: 100%;
    border-collapse: collapse;
    background: var(--card-bg);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 25px var(--shadow);
}

.tabla th {
    background: var(--accent);
    color: white;
    padding: 12px;
}

.tabla td {
    padding: 12px;
    border-bottom: 1px solid var(--border);
}

/* BOTÓN */
.btn-nuevo {
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    margin-bottom: 20px;
    display: inline-block;
}

/* BADGES */
.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
}

.en-proceso {
    background: orange;
    color: white;
}

.completo {
    background: green;
    color: white;
}

</style>
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Actividades Extras</h2>
<div class="subtitle">Registro y control de tiempo de actividades</div>

<a href="actividades_extras_nuevo.php" class="btn-nuevo">+ Nueva actividad</a>

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
    <?= $row['fecha_fin'] 
        ? substr($row['fecha_fin'],0,19) 
        : '<span class="badge en-proceso">En proceso</span>' ?>
</td>

<td>
<?php
if ($row['fecha_fin']) {
    echo round($row['duracion_min'],1) . " min";
} else {
    echo "⏳";
}
?>
</td>

<td><?= safe($row['ingeniero']) ?></td>
<td><?= safe($row['actividad']) ?></td>
<td><?= safe($row['usuario_afectado']) ?></td>
<td><?= safe($row['equipo']) ?></td>

<td>
<?= $row['estatus'] === 'completado'
    ? '<span class="badge completo">Completado</span>'
    : '<span class="badge en-proceso">En proceso</span>'
?>
</td>

<td>
<a href="actividades_extras_ver.php?id=<?= $row['idextra'] ?>">Ver</a>
<a href="actividades_extras_editar.php?id=<?= $row['idextra'] ?>">Editar</a>
</td>

</tr>
<?php endforeach; ?>

</table>

</div>

<script src="theme.js"></script>

</body>
</html>