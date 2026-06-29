<?php
require "session_config.php";
require "db.php";

$hoy = date("Y-m-d");

$fecha_inicio = $_GET['inicio'] ?? $hoy;
$fecha_fin    = $_GET['fin'] ?? $hoy;

if (isset($_GET['rango'])) {

    if ($_GET['rango'] === 'hoy') {
        $fecha_inicio = $hoy;
        $fecha_fin = $hoy;
    }

    if ($_GET['rango'] === '7') {
        $fecha_inicio = date("Y-m-d", strtotime("-6 days"));
        $fecha_fin = $hoy;
    }

    if ($_GET['rango'] === 'mes') {
        $fecha_inicio = date("Y-m-01");
        $fecha_fin = $hoy;
    }
}

$tecnicoFiltro = isset($_GET['tecnico'])
    ? intval($_GET['tecnico'])
    : null;

$paramsBase = [
    ':inicio' => $fecha_inicio . " 00:00:00",
    ':fin'    => $fecha_fin . " 23:59:59"
];

function filtroTecnicoSQL(&$sql, &$params, $tecnicoFiltro)
{
    if ($tecnicoFiltro) {
        $sql .= " AND a.idingeniero = :tecnico";
        $params[':tecnico'] = $tecnicoFiltro;
    }
}

$sql = "
SELECT COUNT(*)
FROM actividades_extras a
WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql, $params, $tecnicoFiltro);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$total = $stmt->fetchColumn();

$sql = "
SELECT COUNT(*)
FROM actividades_extras a
WHERE LOWER(estatus)='completado'
AND fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$totalCompletadas = $stmt->fetchColumn();

$sql = "
SELECT COUNT(*)
FROM actividades_extras a
WHERE LOWER(estatus) <> 'completado'
AND fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$totalPendientes = $stmt->fetchColumn();

$sql = "
SELECT ROUND(AVG(duracion_minutos),2)
FROM actividades_extras a
WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$duracionPromedio = $stmt->fetchColumn() ?: 0;

$sql = "
SELECT COALESCE(SUM(duracion_minutos),0)
FROM actividades_extras a
WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$tiempoTotal = $stmt->fetchColumn();

$sql = "
SELECT COUNT(DISTINCT idingeniero)
FROM actividades_extras a
WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$tecnicosActivos = $stmt->fetchColumn();


$sql = "
    SELECT
        u.id,
        COALESCE(u.nombre,'Sin técnico') AS tecnico,
        COUNT(*) AS total
    FROM actividades_extras a
    LEFT JOIN usuarios u
        ON u.id = a.idingeniero
    WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$sql .= "
    GROUP BY u.id,u.nombre
    ORDER BY total DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$porTecnico = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chartTecnicoIDs    = array_column($porTecnico,'id');
$chartTecnicoLabels = array_column($porTecnico,'tecnico');
$chartTecnicoData   = array_column($porTecnico,'total');


$sql = "
    SELECT
        c.actividad,
        COUNT(*) AS total
    FROM actividades_extras a
    INNER JOIN catalogo_actividades c
        ON c.idactividad = a.idactividad
    WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$sql .= "
    GROUP BY c.actividad
    ORDER BY total DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$porActividad = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chartActividadLabels = array_column($porActividad,'actividad');
$chartActividadData   = array_column($porActividad,'total');

$sql = "
    SELECT
        estatus,
        COUNT(*) AS total
    FROM actividades_extras a
    WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$sql .= "
    GROUP BY estatus
    ORDER BY total DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$porEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chartEstadoLabels = array_column($porEstado,'estatus');
$chartEstadoData   = array_column($porEstado,'total');

$sql = "
    SELECT
        TO_CHAR(fecha_inicio,'YYYY-MM') AS mes,
        COUNT(*) AS total
    FROM actividades_extras a
    WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$sql .= "
    GROUP BY mes
    ORDER BY mes
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$mensual = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chartMensualLabels = array_column($mensual,'mes');
$chartMensualData   = array_column($mensual,'total');

