<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");
$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;
$tecnico = $_GET['tecnico'] ?? null;

/* SOLO 4 TECNICOS */
$tecnicos = [
['id'=>29,'nombre'=>'ERICK ARIAS RAMIREZ'],
['id'=>2,'nombre'=>'SERGIO VALENZUELA'],
['id'=>4,'nombre'=>'ANTONIETA RODRIGUEZ'],
['id'=>26,'nombre'=>'JUAN CARLOS ARAUJO SANCHEZ']
];
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

/* ================= BASE GLOBAL ================= */
body{
    margin:0;
    font-family:"Segoe UI", Arial;
    background:var(--bg);
    color:var(--text);
    display:flex;
}

/* MAIN */
.main{
    margin-left:240px;
    width:calc(100% - 240px);
    padding:20px 40px;
}

/* HEADER */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* SWITCH MODULO */
.switch a{
    padding:10px 15px;
    border-radius:10px;
    margin-left:10px;
    background:var(--card-bg);
    text-decoration:none;
}
.switch .active{
    background:var(--accent);
    color:white;
}

/* FILTRO */
.filtro{
    text-align:center;
    margin:20px 0;
}

/* TECNICOS BOTONES */
.tecnicos{
    text-align:center;
}
.tecnicos a{
    padding:10px 14px;
    margin:6px;
    border-radius:12px;
    background:var(--card-bg);
    display:inline-block;
    text-decoration:none;
    color:var(--text);
    box-shadow:0 6px 15px var(--shadow);
    transition:.2s;
}
.tecnicos a:hover{
    transform:translateY(-3px);
}
.tecnicos a.active{
    background:var(--accent);
    color:white;
}

/* KPIs */
.kpis{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
    margin-top:20px;
}
.card{
    background:var(--card-bg);
    padding:20px;
    border-radius:16px;
    box-shadow:0 10px 25px var(--shadow);
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-top:30px;
}

.box{
    background:var(--card-bg);
    padding:20px;
    border-radius:16px;
    box-shadow:0 10px 25px var(--shadow);
}

</style>
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<!-- HEADER -->
<div class="header">
<h2>Dashboard</h2>

<div class="switch">
    il" class="<?= $modulo=='itil'?'active':'' ?>">ITIL</a>
    idades" class="<?= $modulo=='actividades'?'active':'' ?>">Actividades</a>
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

<!-- TECNICOS -->
<div class="tecnicos">
<?php foreach($tecnicos as $t): ?>
    cnico=<?= $t['id'] ?>&modulo=<?= $modulo ?>"
       class="<?= ($tecnico==$t['id'])?'active':'' ?>">
       <?= $t['nombre'] ?>
    </a>
<?php endforeach; ?>
</div>

<!-- KPIs -->
<div class="kpis">

<div class="card">
    <h4>Total</h4>
    <div class="kpi-total">0</div>
</div>

<div class="card">
    <h4>Completadas</h4>
    <div class="kpi-comp">0</div>
</div>

<div class="card">
    <h4>En proceso</h4>
    <div class="kpi-proc">0</div>
</div>

<div class="card">
    <h4>MTTR</h4>
    <div class="kpi-mttr">0h</div>
</div>

<div class="card">
    <h4>SLA</h4>
    <div class="kpi-sla">0%</div>
</div>

<div class="card">
    <h4>Backlog</h4>
    <div class="kpi-backlog">0</div>
</div>

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

/* DARK MODE REAL */
if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

/* SIDEBAR */
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed");
}

/* DATA */
const params = new URLSearchParams(window.location.search);

fetch("api_dashboard.php?"+params.toString())
.then(r=>r.json())
.then(d=>{

document.querySelector(".kpi-total").textContent=d.total;
document.querySelector(".kpi-comp").textContent=d.completadas;
document.querySelector(".kpi-proc").textContent=d.proceso;
document.querySelector(".kpi-backlog").textContent=d.proceso;

/* GRAFICAS */
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