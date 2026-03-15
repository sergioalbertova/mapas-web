<?php
// itil_incidente_ver.php
require "session_config.php";
require "db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de incidente inválido.");
}
$incidente_id = (int)$_GET['id'];

/* ============================
   OBTENER INCIDENTE
   ============================ */
$sql = "
SELECT 
    i.*,
    ur.nomuser  AS usuario_reporta_nombre,
    uf.nomuser  AS usuario_final_nombre,
    ta.nombre   AS tecnico_asignado_nombre,
    ta.usuario  AS tecnico_asignado_usuario
FROM itil_incidentes i
LEFT JOIN activeuser ur ON ur.idu = i.usuario_reporta
LEFT JOIN activeuser uf ON uf.idu = i.usuario_final_id
LEFT JOIN usuarios  ta ON ta.id = i.tecnico_asignado
WHERE i.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$incidente_id]);
$incidente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$incidente) {
    die("Incidente no encontrado.");
}

/* ============================
   FORMATEO DE FECHAS
   ============================ */
function fmt_fecha($valor) {
    if (!$valor) return '';
    try {
        $dt = new DateTime($valor);
        return $dt->format('Y-m-d H:i'); // sin segundos ni microsegundos
    } catch (Exception $e) {
        return $valor;
    }
}

/* ============================
   OBTENER NOTAS
   ============================ */
$sqlNotas = "
SELECT n.*, au.nomuser AS usuario_nombre
FROM itil_incidente_notas n
LEFT JOIN activeuser au ON au.idu = n.usuario_id
WHERE n.incidente_id = ?
ORDER BY n.fecha DESC
";
$stmtNotas = $pdo->prepare($sqlNotas);
$stmtNotas->execute([$incidente_id]);
$notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

/* ============================
   OBTENER HISTORIAL
   ============================ */
$sqlHist = "
SELECT h.*, au.nomuser AS usuario_nombre
FROM itil_incidente_historial h
LEFT JOIN activeuser au ON au.idu = h.usuario_id
WHERE h.incidente_id = ?
ORDER BY h.fecha DESC
";
$stmtHist = $pdo->prepare($sqlHist);
$stmtHist->execute([$incidente_id]);
$historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

/* ============================
   OBTENER TÉCNICOS (usuarios)
   ============================ */
$sqlTec = "SELECT id, nombre, usuario FROM usuarios WHERE activo = true ORDER BY nombre";
$stmtTec = $pdo->query($sqlTec);
$tecnicos = $stmtTec->fetchAll(PDO::FETCH_ASSOC);

/* ============================
   CÁLCULO SIMPLE DE SLA
   ============================ */
function sla_objetivo_horas($prioridad) {
    switch (strtolower(trim($prioridad))) {
        case 'alta':   return 4;
        case 'media':  return 8;
        case 'baja':   return 24;
        default:       return 12;
    }
}

$fecha_reporte_dt = $incidente['fecha_reporte'] ? new DateTime($incidente['fecha_reporte']) : new DateTime();
$ahora = new DateTime();
$diff = $fecha_reporte_dt->diff($ahora);
$horas_transcurridas = ($diff->days * 24) + $diff->h + ($diff->i / 60);

$objetivo = sla_objetivo_horas($incidente['prioridad'] ?? '');
$restante = $objetivo - $horas_transcurridas;

if ($restante >= 2) {
    $sla_estado = "Dentro de SLA";
    $sla_color = "#16a34a";
} elseif ($restante >= 0) {
    $sla_estado = "En riesgo";
    $sla_color = "#f59e0b";
} else {
    $sla_estado = "Vencido";
    $sla_color = "#dc2626";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Incidente #<?= htmlspecialchars((string)$incidente['id']) ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
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
    --primary: #4FC3F7; /* más claro en oscuro */
    --primary-hover: #81D4FA;
    --shadow: rgba(0,0,0,0.45);
}