$sql = "
    SELECT
        COALESCE(u.nombre,'Sin técnico') AS tecnico,
        COUNT(*) AS total
    FROM actividades_extras a
    LEFT JOIN usuarios u
        ON u.id = a.idingeniero
    WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$sql .= "
    GROUP BY tecnico
    ORDER BY total DESC
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$topTecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT
        c.actividad,
        COUNT(*) AS total
    FROM actividades_extras a
    INNER JOIN catalogo_actividades c
        ON c.idactividad = a.idactividad
    WHERE fecha_inicio BETWEEN :inicio AND :fin
";

$params = $paramsBase;

filtroTecnicoSQL($sql,$params,$tecnicoFiltro);

$sql .= "
    GROUP BY c.actividad
    ORDER BY total DESC
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$topActividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT DISTINCT
        u.id,
        u.nombre
    FROM actividades_extras a
    INNER JOIN usuarios u
        ON u.id = a.idingeniero
    ORDER BY u.nombre
";

$tecnicos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard Actividades Extras</title>

<link rel="stylesheet" href="itil_estadisticas.css">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>

.tecnicos-cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:12px;
    margin-bottom:20px;
}

.tec-card{
    background:var(--card-bg);
    border-radius:10px;
    padding:12px;
    text-align:center;
    text-decoration:none;
    color:var(--text);
    font-weight:600;
    box-shadow:0 2px 6px var(--shadow);
    transition:.2s;
}

.tec-card:hover{
    transform:translateY(-2px);
}

.tec-card.active{
    background:#00AEEF;
    color:white;
}

.kpi-value{
    font-size:32px;
    font-weight:bold;
    margin-top:8px;
}

.kpi-sub{
    color:var(--subtext);
    font-size:13px;
    margin-top:5px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:8px;
    border-bottom:1px solid rgba(0,0,0,.08);
    text-align:left;
}

body.dark th,
body.dark td{
    border-color:rgba(255,255,255,.08);
}

</style>
</head>

<body>

<?php require "sidebar.php"; ?>
<?php require "topbar.php"; ?>

<br><br><br><br>

<div class="filtro-bar">

<form method="GET" class="filtro-row">

    <input type="date" name="inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
    <input type="date" name="fin" value="<?= htmlspecialchars($fecha_fin) ?>">

    <?php if($tecnicoFiltro): ?>
        <input type="hidden" name="tecnico" value="<?= $tecnicoFiltro ?>">
    <?php endif; ?>

    <button type="submit">Filtrar</button>

</form>

<div class="filtro-row">

    <form method="GET">
        <input type="hidden" name="rango" value="hoy">
        <button>Hoy</button>
    </form>

    <form method="GET">
        <input type="hidden" name="rango" value="7">
        <button>Últimos 7 días</button>
    </form>

    <form method="GET">
        <input type="hidden" name="rango" value="mes">
        <button>Mes actual</button>
    </form>

</div>

</div>

<div class="main">

<h2 class="dashboard-title">
Dashboard Actividades Extras
</h2>

<div class="dashboard-subtitle">
Vista ejecutiva de actividades registradas
</div>

<div class="tecnicos-cards">

<?php foreach($tecnicos as $t): ?>

<a
class="tec-card <?= ($tecnicoFiltro==$t['id']) ? 'active' : '' ?>"
href="?inicio=<?= urlencode($fecha_inicio) ?>&fin=<?= urlencode($fecha_fin) ?>&tecnico=<?= $t['id'] ?>">

<?= htmlspecialchars($t['nombre']) ?>

</a>

<?php endforeach; ?>

</div>

<div class="dashboard-grid">

<div class="card">
<h3>Total Actividades</h3>
<div class="kpi-value"><?= $total ?></div>
<div class="kpi-sub">Registradas</div>
</div>

<div class="card">
<h3>Completadas</h3>
<div class="kpi-value"><?= $totalCompletadas ?></div>
<div class="kpi-sub">Finalizadas</div>
</div>

<div class="card">
<h3>Pendientes</h3>
<div class="kpi-value"><?= $totalPendientes ?></div>
<div class="kpi-sub">Abiertas</div>
</div>

<div class="card">
<h3>Duración Promedio</h3>
<div class="kpi-value"><?= number_format($duracionPromedio,2) ?></div>
<div class="kpi-sub">Minutos</div>
</div>

