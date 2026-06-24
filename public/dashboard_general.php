<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");
$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;
$tecnico = $_GET['tecnico'] ?? null;

/* FILTROS RÁPIDOS */
if (isset($_GET['rango'])) {
    if ($_GET['rango'] == 'hoy') {
        $inicio = $fin = $hoy;
    }
    if ($_GET['rango'] == '7') {
        $inicio = date("Y-m-d", strtotime("-6 days"));
        $fin = $hoy;
    }
    if ($_GET['rango'] == 'mes') {
        $inicio = date("Y-m-01");
        $fin = $hoy;
    }
}

/* LISTA DE TECNICOS */
$tecnicos = $pdo->query("SELECT id, nombre FROM usuarios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

function url($params){
    return '?' . http_build_query(array_merge($_GET,$params));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<style>

/* ================= BASE ================= */
.main {
    margin-left:240px;
    padding:20px 40px;
    width:calc(100% - 240px);
}

/* HEADER */
.header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

/* TABS */
.tabs a {
    padding:10px 15px;
    border-radius:10px;
    background:var(--card-bg);
    margin-left:10px;
    text-decoration:none;
}
.tabs .active {
    background:var(--accent);
    color:white;
}

/* FILTROS */
.filtros {
    margin-bottom:10px;
}

.rapidos a {
    margin-right:10px;
    font-weight:600;
}

/* BOTONES TECNICOS */
.tecnicos {
    margin:10px 0;
}
.tecnicos a {
    display:inline-block;
    padding:6px 10px;
    margin:4px;
    border-radius:8px;
    background:var(--card-bg);
    text-decoration:none;
}
.tecnicos a.active {
    background:var(--accent);
    color:white;
}

/* KPI */
.kpis {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-top:10px;
}

.card {
    background:var(--card-bg);
    padding:20px;
    border-radius:14px;
    box-shadow:0 8px 20px var(--shadow);
    font-size:18px;
}

/* GRID */
.grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-top:20px;
}

.box {
    background:var(--card-bg);
    padding:20px;
    border-radius:14px;
    box-shadow:0 5px 15px var(--shadow);
}

/* DARK MODE */
body.dark .box {
    background:#1f2937;
}
body.dark .card {
    background:#1f2937;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>
<?php require "topbar.php"; ?>

<div class="main">

<!-- HEADER -->
<div class="header">
<h2>Dashboard Power BI</h2>

<div class="tabs">
    il']) ?>" class="<?= $modulo=='itil'?'active':'' ?>">ITIL</a>
    idades']) ?>" class="<?= $modulo=='actividades'?'active':'' ?>">Actividades</a>
</div>
</div>

<!-- FILTROS -->
<div class="filtros">

<form method="GET">
<input type="hidden" name="modulo" value="<?= $modulo ?>">

<input type="date" name="inicio" value="<?= $inicio ?>">
<input type="date" name="fin" value="<?= $fin ?>">

<?php if($tecnico): ?>
<input type="hidden" name="tecnico" value="<?= $tecnico ?>">
<?php endif; ?>

<button>Filtrar</button>
</form>

<div class="rapidos">
    oy']) ?>">Hoy</a>
    7']) ?>">Últimos 7 días</a>
    ']) ?>">Mes</a>
</div>

</div>

<!-- TECNICOS -->
<div class="tecnicos">
<?php foreach($tecnicos as $t): ?>
    =<?= $t['id'] ?>"
       class="<?= ($tecnico==$t['id'])?'active':'' ?>">
       <?= htmlspecialchars($t['nombre']) ?>
    </a>
<?php endforeach; ?>
</div>

<!-- FILTRO ACTIVO -->
<?php if($tecnico): ?>
<div class="card">
Filtrando por técnico:
<strong>
<?= htmlspecialchars($pdo->query("SELECT nombre FROM usuarios WHERE id=$tecnico")->fetchColumn()) ?>
</strong>

    inicio=<?= $inicio ?>&fin=<?= $fin ?>">Quitar filtro</a>
</div>
<?php endif; ?>

<!-- KPIs -->
<div class="kpis">
<div class="card">Total: <span class="kpi-total">0</span></div>
<div class="card">Completadas: <span class="kpi-comp">0</span></div>
<div class="card">En proceso: <span class="kpi-proc">0</span></div>
</div>

<!-- GRAFICAS -->
<div class="grid">

<div class="box">
<h3>Por técnico</h3>
<div id="chartTec"></div>
</div>

<div class="box">
<h3>Por estado</h3>
<div id="chartEstado"></div>
</div>

<div class="box">
<h3>Por mes</h3>
<div id="chartMes"></div>
</div>

<div class="box">
<h3>Por tipo</h3>
<div id="chartTipo"></div>
</div>

</div>

</div>

<script>

/* DARK MODE */
if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

/* VARS */
const modulo="<?= $modulo ?>";
const inicio="<?= $inicio ?>";
const fin="<?= $fin ?>";
const tecnico="<?= $tecnico ?>";

/* CHARTS */
let chartTec=new ApexCharts(document.querySelector("#chartTec"),{chart:{type:'bar',height:250},series:[{data:[]}],xaxis:{categories:[]}});chartTec.render();
let chartEstado=new ApexCharts(document.querySelector("#chartEstado"),{chart:{type:'bar',height:250},series:[{data:[]}],xaxis:{categories:[]}});chartEstado.render();
let chartMes=new ApexCharts(document.querySelector("#chartMes"),{chart:{type:'line',height:250},series:[{data:[]}],xaxis:{categories:[]}});chartMes.render();
let chartTipo=new ApexCharts(document.querySelector("#chartTipo"),{chart:{type:'pie',height:250},series:[],labels:[]});chartTipo.render();

/* LOAD */
fetch(`api_dashboard.php?modulo=${modulo}&inicio=${inicio}&fin=${fin}&tecnico=${tecnico}`)
.then(r=>r.json())
.then(d=>{

document.querySelector(".kpi-total").textContent=d.total;
document.querySelector(".kpi-comp").textContent=d.completadas;
document.querySelector(".kpi-proc").textContent=d.proceso;

chartTec.updateOptions({xaxis:{categories:d.tecLabels}});
chartTec.updateSeries([{data:d.tecData}]);

chartEstado.updateOptions({xaxis:{categories:d.estadoLabels}});
chartEstado.updateSeries([{data:d.estadoData}]);

chartMes.updateOptions({xaxis:{categories:d.mesLabels||[]}});
chartMes.updateSeries([{data:d.mesData||[]}]);

chartTipo.updateOptions({labels:d.tipoLabels||[]});
chartTipo.updateSeries(d.tipoData||[]);

});

</script>

</body>
</html>