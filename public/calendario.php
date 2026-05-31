<?php
require "session_config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('America/Mexico_City');
require "db.php";

$id = $_SESSION['user_id'];

// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";

// Obtener mes y año
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

// Contador
$conteo = [];
foreach ($colores as $nombre => $c) $conteo[$nombre] = 0;
foreach ($guardias as $g) {
    if (isset($conteo[$g['tecnico']])) $conteo[$g['tecnico']]++;
}

// Día actual
$hoy = date('Y-m-d');
$tecnicoHoy = $mapa[$hoy]['tecnico'] ?? "Sin guardia";
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

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>
/* VARIABLES TIHIL */
:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --subtext: #6B7280;
    --primary: #0054A6;
    --primary-hover: #003F7D;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --subtext: #9CA3AF;
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
}

/* LAYOUT */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: 0.3s;
}

.main {
    margin-left: 240px;
    padding: 30px;
    width: calc(100% - 240px);
    transition: margin-left 0.25s ease;
}

.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* CONTENEDOR */
.contenedor {
    max-width: 900px;
    margin: 0 auto;
    background: var(--card-bg);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px var(--shadow);
}

/* CALENDARIO */
h1 {
    text-align: center;
    margin-bottom: 10px;
    font-size: 36px;
    font-weight: 700;
    letter-spacing: -1px;
    color: var(--primary);
}


.navegacion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.boton {
    background: var(--primary);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
}

.boton:hover {
    background: var(--primary-hover);
}

.tabla-calendario {
    width: 100%;
    border-collapse: collapse;
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
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.hoy {
    border: 3px solid var(--primary);
    background: rgba(0,174,239,0.15) !important;
}

.tecnico {
    margin-top: 4px;
    padding: 3px;
    border-radius: 4px;
    color: white;
    font-size: 13px;
    display: inline-block;
}

.cumple-dia {
    background: #E3F2FD !important;
}

.cumple-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

.cumpleanero {
    font-weight: bold;
    color: #ff4081;
    font-size: 13px;
}

.leyenda, .resumen {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.item-leyenda, .item-resumen {
    font-size: 13px;
    color: var(--subtext);
    display: flex;
    align-items: center;
    gap: 6px;
}

.color {
    width: 14px;
    height: 14px;
    border-radius: 3px;
}

.sabado { background: #9ea0a1 !important; }
.domingo { background: #838788 !important; }

.info-hoy {
    font-size: 18px;
    font-weight: 600;
    color: var(--text);
    background: rgba(0, 174, 239, 0.12);
    padding: 10px 16px;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}

</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<div class="contenedor">

<h1><?= $nombreMes ?></h1>

<div class="navegacion">
    <a href="?mes=<?= $mesAnterior ?>&anio=<?= $anioAnterior ?>" class="boton">◀</a>

    <?php if ($mostrarHoy): ?>
        <div><strong>Hoy:</strong> <?= date("d/m/Y") ?> — Guardia: <strong><?= htmlspecialchars($tecnicoHoy) ?></strong></div>
    <?php endif; ?>

    <a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>" class="boton">▶</a>
</div>

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
    if ($dow == 6) $clases[] = "sabado";
    if ($dow == 7) $clases[] = "domingo";
    if ($cumple) $clases[] = "cumple-dia";

    echo "<td class='".implode(' ', $clases)."'>";
    echo "<div class='dia-numero'>";
    echo "$dia</div>";

    if ($cumple) {
        echo "<div class='cumple-wrapper'>
                <span class='cumpleanero'>" . htmlspecialchars($cumpleanero) . "</span>
              </div>";
    }

    if ($tecnico) {
        $color = $colores[$tecnico] ?? "#333";
        echo "<div class='tecnico' style='background:$color'>" . htmlspecialchars($tecnico) . "</div>";
    }

    echo "</td>";

    if ($dow == 7) echo "</tr><tr>";

    $dia++;
}
?>
</tr>
</table>

</div>
</div>

<script src="theme.js"></script>

</body>
</html>
