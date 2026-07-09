<?php

require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

if (!isset($_GET['id'])) {

    header("Location: respaldos_usuarios.php");
    exit;

}

$idrespaldo = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT
        r.*,
        d.nombre AS disco,
        u.nombre AS ingeniero

    FROM respaldos_usuarios r

    INNER JOIN discos_respaldo d
        ON d.iddisco = r.iddisco

    INNER JOIN usuarios u
        ON u.id = r.creado_por

    WHERE r.idrespaldo = ?
");

$stmt->execute([$idrespaldo]);

$respaldo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$respaldo) {

    header("Location: respaldos_usuarios.php");
    exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Detalle del Respaldo</title>

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
    --border: rgba(255,255,255,.15);
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

.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    max-width: 700px;
    margin: auto;
    border: 1px solid var(--border);
}

h2 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    opacity: .7;
    margin-bottom: 30px;
}

.label {
    font-weight: 600;
    margin-top: 15px;
}

.valor {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-top: 5px;
}

.btn-volver {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 18px;
    background: #00AEEF;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Detalle del Respaldo</h2>

<div class="subtitle">
Información completa del respaldo
</div>

<div class="form-card">

    <div class="label">Usuario</div>
    <div class="valor">
        <?= htmlspecialchars($respaldo['usuario']) ?>
    </div>

    <div class="label">Disco</div>
    <div class="valor">
        <?= htmlspecialchars($respaldo['disco']) ?>
    </div>

    <div class="label">Tamaño del Respaldo</div>
    <div class="valor">
        <?= number_format($respaldo['tamano_gb'], 2) ?> GB
    </div>

    <div class="label">Observaciones</div>
    <div class="valor">
        <?= nl2br(htmlspecialchars($respaldo['observaciones'] ?? '—')) ?>
    </div>

    <div class="label">Fecha de Registro</div>
    <div class="valor">
        <?= date('Y-m-d H:i:s', strtotime($respaldo['fecha_respaldo'])) ?>
    </div>

    <div class="label">Ingeniero</div>
    <div class="valor">
        <?= htmlspecialchars($respaldo['ingeniero']) ?>
    </div>

    <a href="respaldos_usuarios.php" class="btn-volver">
        ← Volver
    </a>

</div>

</div>

<script src="theme.js"></script>

</body>

</html>