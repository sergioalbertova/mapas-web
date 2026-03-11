<?php
date_default_timezone_set('America/Mexico_City');

$pdo = new PDO("mysql:host=localhost;dbname=tu_db;charset=utf8", "user", "pass");
?>


<?php
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

// Obtener guardias del mes
$stmt = $pdo->prepare("
    SELECT fecha, tecnico
    FROM guardias
    WHERE EXTRACT(MONTH FROM fecha) = :mes
      AND EXTRACT(YEAR FROM fecha) = :anio
    ORDER BY fecha ASC
");
$stmt->execute(['mes' => $mes, 'anio' => $anio]);
$guardias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convertir a mapa por fecha
$mapa = [];
foreach ($guardias as $g) {
    $mapa[$g['fecha']] = $g['tecnico'];
}

// Colores por técnico
$colores = [
    "JUAN CARLOS" => "#1976D2",
    "SERGIO"      => "#388E3C",
    "ANTONIETA"   => "#F57C00",
    "ERIK"        => "#7B1FA2",
];

// Día actual
$hoy = date('Y-m-d');
$tecnicoHoy = $mapa[$hoy] ?? "Sin guardia";

// Mostrar info de hoy solo si estamos en el mes actual
$mostrarHoy = ($mes == date('n') && $anio == date('Y'));

// Meses en español
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
body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 20px;
    transition: background 0.3s, color 0.3s;
}

.contenedor {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    transition: background 0.3s, color 0.3s;
}

h1 {
    text-align: center;
    margin-bottom: 5px;
    font-size: 28px;
}

.navegacion {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
}

.boton {
    background: #1976D2;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
}

.boton:hover {
    background: #0D47A1;
}

.info-hoy {
    font-size: 14px;
    padding-top: 8px;
}

/* Ocultar en impresión */
@media print {
    .no-print {
        display: none !important;
    }
}

/* TABLA */
.tabla-calendario {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.tabla-calendario th {
    background: #1976D2;
    color: white;
    padding: 10px;
}

.tabla-calendario td {
    height: 90px;
    padding: 5px;
    border: 1px solid #ddd;
    font-size: 14px;
    background: white;
    overflow: hidden;
}

/* Contenedor interno */
.celda {
    display: block;
    width: 100%;
}

/* Número del día */
.dia-numero {
    font-weight: bold;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Icono del día actual */
.icono-hoy {
    width: 10px;
    height: 10px;
    background: #1976D2;
    border-radius: 50%;
}

/* Día actual */
.hoy {
    border: 3px solid #000;
}

/* Festivo */
.festivo {
    background: #FFE082 !important;
}

/* Fines de semana */
.sabado {
    background: #FFCDD2 !important;
}

.domingo {
    background: #EF9A9A !important;
}

/* Técnico */
.tecnico {
    margin-top: 4px;
    padding: 3px;
    border-radius: 4px;
    color: white;
    font-size: 13px;
    display: inline-block;
}

/* MODO OSCURO */
body.dark {
    background: #1e1e1e;
    color: #e0e0e0;
}

body.dark .contenedor {
    background: #2c2c2c;
    color: #e0e0e0;
}

body.dark .tabla-calendario th {
    background: #333;
}

body.dark .tabla-calendario td {
    background: #3a3a3a;
    border-color: #555;
}

body.dark .boton {
    background: #444;
}

body.dark .boton:hover {
    background: #666;
}

/* Colores especiales en modo oscuro */
body.dark .festivo {
    background: #8d6e63 !important;
}

body.dark .sabado {
    background: #6d2c41 !important;
}

body.dark .domingo {
    background: #8e3b46 !important;
}

body.dark .hoy {
    border: 3px solid #64b5f6 !important;
}

/* Técnicos en modo oscuro */
body.dark .tecnico[style*="#1976D2"] { background: #64b5f6 !important; }
body.dark .tecnico[style*="#388E3C"] { background: #81c784 !important; }
body.dark .tecnico[style*="#F57C00"] { background: #ffb74d !important; }
body.dark .tecnico[style*="#7B1FA2"] { background: #ba68c8 !important; }
</style>

</head>
<body>

<div class="contenedor">

<h1><?= $nombreMes ?></h1>

<div class="navegacion no-print">

    <a href="?mes=<?= $mesAnterior ?>&anio=<?= $anioAnterior ?>" class="boton">◀</a>

    <?php if ($mostrarHoy): ?>
    <div class="info-hoy">
        <strong>Hoy:</strong> <?= date("d/m/Y") ?> — Guardia: <strong><?= $tecnicoHoy ?></strong>
    </div>
    <?php endif; ?>

    <a href="exportar_pdf.php?mes=<?= $mes ?>&anio=<?= $anio ?>" class="boton">📄 PDF</a>

    <button class="boton" onclick="toggleDarkMode()">🌙 Tema</button>

    <a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>" class="boton">▶</a>
</div>

<table class="tabla-calendario">
<tr>
    <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
</tr>

<tr>
<?php
for ($i = 1; $i < $diaSemana; $i++) echo "<td></td>";

$dia = 1;
while ($dia <= $diasMes) {
    $fecha = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
    $dow = date('N', strtotime($fecha));
    $tecnico = $mapa[$fecha] ?? null;

    $clase = "";
    if ($fecha == $hoy) $clase = "hoy";
    if ($tecnico === "FESTIVO") $clase = "festivo";
    if ($dow == 6) $clase = "sabado";
    if ($dow == 7) $clase = "domingo";

    echo "<td class='$clase'>";
    echo "<div class='celda'>";

    echo "<div class='dia-numero'>";
    if ($fecha == $hoy) echo "<span class='icono-hoy'></span>";
    echo "$dia</div>";

    if ($tecnico) {
        $color = $colores[$tecnico] ?? "#333";
        echo "<div class='tecnico' style='background:$color'>" . htmlspecialchars($tecnico) . "</div>";
    }

    echo "</div>";
    echo "</td>";

    if ($dow == 7) echo "</tr><tr>";

    $dia++;
}
?>
</tr>
</table>

</div>

<script>
// Tema oscuro persistente
function toggleDarkMode() {
    document.body.classList.toggle("dark");
    localStorage.setItem("tema", document.body.classList.contains("dark") ? "dark" : "light");
}

if (localStorage.getItem("tema") === "dark") {
    document.body.classList.add("dark");
}

// Auto-actualizar si cambia el día real
setInterval(() => {
    const hoy = new Date().toISOString().slice(0, 10);
    const fechaMostrada = "<?= date('Y-m-d') ?>";

    if (hoy !== fechaMostrada) {
        location.reload();
    }
}, 60000);
</script>

</body>
</html>
