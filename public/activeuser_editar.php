<?php
require "session_config.php";
require "db.php";

$id = $_SESSION['user_id'];

// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";

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
<title>Editar ActiveUser</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>
:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --card-text: #1F2933;
    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --card-text: #E5E7EB;
    --shadow: rgba(0,0,0,0.45);
}

/* BASE TIHIL */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* MAIN */
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

/* CENTRAR TODO */
.contenedor {
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.titulo {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 15px;
}

/* FORM CARD */
.form-card {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 10px 25px var(--shadow);
    max-width: 600px;
    width: 100%;
    margin-bottom: 30px;
}

.form-card label {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

.form-card input {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-bottom: 12px;
    font-size: 14px;
}

/* BOTONES */
.btn-guardar,
.btn-regresar {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    transition: 0.2s ease;
    font-size: 14px;
}

.btn-guardar {
    background: var(--accent);
    color: white;
    border: none;
}

.btn-regresar {
    background: #6b7280;
    color: white;
    text-decoration: none;
    margin-left: 8px;
}

/* MAPA */
.mapa-wrapper {
    margin-top: 10px;
    width: 100%;
    max-width: 900px;
}

.mapa-wrapper h3 {
    margin-bottom: 10px;
}

.mapa-container {
    position: relative;
    width: 100%;
    max-height: 600px;
    overflow: auto;
    border-radius: 12px;
    box-shadow: 0 10px 25px var(--shadow);
    background: #fff;
}

/* Contenedor interno que se escala completo */
.mapa-inner {
    position: relative;
    display: inline-block;
    transform-origin: center center;
}

.mapa {
    display: block;
    width: 100%;
}

/* MARCADOR */
.marcador {
    position: absolute;
    width: 48px;
    height: 48px;
    transform: translate(-50%, -100%);
    opacity: 0;
    z-index: 50;
    pointer-events: auto;
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

/* RADAR */
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
    z-index: 40;
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
    font-size: 13px;
    white-space: nowrap;
    transform: translate(-50%, -140%);
    display: none;
    pointer-events: none;
    z-index: 999;
}

/* BOTÓN CENTRAR */
.btn-center {
    margin-top: 15px;
    padding: 10px 16px;
    background: #2563eb;
    color: white;
    border-radius: 10px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    font-size: 14px;
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

            <label style="margin-top:10px; display:flex; align-items:center; gap:8px;">
                <input type="checkbox" id="permitirMover" checked>
                Permitir reasignar ubicación
            </label>
        <?php else: ?>
            <input type="hidden" id="xm" value="<?= safe($xm) ?>">
            <input type="hidden" id="ym" value="<?= safe($ym) ?>">
        <?php endif; ?>

        <button class="btn-guardar">Guardar cambios</button>
        <a href="activeuser_admin.php" class="btn-regresar">Regresar</a>

    </form>

    <div class="mapa-wrapper">
        <h3>Ubicación en el mapa</h3>

        <div class="mapa-container" id="mapaContainer">
            <div class="mapa-inner" id="mapaInner">
                <img id="mapa" src="piso<?= safe($user['piso']) ?>.jpg" class="mapa">

                <div id="marcador" class="marcador">
                    <svg viewBox="0 0 24 24" width="48" height="48" class="pin">
                        <path fill="#00AEEF" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                    </svg>
                </div>

                <div id="radar" class="radar"></div>

                <div id="tooltip" class="tooltip"></div>
            </div>
        </div>

        <?php if ($xm !== null && $ym !== null): ?>
        <button class="btn-center" type="button" onclick="centrarMarcador()">Centrar marcador</button>
        <?php endif; ?>

        <?php if ($_SESSION['rol'] === 'administrador'): ?>
        <button type="button" onclick="guardarXY()" class="btn-guardar" style="margin-top:20px;">
            Guardar ubicación
        </button>
        <?php endif; ?>
    </div>

</div>

</div>

<script>
let xm = <?= $xm !== null ? $xm : "null" ?>;
let ym = <?= $ym !== null ? $ym : "null" ?>;

const mapa = document.getElementById("mapa");
const marcador = document.getElementById("marcador");
const radar = document.getElementById("radar");
const tooltip = document.getElementById("tooltip");
const container = document.getElementById("mapaContainer");
const mapaInner = document.getElementById("mapaInner");

let zoom = 1;
let baseW = null;
let baseH = null;

// Posicionar marcador en coordenadas base (sin zoom)
function posicionarMarcador() {
    if (xm === null || ym === null || baseW === null || baseH === null) return;

    const x = xm * baseW;
    const y = ym * baseH;

    marcador.style.left = x + "px";
    marcador.style.top = y + "px";

    radar.style.left = x + "px";
    radar.style.top = y + "px";

    marcador.classList.add("visible");
    radar.classList.add("visible");
}

// Al cargar la imagen
mapa.onload = () => {
    baseW = mapa.offsetWidth;
    baseH = mapa.offsetHeight;

    if (xm !== null && ym !== null) {
        posicionarMarcador();
        centrarMarcador(false);
    }
};

// Clic para mover marcador (solo admin y si checkbox está activo)
mapa.addEventListener("click", function(e) {

    const permitir = document.getElementById("permitirMover");
    if (!permitir || !permitir.checked) return;

    const rect = mapa.getBoundingClientRect();
    const relX = (e.clientX - rect.left) / rect.width;
    const relY = (e.clientY - rect.top) / rect.height;

    xm = relX;
    ym = relY;

    document.getElementById("xm").value = xm.toFixed(6);
    document.getElementById("ym").value = ym.toFixed(6);

    posicionarMarcador();
});

// Tooltip
marcador.addEventListener("mouseenter", () => {
    if (!marcador.classList.contains("visible")) return;

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

// Zoom con rueda (escala todo el mapaInner)
container.addEventListener("wheel", function(e) {
    e.preventDefault();

    zoom += e.deltaY * -0.001;
    zoom = Math.min(Math.max(zoom, 1), 3);

    mapaInner.style.transform = `scale(${zoom})`;
});

// Centrar marcador
function centrarMarcador(animar = true) {
    if (xm === null || ym === null || baseW === null || baseH === null) return;

    const markerX = xm * baseW * zoom;
    const markerY = ym * baseH * zoom;

    const targetLeft = markerX - container.clientWidth / 2;
    const targetTop = markerY - container.clientHeight / 2;

    container.scrollTo({
        left: targetLeft,
        top: targetTop,
        behavior: animar ? "smooth" : "auto"
    });
}

// Guardar XM/YM
function guardarXY() {
    if (xm === null || ym === null) {
        alert("Primero selecciona una ubicación en el mapa.");
        return;
    }

    const idubicacion = <?= $ubimapa2 ? $ubimapa2 : "null" ?>;
    if (idubicacion === null) {
        alert("No hay idubicacion asociado.");
        return;
    }

    fetch("guardar_xy.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `idubicacion=${encodeURIComponent(idubicacion)}&xm=${encodeURIComponent(xm)}&ym=${encodeURIComponent(ym)}`
    })
    .then(r => r.text())
    .then(t => alert(t))
    .catch(() => alert("Error al guardar la ubicación."));
}
</script>

<script src="theme.js"></script>

</body>
</html>
