<?php
require "db.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Checklist de revisión</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: #0d1117;
    color: #e5e7eb;
    padding: 20px;
}
.container {
    max-width: 600px;
    margin: auto;
    background: #161b22;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.45);
}
input, select, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #30363d;
    background: #0d1117;
    color: #e5e7eb;
    font-size: 15px;
    margin-top: 5px;
}
#resultados {
    background: #1f2937;
    margin-top: 5px;
    border-radius: 10px;
    overflow: hidden;
}
.result-item {
    padding: 10px;
    border-bottom: 1px solid #30363d;
    cursor: pointer;
}
.result-item:hover {
    background: #374151;
}
.btn {
    width: 100%;
    padding: 14px;
    background: #00AEEF;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
    font-weight: bold;
}
.btn:hover {
    background: #0088C0;
}
</style>
</head>

<body>

<div class="container">
    <h2>Checklist de revisión</h2>

    <!-- BUSCADOR -->
    <label>Usuario</label>
    <input type="text" id="buscar" placeholder="Buscar usuario...">
    <div id="resultados"></div>

    <!-- PISO -->
    <label style="margin-top:15px;">Piso</label>
    <select id="piso">
        <option value="">Selecciona un piso</option>
        <option value="1">Piso 1</option>
        <option value="2">Piso 2</option>
        <option value="3">Piso 3</option>
        <option value="4">Piso 4</option>
        <option value="5">Piso 5</option>
        <option value="11">Piso 11</option>
    </select>

    <!-- CHECKLIST -->
    <div id="checklist" style="display:none; margin-top:20px;">
        <h3>Revisión del equipo</h3>

        <label><input type="checkbox" id="fondo"> Fondo de pantalla</label>
        <label><input type="checkbox" id="correo"> Correo</label>
        <label><input type="checkbox" id="teams"> Teams</label>

        <label style="margin-top:15px;">Notas</label>
        <textarea id="notas" rows="3"></textarea>

        <button class="btn" id="btnCompleto">Completo</button>
    </div>
</div>

<script>
let usuarioSeleccionado = null;

// BUSCADOR
document.getElementById("buscar").addEventListener("keyup", function() {
    let q = this.value.trim();

    if (q.length < 2) {
        document.getElementById("resultados").innerHTML = "";
        return;
    }

    fetch("buscar_usuario.php?q=" + encodeURIComponent(q))
        .then(res => res.json())
        .then(data => {
            let html = "";
            data.forEach(u => {
                html += `<div class='result-item' onclick='seleccionar(${u.idu}, "${u.usuario_nombre.replace(/"/g, '&quot;')}")'>
                            ${u.usuario_nombre}
                         </div>`;
            });
            document.getElementById("resultados").innerHTML = html;
        });
});

function seleccionar(idu, usuario_nombre) {
    usuarioSeleccionado = { idu, usuario_nombre };
    document.getElementById("buscar").value = usuario_nombre;
    document.getElementById("resultados").innerHTML = "";
    document.getElementById("checklist").style.display = "block";
}

// GUARDAR
document.getElementById("btnCompleto").addEventListener("click", function() {
    if (!usuarioSeleccionado) {
        alert("Selecciona un usuario.");
        return;
    }

    const piso = document.getElementById("piso").value;
    if (!piso) {
        alert("Selecciona un piso.");
        return;
    }

    const notas = document.getElementById("notas").value.trim();

    fetch("guardar_checklist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            idu: usuarioSeleccionado.idu,
            usuario_nombre: usuarioSeleccionado.usuario_nombre,
            piso: piso,
            notas: notas,
            fondo: document.getElementById("fondo").checked,
            correo: document.getElementById("correo").checked,
            teams: document.getElementById("teams").checked
        })
    })
    .then(res => res.text())
    .then(msg => {
        alert("Revisión registrada.");
        location.reload();
    });
});
</script>

</body>
</html>
