<?php
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

        /* Barra superior */
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

        /* Contenedor principal */
        .main-container {
            display: flex;
            padding: 20px;
            gap: 20px;
        }

        /* Panel de datos */
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

        /* Tabla */
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

        /* Mapa */
        .mapa-box {
            margin: 20px;
            text-align: center;
        }

        #imgMapa {
            max-width: 90%;
            border: 2px solid #7f8c8d;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<h2 id="tituloMapa">MAPA DE NODOS - 0</h2>

<!-- BARRA SUPERIOR -->
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

<!-- PANEL IZQUIERDO + TABLA -->
<div class="main-container">

    <!-- Panel de datos -->
    <div class="panel-datos">
        <h3>Datos</h3>
        <div style="width: 15px; height: 15px; background: green; border-radius: 50%; margin-bottom: 10px;"></div>

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

    <!-- Tabla -->
    <div class="tabla-box">
        <h3>Listado</h3>

        <div class="tabla-scroll">
            <table id="tablaUbicaciones">
                <thead>
                    <tr>
                        <th>Ubicación</th>
                        <th>Nodo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

<!-- MAPA ABAJO -->
<div class="mapa-box">
    <img id="imgMapa" src="">
</div>

<script>
let contador = 0;

document.addEventListener("DOMContentLoaded", () => {

    const selectPiso   = document.getElementById("selectPiso");
    const imgMapa      = document.getElementById("imgMapa");
    const tablaBody    = document.querySelector("#tablaUbicaciones tbody");

    const datoNodo      = document.getElementById("datoNodo");
    const datoUbicacion = document.getElementById("datoUbicacion");
    const datoPiso      = document.getElementById("datoPiso");
    const datoSwitch    = document.getElementById("datoSwitch");
    const datoPuerto    = document.getElementById("datoPuerto");

    const tituloMapa = document.getElementById("tituloMapa");

    function actualizarTitulo() {
        contador++;
        tituloMapa.textContent = "MAPA DE NODOS - " + contador;
    }

    async function cargarPisoCompleto(idpiso) {
        actualizarTitulo();

        if (!idpiso) {
            tablaBody.innerHTML = "";
            imgMapa.src = "";
            return;
        }

        // Cargar mapa
        const resMapa = await fetch("cargarPiso.php?idpiso=" + idpiso);
        const dataMapa = await resMapa.json();
        if (dataMapa.status === "success") {
            imgMapa.src = dataMapa.imagen;
        }

        // Cargar listado
        const resLista = await fetch("listarPiso.php?piso=" + idpiso);
        const dataLista = await resLista.json();

        if (dataLista.status === "success") {
            tablaBody.innerHTML = "";
            dataLista.data.forEach(item => {
                tablaBody.insertAdjacentHTML("beforeend", `
                    <tr data-ubicacion="${item.ubicacion}" data-nodo="${item.nodo ?? ""}">
                        <td>${item.ubicacion}</td>
                        <td>${item.nodo ?? ""}</td>
                        <td>${item.usuario ?? ""}</td>
                    </tr>
                `);
            });
        }
    }

    function llenarPanelDatos(reg) {
        datoNodo.value      = reg.nodo ?? "";
        datoUbicacion.value = reg.ubicacion ?? "";
        datoPiso.value      = reg.piso ?? "";
        datoSwitch.value    = reg.switch ?? "";
        datoPuerto.value    = reg.puerto ?? "";
    }

    function resaltarFilaPorNodo(nodo) {
        const filas = tablaBody.querySelectorAll("tr");
        filas.forEach(tr => tr.style.background = "");
        const target = Array.from(filas).find(tr => tr.dataset.nodo == nodo);
        if (target) {
            target.style.background = "#ffeaa7";
            target.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }

    // Cambio de piso
    selectPiso.addEventListener("change", async function () {
        const idpiso = this.value;
        await cargarPisoCompleto(idpiso);
    });

    // Buscar nodo
    document.getElementById("btnBuscarNodo").addEventListener("click", async () => {
        const nodo = document.getElementById("inputNodo").value.trim();
        if (!nodo) return;

        const res = await fetch("buscarNodo.php?nodo=" + encodeURIComponent(nodo));
        const data = await res.json();

        if (data.status === "success" && data.data) {
            const reg = data.data;

            selectPiso.value = reg.piso;
            await cargarPisoCompleto(reg.piso);

            llenarPanelDatos(reg);
            resaltarFilaPorNodo(reg.nodo);
        } else {
            alert("Nodo no encontrado");
        }
    });

    // Buscar usuario (modo lista)
document.getElementById("btnBuscarUsuario").addEventListener("click", async () => {
    const usuario = document.getElementById("inputUsuario").value.trim();
    if (!usuario) return;

    try {
        const res = await fetch("buscarUsuario.php?usuario=" + encodeURIComponent(usuario));
        const data = await res.json();

        if (data.status === "success") {

            // Limpiar tabla
            tablaBody.innerHTML = "";

            // Llenar tabla con coincidencias
            data.data.forEach(item => {
                tablaBody.insertAdjacentHTML("beforeend", `
                    <tr>
                        <td>${item.ubicacion ?? ""}</td>
                        <td></td>
                        <td>${item.nomuser}</td>
                    </tr>
                `);
            });

            // No tocar mapa
            // No cambiar piso
            // No llenar panel de datos

        } else {
            alert("No se encontraron usuarios");
        }

    } catch (e) {
        console.error("Error en buscar usuario:", e);
    }
});


});
</script>

</body>
</html>
