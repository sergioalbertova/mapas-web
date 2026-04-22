<?php
require "session_config.php";
require "db.php";

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM catapoyo WHERE idapoyo = ?");
$stmt->execute([$id]);
$apoyo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apoyo) {
    die("Apoyo no encontrado");
}

/* Obtener categorías generales */
$categorias = $pdo->query("SELECT id, nombre FROM itil_categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar apoyo</title>
<link rel="stylesheet" href="itil_estadisticas.css">
<style>
/* ========================= */
/* VARIABLES                 */
/* ========================= */
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
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
}

/* ========================= */
/* GENERAL                   */
/* ========================= */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* ========================= */
/* SIDEBAR ORIGINAL          */
/* ========================= */
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

.sidebar h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: var(--primary);
    transition: opacity 0.25s ease;
}
.sidebar.collapsed h2 { opacity: 0; }

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

/* TOOLTIP */
.tooltip {
    position: absolute;
    left: 80px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--sidebar-bg);
    padding: 6px 12px;
    border-radius: 6px;
    box-shadow: 0 2px 8px var(--shadow);
    font-size: 13px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, left 0.2s ease;
    z-index: 99999;
}
.sidebar.collapsed .nav-item:hover .tooltip {
    opacity: 1;
    left: 75px;
}

/* ====== TOPBAR ITIL ====== */
.itil-topbar {
    position: fixed;
    top: 0;
    left: 240px;
    right: 0;
    height: 55px;
    background: var(--sidebar-bg);
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 0 25px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 2100;
}
.sidebar.collapsed ~ .itil-topbar { left: 70px; }

.itil-topbar a {
    text-decoration: none;
    color: var(--text);
    font-weight: bold;
    padding: 8px 12px;
    border-radius: 6px;
    display:flex;
    align-items:center;
    gap:8px;
}
.itil-topbar a:hover { background: var(--sidebar-hover); }

.itil-topbar svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
}


/* ========================= */
/* MAIN                      */
/* ========================= */
.main {
    margin-left: 240px;
    width: calc(100% - 240px);
    margin-top: 95px;
    padding: 25px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}
#sidebar.collapsed + .itil-topbar + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ========================= */
/* TABLA                     */
/* ========================= */
.table-box {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 10px var(--shadow);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th {
    background: var(--primary);
    color: white;
    padding: 10px;
    text-align: left;
}

td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--sidebar-hover);
}

.estado {
    font-weight: bold;
    padding: 4px 8px;
    border-radius: 6px;
}
.estado_Abierto { background: #ffebee; color: #c62828; }
.estado_En_progreso { background: #fff3cd; color: #b8860b; }
.estado_Cerrado { background: #e8f5e9; color: #2e7d32; }
</style>
</head>
<body>

<?php include "sidebar.php"; ?>

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
        Catálogo Incidentes
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

<div class="main">

    <h2 class="dashboard-title">Editar apoyo</h2>
    <div class="dashboard-subtitle">Modifica la información del apoyo seleccionado</div>

    <form action="itil_catalogo_accion.php" method="POST" class="form-card">

        <input type="hidden" name="id" value="<?= $apoyo['id'] ?>">

        <label>Nombre del apoyo</label>
        <input type="text" name="apoyo" value="<?= htmlspecialchars($apoyo['apoyo']) ?>" required>

        <label>Categoría</label>
        <select name="categoria">
            <option value="">— Sin categoría —</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" 
                    <?= $apoyo['categoria'] == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Prioridad</label>
        <select name="prioridad" required>
            <option value="Alta"   <?= $apoyo['prioridad'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
            <option value="Media"  <?= $apoyo['prioridad'] === 'Media' ? 'selected' : '' ?>>Media</option>
            <option value="Baja"   <?= $apoyo['prioridad'] === 'Baja' ? 'selected' : '' ?>>Baja</option>
        </select>

        <label>Activo</label>
        <select name="activo">
            <option value="1" <?= $apoyo['activo'] == 1 ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= $apoyo['activo'] == 0 ? 'selected' : '' ?>>No</option>
        </select>

        <button type="submit" class="btn-guardar">Guardar cambios</button>
        <a href="itil_catalogo.php" class="btn-cancelar">Cancelar</a>

    </form>

</div>

</body>
</html>
