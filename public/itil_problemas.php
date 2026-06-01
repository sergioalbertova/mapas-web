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
   OBTENER LISTA DE PROBLEMAS
   ============================ */
$sql = "
SELECT p.id, p.titulo, p.estado, p.fecha_creacion,
       u.nombre AS tecnico
FROM problemas p
LEFT JOIN usuarios u ON u.id = p.tecnico_responsable
ORDER BY p.fecha_creacion DESC
";
$stmt = $pdo->query($sql);
$problemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Problemas ITIL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

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
    --primary: #4FC3F7;
    --primary-hover: #81D4FA;
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

.sidebar.collapsed ~ .itil-topbar {
    left: 70px;
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

.sidebar.collapsed ~ .topbar + .itil-topbar + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ========================= */
/* TARJETAS                  */
/* ========================= */
.card-itil {
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 2px 6px var(--shadow);
    padding: 14px 16px;
    margin-bottom: 14px;
}

.modal { position: fixed !important; }
</style>
</head>

<body>

<?php require "sidebar.php"; ?>

<!-- === TOPBAR GENERAL (PRIMERO) === -->
<?php require "topbar.php"; ?>

<!-- === TOPBAR ITIL (DEBAJO DEL GENERAL) === -->
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

<!-- === MAIN: LISTADO DE PROBLEMAS === -->
<div class="main">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Problemas ITIL</h4>
        <a href="itil_problema_nuevo.php" class="btn btn-primary">+ Nuevo problema</a>
    </div>

    <div class="card-itil">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Técnico responsable</th>
                    <th>Fecha creación</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($problemas as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['titulo']) ?></td>
                    <td><?= $p['estado'] ?></td>
                    <td><?= $p['tecnico'] ?: 'Sin asignar' ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                    <td>
                        <a href="itil_problema_ver.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                            Ver
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- === SCRIPTS === -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
