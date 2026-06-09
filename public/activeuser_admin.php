<?php
require "session_config.php";
require "db.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administrar ActiveUser</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>
.contenedor {
    padding: 20px;
}

.titulo {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 5px;
}

.subtitulo {
    opacity: 0.7;
    margin-bottom: 25px;
}

#buscar {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
}

#resultados {
    margin-top: 15px;
    background: var(--card-bg);
    border-radius: 10px;
    border: 1px solid var(--sidebar-border);
}

.item {
    padding: 12px;
    border-bottom: 1px solid var(--sidebar-border);
    cursor: pointer;
}

.item:hover {
    background: rgba(0,0,0,0.05);
}

body.dark .item:hover {
    background: rgba(255,255,255,0.08);
}

.nuevo-btn {
    margin-top: 15px;
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    display: inline-block;
    text-decoration: none;
    font-weight: 600;
}
</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<div class="contenedor">

    <div class="titulo">Administrar ActiveUser</div>
    <div class="subtitulo">Buscar, editar o crear usuarios del módulo ActiveUser</div>

    <input type="text" id="buscar" placeholder="Escribe un nombre…">

    <div id="resultados"></div>

    <a href="activeuser_nuevo.php" class="nuevo-btn" id="btnNuevo" style="display:none;">
        + Nuevo usuario
    </a>

</div>

</div>

<script src="theme.js"></script>

<script>
const input = document.getElementById("buscar");
const resultados = document.getElementById("resultados");
const btnNuevo = document.getElementById("btnNuevo");

input.addEventListener("keyup", () => {
    let q = input.value.trim();

    if (q.length === 0) {
        resultados.innerHTML = "";
        btnNuevo.style.display = "none";
        return;
    }

    fetch("buscar_activeuser.php?q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {

            resultados.innerHTML = "";

            if (data.length === 0) {
                resultados.innerHTML = "<div class='item'>No encontrado</div>";
                btnNuevo.style.display = "inline-block";
                return;
            }

            btnNuevo.style.display = "none";

            data.forEach(u => {
                let div = document.createElement("div");
                div.className = "item";
                div.textContent = u.nomuser + " — " + (u.ubicacion ?? "");
                div.onclick = () => {
                    window.location = "activeuser_editar.php?idu=" + u.idu;
                };
                resultados.appendChild(div);
            });
        });
});
</script>

</body>
</html>