/* FIX: Títulos tipo “Prioridad”, “Impacto”, “Urgencia”, etc. en modo oscuro */
body.dark .text-muted {
    color: #B0BEC5 !important; /* gris claro visible */
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* SIDEBAR */
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

/* TOPBAR ITIL */
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

/* MAIN */
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

/* TARJETAS */
.card-itil {
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 2px 6px var(--shadow);
    padding: 14px 16px;
    margin-bottom: 14px;
}
.card-itil h5 {
    margin: 0 0 10px;
    font-size: 15px;
    font-weight: 600;
    color: var(--primary);
}
.card-itil small {
    color: var(--subtext);
}

/* Títulos generales en oscuro */
body.dark h4 {
    color: #E5E7EB;
}

/* BADGES ESTADO */
.badge-estado {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 999px;
}
.badge-estado-Abierto { background:#fee2e2; color:#b91c1c; }
.badge-estado-En\ progreso { background:#fef3c7; color:#92400e; }
.badge-estado-Resuelto { background:#dcfce7; color:#166534; }
.badge-estado-Cerrado { background:#e5e7eb; color:#374151; }

textarea.form-control {
    font-size: 14px;
}
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="nav-item" onclick="toggleSidebar()">
        <svg><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        <span class="nav-text">Menú</span>
    </div>

    <h2>Panel</h2>

    <div class="nav-item">
        <a href="index.php">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            <span class="nav-text">Inicio</span>
        </a>
        <span class="tooltip">Inicio</span>
    </div>

    <div class="nav-item">
        <a href="itil_incidentes.php">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
            <span class="nav-text">Incidentes ITIL</span>
        </a>
        <span class="tooltip">Incidentes ITIL</span>
    </div>

    <div class="nav-item">
        <a href="dashboard.php">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            <span class="nav-text">Mapeo de nodos</span>
        </a>
        <span class="tooltip">Mapeo de nodos</span>
    </div>

    <div class="nav-item">
        <a href="calendario.php">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <span class="nav-text">Calendario</span>
        </a>
        <span class="tooltip">Calendario</span>
    </div>

    <div class="nav-item">
        <a href="incidentes.php">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
            <span class="nav-text">Incidentes TI</span>
        </a>
        <span class="tooltip">Incidentes TI</span>
    </div>

    <div class="nav-item">
        <a href="cambiar_password.php">
            <svg><path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/></svg>
            <span class="nav-text">Cambiar contraseña</span>
        </a>
        <span class="tooltip">Cambiar contraseña</span>
    </div>

    <div class="nav-item">
        <a href="logout.php">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm2-10H8v2h10v14H8v2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
            <span class="nav-text">Cerrar sesión</span>
        </a>
        <span class="tooltip">Cerrar sesión</span>
    </div>

    <div class="nav-item" onclick="toggleDarkMode()">
        <svg id="darkToggleIcon" viewBox="0 0 24 24"></svg>
        <span class="nav-text" id="darkToggleText">Tema oscuro</span>
        <span class="tooltip" id="darkToggleTooltip">Tema oscuro</span>
    </div>
</div>

<div class="itil-topbar">
    <a href="itil_incidentes.php">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/>
        </svg>
        Incidentes
    </a>
    <a href="itil_incidente_nuevo.php">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="2" fill="none"/>
        </svg>
        Nuevo
    </a>
    <a href="itil_problemas.php">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
        </svg>
        Problemas
    </a>
    <a href="itil_cambios.php">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M4 4h16v4H4zm0 6h16v10H4z"/>
        </svg>
        Cambios
    </a>
    <a href="itil_solicitudes.php">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <rect x="3" y="6" width="18" height="12" stroke="currentColor" stroke-width="2" fill="none"/>
        </svg>
        Solicitudes
    </a>
    <a href="itil_sla.php">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M12 2v20m10-10H2" stroke="currentColor" stroke-width="2" fill="none"/>
        </svg>
        SLA
    </a>
    <a href="itil_estadisticas.php">
        <svg width="16" height="16" viewBox="0 0 24 24">
            <path d="M4 20V10m6 10V4m6 16v-6m6 6V8" stroke="currentColor" stroke-width="2" fill="none"/>
        </svg>
        Estadísticas
    </a>
</div>

<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Incidente #<?= htmlspecialchars((string)$incidente['id']) ?></h4>
        <span class="badge bg-secondary"><?= htmlspecialchars($incidente['categoria'] ?? 'Sin categoría') ?></span>
    </div>
<?php if (!empty($_SESSION['error'])): ?>
<div class="alert alert-danger">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>
    <div class="row g-3">
        <!-- INFO GENERAL -->
        <div class="col-md-6">
            <div class="card-itil">
                <h5>Información general</h5>
                <div class="mb-1">
                    <small class="text-muted">Título</small><br>
                    <strong><?= htmlspecialchars($incidente['titulo'] ?? '') ?></strong>
                </div>
                <div class="mb-1">
                    <small class="text-muted">Descripción</small><br>
                    <span><?= nl2br(htmlspecialchars($incidente['descripcion'] ?? '')) ?></span>
                </div>
                <div class="row mt-2">
                    <div class="col-4">
                        <small class="text-muted">Prioridad</small><br>
                        <span><?= htmlspecialchars($incidente['prioridad'] ?? '') ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Impacto</small><br>
                        <span><?= htmlspecialchars($incidente['impacto'] ?? '') ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Urgencia</small><br>
                        <span><?= htmlspecialchars($incidente['urgencia'] ?? '') ?></span>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Estado</small><br>
                    <?php
                        $estado = $incidente['estado'] ?? 'Abierto';
                        $claseEstado = "badge-estado-" . str_replace(" ", "\\ ", $estado);
                    ?>
                    <span class="badge-estado <?= $claseEstado ?>">
                        <?= htmlspecialchars($estado) ?>
                    </span>
                    <?php if ($estado !== 'Cerrado'): ?>
                        <button class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#modalEstado">
                         Cambiar estado
                        </button>
                    <?php endif; ?>
                </div>
                <div class="row mt-2">
                    <div class="col-6">
                        <small class="text-muted">Fecha reporte</small><br>
                        <span><?= htmlspecialchars(fmt_fecha($incidente['fecha_reporte'] ?? '')) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Fecha asignación</small><br>
                        <span><?= htmlspecialchars(fmt_fecha($incidente['fecha_asignacion'] ?? '')) ?></span>
                    </div>
                    <div class="col-6 mt-1">
                        <small class="text-muted">Fecha resolución</small><br>
                        <span><?= htmlspecialchars(fmt_fecha($incidente['fecha_resolucion'] ?? '')) ?></span>
                    </div>
                    <div class="col-6 mt-1">
                        <small class="text-muted">Fecha cierre</small><br>
                        <span><?= htmlspecialchars(fmt_fecha($incidente['fecha_cierre'] ?? '')) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- USUARIOS / ACTIVO / TÉCNICO / SLA -->
        <div class="col-md-6">
            <div class="card-itil mb-2">
                <h5>Usuario que reporta</h5>
                <div>
                    <small class="text-muted">Nombre</small><br>
                    <strong><?= htmlspecialchars($incidente['usuario_reporta_nombre'] ?? 'N/D') ?></strong>
                </div>
            </div>

            <div class="card-itil mb-2">
                <h5>Usuario afectado</h5>
                <div>
                    <small class="text-muted">Nombre</small><br>
                    <strong><?= htmlspecialchars($incidente['usuario_final_nombre'] ?? 'N/D') ?></strong>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Activo inventario</small><br>
                    <span><?= htmlspecialchars($incidente['activo_inventario'] ?? 'No especificado') ?></span>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Ubicación / detalle</small><br>
                    <span><?= nl2br(htmlspecialchars($incidente['ubicacion_detalle'] ?? 'No especificado')) ?></span>
                </div>
            </div>

            <div class="card-itil mb-2">
                <h5>Técnico asignado</h5>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">Nombre</small><br>
                        <?php
                            $tecNombre = $incidente['tecnico_asignado_nombre'] ?: ($incidente['tecnico_asignado_usuario'] ?? '');
                        ?>
                        <strong><?= htmlspecialchars($tecNombre ?: 'Sin asignar') ?></strong>
                    </div>
                    <?php if ($incidente['estado'] !== 'Cerrado'): ?>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalReasignar">
                        Reasignar técnico
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-itil">
                <h5>SLA</h5>
                <div class="row">
                    <div class="col-4">
                        <small class="text-muted">Prioridad</small><br>
                        <span><?= htmlspecialchars($incidente['prioridad'] ?? 'N/D') ?></span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Objetivo</small><br>
                        <span><?= $objetivo ?> h</span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Transcurrido</small><br>
                        <span><?= number_format($horas_transcurridas, 1) ?> h</span>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Estado SLA</small><br>
                    <span class="badge" style="background: <?= $sla_color ?>; color:white;">
                        <?= $sla_estado ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- SOLUCIÓN -->
    <div class="card-itil">
        <h5>Solución</h5>
        <?php if (!empty($incidente['solucion'])): ?>
            <div class="mb-2">
                <small class="text-muted">Solución registrada</small><br>
                <span><?= nl2br(htmlspecialchars($incidente['solucion'] ?? '')) ?></span>
            </div>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalSolucion">
                Actualizar solución
            </button>
        <?php else: ?>
            <small class="text-muted d-block mb-1">No hay solución registrada.</small>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSolucion">
                Registrar solución
            </button>
        <?php endif; ?>
    </div>

    <!-- NOTAS E HISTORIAL -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card-itil">
                <h5>Notas internas</h5>
                <form method="post" action="itil_incidente_accion.php" class="mb-2">
                    <input type="hidden" name="accion" value="agregar_nota">
                    <input type="hidden" name="incidente_id" value="<?= $incidente_id ?>">
                    <div class="mb-2">
                        <textarea name="nota" class="form-control" rows="3" placeholder="Agregar nota interna..." required></textarea>
                    </div>
                    <?php if ($incidente['estado'] !== 'Cerrado'): ?>
                        <button class="btn btn-primary btn-sm">Guardar nota</button>
                    <?php endif; ?>
                </form>
                <hr class="my-2">
                <?php if (count($notas) === 0): ?>
                    <small class="text-muted">No hay notas registradas.</small>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notas as $n): ?>
                            <div class="list-group-item px-0 py-1" style="background:transparent; border:none;">
                                <small class="text-muted">
                                    <?= htmlspecialchars(fmt_fecha($n['fecha'] ?? '')) ?> · 
                                    <?= htmlspecialchars($n['usuario_nombre'] ?? 'N/D') ?>
                                </small><br>
                                <span><?= nl2br(htmlspecialchars($n['nota'] ?? '')) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-itil">
                <h5>Historial</h5>
                <?php if (count($historial) === 0): ?>
                    <small class="text-muted">No hay historial registrado.</small>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($historial as $h): ?>
                            <div class="list-group-item px-0 py-1" style="background:transparent; border:none;">
                                <small class="text-muted">
                                    <?= htmlspecialchars(fmt_fecha($h['fecha'] ?? '')) ?> · 
                                    <?= htmlspecialchars($h['usuario_nombre'] ?? 'N/D') ?>
                                </small><br>
                                <span>
                                    Estado: 
                                    <?= htmlspecialchars($h['estado_anterior'] ?? 'N/D') ?> 
                                    → 
                                    <?= htmlspecialchars($h['estado_nuevo'] ?? 'N/D') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CAMBIAR ESTADO -->
<div class="modal fade" id="modalEstado" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="itil_incidente_accion.php">
      <div class="modal-header">
        <h5 class="modal-title">Cambiar estado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="accion" value="cambiar_estado">
        <input type="hidden" name="incidente_id" value="<?= $incidente_id ?>">
        <div class="mb-2">
            <label class="form-label">Estado actual</label>
            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($estado) ?>" disabled>
        </div>
        <div class="mb-2">
            <label class="form-label">Nuevo estado</label>
            <select name="estado_nuevo" class="form-select form-select-sm" required>
                <?php
                $estados = ['Abierto','En progreso','Resuelto','Cerrado'];
                foreach ($estados as $e):
                ?>
                    <option value="<?= $e ?>" <?= $e == $estado ? 'selected' : '' ?>>
                        <?= $e ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL REASIGNAR TÉCNICO -->
<div class="modal fade" id="modalTecnico" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" method="post" action="itil_incidente_accion.php">
      <div class="modal-header">
        <h5 class="modal-title">Reasignar técnico</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="accion" value="reasignar_tecnico">
        <input type="hidden" name="incidente_id" value="<?= $incidente_id ?>">
        <div class="mb-2">
            <label class="form-label">Técnico actual</label>
            <input type="text" class="form-control form-control-sm" 
                   value="<?= htmlspecialchars($tecNombre ?: 'Sin asignar') ?>" disabled>
        </div>
        <div class="mb-2">
            <label class="form-label">Nuevo técnico</label>
            <select name="tecnico_nuevo" class="form-select form-select-sm" required>
                <option value="">Seleccionar...</option>
                <?php foreach ($tecnicos as $t): 
                    $label = $t['nombre'] ?: $t['usuario'];
                ?>
                    <option value="<?= $t['id'] ?>" 
                        <?= ($incidente['tecnico_asignado'] == $t['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL SOLUCIÓN -->
<div class="modal fade" id="modalSolucion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form class="modal-content" method="post" action="itil_incidente_accion.php">
      <div class="modal-header">
        <h5 class="modal-title">Registrar / actualizar solución</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="accion" value="registrar_solucion">
        <input type="hidden" name="incidente_id" value="<?= $incidente_id ?>">
        <div class="mb-2">
            <label class="form-label">Solución</label>
            <textarea name="solucion" class="form-control" rows="6" required><?= htmlspecialchars($incidente['solucion'] ?? '') ?></textarea>
        </div>
        <small class="text-muted">
            Al registrar solución, el estado puede cambiar a <strong>Resuelto</strong> automáticamente.
        </small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

function setDarkIcon(isDark) {
    const icon = document.getElementById("darkToggleIcon");
    const text = document.getElementById("darkToggleText");
    const tooltip = document.getElementById("darkToggleTooltip");
    if (!icon) return;

    if (isDark) {
        icon.innerHTML = '<path d="M21 12.79A9 9 0 0111.21 3 7 7 0 1021 12.79z"/>';
        text.textContent = "Tema claro";
        tooltip.textContent = "Tema claro";
    } else {
        icon.innerHTML = '<path d="M12 3a1 1 0 011 1v1a1 1 0 01-2 0V4a1 1 0 011-1zm0 12a4 4 0 100-8 4 4 0 000 8zm7-3a1 1 0 010 2h-1a1 1 0 010-2h1zM6 12a1 1 0 01-1 1H4a1 1 0 010-2h1a1 1 0 011 1zm11.66-6.66a1 1 0 010 1.41l-.71.71a1 1 0 11-1.41-1.41l.71-.71a1 1 0 011.41 0zM7.46 16.54a1 1 0 010 1.41l-.71.71a1 1 0 01-1.41-1.41l.71-.71a1 1 0 011.41 0zM7.46 5.46a1 1 0 01-1.41 0l-.71-.71A1 1 0 016.75 3.34l.71.71a1 1 0 010 1.41zm11.19 11.19a1 1 0 01-1.41 0l-.71-.71a1 1 0 011.41-1.41l.71.71a1 1 0 010 1.41zM12 18a1 1 0 011 1v1a1 1 0 01-2 0v-1a1 1 0 011-1z"/>';
        text.textContent = "Tema oscuro";
        tooltip.textContent = "Tema oscuro";
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
</body>
</html>