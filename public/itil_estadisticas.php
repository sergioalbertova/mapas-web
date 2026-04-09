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
   ESTADÍSTICAS ITIL
   ============================================================ */

/* KPIs generales */
$sqlTotal = "SELECT COUNT(*) AS total FROM itil_incidentes";
$total = $pdo->query($sqlTotal)->fetch(PDO::FETCH_ASSOC)['total'];

$sqlResueltos = "SELECT COUNT(*) AS total FROM itil_incidentes WHERE estado ILIKE 'Resuelto'";
$totalResueltos = $pdo->query($sqlResueltos)->fetch(PDO::FETCH_ASSOC)['total'];

$sqlActivos = "SELECT COUNT(*) AS total FROM itil_incidentes WHERE estado NOT ILIKE 'Resuelto'";
$totalActivos = $pdo->query($sqlActivos)->fetch(PDO::FETCH_ASSOC)['total'];

/* MTTR (en horas) usando fecha_reporte y fecha_resolucion */
$sqlMTTR = "
    SELECT AVG(EXTRACT(EPOCH FROM (fecha_resolucion - fecha_reporte))/3600) AS mttr_horas
    FROM itil_incidentes
    WHERE fecha_resolucion IS NOT NULL
";
$mttr = $pdo->query($sqlMTTR)->fetch(PDO::FETCH_ASSOC)['mttr_horas'];
$mttr = $mttr ? round($mttr, 2) : 0;

/* SLA compliance (ejemplo simple: resueltos en menos de 24h) */
$sqlSLA = "
    SELECT 
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL 
                         AND fecha_resolucion - fecha_reporte <= INTERVAL '24 hours') AS dentro_sla,
        COUNT(*) AS total_con_resolucion
    FROM itil_incidentes
";
$slaRow = $pdo->query($sqlSLA)->fetch(PDO::FETCH_ASSOC);
$slaPorcentaje = 0;
if ($slaRow['total_con_resolucion'] > 0) {
    $slaPorcentaje = round(($slaRow['dentro_sla'] / $slaRow['total_con_resolucion']) * 100, 1);
}

/* Backlog (incidentes no resueltos) */
$backlog = $totalActivos;

/* Incidentes por técnico */
$sqlPorTecnico = "
    SELECT 
        COALESCE(u.nombre, 'Sin técnico') AS tecnico,
        COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    GROUP BY tecnico
    ORDER BY total DESC
";
$porTecnico = $pdo->query($sqlPorTecnico)->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por tipo (título) */
$sqlPorTipo = "
    SELECT 
        titulo,
        COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY titulo
    ORDER BY total DESC
";
$porTipo = $pdo->query($sqlPorTipo)->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por estado */
$sqlPorEstado = "
    SELECT 
        estado,
        COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY estado
    ORDER BY total DESC
";
$porEstado = $pdo->query($sqlPorEstado)->fetchAll(PDO::FETCH_ASSOC);

/* Tendencia mensual (últimos 12 meses) */
$sqlMensual = "
    SELECT 
        TO_CHAR(fecha_reporte, 'YYYY-MM') AS mes,
        COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY mes
    ORDER BY mes
";
$mensual = $pdo->query($sqlMensual)->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por hora del día */
$sqlPorHora = "
    SELECT 
        EXTRACT(HOUR FROM fecha_reporte) AS hora,
        COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY hora
    ORDER BY hora
";
$porHora = $pdo->query($sqlPorHora)->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por día de la semana (0=domingo, 1=lunes...) */
$sqlPorDiaSemana = "
    SELECT 
        EXTRACT(DOW FROM fecha_reporte) AS dow,
        COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY dow
    ORDER BY dow
";
$porDiaSemana = $pdo->query($sqlPorDiaSemana)->fetchAll(PDO::FETCH_ASSOC);

/* Top técnicos */
$sqlTopTecnicos = "
    SELECT 
        COALESCE(u.nombre, 'Sin técnico') AS tecnico,
        COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    GROUP BY tecnico
    ORDER BY total DESC
    LIMIT 10
";
$topTecnicos = $pdo->query($sqlTopTecnicos)->fetchAll(PDO::FETCH_ASSOC);

