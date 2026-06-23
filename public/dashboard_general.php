<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'] ?? 'itil';

$hoy = date("Y-m-d");
$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;

$tecnico = $_GET['tecnico'] ?? "";

/* ===== FILTROS RÁPIDOS ===== */
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

function url($params){
    return '?' . http_build_query(array_merge($_GET,$params));
}
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

<!-- HEADER -->
<div class="header">
    <h2>Dashboard Power BI</h2>

    <div class="tabs">
        <a href="<?php echo url(['modulo'=>'itil']); ?>"
           class="tab <?= $modulo=='itil'?'active':'' ?>">
           📊 ITIL
        </a>

        <a href="<?php echo url(['modulo'=>'actividades']); ?>"
           class="tab <?= $modulo=='actividades'?'active':'' ?>">
           ⚙ Actividades
        </a>
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

    <a href="<?php echo url(['rango'=>'hoy']); ?>">Hoy</a>
    <a href="<?php echo url(['rango'=>'7']); ?>">7 días</a>
    <a href="<?php echo url(['rango'=>'mes']); ?>">Mes</a>

</div>

</div>

<!-- KPIs -->
<div class="kpis">

<div class="card">
    <span>Total</span>
    <strong class="kpi-total">0</strong>
</div>

<div class="card">
    <span>Completadas</span>
    <strong class="kpi-comp">0</strong>
</div>

<div class="card">
    <span>En proceso</span>
    <strong class="kpi-proc">0</strong>
</div>

</div>

<!-- GRÁFICAS -->
<div class="grid-2">

<div class="box">
    <h3>Por técnico</h3>
    <div id="chartTec"></div>
</div>

<div class="box">
    <h3>Por estado</h3>
    <div id="chartEstado"></div>
</div>

</div>

</div>

<script>

/* ===== DARK MODE ===== */
if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

/* ===== VARIABLES ===== */
const modulo = "<?= $modulo ?>";
const inicio = "<?= $inicio ?>";
const fin    = "<?= $fin ?>";
const tecnico = "<?= $tecnico ?>";

/* ===== CHARTS ===== */
let chartTec = new ApexCharts(document.querySelector("#chartTec"), {
    chart:{ type:'bar', height:280 },
    series:[{ data:[] }],
    xaxis:{ categories:[] }
});
chartTec.render();

let chartEstado = new ApexCharts(document.querySelector("#chartEstado"), {
    chart:{ type:'bar', height:280 },
    series:[{ data:[] }],
    xaxis:{ categories:[] }
});
chartEstado.render();

/* ===== CARGA ===== */
function cargar(){

fetch(`api_dashboard.php?modulo=${modulo}&inicio=${inicio}&fin=${fin}&tecnico=${tecnico}`)
.then(r=>r.json())
.then(d=>{

    document.querySelector(".kpi-total").textContent = d.total;
    document.querySelector(".kpi-comp").textContent  = d.completadas;
    document.querySelector(".kpi-proc").textContent  = d.proceso;

    chartTec.updateOptions({
        xaxis:{ categories: d.tecLabels },
        chart:{
            events:{
                dataPointSelection:function(e,ctx,cfg){
                    let id = d.tecIDs[cfg.dataPointIndex];
                    let url = new URL(window.location);
                    url.searchParams.set("tecnico", id);
                    window.location = url;
                }
            }
        }
    });

    chartTec.updateSeries([{ data:d.tecData }]);

    chartEstado.updateOptions({
        xaxis:{ categories:d.estadoLabels }
    });

    chartEstado.updateSeries([{ data:d.estadoData }]);

});

}

cargar();

</script>

<script src="theme.js"></script>

</body>
</html>
``