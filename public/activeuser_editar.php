<?php
require "session_config.php";
require "db.php";

$id = $_SESSION['user_id'];

// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";


//if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
  //  header("Location: index.php");
    //exit;
//}

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
/* --- ESTILOS TIHIL --- */
:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
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

.form-card {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 10px 25px var(--shadow);
    max-width: 600px;
    width: 100%;
}

input {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-bottom: 12px;
}

/* --- MAPA --- */
.mapa-wrapper {
    margin-top: 40px;
    width: 100%;
    max-width: 900px;
}

.mapa-container {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.mapa {
    width: 100%;
    border-radius: 10px;
    transition: transform 0.2s ease-out;
}

/* --- MARCADOR PROFESIONAL --- */
.marcador {
    position: absolute;
    width: 30px;
    height: 30px;
    transform: translate(-50%, -100%);
    pointer-events: none;
    display: none;
}

.pin {
    filter: drop-shadow(0 0 6px rgba(0,0,0,0.5));
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}


/* --- TOOLTIP --- */
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
}

@keyframes pulse {
    0% { transform: translate(-50%, -100%) rotate(-45deg) scale(1); }
    50% { transform: translate(-50%, -100%) rotate(-45deg) scale(1.15); }
    100% { transform: translate(-50%, -100%) rotate(-45deg) scale(1); }
}

.btn-guardar,
.btn-regresar {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    transition: 0.2s ease;
}

/* Botón guardar */
.btn-guardar {
    background: var(--accent);
    color: white;
    border: none;
}

.btn-guardar:hover {
    background: #008fcc;
}

/* Botón regresar */
.btn-regresar {
    background: #6b7280;
    color: white;
    text-decoration: none;
}

.btn-regresar:hover {
    background: #4b5563;
}

.mapa-container {
    position: relative;
    width: 100%;
    overflow: hidden;
    transform-origin: top left;
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

    <!-- FORMULARIO -->
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

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
        <button class="btn-guardar">Guardar cambios</button>
         <?php endif; ?>
        <a href="activeuser_admin.php" class="btn-regresar">Regresar</a>

    </form>

    <!-- MAPA -->
    <div class="mapa-wrapper">
        <h3>Ubicación en el mapa</h3>

        <div class="mapa-container" id="mapaContainer">
            <img id="mapa" src="piso<?= safe($user['piso']) ?>.jpg" class="mapa">
            <div id="marcador" class="marcador">
                <svg viewBox="0 0 24 24" width="24" height="24" class="pin">
                <path fill="#00AEEF" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/>
                </svg>
            </div>

            <div id="tooltip" class="tooltip"></div>
        </div>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>    
        <button onclick="guardarXY()" class="btn-guardar" style="margin-top:20px;">
            Guardar ubicación
        </button>
         <?php endif; ?>
    </div>

</div>

</div>

<script src="theme.js"></script>

<script>
let xm = <?= $xm ? $xm : "null" ?>;
let ym = <?= $ym ? $ym : "null" ?>;

const mapa = document.getElementById("mapa");
const marcador = document.getElementById("marcador");
const tooltip = document.getElementById("tooltip");
const container = document.getElementById("mapaContainer");

let zoom = 1;

container.addEventListener("wheel", function(e) {
    e.preventDefault();

    zoom += e.deltaY * -0.001;
    zoom = Math.min(Math.max(zoom, 1), 3);

    container.style.transform = `scale(${zoom})`;
});


// Mostrar marcador inicial
mapa.onload = () => {
    if (xm !== null && ym !== null) {
        const x = xm * mapa.offsetWidth;
        const y = ym * mapa.offsetHeight;

        marcador.style.left = x + "px";
        marcador.style.top = y + "px";
        marcador.style.display = "block";
    }
};

// Capturar clic para nuevas coordenadas
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

    container.style.transform = `scale(${zoom})`;
});

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