<div class="card">
<h3>Tiempo Total</h3>
<div class="kpi-value"><?= round($tiempoTotal/60,1) ?></div>
<div class="kpi-sub">Horas</div>
</div>

<div class="card">
<h3>Ingenieros Activos</h3>
<div class="kpi-value"><?= $tecnicosActivos ?></div>
<div class="kpi-sub">Participantes</div>
</div>

</div>

<div class="dashboard-2col">

<div class="chart-card">
<h3>Actividades por Técnico</h3>
<div id="chartTecnico" class="chart-container"></div>
</div>

<div class="chart-card">
<h3>Actividades por Tipo</h3>
<div id="chartActividad" class="chart-container"></div>
</div>

</div>

<div class="dashboard-2col">

<div class="chart-card">
<h3>Actividades por Estado</h3>
<div id="chartEstado" class="chart-container"></div>
</div>

<div class="chart-card">
<h3>Tendencia Mensual</h3>
<div id="chartMensual" class="chart-container"></div>
</div>

</div>

<div class="dashboard-2col">

<div class="table-box">

<h3>Top Técnicos</h3>

<table>

<tr>
<th>Técnico</th>
<th>Total</th>
</tr>

<?php foreach($topTecnicos as $row): ?>

<tr>
<td><?= htmlspecialchars($row['tecnico']) ?></td>
<td><?= $row['total'] ?></td>
</tr>

<?php endforeach; ?>

</table>

</div>

<div class="table-box">

<h3>Top Actividades</h3>

<table>

<tr>
<th>Actividad</th>
<th>Total</th>
</tr>

<?php foreach($topActividades as $row): ?>

<tr>
<td><?= htmlspecialchars($row['actividad']) ?></td>
<td><?= $row['total'] ?></td>
</tr>

<?php endforeach; ?>

</table>

</div>

</div>

<script>

if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}

const isDark = document.body.classList.contains("dark");

let chartTecnicoIDs    = <?= json_encode($chartTecnicoIDs) ?>;
let chartTecnicoLabels = <?= json_encode($chartTecnicoLabels) ?>;
let chartTecnicoData   = <?= json_encode($chartTecnicoData) ?>;

let chartActividadLabels = <?= json_encode($chartActividadLabels) ?>;
let chartActividadData   = <?= json_encode($chartActividadData) ?>;

let chartEstadoLabels = <?= json_encode($chartEstadoLabels) ?>;
let chartEstadoData   = <?= json_encode($chartEstadoData) ?>;

let chartMensualLabels = <?= json_encode($chartMensualLabels) ?>;
let chartMensualData   = <?= json_encode($chartMensualData) ?>;

new ApexCharts(document.querySelector("#chartTecnico"),{
    chart:{
        type:'bar',
        height:280,
        events:{
            dataPointSelection:function(event,ctx,config){

                const id=chartTecnicoIDs[config.dataPointIndex];

                const params=new URLSearchParams(window.location.search);

                params.set("tecnico",id);

                window.location.href="actividades_estadisticas.php?"+params.toString();
            }
        }
    },
    series:[{
        name:'Actividades',
        data:chartTecnicoData
    }],
    xaxis:{
        categories:chartTecnicoLabels
    },
    theme:{mode:isDark?'dark':'light'}
}).render();

new ApexCharts(document.querySelector("#chartActividad"),{
    chart:{type:'pie',height:280},
    labels:chartActividadLabels,
    series:chartActividadData,
    theme:{mode:isDark?'dark':'light'}
}).render();

new ApexCharts(document.querySelector("#chartEstado"),{
    chart:{type:'bar',height:280},
    plotOptions:{
        bar:{
            horizontal:true
        }
    },
    series:[{
        name:'Actividades',
        data:chartEstadoData
    }],
    xaxis:{
        categories:chartEstadoLabels
    },
    theme:{mode:isDark?'dark':'light'}
}).render();

new ApexCharts(document.querySelector("#chartMensual"),{
    chart:{type:'line',height:280},
    series:[{
        name:'Actividades',
        data:chartMensualData
    }],
    xaxis:{
        categories:chartMensualLabels
    },
    stroke:{
        curve:'smooth'
    },
    theme:{mode:isDark?'dark':'light'}
}).render();

</script>

</body>
</html>
