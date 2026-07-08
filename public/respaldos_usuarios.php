<?php

require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

$stmt = $pdo->query("
    SELECT
        r.*,
        d.nombre AS disco,
        u.nombre AS ingeniero

    FROM respaldos_usuarios r

    INNER JOIN discos_respaldo d
        ON d.iddisco = r.iddisco

    INNER JOIN usuarios u
        ON u.id = r.creado_por

    ORDER BY r.fecha_respaldo DESC
");

$respaldos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Gestión de Respaldos</title>

<link rel="stylesheet" href="sidebar.css">
.css">

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
    font-family: Segoe UI, Arial;
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
    color: #fff;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 12px;
    border-bottom: 1px solid var(--border);
    text-align: left;
}

th {
    font-weight: 600;
}

.badge {
    background: #10b981;
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
}

.acciones a {
    text-decoration: none;
    margin-right: 10px;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Gestión de Respaldos</h2>

<div class="subtitle">
Respaldos registrados en medios físicos
</div>

<div class="card">

    <a href="respaldos_usuarios_nuevo.php"
       class="btn-nuevo">
        + Nuevo Respaldo
    </a>

    <table>

        <thead>

            <tr>
                <th>Usuario</th>
                <th>Disco</th>
                <th>Tamaño (GB)</th>
                <th>Fecha</th>
                <th>Ingeniero</th>
            </tr>

        </thead>

        <tbody>

        <?php foreach ($respaldos as $r): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($r['usuario']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($r['disco']) ?>
                </td>

                <td>
                    <?= number_format($r['tamano_gb'], 2) ?>
                </td>

                <td>
                    <?= date(
                        'Y-m-d H:i',
                        strtotime($r['fecha_respaldo'])
                    ) ?>
                </td>

                <td>
                    <?= htmlspecialchars($r['ingeniero']) ?>
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