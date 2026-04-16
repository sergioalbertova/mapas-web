<?php
require "session_config.php";
require "db.php";

$hoy = date("Y-m-d");

$fecha_inicio = $_GET['inicio'] ?? $hoy;
$fecha_fin    = $_GET['fin']    ?? $hoy;

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

$tecnicoFiltro = isset($_GET['tecnico']) ? intval($_GET['tecnico']) : null;

function filtroTecnicoSQL(&$sql, &$params, $tecnicoFiltro) {
    if ($tecnicoFiltro) {
        $sql .= " AND tecnico_asignado = :tecnico";
        $params[':tecnico'] = $tecnicoFiltro;
    }
}

$tecnico_id = intval($_SESSION['user_id']);
$stmt = $pdo->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$tecnico_id]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreTecnico = $tecnico ? $tecnico['usuario'] . " - " . $tecnico['nombre'] : "Usuario";

$paramsBase = [
    ':inicio' => $fecha_inicio . " 00:00:00",
    ':fin'    => $fecha_fin . " 23:59:59"
];

/* TOTAL */
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

/* CERRADOS */
$sql = "
    SELECT COUNT(*) 
    FROM itil_incidentes
    WHERE estado ILIKE 'Cerrado'
    AND fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnicoFiltro);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$totalResueltos = $stmt->fetchColumn();

/* ACTIVOS */
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

/* BACKLOG */
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

/* POR TÉCNICO */
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

/* POR TIPO */
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

/* POR ESTADO */
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

/* MENSUAL */
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

/* POR HORA */
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

/* POR DÍA SEMANA */
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

/* TOP TÉCNICOS */
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

/* TOP CATEGORÍAS */
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

/* UBICACIÓN */
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

/* ARRAYS PARA JS */
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

/* URL SIN TÉCNICO */
$paramsURL = $_GET;
unset($paramsURL['tecnico']);
$urlSinTecnico = "itil_estadisticas.php";
if (!empty($paramsURL)) {
    $urlSinTecnico .= "?" . http_build_query($paramsURL);
}
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
    <a href="itil_incidentes.php">Incidentes</a>
    <a href="itil_incidente_nuevo.php">Nuevo</a>
    <a href="itil_problemas.php">Problemas</a>
    <a href="itil_catalogo.php">Catálogo Incidentes</a>
    <a href="itil_solicitudes.php">Solicitudes</a>
    <a href="itil_sla.php">SLA</a>
    <a href="itil_estadisticas.php">Estadísticas</a>
</div>

<!-- FILTROS SUPERIORES -->
<div class="filtro-bar">

    <!-- FILTRO POR FECHAS -->
    <form method="GET" class="filtro-row">
        <input type="date" name="inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
        <input type="date" name="fin" value="<?= htmlspecialchars($fecha_fin) ?>">

        <?php if ($tecnicoFiltro): ?>
            <input type="hidden" name="tecnico" value="<?= $tecnicoFiltro ?>">
        <?php endif; ?>

        <button type="submit">Filtrar</button>
    </form>

    <!-- BOTONES RÁPIDOS -->
    <div class="filtro-rapidos filtro-row">

        <form method="GET">
            <input type="hidden" name="rango" value="hoy">
            <?php if ($tecnicoFiltro): ?>
                <input type="hidden" name="tecnico" value="<?= $tecnicoFiltro ?>">
            <?php endif; ?>
            <button>Hoy</button>
        </form>

        <form method="GET">
            <input type="hidden" name="rango" value="7">
            <?php if ($tecnicoFiltro): ?>
                <input type="hidden" name="tecnico" value="<?= $tecnicoFiltro ?>">
            <?php endif; ?>
            <button>Últimos 7 días</button>
        </form>

        <form method="GET">
            <input type="hidden" name="rango" value="mes">
            <?php if ($tecnicoFiltro): ?>
                <input type="hidden" name="tecnico" value="<?= $tecnicoFiltro ?>">
            <?php endif; ?>
            <button>Mes actual</button>
        </form>

    </div>
</div>

<!-- BANNER DE FILTRO POR TÉCNICO -->
<?php if ($tecnicoFiltro): ?>
<div class="filtro-filtro-activo">
    Filtrando por técnico:
    <strong>
        <?= htmlspecialchars($pdo->query("SELECT nombre FROM usuarios WHERE id = $tecnicoFiltro")->fetchColumn()) ?>
    </strong>
    <a href="<?= htmlspecialchars($urlSinTecnico) ?>">Quitar filtro</a>
