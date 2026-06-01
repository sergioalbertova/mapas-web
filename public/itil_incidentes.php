<?php
require "session_config.php";
require "db.php";

/* ============================================================
   OBTENER TÉCNICO LOGUEADO
   ============================================================ */
$tecnico_id = intval($_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$tecnico_id]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

$nombreTecnico = $tecnico ? $tecnico['usuario'] . " - " . $tecnico['nombre'] : "Usuario";

/* ============================================================
   OBTENER INCIDENTES
   ============================================================ */
$sql = "
SELECT 
    i.id,
    i.titulo,
    i.prioridad,
    i.estado,
    i.fecha_reporte,
    u.nombre AS tecnico_nombre,
    au.nomuser AS usuario_afectado
FROM itil_incidentes i
LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
LEFT JOIN activeuser au ON au.idu = i.usuario_final_id
ORDER BY i.id DESC
";
$stmt = $pdo->query($sql);
$incidentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Incidentes ITIL</title>

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
    margin-top: 95px; /* espacio para topbar ITIL */
    padding: 25px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}

.sidebar.collapsed ~ .itil-topbar + .main {
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

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

</head>

<body>

<?php require "sidebar.php"; ?>

<!-- === TOPBAR ITIL (FIJO) === -->
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
        En Proceso
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

<!-- ========================= -->
<!-- MAIN + TOPBAR GENERAL     -->
<!-- ========================= -->
<div class="main">

    <!-- TOPBAR GENERAL DEL SISTEMA -->
    <?php require "topbar.php"; ?>

    <div class="table-box">
        <h2>Incidentes registrados</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Usuario afectado</th>
                <th>Prioridad</th>
                <th>Estado</th>
                <th>Técnico asignado</th>
                <th>Fecha</th>
            </tr>

            <?php if (count($incidentes) === 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:20px;">
                        No hay incidentes registrados aún.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($incidentes as $i): ?>
                    <tr>
                        <td><?= $i['id'] ?></td>

                        <td>
                            <a href="itil_incidente_ver.php?id=<?= $i['id'] ?>" 
                               style="color: var(--primary); font-weight:bold;">
                               <?= htmlspecialchars($i['titulo']) ?>
                            </a>
                        </td>

                        <td><?= htmlspecialchars($i['usuario_afectado']) ?></td>
                        <td><?= htmlspecialchars($i['prioridad']) ?></td>

                        <td>
                            <?php $clase = "estado_" . str_replace(" ", "_", $i['estado']); ?>
                            <span class="estado <?= $clase ?>">
                                <?= htmlspecialchars($i['estado']) ?>
                            </span>
                        </td>

                        <td><?= htmlspecialchars($i['tecnico_nombre']) ?></td>

                        <td><?= date("Y-m-d H:i:s", strtotime($i['fecha_reporte'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

function toggleTheme() {
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
}
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}
</script>

</body>
</html>
