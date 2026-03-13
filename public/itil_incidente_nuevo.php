<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

// ===============================
// OBTENER TÉCNICO LOGUEADO
// ===============================
$id_tecnico = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id_tecnico]);
$tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

$nombreTecnico = $tecnico ? $tecnico['nombre'] : "Usuario no encontrado";

// ===============================
// OBTENER CATÁLOGO DE APOYOS
// ===============================
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

/* ====== SIDEBAR ====== */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    padding: 20px 15px;
    position: fixed;
    box-shadow: 4px 0 20px var(--shadow);
    transition: width 0.25s ease;
}
.sidebar.collapsed { width: 70px; }

.nav-item {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}
.nav-item:hover { background: var(--sidebar-hover); }

.nav-item svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
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
}
.sidebar.collapsed ~ .itil-topbar { left: 70px; }

.itil-topbar a {
    text-decoration: none;
    color: var(--text);
    font-weight: bold;
    padding: 8px 12px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.itil-topbar a:hover { background: var(--sidebar-hover); }

/* ====== MAIN ====== */
.main {
    width: 100%;
    max-width: 1100px;
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
    <div class="nav-item" onclick="toggleSidebar()">Menú</div>
    <h2>Panel</h2>

    <div class="nav-item"><a href="index.php">Inicio</a></div>
    <div class="nav-item"><a href="incidentes.php">Incidentes TI</a></div>
    <div class="nav-item"><a href="dashboard.php">Mapeo de nodos</a></div>
    <div class="nav-item"><a href="calendario.php">Calendario</a></div>
    <div class="nav-item"><a href="cambiar_password.php">Cambiar contraseña</a></div>
    <div class="nav-item"><a href="logout.php">Cerrar sesión</a></div>
    <div class="nav-item" onclick="toggleTheme()">Tema oscuro</div>
</div>

<!-- ====== TOPBAR ITIL ====== -->
<div class="itil-topbar">
    <a href="itil_incidentes.php">Incidentes</a>
    <a href="itil_incidente_nuevo.php">Nuevo incidente</a>
    <a href="itil_problemas.php">Problemas</a>
    <a href="itil_cambios.php">Cambios</a>
    <a href="itil_solicitudes.php">Solicitudes</a>
    <a href="itil_sla.php">SLA</a>
    <a href="itil_estadisticas.php">Estadísticas</a>
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
