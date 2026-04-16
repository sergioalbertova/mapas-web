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
   FILTRO POR TÉCNICO (ID)
   ============================================================ */
$tecnicoFiltro = isset($_GET['tecnico']) ? intval($_GET['tecnico']) : null;

/* Función para agregar filtro dinámico */
function filtroTecnicoSQL(&$sql, &$params, $tecnicoFiltro) {
    if ($tecnicoFiltro) {
        $sql .= " AND tecnico_asignado = :tecnico";
        $params[':tecnico'] = $tecnicoFiltro;
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
   CONSULTAS SQL FILTRADAS POR FECHA Y TÉCNICO
   ============================================================ */

$paramsBase = [
    ':inicio' => $fecha_inicio . " 00:00:00",
    ':fin'    => $fecha_fin . " 23:59:59"
];

/* Total */
$sql = "
    SELECT COUNT(*) 
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

/* Resueltos */
$sql = "
    SELECT COUNT(*) 
    FROM itil_incidentes
    WHERE estado ILIKE 'Resuelto'
    AND fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$totalResueltos = $stmt->fetchColumn();

/* Activos */
$sql = "
    SELECT COUNT(*) 
    FROM itil_incidentes
    WHERE estado ILIKE ANY (ARRAY['Activo','Abierto','Pendiente','En espera'])
    AND fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$totalActivos = $stmt->fetchColumn();

/* MTTR */
$sql = "
    SELECT ROUND(AVG(EXTRACT(EPOCH FROM (fecha_resolucion - fecha_reporte)) / 3600), 2)
    FROM itil_incidentes
    WHERE fecha_resolucion IS NOT NULL
    AND fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mttr = $stmt->fetchColumn() ?: 0;

/* SLA */
$sql = "
    SELECT 
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL 
                         AND fecha_resolucion - fecha_reporte <= INTERVAL '24 hours') AS dentro,
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$slaRow = $stmt->fetch(PDO::FETCH_ASSOC);
$slaPorcentaje = ($slaRow['total'] > 0)
    ? round(($slaRow['dentro'] / $slaRow['total']) * 100, 1)
    : 0;

/* Backlog */
$sql = "
    SELECT COUNT(*) 
    FROM itil_incidentes
    WHERE estado ILIKE 'En progreso'
    AND fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$backlog = $stmt->fetchColumn();

/* Incidentes por técnico (IDs + nombres) */
$sql = "
    SELECT 
        u.id AS tecnico_id,
        COALESCE(u.nombre, 'Sin técnico') AS tecnico_nombre,
        COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY tecnico_id, tecnico_nombre ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$porTecnico = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por tipo */
$sql = "
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY titulo ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$porTipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Incidentes por estado */
$sql = "
    SELECT estado, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY estado ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$porEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Tendencia mensual */
$sql = "
    SELECT TO_CHAR(fecha_reporte, 'YYYY-MM') AS mes, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY mes ORDER BY mes";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mensual = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por hora */
$sql = "
    SELECT EXTRACT(HOUR FROM fecha_reporte) AS hora, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY hora ORDER BY hora";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$porHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Heatmap por día */
$sql = "
    SELECT EXTRACT(DOW FROM fecha_reporte) AS dow, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY dow ORDER BY dow";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$porDiaSemana = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Top técnicos */
$sql = "
    SELECT 
        COALESCE(u.nombre, 'Sin técnico') AS tecnico,
        COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY tecnico ORDER BY total DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$topTecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Top categorías */
$sql = "
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY titulo ORDER BY total DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$topCategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Ubicación normalizada */
$sql = "
    SELECT 
        COALESCE(TRIM(SPLIT_PART(ubicacion_detalle, '/', 1)), 'Sin ubicación') AS ubicacion,
        COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$sql .= " GROUP BY ubicacion ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$porUbicacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   PREPARAR DATOS PARA JS
   ============================================================ */
$chartTecnicoIDs    = array_column($porTecnico, 'tecnico_id');
$chartTecnicoLabels = array_column($porTecnico, 'tecnico_nombre');
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
<link rel="stylesheet" href="itil_estadisticas.css">

</head>
<body>

<?php include "sidebar.php"; ?>

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
        Catálogo Incidentes
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

<!-- FILTRO SUPERIOR -->
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

<?php if ($tecnicoFiltro): ?>
<div class="filtro-filtro-activo" 
     style="max-width:900px;margin:0 auto 20px auto;padding:15px;background:#d9ecff;border-radius:8px;text-align:center;">
    Filtrando por técnico:
    <strong>
        <?= htmlspecialchars($pdo->query("SELECT nombre FROM usuarios WHERE id = $tecnicoFiltro")->fetchColumn()) ?>
    </strong>
   <?php
    // Obtener todos los parámetros actuales
    $params = $_GET;

    // Quitar el filtro de técnico si existe
    unset($params['tecnico']);

    // Reconstruir la URL sin perder fechas
    $urlSinTecnico = "itil_estadisticas.php";
    if (!empty($params)) {
        $urlSinTecnico .= "?" . http_build_query($params);
    }
?>
<a href="<?= $urlSinTecnico ?>" style="margin-left:15px;">Quitar filtro</a>

</div>


<?php endif; ?>
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
   <div class="dashboard-full">
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
/* ============================================================
   DATOS DESDE PHP HACIA JS
   ============================================================ */
const chartTecnicoIDs    = <?= json_encode($chartTecnicoIDs) ?>;
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

/* Incidentes por técnico (CON FILTRO POWER BI) */
new ApexCharts(document.querySelector("#chartTecnico"), {
    chart: { 
        type: 'bar', 
        height: 280, 
        toolbar: { show: false },
       events: {
    dataPointSelection: function(event, chartContext, config) {

        let tecnicoID = chartTecnicoIDs[config.dataPointIndex];

        // Obtener parámetros actuales de la URL
        const params = new URLSearchParams(window.location.search);

        // Reemplazar/agregar el técnico seleccionado
        params.set("tecnico", tecnicoID);

        // Redirigir con TODOS los parámetros actuales
        window.location.search = params.toString();
    }
}

    },
    series: [{ name: 'Incidentes', data: chartTecnicoData }],
    xaxis: { 
        categories: chartTecnicoLabels, 
        labels: { style: { colors: textColor } } 
    },
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

/* Ubicación normalizada */
new ApexCharts(document.querySelector("#chartUbicacion"), {
    chart: { 
        type: 'bar', 
        height: 350, 
        toolbar: { show: false } 
    },
    series: [{ 
        name: 'Incidentes', 
        data: chartUbicacionData 
    }],
    xaxis: { 
        categories: chartUbicacionLabels,
        labels: { 
            style: { colors: textColor },
            rotate: -45,     // ← Inclina etiquetas
            trim: false      // ← No recorta texto
        }
    },
    plotOptions: { 
        bar: { 
            borderRadius: 6,
            columnWidth: '45%'  // ← Mejor proporción visual
        } 
    },
    colors: ['#00AEEF'],
    theme: { mode: isDark ? 'dark' : 'light' }
}).render();


</script>

</body>
</html>
