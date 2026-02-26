<?php
// Configuración necesaria para Render (HTTPS)
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', '1');

session_start();
require "db.php";

// Validación correcta de sesión
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mapa de Nodos</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }

    #contenedor {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }

    #panelDatos {
        width: 250px;
        border: 1px solid #ccc;
        padding: 10px;
        background: #f8f8f8;
    }

    #tablaPiso {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    #tablaPiso th, #tablaPiso td {
        border: 1px solid #ccc;
        padding: 4px;
        text-align: left;
    }

    #tablaPiso tr:hover {
        background: #e0e0e0;
        cursor: pointer;
    }

    #mapaContenedor {
        position: relative;
        width: 100%;
        max-width: 900px;
        border: 1px solid #ccc;
    }

    #mapa {
        width: 100%;
        display: block;
    }

    #marcador {
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: red;
        border: 2px solid white;
        transform: translate(-50%, -50%);
        display: none;
    }

    .busqueda {
        margin-top: 10px;
        margin-bottom: 10px;
    }
</style>
</head>
<body>

<h2>Mapa de Nodos</h2>

<!-- SELECT DE PISOS -->
<label for="selectPiso">Seleccionar piso:</label>
<select id="selectPiso">
    <option value="">Seleccione un piso</option>
    <?php
    $pisos = $pdo->query("SELECT idpiso, nombrepiso FROM pisos ORDER BY idpiso")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($pisos as $p) {
        echo "<option value='{$p['idpiso']}'>Piso {$p['nombrepiso']}</option>";
    }
    ?>
</select>

<!-- BUSQUEDAS -->
<div class="busqueda">
    <input type="number" id="inputNodo" placeholder="Buscar nodo">
    <button onclick="buscarNodo()">Buscar nodo</button>

    <input type="text" id="inputUsuario" placeholder="Buscar usuario">
    <button onclick="buscarUsuario()">Buscar usuario</button>
</div>

<!-- CONTENEDOR PRINCIPAL -->
<div id="contenedor">

    <!-- PANEL IZQUIERDO -->
    <div id="panelDatos">
        <h3>Datos del nodo</h3>
        <p id="datoNodo"></p>
        <p id="datoUbicacion"></p>
        <p id="datoPiso"></p>
        <p id="datoSwitch"></p>
        <p id="datoPuerto"></p>
        <p id="datoUsuario"></p>
    </div>

    <!-- TABLA Y MAPA -->
    <div style="flex:1; display:flex; flex-direction:column; gap:10px;">

        <!-- TABLA DEL PISO -->
        <table id="tablaPiso">
            <thead>
                <tr>
                    <th>Ubicación</th>
                    <th>Piso</th>
                    <th>Nodo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- MAPA DEL PISO -->
        <div id="mapaContenedor">
            <img id="mapa" src="">
            <div id="marcador"></div>
        </div>

    </div>

</div>

<!-- SCRIPT -->
<script>
const selectPiso = document.getElementById("selectPiso");
const tablaPisoBody = document.querySelector("#tablaPiso tbody");
const mapa = document.getElementById("mapa");
const marcador = document.getElementById("marcador");

// Datos panel izquierdo
const datoNodo = document.getElementById("datoNodo");
const datoUbicacion = document.getElementById("datoUbicacion");
const datoPiso = document.getElementById("datoPiso");
const datoSwitch = document.getElementById("datoSwitch");
const datoPuerto = document.getElementById("datoPuerto");
const datoUsuario = document.getElementById("datoUsuario");

// Cambio de piso: cargar imagen + listar ubicaciones
selectPiso.addEventListener("change", async function () {
    const idpiso = this.value;

    limpiarPanel();
    limpiarTabla();
    ocultarMarcador();

    if (idpiso === "") {
        mapa.src = "";
        return;
    }

    // 1) Cargar imagen del piso
    const resMapa = await fetch("cargarPiso.php?idpiso=" + idpiso);
    const dataMapa = await resMapa.json();

    if (dataMapa.status === "success") {
        mapa.src = dataMapa.imagen;
    } else {
        alert(dataMapa.message);
        return;
    }

    // 2) Listar ubicaciones del piso
    const resLista = await fetch("listarPiso.php?piso=" + idpiso);
    const dataLista = await resLista.json();

    if (dataLista.status === "success") {
        llenarTablaPiso(dataLista.data);
    } else {
        alert(dataLista.message);
    }
});

