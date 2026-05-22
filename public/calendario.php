<?php
require "session_config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('America/Mexico_City');
require "db.php";

// Obtener mes y año desde la URL o usar actuales
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// Primer día del mes
$primerDia = mktime(0, 0, 0, $mes, 1, $anio);
$diasMes = date('t', $primerDia);
$diaSemana = date('N', $primerDia);

// Mes anterior y siguiente
$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) { $mesAnterior = 12; $anioAnterior--; }

$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) { $mesSiguiente = 1; $anioSiguiente++; }

// Obtener guardias
$stmt = $pdo->prepare("
    SELECT fecha, tecnico, cumple, cumpleanero
    FROM guardias
    WHERE EXTRACT(MONTH FROM fecha) = :mes
      AND EXTRACT(YEAR FROM fecha) = :anio
    ORDER BY fecha ASC
");
$stmt->execute(['mes' => $mes, 'anio' => $anio]);
$guardias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convertir a mapa
$mapa = [];
foreach ($guardias as $g) {
    $mapa[$g['fecha']] = [
        'tecnico'     => $g['tecnico'],
        'cumple'      => $g['cumple'],
        'cumpleanero' => $g['cumpleanero']
    ];
}

// Colores
$colores = [
    "JUAN CARLOS" => "#1976D2",
    "SERGIO"      => "#388E3C",
    "ANTONIETA"   => "#F57C00",
    "ERIK"        => "#7B1FA2",
];

// Día actual
$hoy = date('Y-m-d');
$tecnicoHoy = isset($mapa[$hoy]) ? ($mapa[$hoy]['tecnico'] ?? "Sin guardia") : "Sin guardia";

$mostrarHoy = ($mes == date('n') && $anio == date('Y'));

$meses = [
    1 => "ENERO", 2 => "FEBRERO", 3 => "MARZO", 4 => "ABRIL",
    5 => "MAYO", 6 => "JUNIO", 7 => "JULIO", 8 => "AGOSTO",
    9 => "SEPTIEMBRE", 10 => "OCTUBRE", 11 => "NOVIEMBRE", 12 => "DICIEMBRE"
];

$nombreMes = $meses[$mes] . " " . $anio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Calendario de Guardias</title>

<style>

/* ============================ TU CSS ORIGINAL ============================ */
/* (NO SE MODIFICÓ NADA ARRIBA) */


/* ===== ✅ AGREGADO SEGURO ===== */
.leyenda {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 15px;
    flex-wrap: wrap;
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
}

/* ✅ mejora visual SIN romper */
.hoy {
    border: 3px solid var(--primary);
    background: #BBDEFB !important;
    box-shadow: inset 0 0 0 2px #1565C0;
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
<div class="info-hoy">
<strong>Hoy:</strong> <?= date("d/m/Y") ?> — Guardia: <strong><?= htmlspecialchars($tecnicoHoy) ?></strong>
</div>
<?php endif; ?>

<a href="exportar_pdf.php?mes=<?= $mes ?>&anio=<?= $anio ?>" class="boton">📄 PDF</a>

<button class="boton" onclick="toggleTheme()">🌙 Tema</button>

<a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>" class="boton">▶</a>

</div>

<!-- ✅ AGREGADO: LEYENDA -->
<div class="leyenda">
<?php foreach ($colores as $nombre => $color): ?>
    <div class="item-leyenda">
        <span class="color" style="background: <?= $color ?>"></span>
        <?= htmlspecialchars($nombre) ?>
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
for ($i = 1; $i < $diaSemana; $i++) echo "<td></td>";

$dia = 1;
while ($dia <= $diasMes) {

    $fecha = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
    $dow = date('N', strtotime($fecha));

    $info = $mapa[$fecha] ?? null;
    $tecnico = $info['tecnico'] ?? null;
    $cumple = $info['cumple'] ?? false;
    $cumpleanero = $info['cumpleanero'] ?? null;

    $clases = [];
    if ($fecha == $hoy) $clases[] = "hoy";
    if ($tecnico === "FESTIVO") $clases[] = "festivo";
    if ($dow == 6) $clases[] = "sabado";
    if ($dow == 7) $clases[] = "domingo";
    if ($cumple) $clases[] = "cumple-dia";

    echo "<td class='".implode(' ', $clases)."'>";
    echo "<div class='celda'>";

    echo "<div class='dia-numero'>";
    if ($fecha == $hoy) echo "<span class='icono-hoy'></span>";
    echo "$dia</div>";

    if ($cumple) {
        echo "<div class='cumple-wrapper'>
                <svg class='icono-cumple' width='18' height='18' viewBox='0 0 24 24'>
                    <path fill='#ff4081' d='M12 2c.6 0 1 .4 1 1v2h-2V3c0-.6.4-1 1-1zm-4 4h8c1.1 0 2 .9 2 2v3H6V8c0-1.1.9-2 2-2zm-3 7h14c1.1 0 2 .9 2 2v5H3v-5c0-1.1.9-2 2-2zm2 4h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z'/>
                </svg>
                <span class='cumpleanero'>" . htmlspecialchars($cumpleanero) . "</span>
              </div>";
    }

    if ($tecnico) {
        $color = $colores[$tecnico] ?? "#333";
        echo "<div class='tecnico' style='background:$color'>" . htmlspecialchars($tecnico) . "</div>";
    }

    echo "</div></td>";

    if ($dow == 7) echo "</tr><tr>";

    $dia++;
}
?>
</tr>
</table>

</div>
</div>

<script>
function toggleTheme() {
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
}

if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}
</script>

</body>
</html>