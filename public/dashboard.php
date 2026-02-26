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

    #mapa {
        width: 100%;
        max-width: 900px;
        border: 1px solid #ccc;
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
    </div>

    <!-- MAPA DEL PISO -->
    <div>
        <img id="mapa" src="">
    </div>

</div>

<!-- SCRIPT PARA CARGAR PISO -->
<script>
document.getElementById("selectPiso").addEventListener("change", async function () {
    const idpiso = this.value;

    if (idpiso === "") {
        document.getElementById("mapa").src = "";
        return;
    }

    const res = await fetch("cargarPiso.php?idpiso=" + idpiso);
    const data = await res.json();

    if (data.status === "success") {
        document.getElementById("mapa").src = data.imagen;
    } else {
        alert(data.message);
    }
});
</script>

</body>
</html>
