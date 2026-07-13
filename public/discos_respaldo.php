<?php

require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

$stmt = $pdo->query("
    SELECT
        d.iddisco,
        d.nombre,
        d.tamano_total_gb,
        d.observaciones,

        COALESCE(
            SUM(r.tamano_gb),
            0
        ) AS utilizado

    FROM discos_respaldo d

    LEFT JOIN respaldos_usuarios r
        ON r.iddisco = d.iddisco

    GROUP BY
        d.iddisco,
        d.nombre,
        d.tamano_total_gb,
        d.observaciones

    ORDER BY d.nombre
");

$discos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Discos de Respaldo</title>
<link rel="icon" href="apoyo2.png" type="image/x-icon">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --border: #ddd;
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --border: rgba(255,255,255,0.15);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
}

h2 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    opacity: .7;
    margin-bottom: 25px;
}

.btn-nuevo {
    display: inline-block;
    background: #00AEEF;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid var(--border);
    text-align: left;
}

th {
    font-weight: 600;
}

th:last-child,
td:last-child {
    text-align: center;
}

.disponible-ok {
    color: #10b981;
    font-weight: 600;
}

.disponible-alerta {
    color: #f59e0b;
    font-weight: 600;
}

.disponible-critico {
    color: #ef4444;
    font-weight: 600;
}

.barra-disco {
    width: 220px;
    height: 22px;

    display: flex;

    border-radius: 30px;
    overflow: hidden;

    border: 1px solid rgba(0,0,0,.15);

    background: #d1d5db;

    box-shadow:
        inset 0 2px 4px rgba(0,0,0,.20),
        0 1px 2px rgba(255,255,255,.25);
}


.barra-libre {
    background:
        linear-gradient(
            to bottom,
            #34d399,
            #059669
        );
}



.barra-ocupado {
    background:
        linear-gradient(
            to bottom,
            #f87171,
            #dc2626
        );
}


.barra-libre,
.barra-ocupado {
    transition: width .4s ease;
}
.porcentaje {
    font-size: 12px;
    font-weight: 600;
    margin-top: 4px;
}

.barra-usado {
    height: 100%;
    background: #10b981;

    transition:
        width .8s ease,
        background-color .3s ease;
}

.barra-usado {
    height: 100%;

    transition:
        width 1s ease-in-out,
        background-color .3s ease;
}

.uso-verde {
    background: #10b981;
}

.uso-amarillo {
    background: #f59e0b;
}

.uso-rojo {
    background: #ef4444;
}

.porcentaje {
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Discos de Respaldo</h2>

<div class="subtitle">
Administración de medios de respaldo
</div>

<div class="card">

   
<a href="discos_respaldo_nuevo.php" class="btn-nuevo">+ Nuevo Disco</a>


    <table>

        <thead>
            <tr>
                 <th>Nombre</th>
                <th>Total (GB)</th>
                <th>Utilizado (GB)</th>
                <th>Disponible (GB)</th>
                <th>Observaciones</th>
                <th>Uso</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($discos as $d): ?>

            <?php
                $utilizado = (float)$d['utilizado'];
                $disponible = (float)$d['tamano_total_gb'] - $utilizado;


                $porcentajeLibre = 0;
                $porcentajeOcupado = 0;

                if ($d['tamano_total_gb'] > 0) {

                    $porcentajeOcupado =
                    ($utilizado * 100)
                     / $d['tamano_total_gb'];

                    $porcentajeLibre =
                    100 - $porcentajeOcupado;
                }

                $porcentajeUso = 0;

                if ($d['tamano_total_gb'] > 0) {

                $porcentajeUso =
                 ($utilizado * 100)
                / $d['tamano_total_gb'];

                }

                $claseUso = 'uso-verde';

                if ($porcentajeUso >= 90) {

                 $claseUso = 'uso-rojo';

                } elseif ($porcentajeUso >= 70) {

                $claseUso = 'uso-amarillo';

                }


            ?>

<tr>

    <td>
        <?= htmlspecialchars($d['nombre']) ?>
    </td>

    <td>
        <?= number_format($d['tamano_total_gb'], 2) ?>
    </td>

    <td>
        <?= number_format($utilizado, 2) ?>
    </td>

    <td>

<?php

if ($disponible >= 100) {

    echo '<span class="disponible-ok">'
        . number_format($disponible, 2)
        . '</span>';

} elseif ($disponible >= 50) {

    echo '<span class="disponible-alerta">'
        . number_format($disponible, 2)
        . '</span>';

} else {

    echo '<span class="disponible-critico">'
        . number_format($disponible, 2)
        . '</span>';

}

?>

</td>


    <td>
        <?= htmlspecialchars($d['observaciones'] ?? '') ?>
    </td>

<td>

    <div class="barra-disco">

        <div
            class="barra-libre"
            style="width: <?= $porcentajeLibre ?>%;">
        </div>

        <div
            class="barra-ocupado"
            style="width: <?= $porcentajeOcupado ?>%;">
        </div>

    </div>

    <div class="porcentaje">

        Libre:
        <?= number_format($porcentajeLibre,1) ?>%

        |

        Ocupado:
        <?= number_format($porcentajeOcupado,1) ?>%

    </div>

</td>

</tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

</div>

<script src="theme.js"></script>

</body>
</html>