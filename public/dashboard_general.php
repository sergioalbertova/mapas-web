<?php
require "auth.php";
require "db.php";

/* ===== USUARIO ===== */
$id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id=?");
$stmt->execute([$id]);
$nombreUsuario = $stmt->fetchColumn() ?? "Usuario";

/* ===== PARAMETROS ===== */
$modulo  = $_GET['modulo'] ?? 'itil';
$inicio  = $_GET['inicio'] ?? date("Y-m-d");
$fin     = $_GET['fin'] ?? date("Y-m-d");
$tecnico = $_GET['tecnico'] ?? null;

/* ===== FILTROS RAPIDOS ===== */
if(isset($_GET['rango'])){
    if($_GET['rango']=='hoy'){
        $inicio = $fin = date("Y-m-d");
    }
    if($_GET['rango']=='7'){
        $inicio = date("Y-m-d", strtotime("-6 days"));
        $fin = date("Y-m-d");
    }
    if($_GET['rango']=='mes'){
        $inicio = date("Y-m-01");
        $fin = date("Y-m-d");
    }
}

/* ===== TECNICOS ===== */
$tecnicos = [
['id'=>2,'nombre'=>'SERGIO VALENZUELA'],
['id'=>4,'nombre'=>'ANTONIETA RODRIGUEZ'],
['id'=>29,'nombre'=>'ERICK ARIAS RAMIREZ'],
['id'=>26,'nombre'=>'JUAN CARLOS ARAUJO SANCHEZ']
];

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

/* ===== BASE ===== */
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

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

/* ===== SWITCH ===== */
.switch a{
    padding:10px 15px;
    border-radius:10px;
    background:var(--card-bg);
    text-decoration:none;
    color:var(--text);
    margin-left:10px;
    transition:.2s;
}
.switch a:hover{
    transform:translateY(-2px);
}
.switch .active{
    background:var(--accent);
    color:white;
}

/* ===== FILTRO ===== */
.filtro{
    text-align:center;
    margin:20px 0;
}

/* ===== BOTONES ===== */
.boton{
    display:inline-block;
    padding:8px 12px;
    margin:5px;
    border-radius:10px;
    background:var(--card-bg);
    box-shadow:0 5px 10px var(--shadow);
    text-decoration:none;
    color:var(--text);
}

/* ===== TECNICOS PRO ===== */
.tecnicos-grid{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:12px;
    margin:20px 0;
}

.tec-card{
    padding:10px 18px;
    border-radius:14px;
    background:linear-gradient(135deg,#ffffff,#e2e8f0);
    color:#1f2937;
    font-weight:600;
    text-decoration:none;
    transition:.2s;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.tec-card:hover{
    transform:translateY(-3px);
}

.tec-card.active{
    background:linear-gradient(135deg,#00AEEF,#0077b6);
    color:white;
}

/* ===== KPI ===== */
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
    transition:.2s;
}

.card:hover{
    transform:translateY(-4px);
}

/* ===== ESPACIADO FILTRO ACTIVO ===== */
.filtro-activo{
    margin-bottom:20px;
}

/* ===== GRAFICAS ===== */
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

/* ===== DARK MODE CORRECTO ===== */
body.dark{
    background:#0f172a;
    color:#E5E7EB;
}

body.dark .main{
    background:#0f172a;
}

body.dark .card,
body.dark .box{
    background:#1f2937;
}

body.dark h2,
body.dark h3,
body.dark h4{
    color:#E5E7EB;
}

/* SOLO DASHBOARD, NO SIDEBAR */
body.dark .tec-card{
    background:#1f2937;
    color:#E5E7EB;
}

body.dark .tec-card.active{
    background:#00AEEF;
}

/* NO TOCAR SIDEBAR */
body.dark .sidebar{
    background:var(--sidebar-bg);
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
<a href="<?= url(['modulo'=>'itil']) ?>" class="<?= $modulo=='itil'?'active':'' ?>">ITIL</a>
<a href="<?= url(['modulo'=>'actividades']) ?>" class="<?= $modulo=='actividades'?'active':'' ?>">Actividades</a>
</div>
</div>

<!-- FILTRO -->
<div class="filtro">

<form method="GET">
<input type="hidden" name="modulo" value="<?= $modulo ?>">

<input type="date" name="inicio" value="<?= $inicio ?>">
<input type="date" name="fin" value="<?= $fin ?>">

<button class="boton">Filtrar</button>
</form>

<div>
<a href="<?= url(['rango'=>'hoy']) ?>" class="boton">Hoy</a>
<a href="<?= url(['rango'=>'7']) ?>" class="boton">7 días</a>
<a href="<?= url(['rango'=>'mes']) ?>" class="boton">Mes</a>
</div>

</div>

<!-- TECNICOS -->
<div class="tecnicos-grid">
<?php foreach($tecnicos as $t): ?>
<a href="<?= url(['tecnico'=>$t['id']]) ?>"
   class="tec-card <?= ($tecnico==$t['id'])?'active':'' ?>">
   <?= $t['nombre'] ?>
</a>
<?php endforeach; ?>
</div>

<!-- FILTRO ACTIVO -->
<?php if($tecnico): ?>
<div class="card filtro-activo">
Filtrando por:
<strong>
<?= htmlspecialchars($pdo->query("SELECT nombre FROM usuarios WHERE id=$tecnico")->fetchColumn() ?? '') ?>
</strong>
| <a href="<?= url(['tecnico'=>null]) ?>">Quitar filtro</a>
</div>
<?php endif; ?>

<!-- KPIS -->
<div class="kpis">
<div class="card">Total <div class="kpi-total">0</div></div>
<div class="card">Completadas <div class="kpi-comp">0</div></div>
<div class="card">En proceso <div class="kpi-proc">0</div></div>
<div class="card">MTTR <div class="kpi-mttr">0h</div></div>
<div class="card">SLA <div class="kpi-sla">0%</div></div>
<div class="card">Backlog <div class="kpi-backlog">0</div></div>
</div>

<!-- GRAFICAS -->
<div class="grid">
<div class="box"><h3>Técnicos</h3><div id="chartTec"></div></div>
<div class="box"><h3>Estado</h3><div id="chartEstado"></div></div>
</div>

</div>

<script>
if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

fetch("api_dashboard.php?"+window.location.search.replace("?",""))
.then(r=>r.json())
.then(d=>{

document.querySelector(".kpi-total").textContent=d.total;
document.querySelector(".kpi-comp").textContent=d.completadas;
document.querySelector(".kpi-proc").textContent=d.proceso;
document.querySelector(".kpi-backlog").textContent=d.proceso;

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