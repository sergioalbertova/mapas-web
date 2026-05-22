<?php
require "session_config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('America/Mexico_City');
require "db.php";

// Obtener mes y año
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

$primerDia = mktime(0, 0, 0, $mes, 1, $anio);
$diasMes = date('t', $primerDia);
$diaSemana = date('N', $primerDia);

// Navegación
$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) { $mesAnterior = 12; $anioAnterior--; }

$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) { $mesSiguiente = 1; $anioSiguiente++; }

// Guardias
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

// Colores
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
:root {
    --bg:#F4F7FA;
    --card-bg:#FFF;
    --primary:#0054A6;
    --primary-hover:#003F7D;
    --text:#1F2933;
    --subtext:#6B7280;
}

body {
    font-family: Segoe UI;
    background:var(--bg);
}

/* CALENDARIO */
.contenedor {
    max-width:900px;
    margin:auto;
    background:var(--card-bg);
    padding:20px;
    border-radius:12px;
}

.tabla-calendario {
    width:100%;
    border-collapse:collapse;
}

.tabla-calendario td {
    height:90px;
    border:1px solid #ddd;
}

/* ✅ NUEVO: LEYENDA */
.leyenda {
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    justify-content:center;
    margin-bottom:15px;
}

.item-leyenda {
    display:flex;
    align-items:center;
    gap:6px;
    font-size:13px;
    color:var(--subtext);
}

.color {
    width:14px;
    height:14px;
    border-radius:3px;
}

/* ✅ MEJORADO: HOY */
.hoy {
    border:3px solid var(--primary);
    background: #E3F2FD !important;
}

.icono-hoy {
    width:10px;
    height:10px;
    background:var(--primary);
    border-radius:50%;
}

.tecnico {
    color:#fff;
    padding:3px;
    border-radius:4px;
    font-size:13px;
    display:inline-block;
    margin-top:4px;
}
</style>
</head>

<body>

<div class="contenedor">

<h1><?= $nombreMes ?></h1>

<div class="navegacion">
<a href="?mes=<?= $mesAnterior ?>&anio=<?= $anioAnterior ?>">◀</a>

<?php if ($mostrarHoy): ?>
<div>
Hoy: <?= date("d/m/Y") ?> — <b><?= htmlspecialchars($tecnicoHoy) ?></b>
</div>
<?php endif; ?>

<a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>">▶</a>
</div>

<!-- ✅ NUEVO: LEYENDA -->
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
for ($i=1;$i<$diaSemana;$i++) echo "<td></td>";

$dia=1;
while ($dia <= $diasMes):

$fecha = sprintf("%04d-%02d-%02d",$anio,$mes,$dia);
$dow = date('N',strtotime($fecha));

$info = $mapa[$fecha] ?? null;
$tecnico = $info['tecnico'] ?? null;

$clases=[];
if ($fecha==$hoy) $clases[]="hoy";

echo "<td class='".implode(" ",$clases)."'>";

echo "<div>";
echo "<div>";
if ($fecha==$hoy) echo "<span class='icono-hoy'></span>";
echo "$dia</div>";

if ($tecnico){
$color=$colores[$tecnico] ?? "#333";
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
</body>
</html>
``