<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

$idu = $_GET['idu'] ?? null;
if (!$idu) {
    header("Location: activeuser_admin.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM activeuser WHERE idu = ?");
$stmt->execute([$idu]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Usuario no encontrado";
    exit;
}

function safe($v) {
    return htmlspecialchars($v ?? "", ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar usuario</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>
.contenedor {
    padding: 20px;
}

.titulo {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 5px;
}

.subtitulo {
    opacity: 0.7;
    margin-bottom: 25px;
}

.form-card {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 10px 25px var(--shadow);
    max-width: 600px;
}

label {
    display: block;
    margin-top: 12px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid var(--sidebar-border);
    background: var(--bg);
    color: var(--text);
    margin-top: 5px;
}

.btn-guardar {
    margin-top: 20px;
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.btn-regresar {
    margin-top: 20px;
    padding: 12px 18px;
    background: #6b7280;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    display: inline-block;
}
</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<div class="contenedor">

    <div class="titulo">Editar usuario</div>
    <div class="subtitulo">Modifica los datos del usuario seleccionado</div>

    <form action="activeuser_editar_guardar.php" method="POST" class="form-card">

        <input type="hidden" name="idu" value="<?= safe($user['idu']) ?>">

        <label>Nombre</label>
        <input type="text" name="nomuser" value="<?= safe($user['nomuser']) ?>">

        <label>Ubicación</label>
        <input type="text" name="ubicacion" value="<?= safe($user['ubicacion']) ?>">

        <label>HOR</label>
        <input type="text" name="hor" value="<?= safe($user['hor']) ?>">

        <label>Piso</label>
        <input type="text" name="piso" value="<?= safe($user['piso']) ?>">

        <label>Ubicación en mapa</label>
        <input type="number" name="ubimapa2" value="<?= safe($user['ubimapa2']) ?>">

        <button class="btn-guardar">Guardar cambios</button>
        <a href="activeuser_admin.php" class="btn-regresar">Regresar</a>

    </form>

</div>

</div>

<script src="theme.js"></script>

</body>
</html>
