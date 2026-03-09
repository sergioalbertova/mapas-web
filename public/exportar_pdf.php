<?php
require __DIR__ . "/db.php";   // ← CORREGIDO: todo está en /public

require __DIR__ . "/../vendor/autoload.php";  // Ajusta si tu vendor está en otro lugar

use Dompdf\Dompdf;
use Dompdf\Options;

// Obtener mes y año desde la URL
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : date('n');
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date('Y');

// Primer día del mes
$primerDia = mktime(0, 0, 0, $mes, 1, $anio);
$diasMes = date('t', $primerDia);
$diaSemana = date('N', $primerDia);

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

// Colores por técnico (los mismos que en web)
$colores = [
    "JUAN CARLOS" => "#1976D2",
    "SERGIO"      => "#388E3C",
    "ANTONIETA"   => "#F57C00",
    "ERIK"        => "#7B1FA2",
];

// Meses en español
$meses = [
    1 => "ENERO", 2 => "FEBRERO", 3 => "MARZO", 4 => "ABRIL",
    5 => "MAYO", 6 => "JUNIO", 7 => "JULIO", 8 => "AGOSTO",
    9 => "SEPTIEMBRE", 10 => "OCTUBRE", 11 => "NOVIEMBRE", 12 => "DICIEMBRE"
];

$nombreMes = $meses[$mes] . " " . $anio;

// Iniciar HTML
$html = "
<style>
body {
    font-family: Arial, sans-serif;
}

h1 {
    text-align: center;
    margin-bottom: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th {
    background: #1976D2;
    color: white;
    padding: 8px;
    font-size: 14px;
}

td {
    height: 80px;
    border: 1px solid #ccc;
    padding: 4px;
    font-size: 13px;
    vertical-align: top;
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
}

/* Técnico */
.tecnico {
    margin-top: 4px;
    padding: 3px;
    border-radius: 4px;
    color: white;
    font-size: 12px;
    display: inline-block;
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
</style>

<h1>$nombreMes</h1>

<table>
<tr>
    <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
</tr>
<tr>
";

// Espacios antes del primer día
for ($i = 1; $i < $diaSemana; $i++) {
    $html .= "<td></td>";
}

$dia = 1;
while ($dia <= $diasMes) {
    $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
    $dow = date('N', strtotime($fecha));
    $tecnico = $mapa[$fecha] ?? null;

    $clase = "";
    if ($tecnico === "FESTIVO") $clase = "festivo";
    if ($dow == 6) $clase = "sabado";
    if ($dow == 7) $clase = "domingo";

    $html .= "<td class='$clase'><div class='celda'>";

    // Número del día
    $html .= "<div class='dia-numero'>$dia</div>";

    // Técnico
    if ($tecnico) {
        $color = $colores[$tecnico] ?? "#333";
        $html .= "<div class='tecnico' style='background:$color'>" . htmlspecialchars($tecnico) . "</div>";
    }

    $html .= "</div></td>";

    if ($dow == 7) $html .= "</tr><tr>";

    $dia++;
}

$html .= "</tr></table>";

// Generar PDF
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("calendario_$mes-$anio.pdf", ["Attachment" => true]);
