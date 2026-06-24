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

/* ===== FILTROS RAPIDOS FUNCIONALES ===== */
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
body{margin:0;font-family:"Segoe UI";background:var(--bg);color:var(--text);display:flex;}
.main{margin-left:240px;width:calc(100% - 240px);padding:20px 40px;}

.header{display:flex;justify-content:space-between;align-items:center}

/* ===== INDICADOR MODULO ===== */
.badge{
    padding:6px 12px;
    border-radius:8px;
    font-weight:bold;
}
.badge.itil{background:#00AEEF;color:white}
.badge.act{background:#10b981;color:white}

/* ===== SWITCH ===== */
.switch a{
    padding:8px 14px;
    border-radius:10px;
    background:var(--card-bg);
    text-decoration:none;
    margin-left:10px;
}

/* ===== BOTONES ===== */
.boton{
padding:8px 12px;
margin:5px;
border-radius:8px;
background:var(--card-bg);
text-decoration:none;
}

/* ===== TECNICOS ===== */
.tecnicos-grid{
display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin:20px 0;
}
.tec-card{
padding:10px 14px;
border-radius:12px;
background:#e2e8f0;
text-decoration:none;
color:#222;
}
.tec-card.active{
background:#00AEEF;
color:#fff;
}

/* ===== KPI ===== */
.kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.card{background:var(--card-bg);padding:20px;border-radius:14px}

/* ===== FILTRO ACTIVO ===== */
.filtro-activo{
margin-bottom:20px;
padding:12px;
background:var(--card-bg);
border-radius:10px;
}

/* BOTON LIMPIAR */
.btn-clear{
background:#ef4444;
color:#fff;
padding:5px 10px;
border-radius:6px;
text-decoration:none;
margin-left:10px;
}

/* ===== GRAFICAS ===== */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px}
.box{background:var(--card-bg);padding:20px;border-radius:10px}

/* ===== DARK MODE ===== */
body.dark{background:#0f172a;color:#E5E7EB;}
body.dark .card, body.dark .box{background:#1f2937;}
body.dark .tec-card{background:#1f2937;color:#E5E7EB;}

</style>
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<div class="header">
<h2>Dashboard 
<span class="badge <?= $modulo=='itil'?'itil':'act' ?>">
<?= $modulo=='itil'?'ITIL':'ACTIVIDADES' ?>
</span>
</h2>

<div class="switch">
<a href="?modulo=itil">ITIL</a>
<a href="?modulo=actividades">Actividades</a>
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

<!-- 🔥 BOTONES CORRECTOS -->
<div>
<a href="?modulo=<?= $modulo ?>&rango=hoy" class="boton">Hoy</a>
<a href="?modulo=<?= $modulo ?>&rango=7" class="boton">7 días</a>
<a href="?modulo=<?= $modulo ?>&rango=mes" class="boton">Mes</a>
</div>

</div>

<!-- TECNICOS -->
<div class="tecnicos-grid">
<?php foreach($tecnicos as $t): ?>
<a href="?modulo=<?= $modulo ?>&tecnico=<?= $t['id'] ?>" 
class="tec-card <?= ($tecnico==$t['id'])?'active':'' ?>">
<?= $t['nombre'] ?>
</a>
<?php endforeach; ?>
</div>

<!-- FILTRO -->
<?php if($tecnico): ?>
<div class="filtro-activo">
Filtrando por:
<strong><?= $pdo->query("SELECT nombre FROM usuarios WHERE id=$tecnico")->fetchColumn() ?></strong>

<a href="?modulo=<?= $modulo ?>" class="btn-clear">Quitar filtro</a>
</div>
<?php endif; ?>

<!-- KPIS -->
<div class="kpis">
<div class="card">Total <div class="kpi-total">0</div></div>
<div class="card">Completadas <div class="kpi-comp">0</div></div>
<div class="card">Proceso <div class="kpi-proc">0</div></div>
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

fetch("api_dashboard.php?"+window.location.search.replace("?",""))
.then(r=>r.json())
.then(d=>{

document.querySelector(".kpi-total").textContent=d.total;
document.querySelector(".kpi-comp").textContent=d.completadas;
document.querySelector(".kpi-proc").textContent=d.proceso;

new ApexCharts(document.querySelector("#chartTec"),{
chart:{type:'bar'},
series:[{data:d.tecData}],
xaxis:{categories:d.tecLabels,labels:{style:{colors:'#E5E7EB'}}},
yaxis:{labels:{style:{colors:'#E5E7EB'}}}
}).render();

new ApexCharts(document.querySelector("#chartEstado"),{
chart:{type:'bar'},
series:[{data:d.estadoData}],
xaxis:{categories:d.estadoLabels,labels:{style:{colors:'#E5E7EB'}}},
yaxis:{labels:{style:{colors:'#E5E7EB'}}}
}).render();

});

</script>

<script src="theme.js"></script>

</body>
</html>