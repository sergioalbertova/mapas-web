<?php
require "session_config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('America/Mexico_City');
require "db.php";

$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

$primerDia = mktime(0, 0, 0, $mes, 1, $anio);
$diasMes = date('t', $primerDia);
$diaSemana = date('N', $primerDia);

$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) { $mesAnterior = 12; $anioAnterior--; }

$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) { $mesSiguiente = 1; $anioSiguiente++; }

$stmt = $pdo->prepare("
    SELECT fecha, tecnico, cumple, cumpleanero
    FROM guardias
    WHERE EXTRACT(MONTH FROM fecha) = :mes
      AND EXTRACT(YEAR FROM fecha) = :anio
    ORDER BY fecha ASC
");
$stmt->execute(['mes' => $mes, 'anio' => $anio]);
$guardias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapa = [];
foreach ($guardias as $g) {
    $mapa[$g['fecha']] = $g;
}

$colores = [
    "JUAN CARLOS" => "#1976D2",
    "SERGIO"      => "#388E3C",
    "ANTONIETA"   => "#F57C00",
    "ERIK"        => "#7B1FA2",
];

$hoy = date('Y-m-d');
$tecnicoHoy = $mapa[$hoy]['tecnico'] ?? "Sin guardia";
$mostrarHoy = ($mes == date('n') && $anio == date('Y'));

$meses = [
    1=>"ENERO",2=>"FEBRERO",3=>"MARZO",4=>"ABRIL",
    5=>"MAYO",6=>"JUNIO",7=>"JULIO",8=>"AGOSTO",
    9=>"SEPTIEMBRE",10=>"OCTUBRE",11=>"NOVIEMBRE",12=>"DICIEMBRE"
];

$nombreMes = $meses[$mes] . " " . $anio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Calendario</title>

<style>
/* ============================
   PALETA CORPORATIVA
   ============================ */
:root {
    --bg: #F4F7FA;
    --sidebar-bg: #FFFFFF;
    --sidebar-hover: #E8EEF5;
    --card-bg: #FFFFFF;
    --text: #1F2933;
    --subtext: #6B7280;
    --primary: #0054A6;
    --primary-hover: #003F7D;
    --accent-cyan: #00AEEF;
    --accent-red: #EF3E42;
    --shadow: rgba(0,0,0,0.08);
}

/* ============================
   TEMA OSCURO
   ============================ */
body.dark {
    --bg: #1A1D21;
    --sidebar-bg: #24272C;
    --sidebar-hover: #2F3338;
    --card-bg: #2C2F34;
    --text: #E5E7EB;
    --subtext: #9CA3AF;
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
}

/* ============================
   ESTILOS GENERALES
   ============================ */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    transition: 0.3s;
    display: flex;
}

/* ============================
   SIDEBAR (sin cambios)
   ============================ */
/* (todo tu bloque sidebar se queda igual) */

/* ============================
   MAIN
   ============================ */
.main {
    margin-left: 240px;
    padding: 30px;
    width: calc(100% - 240px);
    display: flex;
    justify-content: center;
}

.sidebar.collapsed + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ============================
   CONTENEDOR
   ============================ */
.contenedor {
    max-width: 900px;
    width: 100%;
    background: var(--card-bg);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px var(--shadow);
}

/* ============================
   LEYENDA ✅ FIX PRINCIPAL
   ============================ */
.leyenda {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 14px;
    margin: 10px 0 20px 0;
}

.item-leyenda {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--subtext);
}

.color {
    width: 14px;
    height: 14px;
    border-radius: 3px;
    display: inline-block;
}

/* ============================
   CALENDARIO
   ============================ */
.tabla-calendario {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.tabla-calendario th {
    background: var(--primary);
    color: white;
    padding: 10px;
}

.tabla-calendario td {
    height: 90px;
    padding: 5px;
    border: 1px solid #ddd;
    background: var(--card-bg);
}

.dia-numero {
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ✅ HOY (mejorado) */
.hoy {
    background: #BBDEFB !important;
    border: 3px solid #0D47A1 !important;
    box-shadow: inset 0 0 0 2px #1565C0;
}

.icono-hoy {
    width:10px;
    height:10px;
    background:#0D47A1;
    border-radius:50%;
}

/* técnico */
.tecnico {
    margin-top: 4px;
    padding: 3px;
    border-radius: 4px;
    color: white;
    font-size: 13px;
    display: inline-block;
}

/* ============================
   CUMPLE
   ============================ */
.cumple-dia {
    background: #E3F2FD !important;
}

.cumpleanero {
    font-weight: bold;
    color: #ff4081;
}
</style>
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">
<div class="contenedor">

<h1><?= $nombreMes ?></h1>

<div class="navegacion">

    <a href="?mes=<?= $mesAnterior ?>&anio=<?= $anioAnterior ?>" class="boton">◀</a>

    <?php if ($mostrarHoy): ?>
        <div>
            <strong>Hoy:</strong> <?= date("d/m/Y") ?> — <b><?= htmlspecialchars($tecnicoHoy) ?></b>
        </div>
    <?php endif; ?>

    <a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>" class="boton">▶</a>

</div>

<!-- LEYENDA -->
<div class="leyenda">
<?php foreach ($colores as $nombre => $color): ?>
    <div class="item-leyenda">
        <span class="color" style="background: <?= $color ?>"></span>
        <?= $nombre ?>
    </div>
<?php endforeach; ?>
</div>

<table class="tabla-calendario">
<tr>
<th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th>
<th>Vie</th><th>Sáb</th><th>Dom</th>
</tr>

<tr>
<?php
for ($i=1; $i<$diaSemana; $i++) echo "<td></td>";

$dia=1;
while ($dia <= $diasMes):

$fecha = sprintf("%04d-%02d-%02d",$anio,$mes,$dia);
$dow = date('N',strtotime($fecha));

$info = $mapa[$fecha] ?? null;
$tecnico = $info['tecnico'] ?? null;

$clases=[];
if ($fecha == $hoy) $clases[]="hoy";

echo "<td class='".implode(" ",$clases)."'>";

echo "<div>";
echo "<div>";
if ($fecha==$hoy) echo "<span class='icono-hoy'></span> ";
echo "$dia</div>";

if ($tecnico){
    $color = $colores[$tecnico] ?? "#333";
    echo "<div class='tecnico' style='background:$color'>$tecnico</div>";
}

echo "</div></td>";

if ($dow==7) echo "</tr><tr>";

$dia++;
endwhile;
?>
</tr>
</table>

</div>
</div>

</body>
</html>
``