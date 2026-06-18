<?php
require "session_config.php";
require "db.php";

$id = $_SESSION['user_id'];

/* ============================================================
   OBTENER TÉCNICO LOGUEADO
   ============================================================ */
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";


/* ============================
   OBTENER CATALOGO COMPLETO
   ============================ */
$sql = "
SELECT *
FROM catapoyo
ORDER BY activo DESC, orden ASC, tituloincidente ASC
";
$stmt = $pdo->query($sql);
$apoyos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================
   OBTENER CATEGORÍAS ÚNICAS
   ============================ */
$sqlCat = "SELECT DISTINCT categoria FROM catapoyo WHERE categoria IS NOT NULL ORDER BY categoria";
$stmtCat = $pdo->query($sqlCat);
$categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Catálogo de Apoyos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>
/* === VARIABLES DE COLOR === */
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

body.dark .text-muted {
    color: var(--subtext) !important;
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* ========================= */
/* TOPBAR GENERAL (PRIMERO) */
/* ========================= */
.topbar {
    position: fixed !important;
    top: 0 !important;
    left: 240px;
    right: 0;
    height: 55px;
    z-index: 3000 !important;
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    padding: 0 20px;
    box-shadow: 0 2px 8px var(--shadow);
}

#sidebar.collapsed ~ .topbar {
    left: 70px;
}

/* ========================= */
/* TOPBAR ITIL (DEBAJO)     */
/* ========================= */
.itil-topbar {
    position: fixed;
    top: 60px !important; /* DEBAJO DEL TOPBAR GENERAL */
    left: 240px;
    right: 0;
    height: 55px;
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 0 25px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 2500 !important;
    border-bottom: 1px solid rgba(0,0,0,0.08);
}

.itil-topbar a {
    text-decoration: none;
    color: var(--text);
    font-weight: 600;
    padding: 8px 14px;
    border-radius: 8px;
    display:flex;
    align-items:center;
    gap:10px;
    transition: 0.2s ease;
    font-size: 15px;
}

.itil-topbar a:hover {
    background: var(--sidebar-hover);
    transform: translateY(-1px);
}

.itil-topbar svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
    opacity: 0.85;
}


/* ========================= */
/* MAIN                      */
/* ========================= */
.main {
    margin-left: 240px;
    width: calc(100% - 240px);
    margin-top: 125px !important; /* 55px general + 60px ITIL */
    padding: 20px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}

/* ============================================================
   CORRECCIÓN DEFINITIVA PARA EL SIDEBAR COLAPSADO
   ============================================================ */
#sidebar.collapsed ~ * .itil-topbar {
    left: 70px !important;
}

#sidebar.collapsed ~ * .main {
    margin-left: 70px !important;
    width: calc(100% - 70px) !important;
}

/* === CARD === */
.card-itil {
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 2px 6px var(--shadow);
    padding: 14px 16px;
    margin-bottom: 14px;
}

/* ====== TABLAS EN MODO OSCURO ====== */
body.dark table {
    background: var(--card-bg) !important;
    color: var(--text) !important;
}

body.dark table thead tr {
    background: var(--sidebar-hover) !important;
}

body.dark table tbody tr:hover {
    background: var(--sidebar-hover) !important;
}

body.dark table td,
body.dark table th {
    background: var(--card-bg) !important;
    color: var(--text) !important;
    border-color: var(--sidebar-hover) !important;
}


</style>
</head>

<body>
<?php require "sidebar.php"; ?>

<!-- === TOPBAR GENERAL (PRIMERO) === -->
<?php require "topbar.php"; ?>


<!-- === TOPBAR REAL === -->
<div class="itil-topbar">

    <a href="itil_incidentes.php">
        <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
        Incidentes
    </a>

    <a href="itil_incidente_nuevo.php">
        <svg><path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Nuevo
    </a>

    <a href="itil_problemas.php">
        <svg><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Problemas
    </a>

    <a href="itil_catalogo.php">
        <svg><path d="M4 4h16v4H4zm0 6h16v10H4z"/></svg>
        Catalogo Incidentes
    </a>

    <a href="itil_solicitudes.php">
        <svg><rect x="3" y="6" width="18" height="12" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Solicitudes
    </a>

    <a href="itil_sla.php">
        <svg><path d="M12 2v20m10-10H2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        SLA
    </a>

    <a href="itil_estadisticas.php">
        <svg><path d="M4 20V10m6 10V4m6 16v-6m6 6V8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Estadísticas
    </a>

</div>


<!-- === MAIN === -->
<div class="main">

    <h4 class="mb-3">Catálogo de Apoyos</h4>

    <?php if (!empty($_SESSION['mensaje'])): ?>
        <div class="alert alert-success"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <a href="itil_catalogo_nuevo.php" class="btn btn-primary mb-3">Nuevo apoyo</a>

    <div class="card-itil">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Apoyo</th>
                    <th>Categoría</th>
                    <th>Prioridad</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apoyos as $a): ?>
                <tr>
                    <td><?= $a['idapoyo'] ?></td>
                    <td><?= htmlspecialchars($a['tituloincidente']) ?></td>
                    <td><?= htmlspecialchars($a['categoria'] ?: '—') ?></td>
                    <td><?= $a['prioridad'] ?></td>
                    <td>
                        <?php if ($a['activo']): ?>
                            <span class="badge bg-success">Sí</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="itil_catalogo_ver.php?id=<?= $a['idapoyo'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

function setDarkIcon(isDark) {
    const icon = document.getElementById("darkToggleIcon");
    const text = document.getElementById("darkToggleText");

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
    if (isDark) document.body.classList.add("dark");
    setDarkIcon(isDark);
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
