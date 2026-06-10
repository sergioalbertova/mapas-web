<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
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
<title>Nuevo usuario</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>
:root {
    --bg: #F4F7FA;
    --text: #1F2933;

    --topbar-bg: rgba(255,255,255,0.85);
    --topbar-text: #1F2933;
    --topbar-border: rgba(0,0,0,0.1);

    --sidebar-bg: #FFFFFF;
    --sidebar-text: #1F2933;
    --sidebar-border: rgba(0,0,0,0.1);

    --card-bg: #FFFFFF;
    --card-text: #1F2933;

    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;

    --topbar-bg: rgba(17,24,39,0.85);
    --topbar-text: #E5E7EB;
    --topbar-border: rgba(255,255,255,0.1);

    --sidebar-bg: #020617;
    --sidebar-text: #E5E7EB;
    --sidebar-border: rgba(255,255,255,0.1);

    --card-bg: #1f2937;
    --card-text: #E5E7EB;

    --shadow: rgba(0,0,0,0.45);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: background 0.3s ease, color 0.3s ease;
}

.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
    transition: margin-left 0.25s ease;
}

.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

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

    <div class="titulo">Nuevo usuario</div>
    <div class="subtitulo">Registrar un nuevo usuario en ActiveUser</div>

<form action="activeuser_nuevo_guardar.php" method="POST" class="form-card">

    <label>Nombre</label>
    <input type="text" name="nomuser" required>

    <label>Ubicación</label>
    <input type="text" name="ubicacion">

    <label>HOR</label>
    <input type="text" name="hor">

    <label>Monitor</label>
    <input type="text" name="monitor">

    <label>Piso</label>
    <input type="text" name="piso">

    <label>Ubicación en mapa 2</label>
    <input type="number" name="ubimapa2">

    <button class="btn-guardar">Guardar usuario</button>
    <a href="activeuser_admin.php" class="btn-regresar">Regresar</a>

</form>


</div>

</div>

<script src="theme.js"></script>

</body>
</html>
