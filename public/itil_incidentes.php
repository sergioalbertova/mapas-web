<?php
require "auth.php";
require "db.php";
$id = $_SESSION['user_id'];
/* ============================================================
   OBTENER TÉCNICO LOGUEADO
   ============================================================ */
// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";

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
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Incidentes ITIL</title>
<link rel="icon" href="apoyo2.png" type="image/x-icon">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<style>

/* ========================= */
/* VARIABLES                 */
/* ========================= */
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

/* ========================= */
/* GENERAL                   */
/* ========================= */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: background 0.3s ease, color 0.3s ease;
}

/* ========================= */
/* TOPBAR GENERAL (PRIMERO) */
/* ========================= */


.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ========================= */
/* TOPBAR ITIL (DEBAJO)     */
/* ========================= */
.itil-topbar {
    display: flex;
    align-items: center;
    gap: 18px;
    position: fixed;
    top: 65px;
    left: 240px;
    right: 0;
    height: 55px;
    z-index: 1500;    
    border-radius: 12px;
    margin: 10px 20px 0 20px;
    width: auto;
}

#sidebar.collapsed ~ .itil-topbar {
    left: 70px;
}

#sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}


/* ESTILO PROFESIONAL DEL MENÚ ITIL */
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

    margin-top: 110px;

    padding: 15px 20px;

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

.itil-topbar {
    background: rgba(255, 255, 255, 0.75) !important;
    backdrop-filter: blur(10px);
}

body.dark .itil-topbar {
    background: rgba(36, 39, 44, 0.65) !important;
}

.main-shell {

    margin-left: 240px;

    width: calc(100% - 240px);

    transition:
        margin-left .25s ease,
        width .25s ease;

}

#sidebar.collapsed ~ .main-shell {

    margin-left: 70px;

    width: calc(100% - 70px);

}


.itil-topbar a.active {

    background: #00AEEF;

    color: white;

    box-shadow:
        0 3px 10px rgba(0,174,239,.25);

}

.itil-topbar a.active svg {

    fill: white;

    opacity: 1;

}

.itil-topbar a.active {

    background: #00AEEF;
    color: white;

    border-bottom: 3px solid #ffffff;
}

.itil-topbar a.active {

    background: #00AEEF;

    color: white;

    box-shadow:
        0 3px 10px rgba(0,174,239,.25);

}

.itil-topbar a.active svg {

    fill: white;

    opacity: 1;

}

</style>
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main-shell">

<!-- === TOPBAR GENERAL (PRIMERO) === -->
<?php require "topbar.php"; ?>

<!-- === TOPBAR ITIL (DEBAJO DEL GENERAL) === -->
<div class="itil-topbar">

    <a href="itil_incidentes.php"  class="<?= $paginaActual == 'itil_incidentes.php' ? 'active' : '' ?>">
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
<!-- MAIN + CONTENIDO         -->
<!-- ========================= -->
<div class="main">

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

</div>
<script src="theme.js"></script>
</body>
</html>
