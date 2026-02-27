<?php
require "db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Nodos</title>

    <style>
        body { font-family: Arial; margin: 20px; }

        /* Panel izquierdo */
        .panel-datos input {
            width: 100%;
            margin-bottom: 8px;
            padding: 5px;
        }

        /* Tabla con scroll */
        .tabla-scroll {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        /* Layout principal */
        .contenedor {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .panel-datos {
            width: 250px;
            border: 1px solid #ccc;
            padding: 10px;
        }

        .panel-busqueda {
            width: 250px;
        }

        #imgMapa {
            max-width: 100%;
            border: 1px solid #ccc;
        }
    </style>
</head>

<body>

<h2>Mapa de Nodos</h2>

<div class="contenedor">

    <!-- Panel izquierdo -->
    <div class="panel-datos">
        <h3>Datos:</h3>
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

    <!-- Mapa al centro -->
    <div style="flex: 1; text-align: center;">
        <img id="imgMapa" src="">
    </div>

    <!-- Buscadores a la derecha -->
    <div class="panel-busqueda">
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

        <br><br>

        <label>Nodo:</label>
        <input type="text" id="inputNodo">
        <button id="btnBuscarNodo">Buscar nodo</button>

        <br><br>

        <label>Usuario:</label>
        <input type="text" id="inputUsuario">
        <button id="btnBuscarUsuario">Buscar usuario</button>
    </div>

</div>

<!-- Tabla abajo -->
<div style="margin-top: 20px;">
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

<script>
document.addEventListener("DOMContentLoaded", () => {

    const selectPiso = document.getElementById("selectPiso");
    const imgMapa = document.getElementById("imgMapa");
    const tablaBody = document.querySelector("#tablaUbicaciones tbody");

    selectPiso.addEventListener("change", async function () {

        const idpiso = this.value;

        if (!idpiso) {
            tablaBody.innerHTML = "";
            imgMapa.src = "";
            return;
        }

        // Cargar mapa
        try {
            const resMapa = await fetch("cargarPiso.php?idpiso=" + idpiso);
            const dataMapa = await resMapa.json();
            if (dataMapa.status === "success") {
                imgMapa.src = dataMapa.imagen;
            }
        } catch (e) {
            console.error("Error cargando mapa:", e);
            return;
        }

        // Cargar listado
        try {
            const resLista = await fetch("listarPiso.php?piso=" + idpiso);
            const dataLista = await resLista.json();

            if (dataLista.status === "success") {
                tablaBody.innerHTML = "";
                dataLista.data.forEach(item => {
                    tablaBody.insertAdjacentHTML("beforeend", `
                        <tr>
                            <td>${item.ubicacion}</td>
                            <td>${item.nodo ?? ""}</td>
                            <td>${item.usuario ?? ""}</td>
                        </tr>
                    `);
                });
            }
        } catch (e) {
            console.error("Error cargando lista:", e);
        }
    });

    // Buscar nodo
    document.getElementById("btnBuscarNodo").addEventListener("click", async () => {
        const nodo = document.getElementById("inputNodo").value.trim();
        if (!nodo) return;
        try {
            const res = await fetch("buscarNodo.php?nodo=" + nodo);
            console.log(await res.json());
        } catch (e) {
            console.error("Error en buscar nodo:", e);
        }
    });

    // Buscar usuario
    document.getElementById("btnBuscarUsuario").addEventListener("click", async () => {
        const usuario = document.getElementById("inputUsuario").value.trim();
        if (!usuario) return;
        try {
            const res = await fetch("buscarUsuario.php?usuario=" + usuario);
            console.log(await res.json());
        } catch (e) {
            console.error("Error en buscar usuario:", e);
        }
    });

});
</script>

</body>
</html>