/* Top categorías (usando titulo como tipo/categoría simple) */
$sqlTopCategorias = "
    SELECT 
        titulo,
        COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY titulo
    ORDER BY total DESC
    LIMIT 10
";
$topCategorias = $pdo->query($sqlTopCategorias)->fetchAll(PDO::FETCH_ASSOC);

/* Top usuarios que más reportan (usuario_final_id -> activeuser) */
$sqlTopUsuarios = "
    SELECT 
        COALESCE(au.nomuser, 'Desconocido') AS usuario,
        COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN activeuser au ON au.idu = i.usuario_final_id
    GROUP BY usuario
    ORDER BY total DESC
    LIMIT 10
";
$topUsuarios = $pdo->query($sqlTopUsuarios)->fetchAll(PDO::FETCH_ASSOC);

/* Preparar datos para JS */
$chartTecnicoLabels = array_column($porTecnico, 'tecnico');
$chartTecnicoData   = array_column($porTecnico, 'total');

$chartTipoLabels = array_column($porTipo, 'titulo');
$chartTipoData   = array_column($porTipo, 'total');

$chartEstadoLabels = array_column($porEstado, 'estado');
$chartEstadoData   = array_column($porEstado, 'total');

$chartMensualLabels = array_column($mensual, 'mes');
$chartMensualData   = array_column($mensual, 'total');

$chartHoraLabels = array_column($porHora, 'hora');
$chartHoraData   = array_column($porHora, 'total');

$chartDiaSemanaLabels = array_column($porDiaSemana, 'dow');
$chartDiaSemanaData   = array_column($porDiaSemana, 'total');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estadísticas ITIL</title>

<style>
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

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* Sidebar y topbar igual que en itil_incidentes.php */
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

/* TOPBAR ITIL */
.itil-topbar {
    position: fixed;
    top: 0;
    left: 240px;
    right: 0;
    height: 55px;
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 0 25px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 2100;
}
.sidebar.collapsed ~ .itil-topbar { left: 70px; }

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

.itil-topbar svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
}

/* MAIN */
.main {
    margin-left: 240px;
    width: calc(100% - 240px);
    margin-top: 95px;
    padding: 25px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}
#sidebar.collapsed + .itil-topbar + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* CARDS GRID */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.card {
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: 0 3px 10px var(--shadow);
    padding: 16px 18px;
}

.card h3 {
    margin: 0 0 8px;
    font-size: 16px;
    color: var(--text);
}

.card .kpi-value {
    font-size: 26px;
    font-weight: bold;
    margin-bottom: 4px;
}

.card .kpi-sub {
    font-size: 13px;
    color: var(--subtext);
}

/* TABLAS */
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
    font-size: 13px;
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--sidebar-hover);
    font-size: 13px;
}

/* CONTENEDORES DE GRÁFICAS */
.chart-card {
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: 0 3px 10px var(--shadow);
    padding: 16px 18px;
}
.chart-container {
    width: 100%;
    height: 280px;
}

/* FILTROS (placeholder simple) */
.filters {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}
.filters select, .filters input {
    padding: 6px 8px;
    border-radius: 6px;
    border: 1px solid var(--sidebar-hover);
    background: var(--card-bg);
    color: var(--text);
    font-size: 13px;
}
</style>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>

<!-- SIDEBAR (igual que en tu sistema) -->
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

<!-- TOPBAR ITIL -->
<div class="itil-topbar">

    <a href="itil_incidentes.php">
        <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
        Incidentes
    </a>

    <a href="itil_incidente_nuevo.php">
        <svg><path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Nuevo
    </a>

    <a href="itil_problemas.php">
        <svg><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Problemas
    </a>

    <a href="itil_catalogo.php">
        <svg><path d="M4 4h16v4H4zm0 6h16v10H4z"/></svg>
        Catalogo Incidentes
    </a>

    <a href="itil_solicitudes.php">
        <svg><rect x="3" y="6" width="18" height="12" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Solicitudes
    </a>

    <a href="itil_sla.php">
        <svg><path d="M12 2v20m10-10H2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        SLA
    </a>

    <a href="itil_estadisticas.php">
        <svg><path d="M4 20V10m6 10V4m6 16v-6m6 6V8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Estadísticas
    </a>

