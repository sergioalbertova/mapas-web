<?php
require "db.php"; // aquí tienes tu $pdo
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
h2 { margin-top: 0; }
.search-box input,
#piso,
#notas {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #30363d;
    background: #0d1117;
    color: #e5e7eb;
    font-size: 15px;
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
.checklist {
    margin-top: 20px;
}
.checklist label {
    display: block;
    margin-bottom: 10px;
    font-size: 15px;
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
@media (max-width: 600px) {
    .container { padding: 18px; }
}
</style>
</head>
<body>

<div class="container">
    <h2>Checklist de revisión</h2>
    <p>Busca al usuario, indica el piso y marca como completado.</p>

    <!-- BUSCADOR -->
    <div class="search-box">
        <label>Usuario</label>
        <input type="text" id="buscar" placeholder="Buscar usuario...">
        <div id="resultados"></div>
    </div>

    <!-- PISO -->
    <div style="margin-top:15px;">
        <label>Piso</label>
        <input type="text" id="piso" placeholder="Ej. Piso 3, 4B, etc.">
    </div>

    <!-- CHECKLIST -->
    <div class="checklist" id="checklist" style="display:none;">
        <h3>Revisión del equipo</h3>

        <label><input type="checkbox" id="c1"> Antivirus actualizado</label>
        <label><input type="checkbox" id="c2"> Sistema operativo actualizado</label>
        <label><input type="checkbox" id="c3"> Espacio en disco suficiente</label>
        <label><input type="checkbox" id="c4"> Conexión a red estable</label>

        <label style="margin-top:15px;">Notas adicionales</label>
        <textarea id="notas" rows="3" placeholder="Observaciones, hallazgos, etc."></textarea>

        <button class="btn" id="btnCompleto">Completo</button>
    </div>
</div>

<script>
let usuarioSeleccionado = null;

// BUSCADOR AJAX
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
                html += `<div class='result-item' onclick='seleccionar(${u.idu}, "${u.nomuser.replace(/"/g, '&quot;')}")'>
                            ${u.nomuser}
                         </div>`;
            });
            document.getElementById("resultados").innerHTML = html;
        });
});

function seleccionar(idu, nomuser) {
    usuarioSeleccionado = { idu, nomuser };
    document.getElementById("buscar").value = nomuser;
    document.getElementById("resultados").innerHTML = "";
    document.getElementById("checklist").style.display = "block";
}

document.getElementById("btnCompleto").addEventListener("click", function() {
    if (!usuarioSeleccionado) {
        alert("Selecciona un usuario primero.");
        return;
    }

    const piso = document.getElementById("piso").value.trim();
    if (!piso) {
        alert("Captura el piso.");
        return;
    }

    const notas = document.getElementById("notas").value.trim();

    fetch("guardar_checklist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            idu: usuarioSeleccionado.idu,
            nomuser: usuarioSeleccionado.nomuser,
            piso: piso,
            notas: notas
        })
    })
    .then(res => res.text())
    .then(msg => {
        alert("Revisión registrada correctamente.");
        location.reload();
    })
    .catch(() => alert("Error al guardar."));
});
</script>

</body>
</html>