// Llena la tabla con las ubicaciones del piso
function llenarTablaPiso(filas) {
    limpiarTabla();

    filas.forEach(f => {
        const tr = document.createElement("tr");

        tr.innerinnerHTML = `
            <td>${f.ubicacion}</td>
            <td>${f.piso}</td>
            <td>${f.nodo ?? ""}</td>
            <td>${f.usuario ?? ""}</td>
        `;

        tr.dataset.ubicacion = f.ubicacion;
        tr.dataset.piso = f.piso;
        tr.dataset.cxRel = f.cx_rel;
        tr.dataset.cyRel = f.cy_rel;
        tr.dataset.nodo = f.nodo ?? "";
        tr.dataset.usuario = f.usuario ?? "";

        tr.addEventListener("click", () => {
            manejarClickFila(tr);
        });

        tablaPisoBody.appendChild(tr);
    });
}

function manejarClickFila(tr) {
    const piso = tr.dataset.piso;
    const ubicacion = tr.dataset.ubicacion;
    const nodo = tr.dataset.nodo;
    const usuario = tr.dataset.usuario;
    const cxRel = parseFloat(tr.dataset.cxRel);
    const cyRel = parseFloat(tr.dataset.cyRel);

    datoNodo.textContent = "Nodo: " + (nodo || "(sin nodo)");
    datoUbicacion.textContent = "Ubicación: " + ubicacion;
    datoPiso.textContent = "Piso: " + piso;
    datoUsuario.textContent = "Usuario: " + (usuario || "(sin usuario)");
    datoSwitch.textContent = "Switch: ";
    datoPuerto.textContent = "Puerto: ";

    dibujarMarcador(cxRel, cyRel);
}

function dibujarMarcador(cxRel, cyRel) {
    const rect = mapa.getBoundingClientRect();
    const x = cxRel * rect.width;
    const y = cyRel * rect.height;

    marcador.style.left = x + "px";
    marcador.style.top = y + "px";
    marcador.style.display = "block";
}

function limpiarTabla() {
    tablaPisoBody.innerHTML = "";
}

function limpiarPanel() {
    datoNodo.textContent = "";
    datoUbicacion.textContent = "";
    datoPiso.textContent = "";
    datoSwitch.textContent = "";
    datoPuerto.textContent = "";
    datoUsuario.textContent = "";
}

function ocultarMarcador() {
    marcador.style.display = "none";
}

/* ===========================
   BUSCAR NODO
=========================== */
async function buscarNodo() {
    const nodo = document.getElementById("inputNodo").value.trim();
    if (nodo === "") return alert("Ingrese un nodo");

    const res = await fetch("buscarNodo.php?nodo=" + nodo);
    const data = await res.json();

    if (data.status !== "success") {
        alert(data.message);
        return;
    }

    const d = data.data;

    // Cambiar piso automáticamente
    selectPiso.value = d.piso;
    selectPiso.dispatchEvent(new Event("change"));

    // Llenar panel
    datoNodo.textContent = "Nodo: " + d.nodo;
    datoUbicacion.textContent = "Ubicación: " + d.ubicacion;
    datoPiso.textContent = "Piso: " + d.piso;
    datoSwitch.textContent = "Switch: " + d.switch;
    datoPuerto.textContent = "Puerto: " + d.puerto;
    datoUsuario.textContent = "Usuario: " + (d.usuario ?? "(sin usuario)");

    // Dibujar marcador
    setTimeout(() => {
        dibujarMarcador(d.cx_rel, d.cy_rel);
    }, 500);
}

/* ===========================
   BUSCAR USUARIO
=========================== */
async function buscarUsuario() {
    const usuario = document.getElementById("inputUsuario").value.trim();
    if (usuario === "") return alert("Ingrese un usuario");

    const res = await fetch("buscarUsuario.php?usuario=" + usuario);
    const data = await res.json();

    if (data.status !== "success") {
        alert(data.message);
        return;
    }

    const d = data.data;

    // Cambiar piso automáticamente
    selectPiso.value = d.piso;
    selectPiso.dispatchEvent(new Event("change"));

    // Llenar panel
    datoNodo.textContent = "Nodo: " + (d.nodo ?? "(sin nodo)");
    datoUbicacion.textContent = "Ubicación: " + d.ubicacion;
    datoPiso.textContent = "Piso: " + d.piso;
    datoSwitch.textContent = "Switch: " + (d.switch ?? "");
    datoPuerto.textContent = "Puerto: " + (d.puerto ?? "");
    datoUsuario.textContent = "Usuario: " + d.usuario;

    // Dibujar marcador
    setTimeout(() => {
        dibujarMarcador(d.cx_rel, d.cy_rel);
    }, 500);
}
</script>

</body>
</html>
