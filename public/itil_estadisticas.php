<?php
require "session_config.php";
require "db.php";

/* ============================================================
   FILTRO DE FECHAS (GET)
   ============================================================ */
$hoy = date("Y-m-d");

$fecha_inicio = $_GET['inicio'] ?? $hoy;
$fecha_fin    = $_GET['fin']    ?? $hoy;

/* Botones rápidos */
if (isset($_GET['rango'])) {
    if ($_GET['rango'] === "hoy") {
        $fecha_inicio = $hoy;
        $fecha_fin = $hoy;
    }
    if ($_GET['rango'] === "7") {
        $fecha_inicio = date("Y-m-d", strtotime("-6 days"));
        $fecha_fin = $hoy;
    }
    if ($_GET['rango'] === "mes") {
        $fecha_inicio = date("Y-m-01");
        $fecha_fin = $hoy;
    }
}

/* ============================================================
   OBTENER TÉCNICO LOGUEADO
   ============================================================ */
$tecnico_id = intval($_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$tecnico_id]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

$nombreTecnico = $tecnico ? $tecnico['usuario'] . " - " . $tecnico['nombre'] : "Usuario";

/* ============================================================
   CONSULTAS SQL FILTRADAS POR FECHA
   ============================================================ */

$params = [
    ':inicio' => $fecha_inicio . " 00:00:00",
    ':fin'    => $fecha_fin . " 23:59:59"
];

/* Total */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
");
$stmt->execute($params);
$total = $stmt->fetchColumn();

/* Resueltos */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM itil_incidentes
    WHERE estado ILIKE 'Resuelto'
    AND fecha_reporte BETWEEN :inicio AND :fin
");
$stmt->execute($params);
$totalResueltos = $stmt->fetchColumn();

/* Activos */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM itil_incidentes
    WHERE estado ILIKE ANY (ARRAY['Activo', 'Abierto', 'Pendiente', 'En espera'])
    AND fecha_reporte BETWEEN :inicio AND :fin
");
$stmt->execute($params);
$totalActivos = $stmt->fetchColumn();

/* MTTR */
$stmt = $pdo->prepare("
    SELECT ROUND(AVG(EXTRACT(EPOCH FROM (fecha_resolucion - fecha_reporte)) / 3600), 2)
    FROM itil_incidentes
    WHERE fecha_resolucion IS NOT NULL
    AND fecha_reporte BETWEEN :inicio AND :fin
");
$stmt->execute($params);
$mttr = $stmt->fetchColumn() ?: 0;

/* SLA */
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL 
                         AND fecha_resolucion - fecha_reporte <= INTERVAL '24 hours') AS dentro,
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
");
$stmt->execute($params);
$slaRow = $stmt->fetch(PDO::FETCH_ASSOC);
$slaPorcentaje = ($slaRow['total'] > 0)
    ? round(($slaRow['dentro'] / $slaRow['total']) * 100, 1)
    : 0;

/* Backlog */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM itil_incidentes
    WHERE estado ILIKE 'En progreso'
    AND fecha_reporte BETWEEN :inicio AND :fin
");
$stmt->execute($params);
$backlog = $stmt->fetchColumn();