</div>
<?php endif; ?>

<div class="main">

    <h2 class="dashboard-title">Dashboard de estadísticas</h2>
    <div class="dashboard-subtitle">Vista general de incidentes, técnicos y comportamiento temporal</div>

    <!-- KPIs -->
    <div class="dashboard-grid">

        <div class="card">
            <h3>Total de incidentes</h3>
            <div class="kpi-value kpi-total"><?= $total ?></div>
            <div class="kpi-sub">Registros en el rango</div>
        </div>

        <div class="card">
            <h3>Incidentes cerrados</h3>
            <div class="kpi-value kpi-resueltos"><?= $totalResueltos ?></div>
            <div class="kpi-sub">Estado = Cerrado</div>
        </div>

        <div class="card">
            <h3>Incidentes activos</h3>
            <div class="kpi-value kpi-activos"><?= $totalActivos ?></div>
            <div class="kpi-sub">Pendientes de Atención</div>
        </div>

        <div class="card">
            <h3>MTTR</h3>
            <div class="kpi-value kpi-mttr"><?= $mttr ?> h</div>
            <div class="kpi-sub">Tiempo promedio de resolución</div>
        </div>

        <div class="card">
            <h3>SLA cumplido</h3>
            <div class="kpi-value kpi-sla"><?= $slaPorcentaje ?>%</div>
            <div class="kpi-sub">Resueltos &lt;= 24h</div>
        </div>

        <div class="card">
            <h3>Backlog</h3>
            <div class="kpi-value kpi-backlog"><?= $backlog ?></div>
            <div class="kpi-sub">En proceso</div>
        </div>

    </div>

    <!-- GRÁFICAS PRINCIPALES -->
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

    <!-- UBICACIÓN A FILA COMPLETA -->
    <div class="dashboard-full">
        <div class="chart-card">
            <h3>Incidentes por ubicación</h3>
            <div id="chartUbicacion" class="chart-container"></div>
        </div>
    </div>

    <!-- TABLAS -->
    <div class="dashboard-2col">

        <div class="table-box">
            <h3>Top técnicos</h3>
            <table class="tabla-top-tecnicos">
                <tr><th>Técnico</th><th>Incidentes</th></tr>
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
            <table class="tabla-top-categorias">
                <tr><th>Categoría</th><th>Incidentes</th></tr>
                <?php foreach ($topCategorias as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                    <td><?= $row['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>

</div> <!-- FIN MAIN -->
<script>
/* ===========================
   MODO OSCURO
=========================== */
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}

/* ===========================
   VARIABLES DESDE PHP
=========================== */
let chartTecnicoIDs    = <?= json_encode($chartTecnicoIDs) ?>;
let chartTecnicoLabels = <?= json_encode($chartTecnicoLabels) ?>;
let chartTecnicoData   = <?= json_encode($chartTecnicoData) ?>;

let chartTipoLabels = <?= json_encode($chartTipoLabels) ?>;
let chartTipoData   = <?= json_encode($chartTipoData) ?>;

let chartEstadoLabels = <?= json_encode($chartEstadoLabels) ?>;
let chartEstadoData   = <?= json_encode($chartEstadoData) ?>;

let chartMensualLabels = <?= json_encode($chartMensualLabels) ?>;
let chartMensualData   = <?= json_encode($chartMensualData) ?>;

let chartHoraLabels = <?= json_encode($chartHoraLabels) ?>;
let chartHoraData   = <?= json_encode($chartHoraData) ?>;

let chartDiaSemanaLabelsRaw = <?= json_encode($chartDiaSemanaLabels) ?>;
let chartDiaSemanaData      = <?= json_encode($chartDiaSemanaData) ?>;

let chartUbicacionLabels = <?= json_encode($chartUbicacionLabels) ?>;
let chartUbicacionData   = <?= json_encode($chartUbicacionData) ?>;

const dowNames = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
let chartDiaSemanaLabels = chartDiaSemanaLabelsRaw.map(d => dowNames[parseInt(d)]);

const isDark = document.body.classList.contains('dark');
const textColor = getComputedStyle(document.body).getPropertyValue('--text').trim();

/* ===========================
   GRÁFICA: INCIDENTES POR TÉCNICO
=========================== */
let chartTecnico = new ApexCharts(document.querySelector("#chartTecnico"), {
    chart: { 
        type: 'bar', 
        height: 280, 
        toolbar: { show: false },
        events: {
            dataPointSelection: function(event, chartContext, config) {
                let tecnicoID = chartTecnicoIDs[config.dataPointIndex];
                const params = new URLSearchParams(window.location.search);
                params.set("tecnico", tecnicoID);
                history.replaceState(null, "", "?" + params.toString());
                cargarDashboard();
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
});
chartTecnico.render();

/* ===========================
   GRÁFICA: INCIDENTES POR TIPO
=========================== */
let chartTipo = new ApexCharts(document.querySelector("#chartTipo"), {
    chart: { type: 'pie', height: 280 },
    series: chartTipoData,
    labels: chartTipoLabels,
    colors: ['#0054A6', '#FF7A00', '#E91E63', '#00AEEF'],
    theme: { mode: isDark ? 'dark' : 'light' }
});
chartTipo.render();

/* ===========================
   GRÁFICA: INCIDENTES POR ESTADO
=========================== */
let chartEstado = new ApexCharts(document.querySelector("#chartEstado"), {
    chart: { type: 'bar', height: 280, toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
    series: [{ name: 'Incidentes', data: chartEstadoData }],
    xaxis: { categories: chartEstadoLabels, labels: { style: { colors: textColor } } },
    colors: ['#FF7A00'],
    theme: { mode: isDark ? 'dark' : 'light' }
});
chartEstado.render();

/* ===========================
   GRÁFICA: TENDENCIA MENSUAL
=========================== */
let chartMensual = new ApexCharts(document.querySelector("#chartMensual"), {
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
});
chartMensual.render();

/* ===========================
   GRÁFICA: POR HORA
=========================== */
let chartHora = new ApexCharts(document.querySelector("#chartHora"), {
    chart: { type: 'heatmap', height: 280 },
    series: [{
        name: 'Incidentes',
        data: chartHoraLabels.map((h, i) => ({ x: h + ':00', y: chartHoraData[i] }))
    }],
    colors: ['#00AEEF'],
    theme: { mode: isDark ? 'dark' : 'light' }
});
chartHora.render();

/* ===========================
   GRÁFICA: POR DÍA DE LA SEMANA
=========================== */
let chartDiaSemana = new ApexCharts(document.querySelector("#chartDiaSemana"), {
    chart: { type: 'bar', height: 280 },
    series: [{ name: 'Incidentes', data: chartDiaSemanaData }],
    xaxis: { categories: chartDiaSemanaLabels, labels: { style: { colors: textColor } } },
    plotOptions: { bar: { borderRadius: 6 } },
    colors: ['#E91E63'],
    theme: { mode: isDark ? 'dark' : 'light' }
});
chartDiaSemana.render();

/* ===========================
   GRÁFICA: UBICACIÓN (FILA COMPLETA)
=========================== */
let chartUbicacion = new ApexCharts(document.querySelector("#chartUbicacion"), {
    chart: { type: 'bar', height: 350, toolbar: { show: false } },
    series: [{ name: 'Incidentes', data: chartUbicacionData }],
    xaxis: { 
        categories: chartUbicacionLabels,
        labels: { 
            style: { colors: textColor },
            rotate: -45,
            trim: false
        }
    },
    plotOptions: { 
        bar: { 
            borderRadius: 6,
            columnWidth: '45%'
        } 
    },
    colors: ['#00AEEF'],
    theme: { mode: isDark ? 'dark' : 'light' }
});
chartUbicacion.render();
/* ===========================================================
   AJAX: RECARGAR TODO SIN REFRESH
=========================================================== */
function cargarDashboard() {
    const params = new URLSearchParams(window.location.search);

    fetch("api_estadisticas.php?" + params.toString())
        .then(r => r.json())
        .then(data => {

            /* ===========================
               ACTUALIZAR KPIs
            ============================ */
            document.querySelector(".kpi-total").textContent      = data.total;
            document.querySelector(".kpi-resueltos").textContent  = data.resueltos;
            document.querySelector(".kpi-activos").textContent    = data.activos;
            document.querySelector(".kpi-mttr").textContent       = data.mttr + " h";
            document.querySelector(".kpi-sla").textContent        = data.sla + "%";
            document.querySelector(".kpi-backlog").textContent    = data.backlog;

            /* ===========================
               ACTUALIZAR GRÁFICA: TÉCNICOS
            ============================ */
            const tecnicosLabels = data.porTecnico.map(x => x.nombre);
            const tecnicosData   = data.porTecnico.map(x => x.total);
            chartTecnicoIDs      = data.porTecnico.map(x => x.id);

            chartTecnico.updateSeries([{ data: tecnicosData }]);
            chartTecnico.updateOptions({ xaxis: { categories: tecnicosLabels } });

            // Reasignar evento de clic después de actualizar datos
            chartTecnico.updateOptions({
                chart: {
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            let tecnicoID = chartTecnicoIDs[config.dataPointIndex];
                            const params = new URLSearchParams(window.location.search);
                            params.set("tecnico", tecnicoID);
                            history.replaceState(null, "", "?" + params.toString());
                            cargarDashboard();
                        }
                    }
                }
            });

            /* ===========================
               ACTUALIZAR GRÁFICA: TIPO
            ============================ */
            const tipoLabels = data.porTipo.map(x => x.titulo);
            const tipoData   = data.porTipo.map(x => x.total);
            chartTipo.updateSeries(tipoData);
            chartTipo.updateOptions({ labels: tipoLabels });

            /* ===========================
               ACTUALIZAR GRÁFICA: ESTADO
            ============================ */
            const estadoLabels = data.porEstado.map(x => x.estado);
            const estadoData   = data.porEstado.map(x => x.total);
            chartEstado.updateSeries([{ data: estadoData }]);
            chartEstado.updateOptions({ xaxis: { categories: estadoLabels } });

            /* ===========================
               ACTUALIZAR GRÁFICA: MENSUAL
            ============================ */
            const mensualLabels = data.mensual.map(x => x.mes);
            const mensualData   = data.mensual.map(x => x.total);
            chartMensual.updateSeries([{ data: mensualData }]);
            chartMensual.updateOptions({ xaxis: { categories: mensualLabels } });

            /* ===========================
               ACTUALIZAR GRÁFICA: HORA
            ============================ */
            const horaLabels = data.porHora.map(x => x.hora);
            const horaData   = data.porHora.map(x => x.total);
            chartHora.updateSeries([{
                name: 'Incidentes',
                data: horaLabels.map((h, i) => ({ x: h + ':00', y: horaData[i] }))
            }]);

            /* ===========================
               ACTUALIZAR GRÁFICA: DÍA SEMANA
            ============================ */
            const dowNames = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
            const dowLabels = data.porDiaSemana.map(x => dowNames[parseInt(x.dow)]);
            const dowData   = data.porDiaSemana.map(x => x.total);
            chartDiaSemana.updateSeries([{ data: dowData }]);
            chartDiaSemana.updateOptions({ xaxis: { categories: dowLabels } });

            /* ===========================
               ACTUALIZAR GRÁFICA: UBICACIÓN
            ============================ */
            const ubicLabels = data.ubicacion.map(x => x.ubicacion);
            const ubicData   = data.ubicacion.map(x => x.total);
            chartUbicacion.updateSeries([{ data: ubicData }]);
            chartUbicacion.updateOptions({ xaxis: { categories: ubicLabels } });

            /* ===========================
               ACTUALIZAR TABLA: TOP TÉCNICOS
            ============================ */
            const tablaTec = document.querySelector(".tabla-top-tecnicos");
            if (tablaTec) {
                tablaTec.innerHTML = "<tr><th>Técnico</th><th>Incidentes</th></tr>";
                data.topTecnicos.forEach(r => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `<td>${r.tecnico}</td><td>${r.total}</td>`;
                    tablaTec.appendChild(tr);
                });
            }

            /* ===========================
               ACTUALIZAR TABLA: TOP CATEGORÍAS
            ============================ */
            const tablaCat = document.querySelector(".tabla-top-categorias");
            if (tablaCat) {
                tablaCat.innerHTML = "<tr><th>Categoría</th><th>Incidentes</th></tr>";
                data.topCategorias.forEach(r => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `<td>${r.titulo}</td><td>${r.total}</td>`;
                    tablaCat.appendChild(tr);
                });
            }

        })
        .catch(err => console.error("Error cargando dashboard:", err));
}

/* Ejecutar al cargar */
cargarDashboard();
</script>

</body>
</html>
