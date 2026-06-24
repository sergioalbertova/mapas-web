<?php
require "auth.php";
require "db.php";

/* USUARIO */
$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id=?");
$stmt->execute([$id]);
$nombreUsuario = $stmt->fetchColumn() ?? "Usuario";

/* PARAMETROS */
$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");
$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;
$tecnico = $_GET['tecnico'] ?? null;

/* FILTROS RAPIDOS */
if(isset($_GET['rango'])){
    if($_GET['rango']=='hoy'){
        $inicio = $fin = $hoy;
    }
    if($_GET['rango']=='7'){
        $inicio = date("Y-m-d", strtotime("-6 days"));
        $fin = $hoy;
    }
    if($_GET['rango']=='mes'){
        $inicio = date("Y-m-01");
        $fin = $hoy;
    }
}

/* TECNICOS */
$tecnicos = [
['id'=>2,'nombre'=>'SERGIO VALENZUELA'],
['id'=>4,'nombre'=>'ANTONIETA RODRIGUEZ'],
['id'=>29,'nombre'=>'ERICK ARIAS RAMIREZ'],
['id'=>26,'nombre'=>'JUAN CARLOS ARAUJO SANCHEZ'],
];

function url($p){
    return '?' . http_build_query(array_merge($_GET, $p));
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

/* ===== EXACTAMENTE COMO INDEX ===== */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display:flex;
}

/* MAIN */
.main {
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

/* SWITCH */
.switch a{
    padding:10px 15px;
    border-radius:10px;
    background:var(--sidebar-bg);
    color:var(--text);
    text-decoration:none;
    margin-left:10px;
}
.switch .active{
    background:var(--accent);
    color:#fff;
}

/* FILTROS */
.filtro{
    text-align:center;
    margin:20px 0;
}

.filtro-rapidos a{
    margin:5px;
    padding:6px 10px;
    background:var(--card-bg);
    border-radius:8px;
    text-decoration:none;
}

/* TECNICOS */
.tecnicos{
    text-align:center;
    margin:15px 0;
}
.tecnicos a{
    display:inline-block;
    padding:10px 14px;
    margin:6px;
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

/* KPI */
.kpis{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px;
}
.card{
    background:var(--card-bg);
    padding:20px;
    border-radius:15px;
    box-shadow:0 10px 25px var(--shadow);
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
    border-radius:15px;
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

<?php if($tecnico): ?>
<input type="hidden" name="tecnico" value="<?= $tecnico ?>">
<?php endif; ?>

<button>Filtrar</button>
</form>

<div class="filtro-rapidos">
oy']) ?>">Hoy</a>
7']) ?>">Últimos 7 días</a>
']) ?>">Mes</a>
</div>

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

<!-- FILTRO ACTIVO -->
<?php if($tecnico): ?>
<div class="card">
Filtrando por técnico:
<strong>
<?= htmlspecialchars($pdo->query("SELECT nombre FROM usuarios WHERE id=$tecnico")->fetchColumn()) ?>
</strong>
inicio=<?= $inicio ?>&fin=<?= $fin ?>&modulo=<?= $modulo ?>">
Quitar filtro
</a>
</div>
<?php endif; ?>

<!-- KPIS -->
<div class="kpis">
<div class="card">Total <div class="kpi-total">0</div></div>
<div class="card">Completadas <div class="kpi-comp">0</div></div>
<div class="card">En proceso <div class="kpi-proc">0</div></div>
</div>

<!-- GRAFICAS -->
<div class="grid">
<div class="box"><h3>Técnicos</h3><div id="chartTec"></div></div>
<div class="box"><h3>Estado</h3><div id="chartEstado"></div></div>
</div>

</div>

<script>

/* DARK MODE */
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark");
}

/* SIDEBAR */
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("collapsed");
}

/* DATA */
fetch("api_dashboard.php?"+window.location.search.replace("?",""))
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
