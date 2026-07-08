<?php
require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Nuevo Disco de Respaldo</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

/* VARIABLES */

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

.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    max-width: 650px;
    margin: auto;
    border: 1px solid var(--border);
}

h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    opacity: .7;
    margin-bottom: 30px;
}

label {
    font-weight: 600;
    margin-top: 15px;
    display: block;
}

input,
textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-top: 5px;
    background: var(--card-bg);
    color: var(--text);
    box-sizing: border-box;
}

textarea {
    min-height: 120px;
}

.btn {
    margin-top: 20px;
    padding: 12px;
    background: #00AEEF;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Nuevo Disco de Respaldo</h2>

<div class="subtitle">
Registrar un nuevo medio de almacenamiento
</div>

<div class="form-card">

ar.php" method="POST">

    <label>Nombre del Disco</label>
    <input
        type="text"
        name="nombre"
        required
        placeholder="Ej. Disco 1">

    <label>Tamaño Total (GB)</label>
    <input
        type="number"
        step="0.01"
        min="0"
        name="tamano_total_gb"
        required
        placeholder="Ej. 500">

    <label>Observaciones</label>
    <textarea
        name="observaciones"
        placeholder="Notas adicionales"></textarea>

    <button class="btn">
        Guardar Disco
    </button>

</form>

</div>

</div>

<script src="theme.js"></script>

</body>
</html>