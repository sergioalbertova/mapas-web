<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");

$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;

$params = [
    ':inicio' => $inicio." 00:00:00",
    ':fin'    => $fin." 23:59:59"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard General</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<link rel="stylesheet" href="dashboard_general.css">

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>

<?php require "sidebar.php"; ?>
<?php require "topbar.php"; ?>

<div class="main">

<div class="header">
    <h2>Dashboard General</h2>

    <div class="tabs">
        <a href="?modulo=itil" class="tab <?= $modulo=='itil'?'active':'' ?>">📊 ITIL</a>
        <a href="?modulo=actividades" class="tab <?= $modulo=='actividades'?'active':'' ?>">🛠 Actividades</a>
    </div>
</div>

<!-- FILTRO -->
<div class="filtro">
<form method="GET">
    <input type="hidden" name="modulo" value="<?= $modulo ?>">
    <input type="date" name="inicio" value="<?= $inicio ?>">
    <input type="date" name="fin" value="<?= $fin ?>">
    <button>Filtrar</button>
</form>
</div>

<!-- KPIs -->
<div class="kpis">
    <div class="card"><span>Total</span><strong class="kpi-total">0</strong></div>
    <div class="card"><span>Completadas</span><strong class="kpi-comp">0</strong></div>
    <div class="card"><span>En proceso</span><strong class="kpi-proc">0</strong></div>
    <div class="card"><span>MTTR</span><strong class="kpi-mttr">0</strong></div>
</div>

<!-- GRÁFICAS -->
<div class="grid-2">
    <div class="box"><h3>Por técnico</h3><div id="chartTec"></div></div>
    <div class="box"><h3>Por tipo</h3><div id="chartTipo"></div></div>
</div>

<div class="grid-2">
    <div class="box"><h3>Por estado</h3><div id="chartEstado"></div></div>
    <div class="box"><h3>Tendencia mensual</h3><div id="chartMes"></div></div>
</div>

<div class="grid-2">
    <div class="box"><h3>Por hora</h3><div id="chartHora"></div></div>
    <div class="box"><h3>Día semana</h3><div id="chartDia"></div></div>
</div>

<div class="box">
    <h3>Ubicación (solo ITIL)</h3>
    <div id="chartUbicacion"></div>
</div>

</div>

<script>
const modulo = "<?= $modulo ?>";
const inicio = "<?= $inicio ?>";
const fin    = "<?= $fin ?>";

/* ===== CHARTS ===== */
let chartTec = new ApexCharts(document.querySelector("#chartTec"), {chart:{type:'bar'},series:[{data:[]}]}); chartTec.render();
let chartTipo = new ApexCharts(document.querySelector("#chartTipo"), {chart:{type:'pie'},series:[],labels:[]}); chartTipo.render();
let chartEstado = new ApexCharts(document.querySelector("#chartEstado"), {chart:{type:'bar'},series:[{data:[]}]}); chartEstado.render();
let chartMes = new ApexCharts(document.querySelector("#chartMes"), {chart:{type:'line'},series:[{data:[]}]}); chartMes.render();
let chartHora = new ApexCharts(document.querySelector("#chartHora"), {chart:{type:'heatmap'},series:[]}); chartHora.render();
let chartDia = new ApexCharts(document.querySelector("#chartDia"), {chart:{type:'bar'},series:[{data:[]}]}); chartDia.render();
let chartUbicacion = new ApexCharts(document.querySelector("#chartUbicacion"), {chart:{type:'bar'},series:[{data:[]}]}); chartUbicacion.render();

/* ===== CARGA ===== */
function cargar(){

fetch(`api_dashboard.php?modulo=${modulo}&inicio=${inicio}&fin=${fin}`)
.then(r=>r.json())
.then(d=>{

document.querySelector(".kpi-total").textContent = d.total;
document.querySelector(".kpi-comp").textContent = d.completadas;
document.querySelector(".kpi-proc").textContent = d.proceso;
document.querySelector(".kpi-mttr").textContent = d.mttr;

chartTec.updateOptions({xaxis:{categories:d.tecLabels}});
chartTec.updateSeries([{data:d.tecData}]);

chartTipo.updateOptions({labels:d.tipoLabels});
chartTipo.updateSeries(d.tipoData);

chartEstado.updateOptions({xaxis:{categories:d.estadoLabels}});
chartEstado.updateSeries([{data:d.estadoData}]);

chartMes.updateOptions({xaxis:{categories:d.mesLabels}});
chartMes.updateSeries([{data:d.mesData}]);

});
}

cargar();
</script>

</body>
</html>