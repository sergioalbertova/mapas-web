<?php
require "session_config.php";
require "db.php";

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Problema no especificado.";
    header("Location: itil_problemas.php");
    exit;
}

$id = (int) $_GET['id'];

/* ============================
   OBTENER PROBLEMA
   ============================ */
$sql = "
SELECT p.*, 
       u.nombre AS tecnico_nombre,
       u.usuario AS tecnico_usuario
FROM problemas p
LEFT JOIN usuarios u ON u.id = p.tecnico_responsable
WHERE p.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$problema = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$problema) {
    $_SESSION['error'] = "El problema no existe.";
    header("Location: itil_problemas.php");
    exit;
}

/* ============================
   OBTENER TÉCNICOS
   ============================ */
$sqlTec = "SELECT id, nombre, usuario FROM usuarios WHERE activo = true ORDER BY nombre";
$stmtTec = $pdo->query($sqlTec);
$tecnicos = $stmtTec->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Problema #<?= $problema['id'] ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* === TODO EL CSS ES EL MISMO QUE TU LAYOUT REAL === */
:root {
    --bg: #F4F7FA;
    --sidebar-bg: #FFFFFF;
    --sidebar-hover: #E8EEF5;
    --card-bg: #FFFFFF;
    --text: #1F2933;
    --subtext: #6B7280;
    --primary: #0054A6;
    --primary-hover: #003F7D;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #1A1D21;
    --sidebar-bg: #24272C;
    --sidebar-hover: #2F3338;
    --card-bg: #2C2F34;
    --text: #E5E7EB;
    --subtext: #9CA3AF;
    --primary: #4FC3F7;
    --primary-hover: #81D4FA;
    --shadow: rgba(0,0,0,0.45);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* === SIDEBAR === */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    box-shadow: 4px 0 20px var(--shadow);
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    position: fixed;
    transition: width 0.25s ease;
    overflow: visible;
    z-index: 2000;
}
.sidebar.collapsed { width: 70px; }

.nav-item {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}
.nav-item:hover { background: var(--sidebar-hover); }

.nav-item a {
    display:flex;
    align-items:center;
    gap:12px;
    color:inherit;
    text-decoration:none;
}

.nav-item svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.sidebar.collapsed .nav-text { display: none; }

/* === TOPBAR === */
.itil-topbar {
    position: fixed;
    top: 0;
    left: 240px;
    height: 55px;
    width: calc(100% - 240px);
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    justify-content: space-evenly;
    gap: 10px;
    padding: 0 10px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 2100;
    transition: left 0.25s ease, width 0.25s ease;
}
#sidebar.collapsed + .itil-topbar {
    left: 70px;
    width: calc(100% - 70px);
}

.itil-topbar a {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 6px;
    font-weight: bold;
    color: var(--text);
    text-decoration: none;
    white-space: nowrap;
    font-size: 14px;
}
.itil-topbar a:hover { background: var(--sidebar-hover); }

.itil-topbar svg {
    width: 16px;
    height: 16px;
    fill: currentColor;
}

/* === MAIN === */
.main {
    margin-left: 240px;
    width: calc(100% - 240px);
    margin-top: 75px;
    padding: 20px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}
#sidebar.collapsed + .itil-topbar + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* === TARJETAS === */
.card-itil {
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 2px 6px var(--shadow);
    padding: 14px 16px;
    margin-bottom: 14px;
}
</style>
</head>

<body>

<!-- === SIDEBAR REAL === -->
<?php include "sidebar_real.php"; ?>

<!-- === TOPBAR REAL === -->
<?php include "topbar_real.php"; ?>

<!-- === MAIN === -->
<div class="main">

    <h4 class="mb-3">Problema #<?= $problema['id'] ?></h4>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['mensaje'])): ?>
        <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="card-itil">

        <form method="post" action="itil_problema_accion.php">

            <input type="hidden" name="accion" value="actualizar_problema">
            <input type="hidden" name="id" value="<?= $problema['id'] ?>">

            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" required maxlength="200"
                       value="<?= htmlspecialchars($problema['titulo']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($problema['descripcion']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Causa raíz</label>
                <textarea name="causa_raiz" class="form-control" rows="3"><?= htmlspecialchars($problema['causa_raiz']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <?php
                    $estados = ["Investigando","Diagnóstico","Identificado","En espera","Resuelto","Cerrado"];
                    foreach ($estados as $e):
                    ?>
                        <option value="<?= $e ?>" <?= $problema['estado'] === $e ? 'selected' : '' ?>>
                            <?= $e ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Técnico responsable</label>
                <select name="tecnico_responsable" class="form-select" required>
                    <?php foreach ($tecnicos as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= $problema['tecnico_responsable'] == $t['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['nombre'] ?: $t['usuario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">Fecha creación</small><br>
                    <?= date("d/m/Y H:i", strtotime($problema['fecha_creacion'])) ?>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Fecha resolución</small><br>
                    <?= $problema['fecha_resolucion'] ? date("d/m/Y H:i", strtotime($problema['fecha_resolucion'])) : "—" ?>
                </div>
            </div>

            <button class="btn btn-primary mt-3">Guardar cambios</button>

        </form>

    </div>

</div>

<!-- === SCRIPTS ORIGINALES === -->
<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

function setDarkIcon(isDark) {
    const icon = document.getElementById("darkToggleIcon");
    const text = document.getElementById("darkToggleText");
    if (!icon) return;

    if (isDark) {
        icon.innerHTML = '<path d="M21 12.79A9 9 0 0111.21 3 7 7 0 1021 12.79z"/>';
        text.textContent = "Tema claro";
    } else {
        icon.innerHTML = '<path d="M12 3a1 1 0 011 1v1a1 1 0 01-2 0V4a1 1 0 011-1zm0 12a4 4 0 100-8 4 4 0 000 8zm7-3a1 1 0 010 2h-1a1 1 0 010-2h1zM6 12a1 1 0 01-1 1H4a1 1 0 010-2h1a1 1 0 011 1zm11.66-6.66a1 1 0 010 1.41l-.71.71a1 1 0 11-1.41-1.41l.71-.71a1 1 0 011.41 0zM7.46 16.54a1 1 0 010 1.41l-.71.71a1 1 0 01-1.41-1.41l.71-.71a1 1 0 011.41 0zM7.46 5.46a1 1 0 01-1.41 0l-.71-.71A1 1 0 016.75 3.34l.71.71a1 1 0 010 1.41zm11.19 11.19a1 1 0 01-1.41 0l-.71-.71a1 1 0 011.41-1.41l.71.71a1 1 0 010 1.41zM12 18a1 1 0 011 1v1a1 1 0 01-2 0v-1a1 1 0 011-1z"/>';
        text.textContent = "Tema oscuro";
    }
}

function toggleDarkMode() {
    const isDark = !document.body.classList.contains("dark");
    document.body.classList.toggle("dark", isDark);
    localStorage.setItem("tema", isDark ? "dark" : "light");
    setDarkIcon(isDark);
}

(function initTheme() {
    const saved = localStorage.getItem("tema");
    const isDark = saved === "dark";
    if (isDark) {
        document.body.classList.add("dark");
    }
    setDarkIcon(isDark);
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
