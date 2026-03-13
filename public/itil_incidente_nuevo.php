<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

/* ============================================================
   OBTENER TÉCNICO LOGUEADO (usuario + nombre)
   ============================================================ */
$id_tecnico = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id_tecnico]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

if ($tecnico) {
    $nombreTecnico = $tecnico['usuario'] . " - " . $tecnico['nombre'];
} else {
    $nombreTecnico = "Usuario no encontrado (ID $id_tecnico)";
}

/* ============================================================
   OBTENER CATÁLOGO DE APOYOS
   ============================================================ */
$stmt2 = $pdo->query("SELECT idapoyo, tituloincidente, descripcion FROM catapoyo ORDER BY tituloincidente");
$catalogo = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo incidente ITIL</title>

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

/* ====== SIDEBAR (idéntico al de itil_incidentes.php) ====== */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    padding: 20px 15px;
    position: fixed;
    box-shadow: 4px 0 20px var(--shadow);
    transition: width 0.25s ease;
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
    padding: 0 25px;
    gap: 25px;
    box-shadow: 0 2px 8px var(--shadow);
    z-index: 1500;
    transition: left 0.25s ease;
}
.sidebar.collapsed ~ .itil-topbar {
    left: 70px;
}

.itil-topbar a {
    text-decoration: none;
    color: var(--text);
    font-weight: bold;
    padding: 8px 12px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.2s;
}
.itil-topbar a:hover {
    background: var(--sidebar-hover);
}

.itil-topbar svg {
    width: 18px;
    height: 18px;
    fill: var(--text);
}

/* ====== MAIN ====== */
.main {
    width: 100%;
    max-width: 950px;
    margin: 95px auto 0 auto;
    padding: 25px;
}

/* ====== FORM ====== */
.form-box {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 10px var(--shadow);
}
label { font-weight: bold; margin-top: 15px; display: block; }
input, select, textarea {
    width: 100%; padding: 10px; margin-top: 6px;
    border-radius: 6px; border: 1px solid var(--sidebar-hover);
    background: var(--bg); color: var(--text);
}
textarea { height: 120px; resize: vertical; }

/* ====== AUTOCOMPLETE ====== */
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
</style>
</head>

<body>

<!-- ====== SIDEBAR ====== -->
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
    </div>

    <div class="nav-item">
        <a href="incidentes.php">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 2v2h8v-2H8z"/></svg>
            <span class="nav-text">Incidentes TI</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="dashboard.php">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            <span class="nav-text">Mapeo de nodos</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="calendario.php">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <span class="nav-text">Calendario</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="cambiar_password.php">
            <svg><path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/></svg>
            <span class="nav-text">Cambiar contraseña</span>
        </a>
    </div>

    <div class="nav-item">
        <a href="logout.php">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm2-10H8v2h10v14H8v2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
            <span class="nav-text">Cerrar sesión</span>
        </a>
    </div>

    <div class="nav-item" onclick="toggleTheme()">
        <svg><path d="M12 2a9 9 0 100 18 9 9 0 010-18z"/></svg>
        <span class="nav-text">Tema oscuro</span>
    </div>

</div>

<!-- ====== TOPBAR ITIL ====== -->
<div class="itil-topbar">
    <a href="itil_incidentes.php">
        <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
        Incidentes
    </a>

    <a href="itil_incidente_nuevo.php">
        <svg><path d="M12 5v14m7-7H5"/></svg>
        Nuevo incidente
    </a>

    <a href="itil_problemas.php">
        <svg><path d="M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
        Problemas
    </a>

    <a href="itil_cambios.php">
        <svg><path d="M4 4h16v4H4zm0 6h16v10H4z"/></svg>
        Cambios
    </a>

    <a href="itil_solicitudes.php">
        <svg><path d="M3 6h18v12H3z"/></svg>
        Solicitudes
    </a>

    <a href="itil_sla.php">
        <svg><path d="M12 2v20m10-10H2"/></svg>
        SLA
    </a>

    <a href="itil_estadisticas.php">
        <svg><path d="M4 20V10m6 10V4m6 16v-6m6 6V8"/></svg>
        Estadísticas
    </a>
</div>

<!-- ====== MAIN ====== -->
<div class="main">
    <h2>Registrar nuevo incidente</h2>

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
            <select id="titulo_select">
                <option value="">Seleccione...</option>
                <?php foreach ($catalogo as $c): ?>
                    <option value="<?= htmlspecialchars($c['tituloincidente']) ?>"
                            data-desc="<?= htmlspecialchars($c['descripcion']) ?>">
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
                    <select name="prioridad" required>
                        <option value="Alta">Alta</option>
                        <option value="Media">Media</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>

                <div>
                    <label>Impacto</label>
                    <select name="impacto" required>
                        <option value="Alto">Alto</option>
                        <option value="Medio">Medio</option>
                        <option value="Bajo">Bajo</option>
                    </select>
                </div>

                <div>
                    <label>Urgencia</label>
                    <select name="urgencia" required>
                        <option value="Alta">Alta</option>
                        <option value="Media">Media</option>
                        <option value="Baja">Baja</option>
                    </select>
                </div>
            </div>

            <!-- TECNICO -->
            <label>Técnico asignado</label>
            <input type="text" value="<?= htmlspecialchars($nombreTecnico) ?>" readonly>
            <input type="hidden" name="tecnico_asignado" value="<?= $id_tecnico ?>">

            <button type="submit">Registrar incidente</button>
        </form>
    </div>
</div>

<script>
/* ====== SIDEBAR ====== */
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

/* ====== TEMA OSCURO ====== */
function toggleTheme() {
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
}
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}

/* ====== AUTOCOMPLETADO USUARIO ====== */
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

/* ====== CATÁLOGO: LLENAR DESCRIPCIÓN ====== */
const selectTitulo = document.getElementById("titulo_select");
const txtDescripcion = document.getElementById("descripcion");

selectTitulo.addEventListener("change", function() {
    const opt = this.options[this.selectedIndex];
    const desc = opt.getAttribute("data-desc") || "";
    txtDescripcion.value = desc;
});
</script>

</body>
</html>
