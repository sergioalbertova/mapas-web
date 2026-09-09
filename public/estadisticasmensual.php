<?php
require "auth.php";
require "db.php";

/* MES ANTERIOR */
$inicio = date('Y-m-01 00:00:00', strtotime('first day of last month'));
$fin    = date('Y-m-t 23:59:59', strtotime('last month'));

/* TOTAL INCIDENTES */
$sql = "
SELECT COUNT(*)
FROM itil_incidentes
WHERE fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':inicio' => $inicio,
    ':fin' => $fin
]);

$totalIncidentes = $stmt->fetchColumn();

/* SLA GENERAL */

$sql = "
SELECT

COUNT(*) FILTER (

WHERE fecha_resolucion IS NOT NULL
AND (

(prioridad='Alta'
AND fecha_resolucion <= fecha_reporte + INTERVAL '4 hours')

OR

(prioridad='Media'
AND fecha_resolucion <= fecha_reporte + INTERVAL '8 hours')

OR

(prioridad='Baja'
AND fecha_resolucion <= fecha_reporte + INTERVAL '24 hours')

)

) cumplidos,

COUNT(*) FILTER (
WHERE fecha_resolucion IS NOT NULL
) total

FROM itil_incidentes

WHERE fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':inicio' => $inicio,
    ':fin' => $fin
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$slaGeneral = ($row['total'] > 0)
    ? round(($row['cumplidos'] / $row['total']) * 100, 1)
    : 0;

$noCumplieron = $row['total'] - $row['cumplidos'];

/* SLA ALTA */

$sql = "
SELECT

COUNT(*) FILTER (
WHERE fecha_resolucion <= fecha_reporte + INTERVAL '4 hours'
) cumplidos,

COUNT(*) total

FROM itil_incidentes

WHERE prioridad='Alta'
AND fecha_resolucion IS NOT NULL
AND fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':inicio' => $inicio,
    ':fin' => $fin
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$slaAlta = ($row['total'] > 0)
    ? round(($row['cumplidos'] / $row['total']) * 100, 1)
    : 0;

/* SLA MEDIA */

$sql = "
SELECT

COUNT(*) FILTER (
WHERE fecha_resolucion <= fecha_reporte + INTERVAL '8 hours'
) cumplidos,

COUNT(*) total

FROM itil_incidentes

WHERE prioridad='Media'
AND fecha_resolucion IS NOT NULL
AND fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':inicio' => $inicio,
    ':fin' => $fin
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$slaMedia = ($row['total'] > 0)
    ? round(($row['cumplidos'] / $row['total']) * 100, 1)
    : 0;

/* SLA BAJA */

$sql = "
SELECT

COUNT(*) FILTER (
WHERE fecha_resolucion <= fecha_reporte + INTERVAL '24 hours'
) cumplidos,

COUNT(*) total

FROM itil_incidentes

WHERE prioridad='Baja'
AND fecha_resolucion IS NOT NULL
AND fecha_reporte BETWEEN :inicio AND :fin
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':inicio' => $inicio,
    ':fin' => $fin
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$slaBaja = ($row['total'] > 0)
    ? round(($row['cumplidos'] / $row['total']) * 100, 1)
    : 0;

/* TOP 10 FALLAS */

$sql = "
SELECT
titulo,
COUNT(*) total

FROM itil_incidentes

WHERE fecha_reporte BETWEEN :inicio AND :fin

GROUP BY titulo

ORDER BY total DESC

LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':inicio' => $inicio,
    ':fin' => $fin
]);

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
            background: #f5f6fa;
            padding: 20px;
        }

        .kpis {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
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

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background: #00AEEF;
            color: white;
        }
    </style>

</head>

<body>

    <h2>
        Estadísticas Mensuales
    </h2>

    <p>
        Mes analizado:
        <strong><?= date('F Y', strtotime('last month')); ?></strong>
    </p>

    <div class="kpis">

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

    <h3>Top 10 Fallas</h3>

    <table>

        <tr>
            <th>Falla</th>
            <th>Cantidad</th>
        </tr>

        <?php foreach ($topFallas as $f): ?>

            <tr>
                <td><?= htmlspecialchars($f['titulo']) ?></td>
                <td><?= $f['total'] ?></td>
            </tr>

        <?php endforeach; ?>

    </table>

</body>

</html>