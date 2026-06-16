<?php
require "session_config.php";
require "db.php";

$id = $_SESSION['user_id'];

// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";

// Obtener actividades
$stmt = $pdo->query("
    SELECT ae.idextra, ae.fecha, ae.equipo, ae.usuario_afectado, ae.estatus,
           u.nombre AS ingeniero,
           ca.actividad
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

    --topbar-bg: rgba(255,255,255,0.85);
    --topbar-text: #1F2933;
    --topbar-border: rgba(0,0,0,0.1);

    --sidebar-bg: #FFFFFF;
    --sidebar-text: #1F2933;
    --sidebar-border: rgba(0,0,0,0.1);

    --card-bg: #FFFFFF;
    --card-text: #1F2933;

    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;

    --topbar-bg: rgba(17,24,39,0.85);
    --topbar-text: #E5E7EB;
    --topbar-border: rgba(255,255,255,0.1);

    --sidebar-bg: #020617;
    --sidebar-text: #E5E7EB;
    --sidebar-border: rgba(255,255,255,0.1);

    --card-bg: #1f2937;
    --card-text: #E5E7EB;

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

/* TÍTULOS */
.main h2 {
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
    text-align: left;
}

.tabla td {
    padding: 12px;
    border-bottom: 1px solid var(--sidebar-border);
}

/* BOTÓN NUEVO */
.btn-nuevo {
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 20px;
}

/* BOTONES ACCIÓN */
.btn-accion {
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 13px;
    text-decoration: none;
    margin-right: 6px;
    color: white;
}

.ver { background: #2563eb; }
.editar { background: #059669; }
.eliminar { background: #dc2626; }

.tabla th:last-child,
.tabla td:last-child {
    width: 150px;        /* Espacio suficiente */
    white-space: nowrap; /* Evita que los botones salten de línea */
}


</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Actividades Extras</h2>
<div class="subtitle">Registro de actividades realizadas por los ingenieros</div>

<a href="actividades_extras_nuevo.php" class="btn-nuevo">+ Nueva actividad</a>

<table class="tabla">
    <tr>
        <th>Fecha</th>
        <th>Ingeniero</th>
        <th>Actividad</th>
        <th>Usuario afectado</th>
        <th>Equipo</th>
        <th>Estatus</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($lista as $row): ?>
    <tr>
        <td><?= safe($row['fecha']) ?></td>
        <td><?= safe($row['ingeniero']) ?></td>
        <td><?= safe($row['actividad']) ?></td>
        <td><?= safe($row['usuario_afectado']) ?></td>
        <td><?= safe($row['equipo']) ?></td>
        <td><?= safe($row['estatus']) ?></td>
        <td>
            <a href="actividades_extras_ver.php?id=<?= $row['idextra'] ?>" class="btn-accion ver">Ver</a>
            <a href="actividades_extras_editar.php?id=<?= $row['idextra'] ?>" class="btn-accion editar">Editar</a>
            
        </td>
    </tr>
    <?php endforeach; ?>

</table>

</div>

<script src="theme.js"></script>

</body>
</html>
