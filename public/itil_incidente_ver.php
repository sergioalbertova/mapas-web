<?php
// itil_incidente_ver.php
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

$incidente_id = (int)$_GET['id'];
$paginaActual = basename($_SERVER['PHP_SELF']);
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
        return $dt->format('Y-m-d H:i');
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
   OBTENER TÉCNICOS
   ============================ */
$sqlTec = "SELECT id, nombre, usuario FROM usuarios WHERE activo = true ORDER BY nombre";
$stmtTec = $pdo->query($sqlTec);
$tecnicos = $stmtTec->fetchAll(PDO::FETCH_ASSOC);

/* ============================
   OBTENER PROBLEMAS
   ============================ */
$stmt = $pdo->query("
    SELECT id, titulo, estado
    FROM problemas
    ORDER BY fecha_creacion DESC
");
$lista_problemas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================
   CÁLCULO CORRECTO DE SLA
   ============================ */

function sla_objetivo_horas($prioridad) {
    switch (strtolower(trim($prioridad))) {
        case 'alta':   return 4;
        case 'media':  return 8;
        case 'baja':   return 24;
        default:       return 12;
    }
}

$fecha_reporte_dt = new DateTime($incidente['fecha_reporte']);

// Determinar fecha final según estado
if (!empty($incidente['fecha_cierre'])) {
    $fecha_fin_dt = new DateTime($incidente['fecha_cierre']);
} elseif (!empty($incidente['fecha_resolucion'])) {
    $fecha_fin_dt = new DateTime($incidente['fecha_resolucion']);
} else {
    $fecha_fin_dt = new DateTime();
}

$diff = $fecha_reporte_dt->diff($fecha_fin_dt);
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
<link rel="icon" href="apoyo2.png" type="image/x-icon">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

/* Historial en modo oscuro: texto claro */
body.dark .card-itil h5,
body.dark .card-itil small,
body.dark .card-itil span,
body.dark .list-group-item small,
body.dark .list-group-item span {
    color: #E5E7EB !important;
}

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



<div class="itil-topbar">

    <a href="itil_incidentes.php"  class="<?= $paginaActual == 'itil_incidentes.php' ? 'active' : '' ?>">
        <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
        Incidentes
    </a>

    <a href="itil_incidente_nuevo.php"   class="<?= $paginaActual == 'itil_incidente_nuevo.php' ? 'active' : '' ?>">
        <svg><path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Nuevo
    </a>

    <a href="itil_problemas.php" class="<?= $paginaActual == 'itil_problemas.php' ? 'active' : '' ?>">
        <svg><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Problemas
    </a>

    <a href="itil_catalogo.php"  class="<?= $paginaActual == 'itil_catalogo.php' ? 'active' : '' ?>">
        <svg><path d="M4 4h16v4H4zm0 6h16v10H4z"/></svg>
        Catálogo Incidentes
    </a>

    <a href="itil_solicitudes.php" class="<?= $paginaActual == 'itil_solicitudes.php' ? 'active' : '' ?>">
        <svg><rect x="3" y="6" width="18" height="12" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        En Proceso
    </a>

    <a href="itil_sla.php" class="<?= $paginaActual == 'itil_sla.php' ? 'active' : '' ?>">
        <svg><path d="M12 2v20m10-10H2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        SLA
    </a>

    <a href="itil_estadisticas.php"  class="<?= $paginaActual == 'itil_estadisticas.php' ? 'active' : '' ?>">
        <svg><path d="M4 20V10m6 10V4m6 16v-6m6 6V8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Estadísticas
    </a>

</div>

<!-- ========================= -->
<!-- MAIN + CONTENIDO         -->
<!-- ========================= -->
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

                    <?php if ($estado !== 'Cerrado'): ?>
                        <button class="btn btn-sm btn-outline-warning ms-2" data-bs-toggle="modal" data-bs-target="#modalAsociarProblema">
                            Asociar problema
                        </button>
                    <?php endif; ?>
                </div>

                <div class="row mt-2">
                    <div class="col-6">
                        <small class="text-muted">Fecha reporte</small><br>
                        <span><?= fmt_fecha($incidente['fecha_reporte']) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Fecha asignación</small><br>
                        <span><?= fmt_fecha($incidente['fecha_asignacion']) ?></span>
                    </div>
                    <div class="col-6 mt-1">
                        <small class="text-muted">Fecha resolución</small><br>
                        <span><?= fmt_fecha($incidente['fecha_resolucion']) ?></span>
                    </div>
                    <div class="col-6 mt-1">
                        <small class="text-muted">Fecha cierre</small><br>
                        <span><?= fmt_fecha($incidente['fecha_cierre']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- USUARIOS / TÉCNICO / SLA -->
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

                    <?php if ($estado !== 'Cerrado'): ?>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalTecnico">
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
                <span><?= nl2br(htmlspecialchars($incidente['solucion'])) ?></span>
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

        <!-- NOTAS -->
        <div class="col-md-6">
            <div class="card-itil">
                <h5>Notas internas</h5>

                <form method="post" action="itil_incidente_accion.php" class="mb-2">
                    <input type="hidden" name="accion" value="agregar_nota">
                    <input type="hidden" name="incidente_id" value="<?= $incidente_id ?>">

                    <div class="mb-2">
                        <textarea name="nota" class="form-control" rows="3" placeholder="Agregar nota interna..." required></textarea>
                    </div>

                    <?php if ($estado !== 'Cerrado'): ?>
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
                                    <?= fmt_fecha($n['fecha']) ?>
                                </small><br>
                                <span><?= nl2br(htmlspecialchars($n['nota'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- HISTORIAL -->
        <div class="col-md-6">
            <div class="card-itil">
                <h5>Historial</h5>

                <?php if (count($historial) === 0): ?>
                    <small class="text-muted">No hay historial registrado.</small>
                <?php else: ?>
                    <div class="list-group list-group-flush">
    <?php foreach ($historial as $h): ?>
        <div class="list-group-item px-0 py-1" style="background:transparent; border:none;">
            
            <!-- Solo fecha y hora -->
            <small class="text-muted">
                <?= fmt_fecha($h['fecha'] ?? '') ?>
            </small><br>

            <span>
                Estado:
                <?= htmlspecialchars($h['estado_anterior'] ?? '') ?>
                →
                <?= htmlspecialchars($h['estado_nuevo'] ?? '') ?>
            </span>

        </div>
    <?php endforeach; ?>
</div>

                <?php endif; ?>

            </div>
        </div>

    </div> <!-- row -->
</div> <!-- main -->

<!-- ========================= -->
<!-- MODAL CAMBIAR ESTADO -->
<!-- ========================= -->
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

<!-- ========================= -->
<!-- MODAL REASIGNAR TÉCNICO -->
<!-- ========================= -->
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

<!-- ========================= -->
<!-- MODAL ASOCIAR PROBLEMA -->
<!-- ========================= -->
<div class="modal fade" id="modalAsociarProblema" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Asociar problema</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="itil_incidente_accion.php" method="POST">

        <div class="modal-body">
            <input type="hidden" name="accion" value="asociar_problema">
            <input type="hidden" name="incidente_id" value="<?= $incidente['id'] ?>">

            <label class="form-label">Selecciona un problema</label>
            <select name="problema_id" class="form-select">
                <option value="">Sin problema asociado</option>

                <?php foreach ($lista_problemas as $p): ?>
                    <option value="<?= $p['id'] ?>"
                        <?= ($incidente['problema_id'] ?? null) == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['titulo']) ?> (<?= $p['estado'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- ========================= -->
<!-- MODAL SOLUCIÓN -->
<!-- ========================= -->
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
            <textarea name="solucion" class="form-control" rows="6" required>
<?= htmlspecialchars($incidente['solucion'] ?? '') ?>
            </textarea>
        </div>

        <small class="text-muted">
            Al registrar solución, el incidente se marcará como <strong>Cerrado</strong>.
        </small>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
      </div>

    </form>

  </div>
</div>

<!-- ========================= -->
<!-- SCRIPTS -->
<!-- ========================= -->
<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

function setDarkIcon(isDark) {
    const icon = document.getElementById("darkToggleIcon");
    const text = document.getElementById("darkToggleText");
    const tooltip = document.getElementById("darkToggleTooltip");

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
    if (isDark) document.body.classList.add("dark");
    setDarkIcon(isDark);
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</div>
<script src="theme.js"></script>
</body>
</html>
