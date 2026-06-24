<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");
$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;

$tecnico = $_GET['tecnico'] ?? null;

/* FILTROS */
if (isset($_GET['rango'])) {
    if ($_GET['rango']=='hoy'){
        $inicio=$fin=$hoy;
    }
    if ($_GET['rango']=='7'){
        $inicio=date("Y-m-d",strtotime("-6 days"));
        $fin=$hoy;
    }
    if ($_GET['rango']=='mes'){
        $inicio=date("Y-m-01");
        $fin=$hoy;
    }
}

/* TECNICOS */
$tecnicos=$pdo->query("SELECT id,nombre FROM usuarios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

function url($p){
    return '?'.http_build_query(array_merge($_GET,$p));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

/* HEREDA TU ESTILO */
.main{
margin-left:240px;
padding:20px 40px;
width:calc(100% - 240px);
}

h2{ text-align:center; }

/* BOTONES TECNICOS */
.tecnicos{
margin:15px 0;
}
.tecnicos a{
padding:6px 10px;
background:var(--card-bg);
border-radius:8px;
margin:3px;
display:inline-block;
text-decoration:none;
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
}
.card{
background:var(--card-bg);
padding:20px;
border-radius:12px;
box-shadow:0 5px 15px var(--shadow);
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
border-radius:12px;
}

</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</head>

<body>

<?php require "sidebar.php"; ?>
<?php require "topbar.php"; ?>

<div class="main">

<h2>Dashboard Power BI</h2>

<!-- FILTROS -->
<form method="GET">
<input type="hidden" name="modulo" value="<?= $modulo ?>">

<input type="date" name="inicio" value="<?= $inicio ?>">
<input type="date" name="fin" value="<?= $fin ?>">

<?php if($tecnico): ?>
<input type="hidden" name="tecnico" value="<?= $tecnico ?>">
<?php endif; ?>

<button>Filtrar</button>
</form>

<!-- RAPIDOS -->
<div>
oy']) ?>">Hoy</a>
tcode('7') ?>">7 días</a>
mes']) ?>">Mes</a>
</div>

<!-- TECNICOS -->
<div class="tecnicos">
<?php foreach($tecnicos as $t): ?>
    =<?= $t['id'] ?>"
    class="<?= ($tecnico==$t['id']) ? 'active' : '' ?>">
    <?= htmlspecialchars($t['nombre'] ?? '') ?>
    </a>
<?php endforeach; ?>
</div>

<!-- FILTRO ACTIVO -->
<?php if($tecnico): ?>
<div class="card">
Filtrando:
<strong>
<?= htmlspecialchars(
    $pdo->query("SELECT nombre FROM usuarios WHERE id=$tecnico")->fetchColumn() ?? ''
) ?>
</strong>

inicio=<?= $inicio ?>&fin=<?= $fin ?>">Quitar</a>
</div>
<?php endif; ?>

<!-- KPIs -->
<div class="kpis">
<div class="card">Total: <span class="kpi-total">0</span></div>
<div class="card">Completadas: <span class="kpi-comp">0</span></div>
<div class="card">Proceso: <span class="kpi-proc">0</span></div>
</div>

<!-- GRÁFICAS -->
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

// EVITA NULL ERROR
const tecnico="<?= $tecnico ?? '' ?>";
const modulo="<?= $modulo ?>";
const inicio="<?= $inicio ?>";
const fin="<?= $fin ?>";

let chartTec=new ApexCharts(document.querySelector("#chartTec"),{
chart:{type:'bar',height:250},
series:[{data:[]}],
xaxis:{categories:[]}
});chartTec.render();

let chartEstado=new ApexCharts(document.querySelector("#chartEstado"),{
chart:{type:'bar',height:250},
series:[{data:[]}],
xaxis:{categories:[]}
});chartEstado.render();

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

});

</script>

</body>
</html>
