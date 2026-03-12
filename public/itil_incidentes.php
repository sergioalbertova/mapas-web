<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

// Obtener nombre del técnico logueado
$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Técnico";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Incidentes ITIL</title>

<style>
/* ========================= */
/* VARIABLES DE COLOR        */
/* ========================= */
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
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
}

/* ========================= */
/* ESTILOS GENERALES         */
/* ========================= */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: 0.3s;
}

/* ========================= */
/* SIDEBAR CORPORATIVO       */
/* ========================= */
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
    z-index: 2000;
}
.sidebar.collapsed { width: 70px; }

.sidebar h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: var(--primary);
    transition: opacity 0.25s ease;
}
.sidebar.collapsed h2 { opacity: 0; }

.nav-item {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
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

/* ========================= */
/* TOOLTIP DEL SIDEBAR       */
/* ========================= */
.tooltip {
    position: absolute;
    left: 80px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--sidebar-bg);
    padding: 6px 12px;
    border-radius: 6px;
    box-shadow: 0 2px 8px var(--shadow);
    font-size: 13px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, left 0.2s ease;
    z-index: 99999;
}
.sidebar.collapsed .nav-item:hover .tooltip {
    opacity: 1;
    left: 75px;
}

/* ========================= */
/* BARRA SUPERIOR ITIL       */
/* ========================= */
.itil-topbar {
    position: fixed;
    top: 0;
    left: 240px;
    right: 0;
    height: 55px;
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    padding: 0 25px;
    gap: 25px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 1500;
    transition: left 0.25s ease;
}
.sidebar.collapsed ~ .itil-topbar {
    left: 70px;
}

.itil-topbar a {
    text-decoration: none;
    color: var(--text);
    font-weight: bold;
    padding: 8px 12px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.2s;
}
.itil-topbar a:hover {
    background: var(--sidebar-hover);
}

.itil-topbar svg {
    width: 18px;
    height: 18px;
    fill: var(--text);
}

/* ========================= */
/* CONTENIDO PRINCIPAL       */
/* ========================= */
.main {
    margin-left: 240px;
    margin-top: 70px;
    padding: 25px;
    transition: margin-left 0.25s ease;
}
.sidebar.collapsed ~ .main {
    margin-left: 70px;
}

/* ========================= */
/* TABLA DE INCIDENTES       */
/* ========================= */
.table-box {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 10px var(--shadow);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid var(--sidebar-hover);
}

th {
    text-align: left;
    color: var(--primary);
    font-size: 15px;
}

td {
    font-size: 14px;
}
</style>
</head>

<body>

<!-- ========================= -->
<!-- SIDEBAR CORPORATIVO       -->
<!-- ========================= -->
<div class="sidebar" id="sidebar">
    <div class="nav-item" onclick="toggleSidebar()">
        <svg><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        <span class="nav-text">Menú</span>
        <span class="tooltip">Colapsar menú</span>
    </div>

    <h2>Panel</h2>

    <div class="nav-item">
        <a href="index.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            <span class="nav-text">Inicio</span>
        </a>
        <span class="tooltip">Inicio</span>
    </div>

    <div class="nav-item">
        <a href="itil_incidentes.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
            <span class="nav-text">Incidentes TI</span>
        </a>
        <span class="tooltip">Incidentes TI</span>
    </div>

    <div class="nav-item">
        <a href="logout.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9z"/></svg>
            <span class="nav-text">Cerrar sesión</span>
        </a>
        <span class="tooltip">Cerrar sesión</span>
    </div>
</div>

<!-- ========================= -->
<!-- BARRA SUPERIOR ITIL       -->
<!-- ========================= -->
<div class="itil-topbar">
    <a href="itil_incidentes.php">
        <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
        Incidentes
    </a>

    <a href="itil_incidente_nuevo.php">
        <svg><path d="M12 5v14m7-7H5"/></svg>
        Nuevo incidente
    </a>

    <a href="itil_problemas.php">
        <svg><path d="M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
        Problemas
    </a>

    <a href="itil_cambios.php">
        <svg><path d="M4 4h16v4H4zm0 6h16v10H4z"/></svg>
        Cambios
    </a>

    <a href="itil_solicitudes.php">
        <svg><path d="M3 6h18v12H3z"/></svg>
        Solicitudes
    </a>

    <a href="itil_sla.php">
        <svg><path d="M12 2v20m10-10H2"/></svg>
        SLA
    </a>

    <a href="itil_estadisticas.php">
        <svg><path d="M4 20V10m6 10V4m6 16v-6m6 6V8"/></svg>
        Estadísticas
    </a>
</div>

<!-- ========================= -->
<!-- CONTENIDO PRINCIPAL       -->
<!-- ========================= -->
<div class="main">
    <h2>Incidentes registrados</h2>

    <div class="table-box">
        <table>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Usuario afectado</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Técnico asignado</th>
                <th>Fecha</th>
            </tr>

            <!-- Aquí se cargarán los incidentes reales -->
            <tr>
                <td colspan="7" style="text-align:center;color:var(--subtext);">
                    No hay incidentes registrados aún.
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>

</body>
</html>
