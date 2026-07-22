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
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo incidente ITIL</title>
<link rel="icon" href="apoyo2.png" type="image/x-icon">

<style>
/* ====== VARIABLES ====== */
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


/* ====== GENERAL ====== */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: background 0.3s ease, color 0.3s ease;
}



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

button {
    padding: 10px 15px;
    background: #0054A6;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #003f7d;
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

<script src="theme.js"></script>
</body>
</html>
