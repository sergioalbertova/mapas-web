<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
<title>Mapeo de Nodos</title>

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
    transition: 0.3s;
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
    opacity: 0;
    pointer-events: none;
    transition: 0.2s;
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
    transition: 0.25s ease;
}
.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* TOP BAR */
.top-bar {
    background: var(--card-bg);
    padding: 15px;
    display: flex;
    gap: 20px;
    align-items: center;
    border-radius: 10px;
    box-shadow: 0 3px 10px var(--shadow);
    margin-bottom: 20px;
}
.top-bar label {
    font-weight: 600;
    font-size: 14px;
}
.top-bar input,
.top-bar select {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
}
.top-bar button {
    padding: 8px 14px;
    cursor: pointer;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 6px;
    transition: 0.2s;
}
.top-bar button:hover { background: var(--primary-hover); }

/* PANEL DATOS Y TABLA */
.main-container {
    display: flex;
    gap: 20px;
}

.panel-datos {
    width: 260px;
    background: var(--card-bg);
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 3px 10px var(--shadow);
}
.panel-datos input {
    width: 100%;
    box-sizing: border-box;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
}

.tabla-box {
    flex: 1;
    background: var(--card-bg);
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 3px 10px var(--shadow);
}
.tabla-scroll {
    max-height: 350px;
    overflow-y: auto;
    border-radius: 6px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th {
    background: var(--primary);
    color: white;
    padding: 8px;
}
td {
    padding: 6px;
    border: 1px solid #ccc;
}

/* Fila seleccionada */
.fila-seleccionada {
    background-color: #cce5ff !important;
    color: #000 !important;
}
body.dark .fila-seleccionada {
    background-color: #004b7a !important;
    color: #ffffff !important;
}

/* MAPA */
#mapaContainer {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: 25px auto;
    border: 2px solid #7f8c8d;
    border-radius: 10px;
    box-shadow: 0 3px 10px var(--shadow);
}
#imgMapa {
    width: 100%;
    height: auto;
    display: block;
}
#marcador {
    position: absolute;
    width: 18px;
    height: 18px;
    background: red;
    border-radius: 50%;
    border: 2px solid white;
    transform: translate(-50%, -50%);
    pointer-events: none;
    z-index: 10;
    display: none;
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
        <a href="index.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            <span class="nav-text">Inicio</span>
        </a>
        <span class="tooltip">Inicio</span>
    </div>

    <div class="nav-item">
        <a href="calendario.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <span class="nav-text">Calendario</span>
        </a>
        <span class="tooltip">Calendario</span>
    </div>

   <div class="nav-item">
        <a href="incidentes.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 2v2h8v-2H8z"/></svg>
            <span class="nav-text">Incidentes TI</span>
        </a>
        <span class="tooltip">Incidentes TI</span>
    </div>
    
    <div class="nav-item">
        <a href="cambiar_password.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/></svg>
            <span class="nav-text">Cambiar contraseña</span>
        </a>
        <span class="tooltip">Cambiar contraseña</span>
    </div>

    <div class="nav-item">
        <a href="logout.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
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

<div class="main">

    <div class="top-bar">
        <label>Piso:</label>
        <select id="selectPiso">
            <option value="">Seleccione un piso</option>
            <?php
            $pisos = $pdo->query("SELECT idpiso, nombrepiso FROM pisos ORDER BY idpiso")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($pisos as $p) {
                echo "<option value='{$p['idpiso']}'>Piso {$p['nombrepiso']}</option>";
            }
            ?>
        </select>

        <label>Nodo:</label>
        <input type="text" id="inputNodo">
        <button id="btnBuscarNodo">Buscar nodo</button>

        <label>Usuario:</label>
        <input type="text" id="inputUsuario">
        <button id="btnBuscarUsuario">Buscar usuario</button>
    </div>

    <div class="main-container">

        <div class="panel-datos">
            <h3 style="margin-top:0;">Datos</h3>

            <label>Nodo:</label>
            <input type="text" id="datoNodo" readonly>

            <label>Ubicación:</label>
            <input type="text" id="datoUbicacion" readonly>

            <label>Piso:</label>
            <input type="text" id="datoPiso" readonly>

            <label>Switch:</label>
            <input type="text" id="datoSwitch" readonly>

            <label>Puerto:</label>
            <input type="text" id="datoPuerto" readonly>
        </div>

        <div class="tabla-box">
            <h3 style="margin-top:0;display:flex;align-items:center;gap:15px;">
                Listado
                <span style="font-size:14px;color:var(--subtext);">
                    🟢 Nodo con usuario &nbsp;&nbsp;
                    🟡 Nodo sin usuario &nbsp;&nbsp;
                    🔴 Ubicación libre
                </span>
            </h3>

            <div class="tabla-scroll">
                <table id="tablaUbicaciones">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Ubicación</th>
                            <th>Nodo</th>
                            <th>Usuario</th>
                            <th>Piso</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>

    <div id="mapaContainer">
        <img id="imgMapa" src="">
        <div id="marcador"></div>
    </div>

