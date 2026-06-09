<?php
session_start();
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

<style>
.contenedor {
    padding: 20px;
}

#buscar {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

#resultados {
    margin-top: 15px;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.item:hover {
    background: #f0f0f0;
}

.nuevo-btn {
    margin-top: 15px;
    padding: 12px;
    background: #0078d4;
    color: white;
    border-radius: 8px;
    display: inline-block;
    text-decoration: none;
}
</style>

</head>
<body>

<div class="contenedor">

<h2>Administrar ActiveUser</h2>

<input type="text" id="buscar" placeholder="Escribe un nombre…">

<div id="resultados"></div>

<a href="activeuser_nuevo.php" class="nuevo-btn" id="btnNuevo" style="display:none;">
    + Nuevo usuario
</a>

</div>

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
