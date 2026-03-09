<?php
require "db.php";
require "vendor/autoload.php";

use Dompdf\Dompdf;

// Obtener mes y año
$mes = intval($_GET['mes']);
$anio = intval($_GET['anio']);

// Meses en español
$meses = [
    1 => "ENERO", 2 => "FEBRERO", 3 => "MARZO", 4 => "ABRIL",
    5 => "MAYO", 6 => "JUNIO", 7 => "JULIO", 8 => "AGOSTO",
    9 => "SEPTIEMBRE", 10 => "OCTUBRE", 11 => "NOVIEMBRE", 12 => "DICIEMBRE"
];

$nombreMes = $meses[$mes] . " " . $anio;

// Obtener guardias
$stmt = $pdo->prepare("
    SELECT fecha, tecnico
    FROM guardias
    WHERE EXTRACT(MONTH FROM fecha) = :mes
      AND EXTRACT(YEAR FROM fecha) = :anio
    ORDER BY fecha ASC
");
$stmt->execute(['mes' => $mes, 'anio' => $anio]);
$guardias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapa = [];
foreach ($guardias as $g) $mapa[$g['fecha']] = $g['tecnico'];

$colores = [
    "JUAN CARLOS" => "#1976D2",
    "SERGIO"      => "#388E3C",
    "ANTONIETA"   => "#F57C00",
    "ERIK"        => "#7B1FA2",
];

$primerDia = mktime(0,0,0,$mes,1,$anio);
$diasMes = date('t', $primerDia);
$diaSemana = date('N', $primerDia);

ob_start();
?>

<style>
body { font-family: Arial; }
h1 { text-align:center; }
table { width:100%; border-collapse:collapse; table-layout:fixed; }
th { background:#1976D2; color:white; padding:8px; }
td { border:1px solid #ccc; height:80px; padding:5px; }
.sabado { background:#FFCDD2; }
.domingo { background:#EF9A9A; }
.festivo { background:#FFE082; }
.tecnico { margin-top:5px; padding:3px; border-radius:4px; color:white; font-size:12px; display:inline-block; }
</style>

<h1><?= $nombreMes ?></h1>

<table>
<tr>
    <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th><th>Sáb</th><th>Dom</th>
</tr>
<tr>

<?php
for ($i=1; $i<$diaSemana; $i++) echo "<td></td>";

$dia = 1;
while ($dia <= $diasMes) {
    $fecha = sprintf("%04d-%02d-%02d", $anio, $mes, $dia);
    $dow = date('N', strtotime($fecha));
    $tecnico = $mapa[$fecha] ?? null;

    $clase = "";
    if ($tecnico === "FESTIVO") $clase = "festivo";
    if ($dow == 6) $clase = "sabado";
    if ($dow == 7) $clase = "domingo";

    echo "<td class='$clase'>";
    echo "<strong>$dia</strong>";

    if ($tecnico) {
        $color = $colores[$tecnico] ?? "#333";
        echo "<div class='tecnico' style='background:$color'>$tecnico</div>";
    }

    echo "</td>";

    if ($dow == 7) echo "</tr><tr>";

    $dia++;
}
?>
</tr>
</table>

<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream("calendario_$mes-$anio.pdf");
