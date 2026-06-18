<?php
require "auth.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require "db.php";

$id = $_SESSION['user_id'];

// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Catálogo de Incidentes TI</title>

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
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: 0.3s;
}



/* MAIN */
.main {
    margin-left: 240px;
    padding: 25px;
    width: calc(100% - 240px);
    transition: margin-left 0.25s ease, width 0.25s ease;
}
.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* BUSCADOR */
.search-box {
    background: var(--card-bg);
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 3px 10px var(--shadow);
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
}
.search-box input {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
}

/* TABLA */
.table-box {
    background: var(--card-bg);
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 3px 10px var(--shadow);
}
.table-box table {
    width: 100%;
    border-collapse: collapse;
}
.table-box th {
    background: var(--primary);
    color: white;
    padding: 8px;
}
.table-box td {
    padding: 6px;
    border: 1px solid #ccc;
}
.table-box tr:hover {
    background: rgba(0,0,0,0.05);
    cursor: pointer;
}

/* Fila seleccionada */
.selected-row {
    background-color: #cce5ff !important;
    color: #000 !important;
}
body.dark .selected-row {
    background-color: #004b7a !important;
    color: #fff !important;
}

/* DETALLES */
.details-box {
    margin-top: 20px;
    background: var(--card-bg);
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px var(--shadow);
}
.details-box h3 {
    margin-top: 0;
}
.details-box textarea {
    width: 100%;
    height: 80px;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
    resize: none;
}
.copy-btn {
    margin-top: 5px;
    padding: 6px 12px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.copy-btn:hover { background: var(--primary-hover); }

/* INDICADORES */
.indicators {
    margin-top: 20px;
    display: flex;
    gap: 20px;
}
.indicator {
    flex: 1;
    padding: 15px;
    border-radius: 10px;
    background: var(--card-bg);
    box-shadow: 0 3px 10px var(--shadow);
    text-align: center;
}
.indicator span {
    font-size: 18px;
    font-weight: bold;
}
</style>
</head>
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<body>

<!-- SIDEBAR -->
<?php require "sidebar.php"; ?>

<!-- MAIN -->
<div class="main">
<?php require "topbar.php"; ?>
    <div class="search-box">
        <label for="search">Buscar:</label>
        <input type="text" id="search" placeholder="Escribe parte de cualquier palabra (ej: bit, memoria, vpn, licencia)">
    </div>

    <div class="table-box">
        <table id="tablaIncidentes">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Subcategoría</th>
                    <th>Descripción</th>
                    <th>Detalle</th>
                    <th>Solución</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="details-box" id="details" style="display:none;">
        <h3>Detalles del incidente</h3>

        <label>Descripción:</label>
        <textarea id="det_desc"></textarea>
        <button class="copy-btn" onclick="copyText('det_desc')">Copiar</button>
<br/>
        <label>Descripción detallada:</label>
        <textarea id="det_det"></textarea>
        <button class="copy-btn" onclick="copyText('det_det')">Copiar</button>
<br/>
        <label>Solución:</label>
        <textarea id="det_sol"></textarea>
        <button class="copy-btn" onclick="copyText('det_sol')">Copiar</button>

        <div class="indicators">
            <div class="indicator"><span id="det_pri"></span><br>Prioridad</div>
            <div class="indicator"><span id="det_imp"></span><br>Impacto</div>
            <div class="indicator"><span id="det_urg"></span><br>Urgencia</div>
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

function copyText(id) {
    const t = document.getElementById(id);
    t.select();
    document.execCommand("copy");
}

function cargarIncidentes(q = "") {
    fetch("buscar_incidentes.php?q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            const tbody = document.querySelector("#tablaIncidentes tbody");
            tbody.innerHTML = "";
            document.getElementById("details").style.display = "none";

            data.forEach(row => {
                const tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${row.categoria}</td>
                    <td>${row.subcategoria}</td>
                    <td>${row.desbreve}</td>
                    <td>${row.descdetallada}</td>
                    <td>${row.solucion}</td>
                `;

                tr.addEventListener("click", () => {
                    document.querySelectorAll("#tablaIncidentes tr").forEach(r => r.classList.remove("selected-row"));
                    tr.classList.add("selected-row");

                    document.getElementById("details").style.display = "block";
                    document.getElementById("det_desc").value = row.desbreve;
                    document.getElementById("det_det").value = row.descdetallada;
                    document.getElementById("det_sol").value = row.solucion;
                    document.getElementById("det_pri").innerText = row.prioridad;
                    document.getElementById("det_imp").innerText = row.impacto;
                    document.getElementById("det_urg").innerText = row.urgencia;
                });

                tbody.appendChild(tr);
            });
        });
}

document.addEventListener("DOMContentLoaded", () => {
    // Carga inicial de todos los incidentes
    cargarIncidentes("");

    // Búsqueda en tiempo real
    document.getElementById("search").addEventListener("keyup", function() {
        cargarIncidentes(this.value);
    });
});
</script>

</body>
</html>
