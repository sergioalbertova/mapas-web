<?php

require "auth.php";
require "db.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Consulta de Respaldos</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<style>

:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --border: #ddd;
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --border: rgba(255,255,255,.15);
}

body{
    margin:0;
    font-family:"Segoe UI",Arial;
    background:var(--bg);
    color:var(--text);
    display:flex;
}

.main{
    margin-left:240px;
    padding:20px 40px;
    width:calc(100% - 240px);
}

.card{
    background:var(--card-bg);
    border:1px solid var(--border);
    border-radius:12px;
    padding:25px;
}

h2{
    text-align:center;
    margin-bottom:10px;
}

.subtitle{
    text-align:center;
    opacity:.7;
    margin-bottom:25px;
}

label{
    font-weight:600;
}

input{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:1px solid var(--border);
    margin-top:5px;
    box-sizing:border-box;
    background:var(--card-bg);
    color:var(--text);
}

#sugerencias{
    border:1px solid var(--border);
    border-radius:8px;
    margin-top:5px;
}

.item{
    padding:10px;
    cursor:pointer;
}

.item:hover{
    background:rgba(0,174,239,.10);
}

#resultado{
    margin-top:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:12px;
    border-bottom:1px solid var(--border);
}

th{
    text-align:center;
}

.btn-ver{
    background:#00AEEF;
    color:white;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Consulta de Respaldos</h2>

<div class="subtitle">
Encuentra rápidamente dónde está almacenado un respaldo
</div>

<div class="card">

    <label>Usuario</label>

    <input
        type="text"
        id="buscar_usuario"
        autocomplete="off">

    <div id="sugerencias"></div>

    <div id="resultado"></div>

</div>

</div>

<script src="theme.js"></script>

<script>

const txtBuscar =
    document.getElementById("buscar_usuario");

const sugerencias =
    document.getElementById("sugerencias");

const resultado =
    document.getElementById("resultado");

txtBuscar.addEventListener("keyup", () => {

    const q = txtBuscar.value.trim();

    if (!q) {

        sugerencias.innerHTML = "";
        resultado.innerHTML = "";
        return;

    }

    fetch(
        "buscar_respaldos_usuario.php?q="
        + encodeURIComponent(q)
    )
    .then(r => r.json())
    .then(data => {

        sugerencias.innerHTML = "";

        data.forEach(usuario => {

            const div =
                document.createElement("div");

            div.className = "item";

            div.textContent = usuario;

            div.onclick = () => {

                txtBuscar.value = usuario;
                sugerencias.innerHTML = "";

                buscarRespaldos(usuario);

            };

            sugerencias.appendChild(div);

        });

    });

});

function buscarRespaldos(usuario){

    fetch(
        "consulta_respaldos_ajax.php?usuario="
        + encodeURIComponent(usuario)
    )
    .then(r => r.text())
    .then(html => {

        resultado.innerHTML = html;

    });

}

</script>

</body>
</html>
