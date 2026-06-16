<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "session_config.php";
require "db.php";

$esAdmin = ($_SESSION['rol'] === 'administrador');

// Obtener actividades con ingeniero y nombre de actividad
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
body {
    background: var(--bg);
    color: var(--text);
    margin: 0;
    font-family: "Segoe UI", Arial;
}

/* Contenedor principal */
.contenedor {
    padding: 25px;
}

/* Título */
.titulo {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 15px;
}

/* Botón nuevo */
.btn-nuevo {
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
}

/* Tabla TIHIL */
.tabla {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
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
    font-size: 14px;
}

.tabla td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}

/* Botones de acción */
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
</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">   <!-- ESTA LÍNEA ES CRÍTICA -->

<?php require "topbar.php"; ?>

<div class="contenedor">

    <div class="titulo">Actividades Extras</div>

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

                <?php if ($esAdmin): ?>
                <a href="actividades_extras_editar.php?id=<?= $row['idextra'] ?>" class="btn-accion editar">Editar</a>
                <a href="actividades_extras_eliminar.php?id=<?= $row['idextra'] ?>" class="btn-accion eliminar"
                   onclick="return confirm('¿Eliminar actividad?')">Eliminar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

</div>

</div> <!-- CIERRE REAL DE .main -->

<script src="theme.js"></script>

</body>
</html>
