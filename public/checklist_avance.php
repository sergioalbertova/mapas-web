<?php
require "db.php";

// total registros
$total = $pdo->query("SELECT COUNT(*) FROM checklist_revision")->fetchColumn();

// top usuarios
$top = $pdo->query("
    SELECT nomuser, COUNT(*) AS total
    FROM checklist_revision
    GROUP BY nomuser
    ORDER BY total DESC, nomuser
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// últimos registros
$rows = $pdo->query("
    SELECT id, nomuser, piso, fecha, notas
    FROM checklist_revision
    ORDER BY fecha DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Avance checklist</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: #0d1117;
    color: #e5e7eb;
    padding: 20px;
}
.container {
    max-width: 900px;
    margin: auto;
}
.card {
    background: #161b22;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.4);
}
h2, h3 { margin-top: 0; }
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}
th, td {
    padding: 8px;
    border-bottom: 1px solid #30363d;
}
th {
    text-align: left;
    color: #9ca3af;
}
.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    background: #00AEEF;
    color: white;
    font-size: 13px;
}
</style>
</head>
<body>
<div class="container">

    <div class="card">
        <h2>Avance de checklist</h2>
        <p>Total de revisiones registradas: <span class="badge"><?= (int)$total ?></span></p>
    </div>

    <div class="card">
        <h3>Top usuarios revisados</h3>
        <table>
            <tr>
                <th>Usuario</th>
                <th>Total revisiones</th>
            </tr>
            <?php foreach ($top as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['nomuser']) ?></td>
                <td><?= (int)$t['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h3>Últimas revisiones</h3>
        <table>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Piso</th>
                <th>Notas</th>
            </tr>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['fecha']) ?></td>
                <td><?= htmlspecialchars($r['nomuser']) ?></td>
                <td><?= htmlspecialchars($r['piso']) ?></td>
                <td><?= nl2br(htmlspecialchars($r['notas'] ?? '')) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div>
</body>
</html>
