<?php
require "session_config.php";
require "db.php";

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=no_session");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo catálogo ITIL</title>

<style>
:root {
    --bg: #F4F7FA;
    --card-bg: #FFFFFF;
    --text: #1F2933;
    --subtext: #6B7280;
    --primary: #0054A6;
    --primary-hover: #003F7D;
    --shadow: rgba(0,0,0,0.08);
    --input-bg: #FFFFFF;
    --input-border: #D1D5DB;
}

body.dark {
    --bg: #1A1D21;
    --card-bg: #2C2F34;
    --text: #E5E7EB;
    --subtext: #9CA3AF;
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
    --input-bg: #1F2226;
    --input-border: #3A3D42;
}

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

/* MAIN */
.main {
    margin-left: 240px;
    padding: 40px;
    width: calc(100% - 240px);
    transition: margin-left 0.25s ease;
}
.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* TITULO */
h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 10px;
    font-weight: 600;
}

.subtitle {
    text-align: center;
    color: var(--subtext);
    margin-bottom: 40px;
    font-size: 15px;
}

/* FORMULARIO */
.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 4px 14px var(--shadow);
    max-width: 900px;
    margin: auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 22px;
}

label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--input-border);
    background: var(--input-bg);
    color: var(--text);
    font-size: 14px;
}

textarea {
    height: 120px;
    resize: vertical;
}

/* BOTÓN */
.btn-guardar {
    margin-top: 30px;
    width: 100%;
    padding: 14px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 17px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.btn-guardar:hover {
    background: var(--primary-hover);
}

/* MODAL */
#modalCategoria {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.55);
    backdrop-filter:blur(3px);
    justify-content:center;
    align-items:center;
    z-index:9999;
}
</style>
</head>

<body>

<?php require "sidebar.php"; ?>

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
<div class="main">

    <h2>Nuevo elemento del catálogo ITIL</h2>
    <div class="subtitle">Registrar un nuevo tipo de incidente o apoyo</div>

    <form action="itil_catalogo_guardar.php" method="POST" class="form-card">

        <div class="form-grid">

            <div>
                <label>Título del incidente *</label>
                <input type="text" name="tituloincidente" required>
            </div>

            <div>
                <label>Categoría *</label>
                <div style="display:flex; gap:10px;">
                    <select name="categoria" id="categoria" required style="flex:1;">
                        <option value="">Seleccione...</option>
                        <?php
                        $cats = $pdo->query("SELECT nombre FROM categorias WHERE activo = true ORDER BY orden, nombre");
                        foreach ($cats as $c) {
                            echo "<option value='{$c['nombre']}'>{$c['nombre']}</option>";
                        }
                        ?>
                    </select>

                    <button type="button" onclick="openCategoriaModal()" 
                        style="padding:10px 14px; border:none; background:var(--primary); color:white; border-radius:8px; cursor:pointer;">
                        +
                    </button>
                </div>
            </div>

            <div>
                <label>Prioridad</label>
                <select name="prioridad">
                    <option>Alta</option>
                    <option>Media</option>
                    <option>Baja</option>
                </select>
            </div>

            <div>
                <label>Impacto</label>
                <select name="impacto">
                    <option>Alto</option>
                    <option>Medio</option>
                    <option>Bajo</option>
                </select>
            </div>

            <div>
                <label>Urgencia</label>
                <select name="urgencia">
                    <option>Alta</option>
                    <option>Media</option>
                    <option>Baja</option>
                </select>
            </div>

            <div>
                <label>Activo</label>
                <input type="checkbox" name="activo" value="1" checked>
            </div>

        </div>

        <div style="margin-top:25px;">
            <label>Descripción</label>
            <textarea name="descripcion"></textarea>
        </div>

        <div style="margin-top:25px;">
            <label>Solución propuesta</label>
            <textarea name="solucion_propuesta"></textarea>
        </div>

        <div style="margin-top:25px;">
            <label>Notas internas</label>
            <textarea name="notas_internas"></textarea>
        </div>

        <button class="btn-guardar">Guardar en catálogo</button>

    </form>

</div>

<!-- MODAL PARA NUEVA CATEGORÍA -->
<div id="modalCategoria">
    <div style="background:var(--card-bg); padding:25px; width:380px; border-radius:12px; box-shadow:0 4px 14px var(--shadow);">
        <h3 style="margin-top:0; margin-bottom:15px; color:var(--primary);">Nueva categoría</h3>

        <label>Nombre *</label>
        <input type="text" id="catNombre" style="margin-bottom:15px; width:100%;">

        <label>Descripción</label>
        <textarea id="catDesc" style="margin-bottom:20px; width:100%; height:80px;"></textarea>

        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button onclick="closeCategoriaModal()" 
                style="padding:10px 14px; background:#777; color:white; border:none; border-radius:8px;">
                Cancelar
            </button>

            <button onclick="guardarCategoria()" 
                style="padding:10px 14px; background:var(--primary); color:white; border:none; border-radius:8px;">
                Guardar
            </button>
        </div>
    </div>
</div>

<script>
function openCategoriaModal() {
    document.getElementById("modalCategoria").style.display = "flex";
}

function closeCategoriaModal() {
    document.getElementById("modalCategoria").style.display = "none";
}

function guardarCategoria() {
    const nombre = document.getElementById("catNombre").value.trim();
    const desc = document.getElementById("catDesc").value.trim();

    if (nombre === "") {
        alert("El nombre es obligatorio");
        return;
    }

    fetch("itil_categoria_guardar.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "nombre=" + encodeURIComponent(nombre) + "&descripcion=" + encodeURIComponent(desc)
    })
    .then(r => r.text())
    .then(resp => {
        if (resp === "OK") {
            location.reload();
        } else {
            alert("Error: " + resp);
        }
    });
}

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
