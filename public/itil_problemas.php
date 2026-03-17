<?php
require "session_config.php";
require "db.php";

/* ============================
   OBTENER LISTA DE PROBLEMAS
   ============================ */
$sql = "
SELECT p.id, p.titulo, p.estado, p.fecha_creacion,
       u.nombre AS tecnico
FROM problemas p
LEFT JOIN usuarios u ON u.id = p.tecnico_responsable
ORDER BY p.fecha_creacion DESC
";
$stmt = $pdo->query($sql);
$problemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Problemas ITIL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* === COPIADO DE TU ARCHIVO REAL === */
:root {
    --bg: #F4F7FA;
    --sidebar-bg: #FFFFFF;
    --sidebar-hover: #E8EEF5;
    --card-bg: #FFFFFF;
    --text: #1F2933;
    --subtext: #6B7280;
    --primary: #0054A6;
    --primary-hover: #003F7D;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #1A1D21;
    --sidebar-bg: #24272C;
    --sidebar-hover: #2F3338;
    --card-bg: #2C2F34;
    --text: #E5E7EB;
    --subtext: #9CA3AF;
    --primary: #4FC3F7;
    --primary-hover: #81D4FA;
    --shadow: rgba(0,0,0,0.45);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* SIDEBAR */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    box-shadow: 4px 0 20px var(--shadow);
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    position: fixed;
    transition: width 0.25s ease;
    overflow: visible;
    z-index: 2000;
}
.sidebar.collapsed { width: 70px; }

.nav-item {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}
.nav-item:hover { background: var(--sidebar-hover); }

.nav-item a {
    display:flex;
    align-items:center;
    gap:12px;
    color:inherit;
    text-decoration:none;
}

.nav-item svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.sidebar.collapsed .nav-text { display: none; }

/* TOPBAR ITIL */
.itil-topbar {
    position: fixed;
    top: 0;
    left: 240px;
    height: 55px;
    width: calc(100% - 240px);
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    justify-content: space-evenly;
    gap: 10px;
    padding: 0 10px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 2100;
    transition: left 0.25s ease, width 0.25s ease;
}
#sidebar.collapsed + .itil-topbar {
    left: 70px;
    width: calc(100% - 70px);
}

/* MAIN */
.main {
    margin-left: 240px;
    width: calc(100% - 240px);
    margin-top: 75px;
    padding: 20px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}
#sidebar.collapsed + .itil-topbar + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* TARJETAS */
.card-itil {
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 2px 6px var(--shadow);
    padding: 14px 16px;
    margin-bottom: 14px;
}
</style>
</head>

<body>

<!-- SIDEBAR (copiado tal cual de tu archivo) -->
<div class="sidebar" id="sidebar">
    <div class="nav-item" onclick="toggleSidebar()">
        <svg><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        <span class="nav-text">Menú</span>
    </div>

    <h2>Panel</h2>

    <div class="nav-item">
        <a href="index.php">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            <span class="nav-text">Inicio</span>
        </a>
        <span class="tooltip">Inicio</span>
    </div>

    <div class="nav-item">
        <a href="itil_incidentes.php">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
            <span class="nav-text">Incidentes ITIL</span>
        </a>
        <span class="tooltip">Incidentes ITIL</span>
    </div>

    <div class="nav-item">
        <a href="itil_problemas.php">
            <svg><circle cx="12" cy="12" r="10"/></svg>
            <span class="nav-text">Problemas</span>
        </a>
        <span class="tooltip">Problemas</span>
    </div>

    <div class="nav-item" onclick="toggleDarkMode()">
        <svg id="darkToggleIcon" viewBox="0 0 24 24"></svg>
        <span class="nav-text" id="darkToggleText">Tema oscuro</span>
        <span class="tooltip" id="darkToggleTooltip">Tema oscuro</span>
    </div>
</div>

<!-- TOPBAR -->
<div class="itil-topbar">
    <a href="itil_incidentes.php">Incidentes</a>
    <a href="itil_incidente_nuevo.php">Nuevo</a>
    <a href="itil_problemas.php"><strong>Problemas</strong></a>
    <a href="itil_cambios.php">Cambios</a>
</div>

<!-- CONTENIDO -->
<div class="main">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Problemas ITIL</h3>
        <a href="itil_problema_nuevo.php" class="btn btn-primary">+ Nuevo problema</a>
    </div>

    <div class="card-itil">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Técnico responsable</th>
                    <th>Fecha creación</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($problemas as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['titulo']) ?></td>
                    <td><?= $p['estado'] ?></td>
                    <td><?= $p['tecnico'] ?: 'Sin asignar' ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                    <td>
                        <a href="itil_problema_ver.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                            Ver
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>

</body>
</html>
