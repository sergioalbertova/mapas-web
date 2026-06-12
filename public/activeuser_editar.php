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

// Obtener XM/YM desde la tabla ubicacion
$ubimapa2 = $user['ubimapa2'];

$stmt2 = $pdo->prepare("SELECT xm, ym FROM ubicacion WHERE idubicacion = ?");
$stmt2->execute([$ubimapa2]);
$coords = $stmt2->fetch(PDO::FETCH_ASSOC);

$xm = $coords['xm'] ?? null;
$ym = $coords['ym'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar usuario</title>

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
}

.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

.contenedor {
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.titulo {
    font-size: 26px;
    font-weight: 600;
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
    width: 100%;
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
    margin-bottom: 12px;
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

/* MAPA */
.mapa-wrapper {
    margin-top: 40px;
    width: 100%;
    max-width: 900px;
}

.mapa-container {
    position: relative;
    width: 100%;
}

.mapa {
    width: 100%;
    border-radius: 10px;
    border: 2px solid var(--sidebar-border);
}

.marcador {
    position: absolute;
    width: 22px;
    height: 22px;
    background: red;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 12px rgba(255,0,0,0.8);
    transform: translate(-50%, -50%);
    pointer-events: none;
    display: none;
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
        <input type="text" name="hor" value="<?= safe($user['hor1']) ?>">

        <label>Monitor</label>
        <input type="text" name="monitor" value="<?= safe($user['hor2']) ?>">

        <label>Piso</label>
        <input type="text" name="piso" value="<?= safe($user['piso']) ?>">

        <label>Ubicación en mapa 2</label>
        <input type="number" name="ubimapa2" value="<?= safe($user['ubimapa2']) ?>">

        <label>XM (desde tabla ubicacion)</label>
        <input type="text" id="xm" value="<?= safe($xm) ?>">

        <label>YM (desde tabla ubicacion)</label>
        <input type="text" id="ym" value="<?= safe($ym) ?>">

        <button class="btn-guardar">Guardar cambios</button>
        <a href="activeuser_admin.php" class="btn-regresar">Regresar</a>

    </form>

    <!-- MAPA -->
    <div class="mapa-wrapper">
        <h3>Ubicación en el mapa</h3>

        <div class="mapa-container">
            <img id="mapa" src="piso<?= safe($user['piso']) ?>.jpg" class="mapa">
            <div id="marcador" class="marcador"></div>
        </div>

        <button onclick="guardarXY()" class="btn-guardar" style="margin-top:20px;">
            Guardar XM/YM en tabla ubicacion
        </button>
    </div>

</div>

</div>

<script src="theme.js"></script>

<script>
let xm = <?= $xm ? $xm : "null" ?>;
let ym = <?= $ym ? $ym : "null" ?>;

const mapa = document.getElementById("mapa");
const marcador = document.getElementById("marcador");

mapa.onload = () => {
    if (xm !== null && ym !== null) {
        const x = xm * mapa.offsetWidth;
        const y = ym * mapa.offsetHeight;

        marcador.style.left = x + "px";
        marcador.style.top = y + "px";
        marcador.style.display = "block";
    }
};

// CAPTURAR NUEVAS COORDENADAS
mapa.addEventListener("click", function(e) {
    const rect = mapa.getBoundingClientRect();

    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    xm = x / mapa.offsetWidth;
    ym = y / mapa.offsetHeight;

    document.getElementById("xm").value = xm.toFixed(6);
    document.getElementById("ym").value = ym.toFixed(6);

    marcador.style.left = (xm * mapa.offsetWidth) + "px";
    marcador.style.top = (ym * mapa.offsetHeight) + "px";
    marcador.style.display = "block";
});

// GUARDAR XM/YM EN TABLA UBICACION
function guardarXY() {
    const idubicacion = <?= $ubimapa2 ?>;

    fetch("guardar_xy.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `idubicacion=${idubicacion}&xm=${xm}&ym=${ym}`
    })
    .then(r => r.text())
    .then(t => alert(t));
}
</script>

</body>
</html>
