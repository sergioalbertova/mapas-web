<?php
require "db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Pisos</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        #imgMapa { max-width: 600px; border: 1px solid #ccc; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>

<h2>Dashboard de Pisos</h2>

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

<img id="imgMapa" src="" alt="Mapa del piso">

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

<h3>Buscar Nodo</h3>
<input type="text" id="inputNodo" placeholder="Número de nodo">
<button id="btnBuscarNodo">Buscar nodo</button>

<h3>Buscar Usuario</h3>
<input type="text" id="inputUsuario" placeholder="Nombre de usuario">
<button id="btnBuscarUsuario">Buscar usuario</button>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const selectPiso = document.getElementById("selectPiso");
    const imgMapa = document.getElementById("imgMapa");
    const tablaBody = document.querySelector("#tablaUbicaciones tbody");

    selectPiso.addEventListener("change", async function () {

        const idpiso = this.value;
        console.log("ID de piso seleccionado:", idpiso);

        if (!idpiso) {
            tablaBody.innerHTML = "";
            imgMapa.src = "";
            return;
        }

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
