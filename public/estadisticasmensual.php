<?php
require "auth.php";
require "db.php";

$inicioMesAnterior = date('Y-m-01', strtotime('first day of last month'));
$finMesAnterior    = date('Y-m-t', strtotime('last month'));

$params = [
    ':inicio' => $inicioMesAnterior . " 00:00:00",
    ':fin'    => $finMesAnterior . " 23:59:59"
];

/* TOTAL INCIDENTES */
$sql = "
SELECT COUNT(*)
FROM itil_incidentes
WHERE fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$totalIncidentes = $stmt->fetchColumn();

/* SLA GENERAL */
$sql = "
SELECT

COUNT(*) FILTER (
WHERE fecha_resolucion IS NOT NULL
AND (
      (prioridad='Alta'
       AND fecha_resolucion <= fecha_reporte + INTERVAL '4 hours')

   OR (prioridad='Media'
       AND fecha_resolucion <= fecha_reporte + INTERVAL '8 hours')

   OR (prioridad='Baja'
       AND fecha_resolucion <= fecha_reporte + INTERVAL '24 hours')
)
) AS cumplidos,

COUNT(*) FILTER (
WHERE fecha_resolucion IS NOT NULL
) AS total

FROM itil_incidentes
WHERE fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$slaGeneralRow = $stmt->fetch(PDO::FETCH_ASSOC);

$slaGeneral =
    ($slaGeneralRow['total'] > 0)
    ? round(($slaGeneralRow['cumplidos'] / $slaGeneralRow['total']) * 100, 1)
    : 0;

$noCumplieron =
    $slaGeneralRow['total'] - $slaGeneralRow['cumplidos'];

/* SLA ALTA */
$sql = "
SELECT

COUNT(*) FILTER (
WHERE fecha_resolucion <= fecha_reporte + INTERVAL '4 hours'
) AS cumplidos,

COUNT(*) AS total

FROM itil_incidentes

WHERE prioridad='Alta'
AND fecha_resolucion IS NOT NULL
AND fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$slaAlta =
    ($row['total'] > 0)
    ? round(($row['cumplidos'] / $row['total']) * 100, 1)
    : 0;

/* SLA MEDIA */
$sql = "
SELECT

COUNT(*) FILTER (
WHERE fecha_resolucion <= fecha_reporte + INTERVAL '8 hours'
) AS cumplidos,

COUNT(*) AS total

FROM itil_incidentes

WHERE prioridad='Media'
AND fecha_resolucion IS NOT NULL
AND fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$slaMedia =
    ($row['total'] > 0)
    ? round(($row['cumplidos'] / $row['total']) * 100, 1)
    : 0;

/* SLA BAJA */
$sql = "
SELECT

COUNT(*) FILTER (
WHERE fecha_resolucion <= fecha_reporte + INTERVAL '24 hours'
) AS cumplidos,

COUNT(*) AS total

FROM itil_incidentes

WHERE prioridad='Baja'
AND fecha_resolucion IS NOT NULL
AND fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$slaBaja =
    ($row['total'] > 0)
    ? round(($row['cumplidos'] / $row['total']) * 100, 1)
    : 0;

/* TOP 10 FALLAS */
$sql = "
SELECT
titulo,
COUNT(*) AS total

FROM itil_incidentes

WHERE fecha_reporte BETWEEN :inicio AND :fin

GROUP BY titulo

ORDER BY total DESC

LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$topFallas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estadísticas Mensuales</title>

    <style>
        body {
            font-family: Segoe UI, Arial;
            background: #f3f5f7;
            margin: 0;
            padding: 20px;
        }

        h1 {
            margin-bottom: 5px;
        }

        .subtitulo {
            color: #666;
            margin-bottom: 25px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
        }

        .valor {
            font-size: 32px;
            font-weight: bold;
            color: #00AEEF;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: #00AEEF;
            color: white;
            text-align: left;
            padding: 10px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>

</head>

<body>

    <h1>Reporte Mensual</h1>

    <div class="subtitulo">
        Mes analizado:
        <?= date("F Y", strtotime($inicioMesAnterior)); ?>
    </div>

    <div class="grid">

        <div class="card">
            <h3>Total Incidentes</h3>
            <div class="valor"><?= $totalIncidentes ?></div>
        </div>

        <div class="card">
            <h3>SLA General</h3>
            <div class="valor"><?= $slaGeneral ?>%</div>
        </div>

        <div class="card">
            <h3>SLA Alta (4h)</h3>
            <div class="valor"><?= $slaAlta ?>%</div>
        </div>

        <div class="card">
            <h3>SLA Media (8h)</h3>
            <div class="valor"><?= $slaMedia ?>%</div>
        </div>

        <div class="card">
            <h3>SLA Baja (24h)</h3>
            <div class="valor"><?= $slaBaja ?>%</div>
        </div>

        <div class="card">
            <h3>No Cumplieron SLA</h3>
            <div class="valor"><?= $noCumplieron ?></div>
        </div>

    </div>

    <br><br>

    <div class="card">

        <h3>Top 10 Fallas</h3>

        <table>

            <tr>
                <th>Falla</th>
                <th>Cantidad</th>
            </tr>

            <?php foreach ($topFallas as $falla): ?>

                <tr>
                    <td><?= htmlspecialchars($falla['titulo']) ?></td>
                    <td><?= $falla['total'] ?></td>
                </tr>

            <?php endforeach; ?>

        </table>

    </div>

</body>

</html>