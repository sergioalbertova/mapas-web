<?php
require "db.php";

// TOTAL
$total = $pdo->query("SELECT COUNT(*) FROM checklist_revision")->fetchColumn();

// AGRUPADO POR PISO
$pisoData = $pdo->query("
    SELECT piso, COUNT(*) AS total
    FROM checklist_revision
    GROUP BY piso
    ORDER BY piso
")->fetchAll(PDO::FETCH_ASSOC);

// ÚLTIMOS REGISTROS
$rows = $pdo->query("
    SELECT id, usuario_nombre, piso, fecha, notas, fondo, correo, teams
    FROM checklist_revision
    ORDER BY fecha DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Avance por Piso</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: #0d1117;
    color: #e5e7eb;
    padding: 20px;
}
.container {
    max-width: 1000px;
    margin: auto;
}
.card {
    background: #161b22;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.4);
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 8px;
    border-bottom: 1px solid #30363d;
}
.badge {
    padding: 4px 10px;
    border-radius: 999px;
    background: #00AEEF;
    color: white;
}
</style>
</head>

<body>
<div class="container">

    <!-- TOTAL -->
    <div class="card">
        <h2>Avance del Checklist por Piso</h2>
        <p>Total de revisiones: <span class="badge"><?= $total ?></span></p>
    </div>

    <!-- GRÁFICA -->
    <div class="card">
        <h3>Gráfica de revisiones por piso</h3>
        <canvas id="graficaPisos" height="120"></canvas>
    </div>

    <!-- TABLA POR PISO -->
    <div class="card">
        <h3>Revisiones por piso</h3>
        <table>
            <tr><th>Piso</th><th>Total</th></tr>
            <?php foreach ($pisoData as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['piso']) ?></td>
                <td><?= $p['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- ÚLTIMOS REGISTROS -->
    <div class="card">
        <h3>Últimas revisiones</h3>
        <table>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Piso</th>
                <th>Fondo</th>
                <th>Correo</th>
                <th>Teams</th>
                <th>Notas</th>
            </tr>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r['fecha'] ?></td>
                <td><?= htmlspecialchars($r['usuario_nombre']) ?></td>
                <td><?= htmlspecialchars($r['piso']) ?></td>
                <td><?= $r['fondo'] ? "✔" : "✘" ?></td>
                <td><?= $r['correo'] ? "✔" : "✘" ?></td>
                <td><?= $r['teams'] ? "✔" : "✘" ?></td>
                <td><?= nl2br(htmlspecialchars($r['notas'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>

<script>
// DATOS PARA LA GRÁFICA
const labels = <?= json_encode(array_column($pisoData, 'piso')) ?>;
const valores = <?= json_encode(array_column($pisoData, 'total')) ?>;

// SI NO HAY DATOS, EVITAMOS ERROR
if (labels.length === 0) {
    document.getElementById("graficaPisos").outerHTML =
        "<p style='color:#aaa;'>No hay datos suficientes para mostrar la gráfica.</p>";
} else {
    new Chart(document.getElementById('graficaPisos'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revisiones por piso',
                data: valores,
                backgroundColor: '#00AEEF',
                borderColor: '#0088C0',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            plugins: { legend: { labels: { color: '#e5e7eb' } } },
            scales: {
                x: { ticks: { color: '#e5e7eb' } },
                y: { ticks: { color: '#e5e7eb' } }
            }
        }
    });
}
</script>

</body>
</html>
