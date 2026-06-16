<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol'])) {
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

// Obtener XM/YM desde tabla ubicacion
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
    --accent: #00AEEF;
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
    overflow: hidden;
    transform-origin: top left;
    cursor: grab;
}

.mapa-container:active {
    cursor: grabbing;
}

.mapa {
    width: 100%;
    border-radius: 10px;
    transition: transform 0.2s ease-out;
}

/* MARCADOR SVG PREMIUM */
.marcador {
    position: absolute;
    width: 48px;
    height: 48px;
    transform: translate(-50%, -100%);
    pointer-events: none;
    opacity: 0; /* ← FIX FIREFOX */
    z-index: 9999;
}

.marcador.visible {
    opacity: 1;
}

.pin {
    filter: drop-shadow(0 0 10px rgba(0,0,0,0.7))
            drop-shadow(0 0 6px rgba(0,174,239,0.9));
    animation: pulse 1.2s infinite ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.25); }
    100% { transform: scale(1); }
}

/* EFECTO RADAR */
.radar {
    position: absolute;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: rgba(0,174,239,0.25);
    transform: translate(-50%, -50%);
    animation: radarPulse 2s infinite ease-out;
    pointer-events: none;
    opacity: 0;
}

.radar.visible {
    opacity: 1;
}

@keyframes radarPulse {
    0% { transform: translate(-50%, -50%) scale(0.2); opacity: 0.8; }
    100% { transform: translate(-50%, -50%) scale(1.8); opacity: 0; }
}

/* TOOLTIP */
.tooltip {
    position: absolute;
    background: black;
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 14px;
    white-space: nowrap;
    transform: translate(-50%, -140%);
    display: none;
    pointer-events: none;
    z-index: 99999;
}
</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<div class="contenedor">

    <div class="titulo">Editar usuario</div>

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

        <?php if ($_SESSION['rol'] === 'administrador'): ?>
            <label>XM</label>
            <input type="text" id="xm" value="<?= safe($xm) ?>">

            <label>YM</label>
            <input type="text" id="ym" value="<?= safe($ym) ?>">
        <?php else: ?>
            <input type="hidden" id="xm" value="<?= safe($xm) ?>">
            <input type="hidden" id="ym" value="<?= safe($ym) ?>">
        <?php endif; ?>

        <button class="btn-guardar">Guardar cambios</button>
        <a href="activeuser_admin.php" class="btn-regresar">Regresar</a>

    </form>

    <!-- MAPA -->
    <div class="mapa-wrapper">
        <h3>Ubicación en el mapa</h3>

        <div class="mapa-container" id="mapaContainer">
            <img id="mapa" src="piso<?= safe($user['piso']) ?>.jpg" class="mapa">

            <!-- MARCADOR -->
            <div id="marcador" class="marcador">
                <svg viewBox="0 0 24 24" width="48" height="48" class="pin">
                    <path fill="#00AEEF" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                </svg>
            </div>

            <!-- RADAR -->
            <div id="radar" class="radar"></div>

            <!-- TOOLTIP -->
            <div id="tooltip" class="tooltip"></div>
        </div>

        <?php if ($_SESSION['rol'] === 'administrador'): ?>
        <button onclick="guardarXY()" class="btn-guardar" style="margin-top:20px;">
            Guardar XM/YM en tabla ubicacion
        </button>
        <?php endif; ?>
    </div>

</div>

</div>

<script>
let xm = <?= $xm ? $xm : "null" ?>;
let ym = <?= $ym ? $ym : "null" ?>;

const mapa = document.getElementById("mapa");
const marcador = document.getElementById("marcador");
const radar = document.getElementById("radar");
const tooltip = document.getElementById("tooltip");
const container = document.getElementById("mapaContainer");

let zoom = 1;
let isDragging = false;
let startX, startY, offsetX = 0, offsetY = 0;

// Mostrar marcador inicial
mapa.onload = () => {
    if (xm !== null && ym !== null) {
        const x = xm * mapa.offsetWidth;
        const y = ym * mapa.offsetHeight;

        marcador.style.left = x + "px";
        marcador.style.top = y + "px";
        radar.style.left = x + "px";
        radar.style.top = y + "px";

        marcador.classList.add("visible");
        radar.classList.add("visible");
    }
};

// Capturar clic para nuevas coordenadas
mapa.addEventListener("click", function(e) {
    <?php if ($_SESSION['rol'] !== 'administrador'): ?>
        return;
    <?php endif; ?>

    const rect = mapa.getBoundingClientRect();

    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    xm = x / mapa.offsetWidth;
    ym = y / mapa.offsetHeight;

    document.getElementById("xm").value = xm.toFixed(6);
    document.getElementById("ym").value = ym.toFixed(6);

    marcador.style.left = (xm * mapa.offsetWidth) + "px";
    marcador.style.top = (ym * mapa.offsetHeight) + "px";

    radar.style.left = marcador.style.left;
    radar.style.top = marcador.style.top;

    marcador.classList.add("visible");
    radar.classList.add("visible");
});

// Tooltip
marcador.addEventListener("mouseenter", () => {
    tooltip.innerHTML = `
        <b><?= safe($user['nomuser']) ?></b><br>
        Piso: <?= safe($user['piso']) ?><br>
        Ubicación: <?= safe($user['ubimapa2']) ?>
    `;
    tooltip.style.left = marcador.style.left;
    tooltip.style.top = marcador.style.top;
    tooltip.style.display = "block";
});

marcador.addEventListener("mouseleave", () => {
    tooltip.style.display = "none";
});

// Zoom con rueda
container.addEventListener("wheel", function(e) {
    e.preventDefault();

    zoom += e.deltaY * -0.001;
    zoom = Math.min(Math.max(zoom, 1), 3);

    container.style.transform = `scale(${zoom}) translate(${offsetX}px, ${offsetY}px)`;
});

// Pan (arrastrar)
container.addEventListener("mousedown", e => {
    isDragging = true;
    startX = e.clientX - offsetX;
    startY = e.clientY - offsetY;
});

container.addEventListener("mousemove", e => {
    if (!isDragging) return;

    offsetX = e.clientX - startX;
    offsetY = e.clientY - startY;

    container.style.transform = `scale(${zoom}) translate(${offsetX}px, ${offsetY}px)`;
});

container.addEventListener("mouseup", () => isDragging = false);
container.addEventListener("mouseleave", () => isDragging = false);

// Guardar XM/YM
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
