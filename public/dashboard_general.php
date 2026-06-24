<?php
require "auth.php";
require "db.php";

$id = $_SESSION['user_id'];

/* USUARIO */
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id=?");
$stmt->execute([$id]);
$nombreUsuario = $stmt->fetchColumn() ?? "Usuario";

/* PARAMETROS */
$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");
$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;
$tecnico = $_GET['tecnico'] ?? null;

/* TECNICOS - SOLO LOS 4 */
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

/* USA EL MISMO SISTEMA */
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
    padding:20px 40px;
    width:calc(100% - 240px);
}

/* TITULO */
.main h2{
    text-align:center;
}

/* FILTRO */
.filtro{
    text-align:center;
    margin:15px 0;
}

/* BOTONES TECNICOS */
.tecnicos{
    text-align:center;
    margin:15px 0;
}
.tecnicos a{
    padding:8px 14px;
    margin:5px;
    display:inline-block;
    border-radius:10px;
    background:var(--card-bg);
    color:var(--text);
    text-decoration:none;
    box-shadow:0 5px 15px var(--shadow);
}
.tecnicos a.active{
    background:var(--accent);
    color:white;
}

/* KPI */
.kpis{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
}
.card{
    background:var(--card-bg);
    padding:20px;
    border-radius:14px;
    box-shadow:0 10px 20px var(--shadow);
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-top:20px;
}

.box{
    background:var(--card-bg);
    padding:20px;
    border-radius:14px;
    box-shadow:0 10px 20px var(--shadow);
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Dashboard</h2>

<!-- FILTRO -->
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
</div>

<!-- TECNICOS -->
<div class="tecnicos">
<?php foreach($tecnicos as $t): ?>
    <a href="?modulo=<?= $modulo ?>&tecnico=<?= $t['id'] ?>"
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

/* 🔥 DARK MODE */
if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

/* 🔥 SIDEBAR */
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed");
}

/* DATA */
const params = new URLSearchParams(window.location.search);

fetch("api_dashboard.php?" + params.toString())
.then(r=>r.json())
.then(d=>{

document.querySelector(".kpi-total").textContent=d.total;
document.querySelector(".kpi-comp").textContent=d.completadas;
document.querySelector(".kpi-proc").textContent=d.proceso;

/* CHART */
let c1=new ApexCharts(document.querySelector("#chartTec"),{
chart:{type:'bar',height:250},
series:[{data:d.tecData}],
xaxis:{categories:d.tecLabels}
});
c1.render();

let c2=new ApexCharts(document.querySelector("#chartEstado"),{
chart:{type:'bar',height:250},
series:[{data:d.estadoData}],
xaxis:{categories:d.estadoLabels}
});
c2.render();

});

</script>

<script src="theme.js"></script>

</body>
</html>