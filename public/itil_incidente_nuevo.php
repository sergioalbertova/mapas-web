<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

/* ============================================================
   OBTENER TÉCNICO LOGUEADO (usuario + nombre)
   ============================================================ */

$tecnico_id = intval($_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$tecnico_id]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tecnico) {
    $nombreTecnico = $tecnico['usuario'] . " - " . $tecnico['nombre'];
} else {
    $nombreTecnico = "Usuario no encontrado (ID $tecnico_id)";
}

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
/* ====== VARIABLES ====== */
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

/* ====== GENERAL ====== */
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
    justify-content: space-between;

    padding: 0 20px;
    box-shadow: 0 2px 8px var(--shadow);
}

.sidebar.collapsed ~ .topbar {
    left: 70px;
}


/* ========================= */
/* TOPBAR ITIL (DEBAJO)     */
/* ========================= */
.itil-topbar {
    position: fixed;
    top: 60px !important;
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
    margin-top: 125px !important;
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

/* ====== FORM ====== */
.form-box {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 10px var(--shadow);
    max-width: 900px;
    margin: auto;
}

/* SOLO el botón del formulario, no los del topbar */
.form-box button {
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

.form-box button:hover {
    background: var(--primary-hover);
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

/* ====== BOTÓN AZUL ====== */


/* ====== AUTOCOMPLETE ====== */
.lista {
    background: var(--card-bg);
    border: 1px solid var(--sidebar-hover);
    borderbutton-radius: 6px;
    max-height: 200px;
    overflow-y: auto;
    display: none;
    position: absolute;
    width: 100%;
    z-index: 3000;
}

.lista div { padding: 10px; cursor: pointer; }
.lista div:hover { background: var(--sidebar-hover); }

/* ====== FILA 3 CAMPOS ====== */
.fila-3 {
    display: flex;
    gap: 20px;
}
.fila-3 > div {
    flex: 1;
}

.itil-topbar {
    background: rgba(255, 255, 255, 0.75) !important;
    backdrop-filter: blur(10px);
}

body.dark .itil-topbar {
    background: rgba(36, 39, 44, 0.65) !important;
}

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

<!-- ====== MAIN ====== -->
<div class="main">
    <center><h2>Registrar nuevo incidente</h2></center>

    <div class="form-box">
        <form action="itil_incidente_guardar.php" method="POST">

            <!-- AUTOCOMPLETADO USUARIO -->
            <label>Usuario afectado</label>
            <input type="text" id="buscar_usuario" placeholder="Escriba el nombre..." autocomplete="off">
            <div id="lista_usuarios" class="lista"></div>
            <input type="hidden" name="usuario_final_id" id="usuario_final_id" required>

            <label>Ubicación</label>
            <input type="text" id="ubicacion" name="ubicacion_detalle" readonly>

            <label>Inventario del equipo</label>
            <input type="text" id="inventario" name="activo_inventario" readonly>

            <!-- TITULO DEL INCIDENTE -->
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

            <!-- PRIORIDAD / IMPACTO / URGENCIA -->
            <div class="fila-3">
                <div>
                    <label>Prioridad</label>
                    <select name="prioridad" id="prioridad" required>
                        <option value="Alta">Alta</option>
                        <option value="Media">Media</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>

                <div>
                    <label>Impacto</label>
                    <select name="impacto" id="impacto" required>
                        <option value="Alto">Alto</option>
                        <option value="Medio">Medio</option>
                        <option value="Bajo">Bajo</option>
                    </select>
                </div>

                <div>
                    <label>Urgencia</label>
                    <select name="urgencia" id="urgencia" required>
                        <option value="Alta">Alta</option>
                        <option value="Media">Media</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>
            </div>

            <!-- TECNICO -->
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

const buscar = document.getElementById("buscar_usuario");
const lista = document.getElementById("lista_usuarios");
const hiddenId = document.getElementById("usuario_final_id");

buscar.addEventListener("input", function() {
    let q = this.value.trim();
    if (q.length < 2) {
        lista.style.display = "none";
        return;
    }

    fetch("itil_usuario_buscar.php?q=" + encodeURIComponent(q))
        .then(res => res.json())
        .then(data => {
            lista.innerHTML = "";
            if (!data.length) {
                lista.style.display = "none";
                return;
            }
            lista.style.display = "block";

            data.forEach(u => {
                let item = document.createElement("div");
                item.textContent = u.nomuser;
                item.onclick = () => {
                    buscar.value = u.nomuser;
                    hiddenId.value = u.idu;
                    lista.style.display = "none";

                    fetch("itil_usuario_info.php?id=" + u.idu)
                        .then(r => r.json())
                        .then(info => {
                            document.getElementById("ubicacion").value =
                                info.ubicacion + " / Piso " + info.piso + " / Escritorio " + info.ubimapa2;
                            document.getElementById("inventario").value = info.hor1;
                        });
                };
                lista.appendChild(item);
            });
        });
});

const selectTitulo = document.getElementById("titulo_select");
const txtDescripcion = document.getElementById("descripcion");
const selPrio = document.getElementById("prioridad");
const selImp = document.getElementById("impacto");
const selUrg = document.getElementById("urgencia");

selectTitulo.addEventListener("change", function() {
    const opt = this.options[this.selectedIndex];

    txtDescripcion.value = opt.getAttribute("data-desc") || "";

    const prio = opt.getAttribute("data-prio") || "Alta";
    const imp  = opt.getAttribute("data-imp")  || "Alto";
    const urg  = opt.getAttribute("data-urg")  || "Alta";

    [...selPrio.options].forEach(o => { o.selected = (o.value === prio); });
    [...selImp.options].forEach(o => { o.selected = (o.value === imp); });
    [...selUrg.options].forEach(o => { o.selected = (o.value === urg); });
});
</script>

</body>
</html>
