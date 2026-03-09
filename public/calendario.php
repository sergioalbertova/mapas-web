<?php
require "db.php";

// Obtener mes y año desde la URL o usar actuales
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// Primer día del mes
$primerDia = mktime(0, 0, 0, $mes, 1, $anio);
$diasMes = date('t', $primerDia);
$diaSemana = date('N', $primerDia); // 1 = lunes

// Mes anterior y siguiente
$mesAnterior = $mes - 1;
$anioAnterior = $anio;
if ($mesAnterior < 1) { $mesAnterior = 12; $anioAnterior--; }

$mesSiguiente = $mes + 1;
$anioSiguiente = $anio;
if ($mesSiguiente > 12) { $mesSiguiente = 1; $anioSiguiente++; }

// Obtener guardias del mes
$stmt = $pdo->prepare("
    SELECT id, fecha, tecnico
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

// Colores profesionales
$colores = [
    "JUAN CARLOS" => "#1976D2",
    "SERGIO"      => "#388E3C",
    "ANTONIETA"   => "#F57C00",
    "ERIK"        => "#7B1FA2",
];

// Día actual
$hoy = date('Y-m-d');
$tecnicoHoy = $mapa[$hoy] ?? "Sin guardia";

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
}

.contenedor {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

h1 {
    text-align: center;
    margin-bottom: 5px;
}

.navegacion {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.navegacion a {
    text-decoration: none;
    font-size: 22px;
    color: #333;
    padding: 5px 10px;
}

.tabla-calendario {
    width: 100%;
    border-collapse: collapse;
}

.tabla-calendario th {
    background: #1976D2;
    color: white;
    padding: 10px;
}

.tabla-calendario td {
    height: 90px;
    vertical-align: top;
    padding: 5px;
    border: 1px solid #ddd;
    font-size: 14px;
}

.dia-numero {
    font-weight: bold;
}

.hoy {
    border: 3px solid #000;
    background: #FFF59D !important;
}

.festivo {
    background: #FFE082 !important;
}

.sabado {
    background: #FFCDD2 !important;
}

.domingo {
    background: #EF9A9A !important;
}

.tecnico {
    margin-top: 5px;
    padding: 3px;
    border-radius: 4px;
    color: white;
    font-size: 13px;
    display: inline-block;
}
</style>

</head>
<body>

<div class="contenedor">

<h1><?= strtoupper(strftime('%B %Y', $primerDia)) ?></h1>

<div class="navegacion">
    <a href="?mes=<?= $mesAnterior ?>&anio=<?= $anioAnterior ?>">◀</a>
    <div><strong>Hoy:</strong> <?= date("d/m/Y") ?> — Guardia: <strong><?= $tecnicoHoy ?></strong></div>
    <a href="?mes=<?= $mesSiguiente ?>&anio=<?= $anioSiguiente ?>">▶</a>
</div>

<table class="tabla-calendario">
<tr>
    <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
</tr>

<tr>
<?php
// Celdas vacías antes del primer día
for ($i = 1; $i < $diaSemana; $i++) {
    echo "<td></td>";
}

$dia = 1;
while ($dia <= $diasMes) {
    $fecha = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
    $dow = date('N', strtotime($fecha));
    $tecnico = $mapa[$fecha] ?? null;

    $clase = "";
    $color = "";

    if ($fecha == $hoy) $clase = "hoy";
    if ($tecnico === "FESTIVO") $clase = "festivo";
    if ($dow == 6) $clase = "sabado";
    if ($dow == 7) $clase = "domingo";

    if (isset($colores[$tecnico])) {
        $color = $colores[$tecnico];
    }

    echo "<td class='$clase'>";
    echo "<div class='dia-numero'>$dia</div>";

    if ($tecnico) {
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

</body>
</html>
