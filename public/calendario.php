<?php
require "auth.php";
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
    --shadow: rgba(0,0,0,0.12);
    --border: rgba(0,0,0,0.18);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --subtext: #9CA3AF;
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
    --border: rgba(255,255,255,0.15);
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

/* CONTENEDOR PREMIUM */
.contenedor {
    max-width: 1200px;
    margin: 0 auto;
    background: var(--card-bg);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 12px 35px var(--shadow);
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* TÍTULO DEL MES */
h1 {
    text-align: center;
    margin-bottom: 15px;
    font-size: 40px;
    font-weight: 700;
    letter-spacing: -1px;
    color: var(--primary);
    border-bottom: 2px solid var(--border);
    padding-bottom: 10px;
}

/* INFO HOY */
.info-hoy {
    font-size: 20px;
    font-weight: 600;
    color: var(--text);
    background: rgba(0, 174, 239, 0.12);
    padding: 12px 18px;
    border-radius: 10px;
    border-left: 4px solid var(--primary);
}

/* NAVEGACIÓN GLASS */
.navegacion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 12px;
    border-radius: 12px;
    backdrop-filter: blur(6px);
    background: rgba(255,255,255,0.4);
}

body.dark .navegacion {
    background: rgba(0,0,0,0.25);
}

.boton {
    background: var(--primary);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    font-size: 15px;
    transition: 0.2s ease;
}

.boton:hover {
    background: var(--primary-hover);
}

/* SELECTORES */
.selector {
    display: flex;
    gap: 10px;
}

.selector select {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--card-bg);
    color: var(--text);
    font-size: 15px;
}

/* CALENDARIO */
.tabla-calendario {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* 🔥 MISMO ANCHO PARA TODAS LAS COLUMNAS */
}

.tabla-calendario th {
    background: var(--primary);
    color: white;
    padding: 12px;
    font-size: 15px;
}

.tabla-calendario td {
    height: 100px;
    padding: 6px;
    border: 1px solid var(--border);
    background: var(--card-bg);
    transition: 0.2s ease;
}

.tabla-calendario td:hover {
    background: rgba(0, 174, 239, 0.12);
    cursor: pointer;
}

.dia-numero {
    font-weight: bold;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* DÍA ACTUAL PREMIUM */
.hoy {
    border: 3px solid var(--primary);
    background: rgba(0,174,239,0.15) !important;
    box-shadow: 0 0 12px rgba(0,174,239,0.5);
}

/* TECNICOS */
.tecnico {
    margin-top: 4px;
    padding: 4px;
    border-radius: 4px;
    color: white;
    font-size: 14px;
    display: inline-block;
}

/* CUMPLEAÑOS */
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
    font-size: 14px;
}

/* LEYENDA Y RESUMEN */
.leyenda, .resumen {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.item-leyenda, .item-resumen {
    font-size: 14px;
    color: var(--subtext);
    display: flex;
    align-items: center;
    gap: 6px;
}

.color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
}

/* FINES DE SEMANA */
.sabado { background: #9ea0a1 !important; }
.domingo { background: #838788 !important; }

.icono-futbol {
    width: 20px;
    height: 20px;
    fill: #00aaff;
    margin-right: 6px;

    display: inline-block;
    transform-box: fill-box;
    transform-origin: center;
}

.icono-futbol:hover {
    transform: none !important;
}
.cumple-wrapper {
    transform: none !important;
}


.rebote {
    animation: reboteFutbol 0.8s ease-in-out infinite;
}

@keyframes reboteFutbol {
    0%   { transform: translateY(0); }
    30%  { transform: translateY(-6px); }
    50%  { transform: translateY(0); }
    70%  { transform: translateY(-3px); }
    100% { transform: translateY(0); }
}


/* MODO OSCURO */
body.dark .icono-futbol {
    fill: #4fc3ff;
}

.icono-guardia {
    width: 18px;
    height: 18px;
    color: #00AEEF !important; /* azul TIHIL */
    margin-right: 6px;
    display: inline-block;
}

body.dark .icono-guardia {
    color: #4fc3ff !important; /* azul claro en modo oscuro */
}

/* Animación */
.rebote {
    animation: reboteGuardia 0.8s ease-in-out infinite;
}

@keyframes reboteGuardia {
    0%   { transform: translateY(0); }
    30%  { transform: translateY(-5px); }
    50%  { transform: translateY(0); }
    70%  { transform: translateY(-3px); }
    100% { transform: translateY(0); }
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

    <div class="selector">
        <form method="GET">
            <select name="mes" onchange="this.form.submit()">
                <?php foreach ($meses as $num => $nombre): ?>
                    <option value="<?= $num ?>" <?= $num == $mes ? 'selected' : '' ?>>
                        <?= $nombre ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="anio" onchange="this.form.submit()">
                <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $anio ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <a href="exportar_pdf.php?mes=<?= $mes ?>&anio=<?= $anio ?>" class="boton">📄 PDF</a>

    <a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>" class="boton">▶</a>

</div>

<?php if ($mostrarHoy): ?>
<div class="info-hoy">
    <strong>Hoy:</strong> <?= date("d/m/Y") ?> — Guardia:
    <strong><?= htmlspecialchars($tecnicoHoy) ?></strong>
</div>
<?php endif; ?>




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
    echo "<div class='dia-numero'>$dia</div>";

    if ($cumple) {
        echo "<div class='cumple-wrapper'>
                <svg class='icono-futbol rebote' viewBox='0 0 24 24' style='overflow: visible;'>
                    <path d='M12 2a10 10 0 100 20 10 10 0 000-20zm5.93 6.36l-2.12-.3-1.06-1.88 1.3-1.8a8.03 8.03 0 012.88 3.98zM9.25 4.18l1.3 1.8-1.06 1.88-2.12.3a8.03 8.03 0 012.88-3.98zM4.07 14.36a8.03 8.03 0 01-.02-4.72l1.8 1.3.3 2.12-1.88 1.06zm3.18 5.46l-.3-2.12 1.88-1.06 1.8 1.3a8.03 8.03 0 01-3.38.88zm6.5-.88l1.8-1.3 1.88 1.06-.3 2.12a8.03 8.03 0 01-3.38-.88zm5.18-4.58l-1.88-1.06.3-2.12 1.8-1.3a8.03 8.03 0 01-.22 4.48zM12 14.5l-2.12-1.06-.3-2.12L12 10l2.42 1.32-.3 2.12L12 14.5z'/>
                </svg>
                <span class='cumpleanero'>" . htmlspecialchars($cumpleanero) . "</span>
              </div>";
    }

    if ($tecnico) {
        $color = $colores[$tecnico] ?? "#333";

        echo "<div class='tecnico' style='background:$color'>";

        // SOLO HOY aparece el ícono animado de guardia
        if ($fecha == $hoy) {
            echo "<svg class='icono-guardia rebote' viewBox='0 0 24 24' fill='currentColor' style='overflow: visible;'>
                    <path d='M12 2l8 4v6c0 5-3.5 9.7-8 10-4.5-.3-8-5-8-10V6l8-4zm-1 13l5-5-1.4-1.4L11 12.2l-1.6-1.6L8 12l3 3z'/>
                  </svg>";
        }

        echo htmlspecialchars($tecnico) . "</div>";
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
