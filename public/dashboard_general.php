<?php
require "auth.php";
require "db.php";

$id = $_SESSION['user_id'];

/* USUARIO */
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id=?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario['nombre'] ?? "Usuario";

/* FILTROS */
$modulo = $_GET['modulo'] ?? 'itil';
$hoy = date("Y-m-d");

$inicio = $_GET['inicio'] ?? $hoy;
$fin    = $_GET['fin'] ?? $hoy;
$tecnico = $_GET['tecnico'] ?? null;

/* TECNICOS FILTRADOS (solo 4) */
$tecnicos = $pdo->query("
SELECT id,nombre FROM usuarios 
WHERE nombre IN (
'ANTONIETA RODRIGUEZ',
'SERGIO VALENZUELA',
'ERICK ARIAS RAMIREZ',
'JUAN CARLOS ARAUJO SANCHEZ'
)
ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

/* ================= BASE (igual index) ================= */
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

/* TITULO */
.main h2{
    text-align:center;
    margin-bottom:20px;
}

/* TECNICOS */
.tecnicos{
    margin:15px 0;
    text-align:center;
}

.tecnicos a{
    display:inline-block;
    padding:8px 12px;
    margin:5px;
    border-radius:10px;
    background:var(--card-bg);
    color:var(--text);
    text-decoration:none;
    box-shadow:0 5px 10px var(--shadow);
    transition:.2s;
}

.tecnicos a:hover{
    transform:translateY(-2px);
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
    border-radius:15px;
    box-shadow:0 10px 20px var(--shadow);
    font-size:18px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-top:25px;
}

.box{
    background:var(--card-bg);
    padding:20px;
    border-radius:15px;
    box-shadow:0 10px 20px var(--shadow);
}

</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Dashboard Power BI</h2>

<!-- FILTRO -->
<form method="GET">
<input type="hidden" name="modulo" value="<?= $modulo ?>">

<input type="date" name="inicio" value="<?= $inicio ?>">
<input type="date" name="fin" value="<?= $fin ?>">

<?php if($tecnico): ?>
<input type="hidden" name="tecnico" value="<?= $tecnico ?>">
<?php endif; ?>

<button>Filtrar</button>
</form>

<!-- TECNICOS -->
<div class="tecnicos">
<?php foreach($tecnicos as $t): ?>
    <a href="?tecnico=<?= $t['id'] ?>"
       class="<?= ($tecnico==$t['id'])?'active':'' ?>">
       <?= htmlspecialchars($t['nombre']) ?>
    </a>
<?php endforeach; ?>
</div>

<!-- KPIs -->
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

if(localStorage.getItem("theme")==="dark"){
    document.body.classList.add("dark");
}

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

fetch(`api_dashboard.php`)
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