</div>

<!-- MAIN -->
<div class="main">
    <h2 style="margin-bottom:15px;">Dashboard de estadísticas ITIL</h2>
    <p style="color:var(--subtext); font-size:13px; margin-bottom:20px;">
        Vista general de incidentes, técnicos, tipos y comportamiento temporal.
    </p>

    <!-- KPIs -->
    <div class="dashboard-grid">
        <div class="card">
            <h3>Total de incidentes</h3>
            <div class="kpi-value"><?= $total ?></div>
            <div class="kpi-sub">Todos los registros en el sistema</div>
        </div>
        <div class="card">
            <h3>Incidentes resueltos</h3>
            <div class="kpi-value"><?= $totalResueltos ?></div>
            <div class="kpi-sub">Estado = Resuelto</div>
        </div>
        <div class="card">
            <h3>Incidentes activos</h3>
            <div class="kpi-value"><?= $totalActivos ?></div>
            <div class="kpi-sub">Pendientes de resolución</div>
        </div>
        <div class="card">
            <h3>MTTR</h3>
            <div class="kpi-value"><?= $mttr ?> h</div>
            <div class="kpi-sub">Tiempo promedio de resolución</div>
        </div>
        <div class="card">
            <h3>SLA cumplido</h3>
            <div class="kpi-value"><?= $slaPorcentaje ?>%</div>
            <div class="kpi-sub">Resueltos &lt;= 24h</div>
        </div>
        <div class="card">
            <h3>Backlog</h3>
            <div class="kpi-value"><?= $backlog ?></div>
            <div class="kpi-sub">Incidentes abiertos/no resueltos</div>
        </div>
    </div>

    <!-- Gráficas principales -->
    <div class="dashboard-grid">
        <div class="chart-card">
            <h3>Incidentes por técnico</h3>
            <div id="chartTecnico" class="chart-container"></div>
        </div>
        <div class="chart-card">
            <h3>Incidentes por tipo</h3>
            <div id="chartTipo" class="chart-container"></div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="chart-card">
            <h3>Incidentes por estado</h3>
            <div id="chartEstado" class="chart-container"></div>
        </div>
        <div class="chart-card">
            <h3>Tendencia mensual de incidentes</h3>
            <div id="chartMensual" class="chart-container"></div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="chart-card">
            <h3>Heatmap por hora del día</h3>
            <div id="chartHora" class="chart-container"></div>
        </div>
        <div class="chart-card">
            <h3>Incidentes por día de la semana</h3>
            <div id="chartDiaSemana" class="chart-container"></div>
        </div>
    </div>

    <!-- Tablas: Top técnicos, categorías, usuarios -->
    <div class="dashboard-grid">
        <div class="table-box">
            <h3>Top técnicos por cantidad de incidentes</h3>
            <table>
                <tr>
                    <th>Técnico</th>
                    <th>Incidentes</th>
                </tr>
                <?php foreach ($topTecnicos as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tecnico']) ?></td>
                        <td><?= $row['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="table-box">
            <h3>Top categorías / tipos de incidentes</h3>
            <table>
                <tr>
                    <th>Tipo / Título</th>
                    <th>Incidentes</th>
                </tr>
                <?php foreach ($topCategorias as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['titulo']) ?></td>
                        <td><?= $row['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="table-box">
            <h3>Top usuarios que más reportan</h3>
            <table>
                <tr>
                    <th>Usuario</th>
                    <th>Incidentes</th>
                </tr>
                <?php foreach ($topUsuarios as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= $row['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
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

/* Datos desde PHP */
const chartTecnicoLabels = <?= json_encode($chartTecnicoLabels) ?>;
const chartTecnicoData   = <?= json_encode($chartTecnicoData) ?>;

const chartTipoLabels = <?= json_encode($chartTipoLabels) ?>;
const chartTipoData   = <?= json_encode($chartTipoData) ?>;

const chartEstadoLabels = <?= json_encode($chartEstadoLabels) ?>;
const chartEstadoData   = <?= json_encode($chartEstadoData) ?>;

const chartMensualLabels = <?= json_encode($chartMensualLabels) ?>;
const chartMensualData   = <?= json_encode($chartMensualData) ?>;

const chartHoraLabels = <?= json_encode($chartHoraLabels) ?>;
const chartHoraData   = <?= json_encode($chartHoraData) ?>;

const chartDiaSemanaLabelsRaw = <?= json_encode($chartDiaSemanaLabels) ?>;
const chartDiaSemanaData   = <?= json_encode($chartDiaSemanaData) ?>;

/* Mapear DOW a nombres */
const dowNames = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
const chartDiaSemanaLabels = chartDiaSemanaLabelsRaw.map(d => dowNames[parseInt(d)]);

/* THEME */
const isDark = document.body.classList.contains('dark');
const textColor = getComputedStyle(document.body).getPropertyValue('--text').trim();
const cardBg = getComputedStyle(document.body).getPropertyValue('--card-bg').trim();

/* Gráfica: Incidentes por técnico */
new ApexCharts(document.querySelector("#chartTecnico"), {
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    series: [{ name: 'Incidentes', data: chartTecnicoData }],
    xaxis: { categories: chartTecnicoLabels, labels: { style: { colors: textColor } } },
    yaxis: { labels: { style: { colors: textColor } } },
    theme: { mode: isDark ? 'dark' : 'light' },
    colors: ['#0054A6'],
    grid: { borderColor: '#e5e7eb' },
    tooltip: { theme: isDark ? 'dark' : 'light' }
}).render();

/* Gráfica: Incidentes por tipo (donut) */
new ApexCharts(document.querySelector("#chartTipo"), {
    chart: { type: 'donut', height: 280 },
    series: chartTipoData,
    labels: chartTipoLabels,
    theme: { mode: isDark ? 'dark' : 'light' },
    legend: { labels: { colors: textColor } },
    tooltip: { theme: isDark ? 'dark' : 'light' }
}).render();

/* Gráfica: Incidentes por estado */
new ApexCharts(document.querySelector("#chartEstado"), {
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true } },
    series: [{ name: 'Incidentes', data: chartEstadoData }],
    xaxis: { categories: chartEstadoLabels, labels: { style: { colors: textColor } } },
    yaxis: { labels: { style: { colors: textColor } } },
    theme: { mode: isDark ? 'dark' : 'light' },
    colors: ['#00AEEF'],
    tooltip: { theme: isDark ? 'dark' : 'light' }
}).render();

/* Gráfica: Tendencia mensual */
new ApexCharts(document.querySelector("#chartMensual"), {
    chart: { type: 'line', height: 280, toolbar: { show: false } },
    series: [{ name: 'Incidentes', data: chartMensualData }],
    xaxis: { categories: chartMensualLabels, labels: { style: { colors: textColor } } },
    yaxis: { labels: { style: { colors: textColor } } },
    stroke: { curve: 'smooth', width: 3 },
    theme: { mode: isDark ? 'dark' : 'light' },
    colors: ['#0054A6'],
    tooltip: { theme: isDark ? 'dark' : 'light' }
}).render();

/* Gráfica: Incidentes por hora (barras) */
new ApexCharts(document.querySelector("#chartHora"), {
    chart: { type: 'heatmap', height: 280, toolbar: { show: false } },
    series: [{
        name: 'Incidentes',
        data: chartHoraLabels.map((h, idx) => ({ x: h + ':00', y: chartHoraData[idx] }))
    }],
    dataLabels: { enabled: false },
    xaxis: { labels: { style: { colors: textColor } } },
    theme: { mode: isDark ? 'dark' : 'light' },
    colors: ['#00AEEF'],
    tooltip: { theme: isDark ? 'dark' : 'light' }
}).render();

/* Gráfica: Incidentes por día de la semana */
new ApexCharts(document.querySelector("#chartDiaSemana"), {
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    series: [{ name: 'Incidentes', data: chartDiaSemanaData }],
    xaxis: { categories: chartDiaSemanaLabels, labels: { style: { colors: textColor } } },
    yaxis: { labels: { style: { colors: textColor } } },
    theme: { mode: isDark ? 'dark' : 'light' },
    colors: ['#FF9800'],
    tooltip: { theme: isDark ? 'dark' : 'light' }
}).render();
</script>

</body>
</html>
