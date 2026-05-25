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
$guardias = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

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

// ✅ CONTADOR DE GUARDIAS (CORREGIDO)
$conteo = [];
foreach ($colores as $nombre => $c) {
    $conteo[$nombre] = 0;
}

foreach ($guardias as $g) {
    $tec = $g['tecnico'] ?? null;
    if ($tec && isset($conteo[$tec])) {
        $conteo[$tec]++;
    }
}

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
   SIDEBAR
   ============================ */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    box-shadow: 4px 0 20px var(--shadow);
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    position: fixed;
    transition: width 0.25s ease;
    overflow: visible;
    z-index: 2000;
}
.sidebar.collapsed { width: 70px; }

.sidebar h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: var(--primary);
    transition: opacity 0.25s ease;
}
.sidebar.collapsed h2 { opacity: 0; }

.nav-item {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}
.nav-item:hover { background: var(--sidebar-hover); }

.nav-item a {
    display:flex;
    align-items:center;
    gap:12px;
    color:inherit;
    text-decoration:none;
}

.nav-item svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.sidebar.collapsed .nav-text { display: none; }

.tooltip {
    position: absolute;
    left: 80px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--sidebar-bg);
    padding: 6px 12px;
    border-radius: 6px;
    box-shadow: 0 2px 8px var(--shadow);
    font-size: 13px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, left 0.2s ease;
    z-index: 99999;
}
.sidebar.collapsed .nav-item:hover .tooltip {
    opacity: 1;
    left: 75px;
}

/* ============================
   CONTENIDO PRINCIPAL (SIN TOPBAR)
   ============================ */
.main {
    margin-left: 240px;
    padding: 30px;
    width: calc(100% - 240px);
    transition: margin-left 0.25s ease, width 0.25s ease;
    display: flex;
    justify-content: center;
}

.sidebar.collapsed + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ============================
   CALENDARIO
   ============================ */
.contenedor {
    max-width: 900px;
    width: 100%;
    background: var(--card-bg);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px var(--shadow);
}

h1 {
    text-align: center;
    margin-bottom: 5px;
    font-size: 28px;
    color: var(--primary);
}

.navegacion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
}

.boton {
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
}

.boton:hover {
    background: var(--primary-hover);
}

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
    font-size: 14px;
    background: var(--card-bg);
}

.dia-numero {
    font-weight: bold;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.icono-hoy {
    width: 10px;
    height: 10px;
    background: var(--primary);
    border-radius: 50%;
}

.hoy {
    border: 3px solid var(--primary);
}

/* puedes tener clases festivo, sabado, domingo si las usas en tu CSS original */

.tecnico {
    margin-top: 4px;
    padding: 3px;
    border-radius: 4px;
    color: white;
    font-size: 13px;
    display: inline-block;
}

/* ============================
   ESTILO CUMPLEAÑOS
   ============================ */
.cumple-dia {
    background: #E3F2FD !important;
}

.cumple-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

.icono-cumple {
    width: 18px;
    height: 18px;
}

.cumpleanero {
    font-weight: bold;
    color: #ff4081;
    font-size: 13px;
}
/* ============================ TU CSS ORIGINAL ============================ */
/* (NO SE MODIFICÓ NADA ARRIBA) */


/* ===== ✅ AGREGADO SEGURO ===== */
.leyenda {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 12px;
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

/* ===== RESUMEN DE GUARDIAS ===== */
.resumen {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.item-resumen {
    font-size: 13px;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ===== FINES DE SEMANA (FORZADO) ===== */

.tabla-calendario td.sabado {
    background: #b6d3f0 !important;
}

.tabla-calendario td.domingo {
    background: #838788 !important;
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

<div class="resumen">
<?php foreach ($conteo as $nombre => $total): ?>
    <div class="item-resumen">
        <span style="color: <?= $colores[$nombre] ?>">■</span>
        <?= htmlspecialchars($nombre) ?>: <strong><?= $total ?></strong>
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