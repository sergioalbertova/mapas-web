<?php
require "session_config.php";
require "db.php";

/* ============================================================
   OBTENER TÉCNICO LOGUEADO
   ============================================================ */
$tecnico_id = intval($_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$tecnico_id]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

$nombreTecnico = $tecnico ? $tecnico['usuario'] . " - " . $tecnico['nombre'] : "Usuario";

/* ============================================================
   OBTENER INCIDENTES
   ============================================================ */
$sql = "
SELECT 
    i.id,
    i.titulo,
    i.prioridad,
    i.estado,
    i.fecha_reporte,
    u.nombre AS tecnico_nombre,
    au.nomuser AS usuario_afectado
FROM itil_incidentes i
LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
LEFT JOIN activeuser au ON au.idu = i.usuario_final_id
ORDER BY i.id DESC
";
$stmt = $pdo->query($sql);
$incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Incidentes ITIL</title>

<style>
/* ========================= */
/* VARIABLES                 */
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
/* GENERAL                   */
/* ========================= */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* ========================= */
/* SIDEBAR ORIGINAL          */
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
    overflow: visible;
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

/* TOOLTIP */
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
/* TOPBAR ITIL               */
/* ========================= */
.itil-topbar {
    position: fixed;
    top: 0;
    left: 240px;
    height: 55px;
    width: calc(100% - 240px);
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 0 25px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 2100;
    transition: left 0.25s ease, width 0.25s ease;
}
.sidebar.collapsed ~ .itil-topbar {
    left: 70px;
    width: calc(100% - 70px);
}

.itil-topbar a {
    text-decoration: none;
    color: var(--text);
    font-weight: bold;
    padding: 8px 12px;
    border-radius: 6px;
    display:flex;
    align-items:center;
    gap:8px;
}
.itil-topbar a:hover { background: var(--sidebar-hover); }

/* ========================= */
/* MAIN                      */
/* ========================= */
.main {
    margin-left: 240px;
    width: calc(100% - 240px);
    margin-top: 95px;
    padding: 25px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}
.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ========================= */
/* TABLA                     */
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

th {
    background: var(--primary);
    color: white;
    padding: 10px;
    text-align: left;
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--sidebar-hover);
}

.estado {
    font-weight: bold;
    padding: 4px 8px;
    border-radius: 6px;
}
.estado_Abierto { background: #ffebee; color: #c62828; }
.estado_En_progreso { background: #fff3cd; color: #b8860b; }
.estado_Cerrado { background: #e8f5e9; color: #2e7d32; }
</style>
</head>

<body>

<!-- ========================= -->
<!-- SIDEBAR REAL              -->
<!-- ========================= -->
<div class="sidebar" id="sidebar">

    <div class="toggle-btn" onclick="toggleSidebar()">
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
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 2v2h8v-2H8z"/></svg>
            <span class="nav-text">Incidentes ITIL</span>
        </a>
        <span class="tooltip">Incidentes ITIL</span>
    </div>

    <div class="nav-item">
        <a href="dashboard.php">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            <span class="nav-text">Mapeo de nodos</span>
        </a>
        <span class="tooltip">Mapeo de nodos</span>
    </div>

    <div class="nav-item">
        <a href="calendario.php">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <span class="nav-text">Calendario</span>
        </a>
        <span class="tooltip">Calendario</span>
    </div>

    <div class="nav-item">
        <a href="incidentes.php">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 2v2h8v-2H8z"/></svg>
            <span class="nav-text">Incidentes TI</span>
        </a>
        <span class="tooltip">Incidentes TI</span>
    </div>

    <div class="nav-item">
        <a href="cambiar_password.php">
            <svg><path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/></svg>
            <span class="nav-text">Cambiar contraseña</span>
        </a>
        <span class="tooltip">Cambiar contraseña</span>
    </div>

    <div class="nav-item">
        <a href="logout.php">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm2-10H8v2h10v14H8v2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
            <span class="nav-text">Cerrar sesión</span>
        </a>
        <span class="tooltip">Cerrar sesión</span>
    </div>

    <div class="nav-item" onclick="toggleTheme()">
        <svg><path d="M12 2a9 9 0 100 18 9 9 0 010-18z"/></svg>
        <span class="nav-text">Tema oscuro</span>
        <span class="tooltip">Tema oscuro</span>
    </div>

</div>

<!-- ========================= -->
<!-- TOPBAR ITIL               -->
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
<!-- MAIN                      -->
<!-- ========================= -->
<div class="main">
    <div class="table-box">
        <h2>Incidentes registrados</h2>

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

            <?php if (count($incidentes) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:20px;">
                        No hay incidentes registrados aún.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($incidentes as $i): ?>
                    <tr>
                        <td><?= $i['id'] ?></td>

                        <td>
                            <a href="itil_incidente_ver.php?id=<?= $i['id'] ?>" 
                               style="color: var(--primary); font-weight:bold;">
                               <?= htmlspecialchars($i['titulo']) ?>
                            </a>
                        </td>

                        <td><?= htmlspecialchars($i['usuario_afectado']) ?></td>
                        <td><?= htmlspecialchars($i['prioridad']) ?></td>

                        <td>
                            <?php $clase = "estado_" . str_replace(" ", "_", $i['estado']); ?>
                            <span class="estado <?= $clase ?>">
                                <?= htmlspecialchars($i['estado']) ?>
                            </span>
                        </td>

                        <td><?= htmlspecialchars($i['tecnico_nombre']) ?></td>

                        <td><?= date("Y-m-d H:i:s", strtotime($i['fecha_reporte'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

function toggleTheme() {
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
}
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}
</script>

</body>
</html>