/* Incidentes por técnico */
$stmt = $pdo->prepare("
    SELECT COALESCE(u.nombre, 'Sin técnico') AS tecnico, COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY tecnico
    ORDER BY total DESC
");
$stmt->execute($params);
$porTecnico = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por tipo */
$stmt = $pdo->prepare("
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY titulo
    ORDER BY total DESC
");
$stmt->execute($params);
$porTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por estado */
$stmt = $pdo->prepare("
    SELECT estado, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY estado
    ORDER BY total DESC
");
$stmt->execute($params);
$porEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Tendencia mensual */
$stmt = $pdo->prepare("
    SELECT TO_CHAR(fecha_reporte, 'YYYY-MM') AS mes, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY mes
    ORDER BY mes
");
$stmt->execute($params);
$mensual = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por hora */
$stmt = $pdo->prepare("
    SELECT EXTRACT(HOUR FROM fecha_reporte) AS hora, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY hora
    ORDER BY hora
");
$stmt->execute($params);
$porHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por día */
$stmt = $pdo->prepare("
    SELECT EXTRACT(DOW FROM fecha_reporte) AS dow, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY dow
    ORDER BY dow
");
$stmt->execute($params);
$porDiaSemana = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Top técnicos */
$stmt = $pdo->prepare("
    SELECT COALESCE(u.nombre, 'Sin técnico') AS tecnico, COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY tecnico
    ORDER BY total DESC
    LIMIT 10
");
$stmt->execute($params);
$topTecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Top categorías */
$stmt = $pdo->prepare("
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY titulo
    ORDER BY total DESC
    LIMIT 10
");
$stmt->execute($params);
$topCategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   UBICACIÓN NORMALIZADA (SIN PISO, SIN ESCRITORIO)
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT 
        COALESCE(TRIM(SPLIT_PART(ubicacion_detalle, '/', 1)), 'Sin ubicación') AS ubicacion,
        COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
    GROUP BY ubicacion
    ORDER BY total DESC
");
$stmt->execute($params);
$porUbicacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Preparar datos JS */
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

$chartUbicacionLabels = array_column($porUbicacion, 'ubicacion');
$chartUbicacionData   = array_column($porUbicacion, 'total');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard de estadísticas</title>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
/* ============================================================
   VARIABLES DE COLOR
   ============================================================ */
:root {
    --bg: #F4F7FA;
    --sidebar-bg: #FFFFFF;
    --sidebar-hover: #E8EEF5;
    --card-bg: #FFFFFF;
    --text: #1F2933;
    --subtext: #6B7280;
    --primary: #0054A6;
    --accent: #FF7A00;
    --accent2: #E91E63;
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
    --accent: #FF9800;
    --accent2: #FF4081;
    --shadow: rgba(0,0,0,0.45);
}

/* ============================================================
   RESETEO
   ============================================================ */
body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
}

/* ============================================================
   SIDEBAR
   ============================================================ */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    box-shadow: 4px 0 20px var(--shadow);
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    left: 0;
    transition: width 0.25s ease;
    overflow: visible;
    z-index: 3000;
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

/* ============================================================
   TOPBAR
   ============================================================ */
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

/* ============================================================
   TOPBAR CON ÍCONOS
   ============================================================ */
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

/* ============================================================
   FILTRO
   ============================================================ */
.filtro-bar {
    position: fixed;
    top: 55px;
    left: 240px;
    right: 0;
    background: var(--card-bg);
    padding: 12px 18px;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 2px 8px var(--shadow);
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 2050;
}
.sidebar.collapsed ~ .filtro-bar { left: 70px; }

/* ============================================================
   MAIN
   ============================================================ */
.main {
    margin-left: 240px;
    padding: 25px;
    margin-top: 165px;
    transition: margin-left 0.25s ease;
}
.sidebar.collapsed ~ .main { margin-left: 70px; }

/* ============================================================
   GRIDS
   ============================================================ */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
    margin-bottom: 25px;
}

.dashboard-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 25px;
}

/* ============================================================
   CARDS
   ============================================================ */
.card, .chart-card, .table-box {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 22px 24px;
    box-shadow: 0 4px 14px var(--shadow);
    border: 1px solid rgba(0,0,0,0.06);
}

.chart-container {
    width: 100%;
    height: 260px;
}

.dashboard-title {
    text-align: center;
    margin-top: 0;
    margin-bottom: 5px;
    font-size: 26px;
    font-weight: 600;
}

.dashboard-subtitle {
    text-align: center;
    color: var(--subtext);
    font-size: 14px;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<?php include "sidebar.php"; ?>

<!-- ========================= -->
<!-- TOPBAR ITIL (CON ÍCONOS)  -->
<!-- ========================= -->
<div class="itil-topbar">

    <a href="itil_incidentes.php">
        <svg viewBox="0 0 24 24"><path d="M3 5h18v2H3zm0 6h18v2H3zm0 6h18v2H3z"/></svg>
        Incidentes
    </a>

    <a href="itil_incidente_nuevo.php">
        <svg viewBox="0 0 24 24"><path d="M19 11H13V5h-2v6H5v2h6v6h2v-6h6z"/></svg>
        Nuevo
    </a>

    <a href="itil_problemas.php">
        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        Problemas
    </a>

    <a href="itil_catalogo.php">
        <svg viewBox="0 0 24 24"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
        Catálogo Incidentes
    </a>

    <a href="itil_solicitudes.php">
        <svg viewBox="0 0 24 24"><path d="M3 3h18v4H3zm0 6h18v12H3z"/></svg>
        Solicitudes
    </a>

    <a href="itil_sla.php">
        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
        SLA
    </a>

    <a href="itil_estadisticas.php">
        <svg viewBox="0 0 24 24"><path d="M3 17h2v-7H3zm4 0h2V7H7zm4 0h2v-4h-2zm4 0h2V3h-2zm4 0h2v-9h-2z"/></svg>
        Estadísticas
    </a>

</div>

<div class="filtro-bar">
    <form method="GET" class="filtro-row">
        <input type="date" name="inicio" value="<?= $fecha_inicio ?>">
        <input type="date" name="fin" value="<?= $fecha_fin ?>">
        <button type="submit">Filtrar</button>
    </form>

    <div class="filtro-rapidos filtro-row">
        <form method="GET"><input type="hidden" name="rango" value="hoy"><button>Hoy</button></form>
        <form method="GET"><input type="hidden" name="rango" value="7"><button>Últimos 7 días</button></form>
        <form method="GET"><input type="hidden" name="rango" value="mes"><button>Mes actual</button></form>
    </div>
</div>

<div class="main">

    <h2 class="dashboard-title">Dashboard de estadísticas</h2>
    <div class="dashboard-subtitle">Vista general de incidentes, técnicos y comportamiento temporal</div>

    <!-- ========================= -->
    <!-- KPIs (3 × 3)              -->
    <!-- ========================= -->
    <div class="dashboard-grid">

        <div class="card">
            <h3>Total de incidentes</h3>
            <div class="kpi-value"><?= $total ?></div>
            <div class="kpi-sub">Registros en el rango</div>
        </div>

        <div class="card">
            <h3>Incidentes resueltos</h3>
            <div class="kpi-value"><?= $totalResueltos ?></div>
            <div class="kpi-sub">Estado = Resuelto</div>
        </div>

        <div class="card">
            <h3>Incidentes activos</h3>
            <div class="kpi-value"><?= $totalActivos ?></div>
            <div class="kpi-sub">Pendientes de Atención</div>
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
            <div class="kpi-sub">En proceso</div>
        </div>

    </div>

    <!-- ========================= -->
    <!-- GRÁFICAS 2×2              -->
    <!-- ========================= -->

    <div class="dashboard-2col">
        <div class="chart-card">
            <h3>Incidentes por técnico</h3>
            <div id="chartTecnico" class="chart-container"></div>
        </div>

        <div class="chart-card">
            <h3>Incidentes por tipo</h3>
            <div id="chartTipo" class="chart-container"></div>
        </div>
    </div>

    <div class="dashboard-2col">
        <div class="chart-card">
            <h3>Incidentes por estado</h3>
            <div id="chartEstado" class="chart-container"></div>
        </div>

        <div class="chart-card">
            <h3>Tendencia mensual</h3>
            <div id="chartMensual" class="chart-container"></div>
        </div>
    </div>

    <div class="dashboard-2col">
        <div class="chart-card">
            <h3>Incidentes por hora del día</h3>
            <div id="chartHora" class="chart-container"></div>
        </div>

        <div class="chart-card">
            <h3>Incidentes por día de la semana</h3>
            <div id="chartDiaSemana" class="chart-container"></div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- NUEVA GRÁFICA UBICACIÓN   -->
    <!-- ========================= -->
    <div class="dashboard-2col">
        <div class="chart-card">
            <h3>Incidentes por ubicación</h3>
            <div id="chartUbicacion" class="chart-container"></div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- TABLAS                    -->
    <!-- ========================= -->
    <div class="dashboard-2col">

        <div class="table-box">
            <h3>Top técnicos</h3>
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
            <h3>Top categorías</h3>
            <table>
                <tr>
                    <th>Categoría</th>
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

    </div>

</div> <!-- Cierra .main -->
<!-- ========================= -->
<!-- SCRIPTS APEXCHARTS        -->
<!-- ========================= -->
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

const chartUbicacionLabels = <?= json_encode($chartUbicacionLabels) ?>;
const chartUbicacionData   = <?= json_encode($chartUbicacionData) ?>;

/* Mapear DOW a nombres */
const dowNames = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
const chartDiaSemanaLabels = chartDiaSemanaLabelsRaw.map(d => dowNames[parseInt(d)]);

/* THEME */
const isDark = document.body.classList.contains('dark');
const textColor = getComputedStyle(document.body).getPropertyValue('--text').trim();

/* ============================================================
   GRÁFICAS PRINCIPALES
   ============================================================ */

/* Incidentes por técnico */
new ApexCharts(document.querySelector("#chartTecnico"), {
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    series: [{ name: 'Incidentes', data: chartTecnicoData }],
    xaxis: { categories: chartTecnicoLabels, labels: { style: { colors: textColor } } },
    plotOptions: { bar: { borderRadius: 6 } },
    colors: ['#0054A6'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* Incidentes por tipo (pie) */
new ApexCharts(document.querySelector("#chartTipo"), {
    chart: { type: 'pie', height: 280 },
    series: chartTipoData,
    labels: chartTipoLabels,
    colors: ['#0054A6', '#FF7A00', '#E91E63', '#00AEEF'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* Incidentes por estado */
new ApexCharts(document.querySelector("#chartEstado"), {
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
    series: [{ name: 'Incidentes', data: chartEstadoData }],
    xaxis: { categories: chartEstadoLabels, labels: { style: { colors: textColor } } },
    colors: ['#FF7A00'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* Tendencia mensual */
new ApexCharts(document.querySelector("#chartMensual"), {
    chart: { type: 'line', height: 280, toolbar: { show: false } },
    series: [{ name: 'Incidentes', data: chartMensualData }],
    xaxis: { categories: chartMensualLabels, labels: { style: { colors: textColor } } },
    colors: ['#00E5FF'],
    stroke: { curve: 'smooth', width: 5 },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 0,
            opacityFrom: 0.9,
            opacityTo: 0.25,
            stops: [0, 100]
        }
    },
    markers: {
        size: 5,
        colors: ['#00E5FF'],
        strokeColors: '#ffffff',
        strokeWidth: 2
    },
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* Por hora */
new ApexCharts(document.querySelector("#chartHora"), {
    chart: { type: 'heatmap', height: 280 },
    series: [{
        name: 'Incidentes',
        data: chartHoraLabels.map((h, i) => ({ x: h + ':00', y: chartHoraData[i] }))
    }],
    colors: ['#00AEEF'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* Por día */
new ApexCharts(document.querySelector("#chartDiaSemana"), {
    chart: { type: 'bar', height: 280 },
    series: [{ name: 'Incidentes', data: chartDiaSemanaData }],
    xaxis: { categories: chartDiaSemanaLabels, labels: { style: { colors: textColor } } },
    plotOptions: { bar: { borderRadius: 6 } },
    colors: ['#E91E63'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* ============================================================
   NUEVA GRÁFICA: UBICACIÓN NORMALIZADA
   ============================================================ */
new ApexCharts(document.querySelector("#chartUbicacion"), {
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    series: [{ name: 'Incidentes', data: chartUbicacionData }],
    xaxis: { categories: chartUbicacionLabels, labels: { style: { colors: textColor } } },
    plotOptions: { bar: { borderRadius: 6 } },
    colors: ['#00AEEF'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

</script>

</body>
</html>
