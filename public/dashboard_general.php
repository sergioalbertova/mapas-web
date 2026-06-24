<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");
$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;
$tecnico = $_GET['tecnico'] ?? null;

/* TECNICOS FIJOS */
$tecnicos = [
['id'=>29,'nombre'=>'ERICK ARIAS RAMIREZ'],
['id'=>2,'nombre'=>'SERGIO VALENZUELA'],
['id'=>4,'nombre'=>'ANTONIETA RODRIGUEZ'],
['id'=>26,'nombre'=>'JUAN CARLOS ARAUJO SANCHEZ']
];

/* FUNCION URL */
function url($params){
    return '?' . http_build_query(array_merge($_GET, $params));
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

body{
    margin:0;
    font-family:"Segoe UI", Arial;
    background:var(--bg);
    color:var(--text);
    display:flex;
}

.main{
    margin-left:240px;
    width:calc(100% - 240px);
    padding:20px 40px;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* MODULO SWITCH */
.switch a{
    padding:10px 15px;
    border-radius:10px;
    background:var(--card-bg);
    text-decoration:none;
    margin-left:10px;
}

.switch .active{
    background:var(--accent);
    color:white;
}

/* FILTROS */
.filtro{
    text-align:center;
    margin:15px 0;
}

.rapidos a{
    margin:0 8px;
    font-weight:600;
}

/* TECNICOS */
.tecnicos{
    text-align:center;
    margin:15px 0;
}

.tecnicos a{
    display:inline-block;
    padding:8px 14px;
    margin:5px;
    border-radius:12px;
    background:var(--card-bg);
    color:var(--text);
    text-decoration:none;
    box-shadow:0 5px 15px var(--shadow);
}

.tecnicos a.active{
    background:var(--accent);
    color:white;
}

/* KPIS */
.kpis{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
}

.card{
    background:var(--card-bg);
    padding:20px;
    border-radius:12px;
    box-shadow:0 10px 20px var(--shadow);
}

/* GRAFICAS */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-top:20px;
}

.box{
    background:var(--card-bg);
    padding:20px;
    border-radius:12px;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<div class="header">
<h2>Dashboard</h2>

<div class="switch">
    <a href="?modulo=itil" class="<?= $modulo=='itil'?'active':'' ?>">ITIL</a>
    <a href="?modulo=actividades" class="<?= $modulo=='actividades'?'active':'' ?>">Actividades</a>
</div>
</div>

<!-- FILTROS -->
<div class="filtro">

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
    <a href="<?= url(['rango'=>'hoy']) ?>">Hoy</a>
    <a href="<?= url(['rango'=>'7']) ?>">Últimos 7 días</a>
    <a href="<?= url(['rango'=>'mes']) ?>">Mes</a>
</div>

</div>

<!-- TECNICOS -->
<div class="tecnicos">
<?php foreach($tecnicos as $t): ?>
<a href="?tecnico=<?= $t['id'] ?>&modulo=<?= $modulo ?>"
   class="<?= ($tecnico==$t['id'])?'active':'' ?>">
   <?= $t['nombre'] ?>
</a>
<?php endforeach; ?>
</div>

<!-- KPIS -->
<div class="kpis">
<div class="card">Total: <span class="kpi-total">0</span></div>
<div class="card">Completadas: <span class="kpi-comp">0</span></div>
<div class="card">Proceso: <span class="kpi-proc">0</span></div>
</div>

<!-- GRAFICAS -->
<div class="grid">

<div class="box">
<h3>Técnicos</h3>
<div id="chartTec"></div>
</div>

<div class="box">
<h3>Estado</h3>
<div id="chartEstado"></div>
</div>

</div>

</div>

<script>

/* DARK MODE */
if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

/* SIDEBAR */
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed");
}

/* DATA */
fetch("api_dashboard.php?"+window.location.search.replace('?',''))
.then(r=>r.json())
.then(d=>{

document.querySelector(".kpi-total").textContent=d.total;
document.querySelector(".kpi-comp").textContent=d.completadas;
document.querySelector(".kpi-proc").textContent=d.proceso;

new ApexCharts(document.querySelector("#chartTec"),{
chart:{type:'bar'},
series:[{data:d.tecData}],
xaxis:{categories:d.tecLabels}
}).render();

new ApexCharts(document.querySelector("#chartEstado"),{
chart:{type:'bar'},
series:[{data:d.estadoData}],
xaxis:{categories:d.estadoLabels}
}).render();

});

</script>

<script src="theme.js"></script>

</body>
</html>
