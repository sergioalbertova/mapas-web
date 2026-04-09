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
   ESTADÍSTICAS OPTIMIZADAS
   ============================================================ */

/* Total */
$total = $pdo->query("SELECT COUNT(*) FROM itil_incidentes")->fetchColumn();

/* Resueltos */
$totalResueltos = $pdo->query("
    SELECT COUNT(*) FROM itil_incidentes 
    WHERE estado ILIKE 'Resuelto'
")->fetchColumn();

/* Activos */
$totalActivos = $pdo->query("
    SELECT COUNT(*) FROM itil_incidentes 
    WHERE estado NOT ILIKE 'Resuelto'
")->fetchColumn();

/* MTTR */
$mttr = $pdo->query("
    SELECT ROUND(AVG(EXTRACT(EPOCH FROM (fecha_resolucion - fecha_reporte)) / 3600), 2)
    FROM itil_incidentes
    WHERE fecha_resolucion IS NOT NULL
")->fetchColumn();
$mttr = $mttr ?: 0;

/* SLA <= 24h */
$slaRow = $pdo->query("
    SELECT 
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL 
                         AND fecha_resolucion - fecha_reporte <= INTERVAL '24 hours') AS dentro,
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL) AS total
    FROM itil_incidentes
")->fetch(PDO::FETCH_ASSOC);

$slaPorcentaje = ($slaRow['total'] > 0)
    ? round(($slaRow['dentro'] / $slaRow['total']) * 100, 1)
    : 0;

/* Backlog */
$backlog = $totalActivos;

/* Incidentes por técnico */
$porTecnico = $pdo->query("
    SELECT COALESCE(u.nombre, 'Sin técnico') AS tecnico, COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    GROUP BY tecnico
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por tipo */
$porTipo = $pdo->query("
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY titulo
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por estado */
$porEstado = $pdo->query("
    SELECT estado, COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY estado
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* Tendencia mensual */
$mensual = $pdo->query("
    SELECT TO_CHAR(fecha_reporte, 'YYYY-MM') AS mes, COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY mes
    ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por hora */
$porHora = $pdo->query("
    SELECT EXTRACT(HOUR FROM fecha_reporte) AS hora, COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY hora
    ORDER BY hora
")->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por día */
$porDiaSemana = $pdo->query("
    SELECT EXTRACT(DOW FROM fecha_reporte) AS dow, COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY dow
    ORDER BY dow
")->fetchAll(PDO::FETCH_ASSOC);

/* Top técnicos */
$topTecnicos = $pdo->query("
    SELECT COALESCE(u.nombre, 'Sin técnico') AS tecnico, COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    GROUP BY tecnico
    ORDER BY total DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/* Top categorías */
$topCategorias = $pdo->query("
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    GROUP BY titulo
    ORDER BY total DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/* Top usuarios */
$topUsuarios = $pdo->query("
    SELECT COALESCE(au.nomuser, 'Desconocido') AS usuario, COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN activeuser au ON au.idu = i.usuario_final_id
    GROUP BY usuario
    ORDER BY total DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard de estadísticas</title>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>
/* ============================================================
   VARIABLES DE COLOR (LIGHT / DARK)
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
   ESTILOS GENERALES
   ============================================================ */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* ============================================================
   DASHBOARD TITLE (CENTRADO, B2 + S3)
   ============================================================ */
.dashboard-title {
    text-align: center;
    margin-bottom: 5px;
    font-size: 26px;
    font-weight: 600;
    letter-spacing: -0.3px;
}

.dashboard-subtitle {
    text-align: center;
    color: var(--subtext);
    font-size: 14px;
    margin-bottom: 10px;
}

.dashboard-divider {
    width: 180px;
    height: 1px;
    background: var(--subtext);
    opacity: 0.25;
    margin: 0 auto 25px auto;
    border-radius: 2px;
}

/* ============================================================
   CARDS (KPIs)
   ============================================================ */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
    margin-bottom: 25px;
}

@media (max-width: 1200px) {
    .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .dashboard-grid { grid-template-columns: repeat(1, 1fr); }
}

.card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 22px 24px;
    box-shadow: 0 4px 14px var(--shadow);
    border: 1px solid rgba(0,0,0,0.06);
    min-height: 130px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.card h3 {
    margin: 0 0 6px;
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
    text-align: left;
}

.kpi-value {
    font-size: 30px;
    font-weight: bold;
    margin-bottom: 4px;
}

.kpi-sub {
    font-size: 13px;
    color: var(--subtext);
}
</style>
</head>

<body>

<!-- ========================= -->
<!-- SIDEBAR (INCLUIDO)        -->
<!-- ========================= -->
<?php include "sidebar.php"; ?>

<!-- ========================= -->
<!-- TOPBAR                    -->
<!-- ========================= -->
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
        Catálogo
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

<!-- ========================= -->
<!-- MAIN                      -->
<!-- ========================= -->
<div class="main">

    <!-- TÍTULO PRINCIPAL -->
    <h2 class="dashboard-title">Dashboard de estadísticas</h2>
    <div class="dashboard-subtitle">Vista general de incidentes, técnicos y comportamiento temporal</div>
    <div class="dashboard-divider"></div>

    <!-- ========================= -->
    <!-- KPIs (3 × 3)              -->
    <!-- ========================= -->
    <div class="dashboard-grid">

        <div class="card">
            <h3>Total de incidentes</h3>
            <div class="kpi-value"><?= $total ?></div>
            <div class="kpi-sub">Todos los registros</div>
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
            <div class="kpi-sub">Incidentes abiertos</div>
        </div>

    </div>

    <!-- ========================= -->
    <!-- GRÁFICAS PRINCIPALES      -->
    <!-- ========================= -->
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
            <h3>Tendencia mensual</h3>
            <div id="chartMensual" class="chart-container"></div>
        </div>

    </div>

        <!-- ========================= -->
    <!-- HEATMAPS                  -->
    <!-- ========================= -->
    <div class="dashboard-grid">

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
    <!-- TABLAS                    -->
    <!-- ========================= -->
    <div class="dashboard-grid">

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
    chart: { type: 'bar', height: 280, toolbar: { show: false }, animations: { easing: 'easeinout', speed: 600 } },
    series: [{ name: 'Incidentes', data: chartTecnicoData }],
    xaxis: { categories: chartTecnicoLabels, labels: { style: { colors: textColor } } },
    plotOptions: { bar: { borderRadius: 6 } },
    colors: ['#0054A6'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* Incidentes por tipo (donut) */
new ApexCharts(document.querySelector("#chartTipo"), {
    chart: { type: 'donut', height: 280 },
    series: chartTipoData,
    labels: chartTipoLabels,
    plotOptions: { pie: { donut: { labels: { show: true } } } },
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
    stroke: { curve: 'smooth', width: 3 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
    colors: ['#0054A6'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();

/* ============================================================
   HEATMAPS
   ============================================================ */

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
</script>

</body>
</html>
