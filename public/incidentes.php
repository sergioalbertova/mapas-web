<?php
require "session_config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require "db.php";
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

/* SIDEBAR corporativo */
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
    display: flex;
    align-items: center;
    gap: 12px;
    color: inherit;
    text-decoration: none;
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

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="nav-item" onclick="toggleSidebar()">
        <svg><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        <span class="nav-text">Menú</span>
        <span class="tooltip">Colapsar menú</span>
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
        <a href="calendario.php">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <span class="nav-text">Calendario</span>
        </a>
        <span class="tooltip">Calendario</span>
    </div>

    <div class="nav-item">
        <a href="dashboard.php">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            <span class="nav-text">Mapeo de nodos</span>
        </a>
        <span class="tooltip">Mapeo de nodos</span>
    </div>

    <div class="nav-item">
        <a href="incidentes.php">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 2v2h8v-2H8z"/></svg>
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

    <div class="nav-item" onclick="toggleTheme()">
        <svg><path d="M12 2a9 9 0 100 18 9 9 0 010-18z"/></svg>
        <span class="nav-text">Tema oscuro</span>
        <span class="tooltip">Tema oscuro</span>
    </div>
</div>

<!-- MAIN -->
<div class="main">

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
