<?php
require "auth.php";
require "db.php";

/* ===== PARAMETROS ===== */
$modulo = $_GET['modulo'] ?? 'itil';
$hoy = date("Y-m-d");

/* ===== FILTROS ===== */
if(isset($_GET['inicio']) && isset($_GET['fin'])){
    $inicio = $_GET['inicio'];
    $fin    = $_GET['fin'];
}else{
    $inicio = $hoy;
    $fin    = $hoy;
}

$tecnico = $_GET['tecnico'] ?? null;

/* ===== TECNICOS ===== */
$tecnicos = [
    ['id'=>2,'nombre'=>'SERGIO VALENZUELA'],
    ['id'=>4,'nombre'=>'ANTONIETA RODRIGUEZ'],
    ['id'=>29,'nombre'=>'ERICK ARIAS RAMIREZ'],
    ['id'=>26,'nombre'=>'JUAN CARLOS ARAUJO SANCHEZ']
];

function baseUrl($params = []){
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
    font-family:"Segoe UI";
    display:flex;
    background:var(--bg);
    color:var(--text);
}

.main{
    margin-left:240px;
    padding:20px;
    width:calc(100% - 240px);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* SWITCH */
.switch a{
    padding:8px 14px;
    border-radius:10px;
    background:var(--card-bg);
    text-decoration:none;
    margin-left:10px;
    color:var(--text);
}
.switch .active{
    background:#00AEEF;
    color:white;
}

/* FILTROS */
.filtro{
    text-align:center;
    margin:20px 0;
}

/* BOTONES */
.boton{
    padding:8px 12px;
    margin:5px;
    border-radius:8px;
    background:var(--card-bg);
    text-decoration:none;
    display:inline-block;
}

/* TECNICOS */
.tecnicos-grid{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:10px;
    margin:20px 0;
}
.tec-card{
    padding:10px 14px;
    border-radius:10px;
    background:#e2e8f0;
    text-decoration:none;
    color:#000;
}
.tec-card.active{
    background:#00AEEF;
    color:white;
}

/* KPI */
.kpis{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}
.card{
    background:var(--card-bg);
    padding:15px;
    border-radius:10px;
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
    border-radius:10px;
}

/* DARK */
body.dark{background:#0f172a;color:#fff}
body.dark .card, body.dark .box{background:#1f2937}
body.dark .tec-card{background:#1f2937;color:#fff}

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

<!-- FILTRO -->
<div class="filtro">

<form method="GET">
<input type="hidden" name="modulo" value="<?= htmlspecialchars($modulo) ?>">

<input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
<input type="date" name="fin" value="<?= htmlspecialchars($fin) ?>">

<button class="boton">Filtrar</button>
</form>

<!-- BOTONES RAPIDOS (CORRECTOS) -->
<div>

<a href="?modulo=<?= $modulo ?>&inicio=<?= $hoy ?>&fin=<?= $hoy ?>" class="boton">Hoy</a>

<a href="?modulo=<?= $modulo ?>&inicio=<?= date('Y-m-d', strtotime('-6 days')) ?>&fin=<?= $hoy ?>" class="boton">7 días</a>

<a href="?modulo=<?= $modulo ?>&inicio=<?= date('Y-m-01') ?>&fin=<?= $hoy ?>" class="boton">Mes</a>

</div>

</div>

<!-- TECNICOS -->
<div class="tecnicos-grid">
<?php foreach($tecnicos as $t): ?>
<a href="?modulo=<?= $modulo ?>&tecnico=<?= $t['id'] ?>" 
   class="tec-card <?= ($tecnico==$t['id'])?'active':'' ?>">
   <?= htmlspecialchars($t['nombre']) ?>
</a>
<?php endforeach; ?>
</div>

<!-- KPIS -->
<div class="kpis">
<div class="card">Total <div class="kpi-total">0</div></div>
<div class="card">Completadas <div class="kpi-comp">0</div></div>
<div class="card">Proceso <div class="kpi-proc">0</div></div>

<div class="card">MTTR <div class="kpi-mttr">0</div></div>
<div class="card">SLA <div class="kpi-sla">0</div></div>
<div class="card">Backlog <div class="kpi-backlog">0</div></div>
</div>

<!-- GRAFICAS -->
<div class="grid">
<div class="box"><div id="chartTec"></div></div>
<div class="box"><div id="chartEstado"></div></div>
</div>

</div>

<script>

if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

fetch(`api_dashboard.php?modulo=<?= $modulo ?>&inicio=<?= $inicio ?>&fin=<?= $fin ?><?= $tecnico?'&tecnico='.$tecnico:'' ?>`)
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
