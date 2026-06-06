<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

/* ============================================================
   OBTENER TÉCNICO LOGUEADO
   ============================================================ */
$tecnico_id = intval($_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$tecnico_id]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

$nombreTecnico = $tecnico
    ? $tecnico['usuario'] . " - " . $tecnico['nombre']
    : "Usuario no encontrado (ID $tecnico_id)";

/* ============================================================
   OBTENER CATÁLOGO DE APOYOS
   ============================================================ */
$stmt2 = $pdo->query("
    SELECT idapoyo, tituloincidente, descripcion, prioridad, impacto, urgencia
    FROM catapoyo
    ORDER BY tituloincidente
");
$catalogo = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo incidente ITIL</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>
/* ============================================================
   VARIABLES
   ============================================================ */
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

/* ============================================================
   GENERAL
   ============================================================ */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* ============================================================
   TOPBAR GENERAL (CORREGIDO)
   ============================================================ */
.topbar {
    position: fixed !important;
    top: 0 !important;
    left: 240px;
    right: 0;
    height: 55px;
    background: var(--sidebar-bg);
    z-index: 3000 !important;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 20px;
    box-shadow: 0 2px 8px var(--shadow);
}

#sidebar.collapsed ~ .topbar {
    left: 70px;
}

/* ============================================================
   TOPBAR ITIL
   ============================================================ */
.itil-topbar {
    position: fixed;
    top: 55px;
    left: 240px;
    right: 0;
    height: 55px;

    background: var(--card-bg);
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 0 25px;

    box-shadow: 0 2px 8px var(--shadow);
    border-bottom: 1px solid rgba(0,0,0,0.08);

    z-index: 2500 !important;
}

#sidebar.collapsed ~ .itil-topbar {
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

/* ============================================================
   MAIN
   ============================================================ */
.main {
    margin-left: 240px;
    width: calc(100% - 240px);
    margin-top: 120px;
    padding: 25px;
    transition: margin-left 0.25s ease, width 0.25s ease;
}

#sidebar.collapsed ~ .topbar + .itil-topbar + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* ============================================================
   FORMULARIO
   ============================================================ */
.form-box {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 10px var(--shadow);

    max-width: 900px;
    margin: auto;
}

label {
    font-weight: bold;
    margin-top: 15px;
    display: block;
}

input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border-radius: 6px;
    border: 1px solid var(--sidebar-hover);
    background: var(--bg);
    color: var(--text);
}

textarea {
    height: 120px;
    resize: vertical;
}

/* ============================================================
   BOTÓN
   ============================================================ */
button {
    margin-top: 25px;
    padding: 12px 18px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: 0.2s;
}

button:hover {
    background: var(--primary-hover);
}

/* ============================================================
   AUTOCOMPLETE
   ============================================================ */
.lista {
    background: var(--card-bg);
    border: 1px solid var(--sidebar-hover);
    border-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
    display: none;
    position: absolute;
    width: 100%;
    z-index: 3000;
}

.lista div {
    padding: 10px;
    cursor: pointer;
}

.lista div:hover {
    background: var(--sidebar-hover);
}

/* ============================================================
   FILA 3 CAMPOS
   ============================================================ */
.fila-3 {
    display: flex;
    gap: 20px;
}

.fila-3 > div {
    flex: 1;
}
</style>
</head>

<body>

<?php require "sidebar.php"; ?>
<?php require "topbar.php"; ?>

<div class="itil-topbar">
    <a href="itil_incidentes.php">Incidentes</a>
    <a href="itil_incidente_nuevo.php">Nuevo incidente</a>
    <a href="itil_problemas.php">Problemas</a>
    <a href="itil_catalogo.php">Catálogo Incidentes</a>
    <a href="itil_solicitudes.php">Solicitudes</a>
    <a href="itil_sla.php">SLA</a>
    <a href="itil_estadisticas.php">Estadísticas</a>
</div>

<div class="main">
    <h2>Registrar nuevo incidente</h2>

    <div class="form-box">
        <form action="itil_incidente_guardar.php" method="POST">

            <label>Usuario afectado</label>
            <input type="text" id="buscar_usuario" placeholder="Escriba el nombre..." autocomplete="off">
            <div id="lista_usuarios" class="lista"></div>
            <input type="hidden" name="usuario_final_id" id="usuario_final_id" required>

            <label>Ubicación</label>
            <input type="text" id="ubicacion" name="ubicacion_detalle" readonly>

            <label>Inventario del equipo</label>
            <input type="text" id="inventario" name="activo_inventario" readonly>

            <label>Título del incidente</label>
            <select id="titulo_select" name="titulo" required>
                <option value="">Seleccione...</option>
                <?php foreach ($catalogo as $c): ?>
                    <option 
                        value="<?= htmlspecialchars($c['tituloincidente']) ?>"
                        data-desc="<?= htmlspecialchars($c['descripcion']) ?>"
                        data-prio="<?= htmlspecialchars($c['prioridad']) ?>"
                        data-imp="<?= htmlspecialchars($c['impacto']) ?>"
                        data-urg="<?= htmlspecialchars($c['urgencia']) ?>"
                    >
                        <?= htmlspecialchars($c['tituloincidente']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Descripción</label>
            <textarea name="descripcion" id="descripcion" required></textarea>

            <div class="fila-3">
                <div>
                    <label>Prioridad</label>
                    <select name="prioridad" id="prioridad" required>
                        <option>Alta</option>
                        <option>Media</option>
                        <option>Baja</option>
                    </select>
                </div>

                <div>
                    <label>Impacto</label>
                    <select name="impacto" id="impacto" required>
                        <option>Alto</option>
                        <option>Medio</option>
                        <option>Bajo</option>
                    </select>
                </div>

                <div>
                    <label>Urgencia</label>
                    <select name="urgencia" id="urgencia" required>
                        <option>Alta</option>
                        <option>Media</option>
                        <option>Baja</option>
                    </select>
                </div>
            </div>

            <label>Técnico asignado</label>
            <input type="text" value="<?= htmlspecialchars($nombreTecnico) ?>" readonly>
            <input type="hidden" name="tecnico_asignado" value="<?= $tecnico_id ?>">

            <button type="submit">Registrar incidente</button>
        </form>
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
