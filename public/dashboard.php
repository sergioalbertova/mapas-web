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

        .mapa-box {
            margin: 20px;
            text-align: center;
        }

        #imgMapa {
            max-width: 90%;
            border: 2px solid #7f8c8d;
            border-radius: 8px;
        }

        .icono {
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>

<body>

<h2 id="tituloMapa">MAPA DE NODOS - 0</h2>

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
    <img id="imgMapa" src="">
</div>

<script>
let contador = 0;

document.addEventListener("DOMContentLoaded", () => {

    const selectPiso   = document.getElementById("selectPiso");
    const imgMapa      = document.getElementById("imgMapa");
    const tablaBody    = document.querySelector("#tablaUbicaciones tbody");

    const tituloMapa = document.getElementById("tituloMapa");

    function actualizarTitulo() {
        contador++;
        tituloMapa.textContent = "MAPA DE NODOS - " + contador;
    }

    function iconoEstado(nodo, usuario) {
        if (nodo && usuario) return "🟢";
        if (nodo && !usuario) return "🟡";
        return "🔴";
    }

    async function cargarPisoCompleto(idpiso) {
        actualizarTitulo();

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

                const icono = iconoEstado(item.nodo, item.usuario);

                tablaBody.insertAdjacentHTML("beforeend", `
                    <tr 
                        data-nodo="${item.nodo ?? ''}" 
                        data-ubicacion="${item.ubicacion}" 
                        data-usuario="${item.usuario ?? ''}"
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

    function resaltarFilaPorNodo(nodo) {
        const filas = tablaBody.querySelectorAll("tr");
        filas.forEach(tr => tr.style.outline = "");

        const target = Array.from(filas).find(tr => tr.dataset.nodo == nodo);

        if (target) {
            target.style.outline = "3px solid #f1c40f";
            target.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }

    function resaltarFilaPorUbicacion(ubicacion) {
        const filas = tablaBody.querySelectorAll("tr");
        filas.forEach(tr => tr.style.outline = "");

        const target = Array.from(filas).find(tr => tr.dataset.ubicacion == ubicacion);

        if (target) {
            target.style.outline = "3px solid #3498db";
            target.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }

    selectPiso.addEventListener("change", async function () {
        const idpiso = this.value;
        await cargarPisoCompleto(idpiso);
    });

    document.getElementById("btnBuscarNodo").addEventListener("click", async () => {
        const nodo = document.getElementById("inputNodo").value.trim();
        if (!nodo) return;

        const res = await fetch("buscarNodo.php?nodo=" + encodeURIComponent(nodo));
        const data = await res.json();

        if (data.status === "success" && data.data) {
            const reg = data.data;

            selectPiso.value = reg.piso;
            await cargarPisoCompleto(reg.piso);

            resaltarFilaPorNodo(reg.nodo);
        } else {
            alert("Nodo no encontrado");
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
                const icono = iconoEstado(item.nodo, item.nomuser);

                tablaBody.insertAdjacentHTML("beforeend", `
                    <tr 
                        data-nodo="${item.nodo ?? ''}" 
                        data-ubicacion="${item.ubicacion ?? ''}" 
                        data-usuario="${item.nomuser}"
                    >
                        <td class="icono">${icono}</td>
                        <td>${item.ubicacion ?? ""}</td>
                        <td>${item.nodo ?? ""}</td>
                        <td>${item.nomuser}</td>
                        <td>${item.piso}</td>
                    </tr>
                `);
            });

        } else {
            alert("No se encontraron usuarios");
        }
    });

    tablaBody.addEventListener("click", async (e) => {
        const tr = e.target.closest("tr");
        if (!tr) return;

        const nodo = tr.dataset.nodo;
        const ubicacion = tr.dataset.ubicacion;
        const usuario = tr.dataset.usuario;

        if (nodo) {
            const res = await fetch("buscarNodo.php?nodo=" + nodo);
            const data = await res.json();

            if (data.status === "success") {
                const reg = data.data;

                selectPiso.value = reg.piso;
                await cargarPisoCompleto(reg.piso);

                resaltarFilaPorNodo(reg.nodo);

                document.getElementById("datoNodo").value = reg.nodo;
                document.getElementById("datoUbicacion").value = reg.ubicacion;
                document.getElementById("datoPiso").value = reg.piso;
                document.getElementById("datoSwitch").value = reg.switch ?? "";
                document.getElementById("datoPuerto").value = reg.puerto ?? "";
            }

            return;
        }

        if (usuario) {
            const res = await fetch("buscarUsuario.php?usuario=" + encodeURIComponent(usuario));
            const data = await res.json();

            if (data.status === "success" && data.data.length > 0) {
                const reg = data.data[0];

                selectPiso.value = reg.piso;
                await cargarPisoCompleto(reg.piso);

                resaltarFilaPorUbicacion(reg.ubicacion);

                document.getElementById("datoNodo").value = reg.nodo ?? "";
                document.getElementById("datoUbicacion").value = reg.ubicacion;
                document.getElementById("datoPiso").value = reg.piso;
                document.getElementById("datoSwitch").value = "";
                document.getElementById("datoPuerto").value = "";
            }
        }
    });

});
</script>

</body>
</html>
