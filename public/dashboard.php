<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);
require "db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Nodos</title>

    <style>
        body {
            font-family: Arial;
            margin: 0;
            padding: 0;
            background: #f4f6f9;
        }

        h2 {
            margin: 0;
            padding: 20px;
            background: #2c3e50;
            color: white;
        }

        .top-bar {
            background: #ecf0f1;
            padding: 15px;
            display: flex;
            gap: 20px;
            align-items: center;
            border-bottom: 2px solid #bdc3c7;
        }

        .top-bar input, .top-bar select {
            padding: 6px;
        }

        .top-bar button {
            padding: 6px 10px;
            cursor: pointer;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .main-container {
            display: flex;
            padding: 20px;
            gap: 20px;
        }

        .panel-datos {
            width: 260px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .panel-datos input {
            width: 100%;
            margin-bottom: 10px;
            padding: 6px;
        }

        .tabla-box {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .tabla-scroll {
            max-height: 350px;
            overflow-y: auto;
            border: 1px solid #ccc;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #eee;
        }

        .icono {
            font-size: 18px;
            text-align: center;
        }

        .fila-seleccionada {
            background-color: #cce5ff !important;
            font-weight: bold;
        }

        #mapaContainer {
            position: relative;
            width: 100%;
            max-width: 1200px;
            margin: auto;
            overflow: hidden;
            border: 2px solid #7f8c8d;
            border-radius: 8px;
        }

        #imgMapa {
            width: 100%;
            height: auto;
            display: block;
            transform-origin: center center;
            cursor: grab;
            position: relative;
            z-index: 1;
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

<h2 id="tituloMapa">MAPA DE NODOS</h2>

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
        <h3>Datos</h3>

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
        <h3>Listado</h3>

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

<div class="mapa-box">
    <div id="mapaContainer">
        <img id="imgMapa" src="">
        <div id="marcador"></div>
    </div>
</div>

<script>
let pisoActual = null;
let zoom = 1, posX = 0, posY = 0, dragging = false, lastX = 0, lastY = 0;

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

    function limpiarResaltado() {
        const filas = tablaBody.querySelectorAll("tr");
        filas.forEach(tr => tr.classList.remove("fila-seleccionada"));
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

    function aplicarTransform() {
        imgMapa.style.transform = `translate(${posX}px, ${posY}px) scale(${zoom})`;
    }

    function colocarMarcador(cx, cy) {
        if (!cx || !cy) {
            marcador.style.display = "none";
            return;
        }

        const cont = mapaContainer.getBoundingClientRect();
        const img = imgMapa.getBoundingClientRect();

        const x = img.left - cont.left + img.width * cx;
        const y = img.top - cont.top + img.height * cy;

        marcador.style.left = x + "px";
        marcador.style.top = y + "px";
        marcador.style.display = "block";
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

        zoom = 1;
        posX = 0;
        posY = 0;
        aplicarTransform();
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

    mapaContainer.addEventListener("wheel", (e) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        zoom = Math.min(Math.max(0.5, zoom + delta), 3);
        aplicarTransform();
    });

    imgMapa.addEventListener("mousedown", (e) => {
        dragging = true;
        lastX = e.clientX;
        lastY = e.clientY;
        imgMapa.style.cursor = "grabbing";
    });

    document.addEventListener("mouseup", () => {
        dragging = false;
        imgMapa.style.cursor = "grab";
    });

    document.addEventListener("mousemove", (e) => {
        if (!dragging) return;

        posX += (e.clientX - lastX);
        posY += (e.clientY - lastY);

        lastX = e.clientX;
        lastY = e.clientY;

        aplicarTransform();
    });

});
</script>

</body>
</html>