</div>

<script>
function toggleTheme() {
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
}
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}

function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

/* LÓGICA ORIGINAL */
let pisoActual = null;

const ajustesPorPiso = {
    1: { offsetX: 15, offsetY: -200 },
    2: { offsetX: -10, offsetY: -250 },
    3: { offsetX: 0, offsetY: 0 },
    4: { offsetX: 0, offsetY: 0 },
    5: { offsetX: 0, offsetY: 0 }
};

function colocarMarcador(cx, cy) {
    if (cx == null || cy == null) {
        marcador.style.display = "none";
        return;
    }

    const cont = mapaContainer.getBoundingClientRect();
    const img  = imgMapa.getBoundingClientRect();

    const ajustes = ajustesPorPiso[pisoActual] || { offsetX: 0, offsetY: 0 };

    const x = img.left - cont.left + (img.width  * cx) + ajustes.offsetX;
    const y = img.top  - cont.top  + (img.height * cy) + ajustes.offsetY;

    marcador.style.left = x + "px";
    marcador.style.top  = y + "px";
    marcador.style.display = "block";
}

document.addEventListener("DOMContentLoaded", () => {

    const selectPiso = document.getElementById("selectPiso");
    const imgMapa = document.getElementById("imgMapa");
    const mapaContainer = document.getElementById("mapaContainer");
    const marcador = document.getElementById("marcador");
    const tablaBody = document.querySelector("#tablaUbicaciones tbody");

    const datoNodo = document.getElementById("datoNodo");
    const datoUbicacion = document.getElementById("datoUbicacion");
    const datoPiso = document.getElementById("datoPiso");
    const datoSwitch = document.getElementById("datoSwitch");
    const datoPuerto = document.getElementById("datoPuerto");

    mapaContainer.onwheel = (e) => e.preventDefault();

    function limpiarResaltado() {
        tablaBody.querySelectorAll("tr").forEach(tr => tr.classList.remove("fila-seleccionada"));
    }

    function resaltarFilaPorNodo(nodo) {
        limpiarResaltado();
        const filas = tablaBody.querySelectorAll("tr");
        const target = Array.from(filas).find(tr => tr.dataset.nodo == nodo);
        if (target) {
            target.classList.add("fila-seleccionada");
            target.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }

    async function cargarSoloMapa(idpiso) {
        const resMapa = await fetch("cargarPiso.php?idpiso=" + idpiso);
        const dataMapa = await resMapa.json();
        if (dataMapa.status === "success") {
            imgMapa.src = dataMapa.imagen;
        }
    }

    async function cargarPisoCompleto(idpiso) {

        if (pisoActual == idpiso) return;

        pisoActual = idpiso;

        marcador.style.display = "none";

        if (!idpiso) {
            tablaBody.innerHTML = "";
            imgMapa.src = "";
            return;
        }

        const resMapa = await fetch("cargarPiso.php?idpiso=" + idpiso);
        const dataMapa = await resMapa.json();
        if (dataMapa.status === "success") {
            imgMapa.src = dataMapa.imagen;
        }

        const resLista = await fetch("listarPiso.php?piso=" + idpiso);
        const dataLista = await resLista.json();

        if (dataLista.status === "success") {
            tablaBody.innerHTML = "";
            dataLista.data.forEach(item => {
                const icono = item.nodo && item.usuario ? "🟢" : item.nodo ? "🟡" : "🔴";

                tablaBody.insertAdjacentHTML("beforeend", `
                    <tr 
                        data-nodo="${item.nodo ?? ''}" 
                        data-ubicacion="${item.ubicacion}" 
                        data-usuario="${item.usuario ?? ''}"
                        style="cursor:pointer"
                    >
                        <td class="icono">${icono}</td>
                        <td>${item.ubicacion}</td>
                        <td>${item.nodo ?? ""}</td>
                        <td>${item.usuario ?? ""}</td>
                        <td>${item.piso}</td>
                    </tr>
                `);
            });
        }
    }

    selectPiso.addEventListener("change", async function () {
        await cargarPisoCompleto(this.value);
    });

    document.getElementById("btnBuscarNodo").addEventListener("click", async () => {
        const nodo = document.getElementById("inputNodo").value.trim();
        if (!nodo) return;

        const res = await fetch(`buscarNodo.php?piso=${selectPiso.value}&nodo=${nodo}`);
        const data = await res.json();

        if (data.status === "success") {
            const reg = data.data;

            selectPiso.value = reg.piso;
            await cargarPisoCompleto(reg.piso);

            resaltarFilaPorNodo(reg.nodo);
            colocarMarcador(reg.cx_rel, reg.cy_rel);

            datoNodo.value = reg.nodo;
            datoUbicacion.value = reg.ubicacion;
            datoPiso.value = reg.piso;
            datoSwitch.value = reg.switch ?? "";
            datoPuerto.value = reg.puerto ?? "";
        }
    });

    document.getElementById("btnBuscarUsuario").addEventListener("click", async () => {
        const usuario = document.getElementById("inputUsuario").value.trim();
        if (!usuario) return;

        const res = await fetch("buscarUsuario.php?usuario=" + encodeURIComponent(usuario));
        const data = await res.json();

        if (data.status === "success") {

            tablaBody.innerHTML = "";

            data.data.forEach(item => {
                const icono = item.nodo && item.nomuser ? "🟢" : item.nodo ? "🟡" : "🔴";

                tablaBody.insertAdjacentHTML("beforeend", `
                    <tr 
                        data-nodo="${item.nodo ?? ''}" 
                        data-ubicacion="${item.ubicacion ?? ''}" 
                        data-usuario="${item.nomuser}"
                        style="cursor:pointer"
                    >
                        <td class="icono">${icono}</td>
                        <td>${item.ubicacion ?? ""}</td>
                        <td>${item.nodo ?? ""}</td>
                        <td>${item.nomuser}</td>
                        <td>${item.piso}</td>
                    </tr>
                `);
            });

            marcador.style.display = "none";
        }
    });

    tablaBody.addEventListener("click", async (e) => {
        const tr = e.target.closest("tr");
        if (!tr) return;

        const nodo = tr.dataset.nodo;
        const ubicacion = tr.dataset.ubicacion;
        const usuario = tr.dataset.usuario;
        const pisoFila = tr.querySelector("td:last-child").innerText.trim();

        limpiarResaltado();
        tr.classList.add("fila-seleccionada");

        if (usuario) {

            if (pisoFila) {
                selectPiso.value = pisoFila;
                await cargarSoloMapa(pisoFila);
            }

            if (nodo) {
                const res = await fetch(`buscarNodo.php?piso=${pisoFila}&nodo=${nodo}`);
                const data = await res.json();

                if (data.status === "success") {
                    const reg = data.data;

                    colocarMarcador(reg.cx_rel, reg.cy_rel);

                    datoNodo.value = reg.nodo;
                    datoUbicacion.value = reg.ubicacion;
                    datoPiso.value = reg.piso;
                    datoSwitch.value = reg.switch ?? "";
                    datoPuerto.value = reg.puerto ?? "";
                }
            } else {
                datoNodo.value = "";
                datoUbicacion.value = ubicacion;
                datoPiso.value = pisoFila;
                datoSwitch.value = "";
                datoPuerto.value = "";
                marcador.style.display = "none";
            }

            return;
        }

        if (nodo) {

            const res = await fetch(`buscarNodo.php?piso=${selectPiso.value}&nodo=${nodo}`);
            const data = await res.json();

            if (data.status === "success") {
                const reg = data.data;

                colocarMarcador(reg.cx_rel, reg.cy_rel);

                datoNodo.value = reg.nodo;
                datoUbicacion.value = reg.ubicacion;
                datoPiso.value = reg.piso;
                datoSwitch.value = reg.switch ?? "";
                datoPuerto.value = reg.puerto ?? "";
            }
            return;
        }
    });

});
</script>

</body>
</html>
