<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Capturar XM / YM para ubicaciones</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    padding: 20px;
}

.panel {
    margin: 20px auto;
    max-width: 600px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.mapa-container {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: auto;
}

.mapa {
    width: 100%;
    display: block;
    border: 2px solid #ccc;
    border-radius: 10px;
}

.marcador {
    position: absolute;
    width: 22px;
    height: 22px;
    background: red;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 12px rgba(255,0,0,0.8);
    transform: translate(-50%, -50%);
    pointer-events: none;
}

input, select {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
}

button {
    margin-top: 15px;
    padding: 12px;
    width: 100%;
    background: #00AEEF;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
}
</style>
</head>

<body>

<h2>Capturar XM / YM para ubicaciones</h2>

<div class="panel">

    <label>Piso:</label>
    <select id="piso" onchange="cargarMapa(); cargarUbicaciones();">
        <option value="1">Piso 1</option>
        <option value="2">Piso 2</option>
        <option value="3">Piso 3</option>
        <option value="4">Piso 4</option>
        <option value="5">Piso 5</option>
    </select>

    <label>Ubicación:</label>
    <select id="ubicacion"></select>

    <label>XM</label>
    <input type="text" id="xm" readonly>

    <label>YM</label>
    <input type="text" id="ym" readonly>

    <button onclick="guardarXY()">Guardar XM / YM</button>

</div>

<div class="mapa-container">
    <img id="mapa" class="mapa">
    <div id="marcador" class="marcador" style="display:none;"></div>
</div>

<script>
function cargarMapa() {
    const piso = document.getElementById("piso").value;
    document.getElementById("mapa").src = "piso" + piso + ".jpg";
    document.getElementById("marcador").style.display = "none";
}

function cargarUbicaciones() {
    const piso = document.getElementById("piso").value;

    fetch("get_ubicaciones.php?piso=" + piso)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById("ubicacion");
            sel.innerHTML = "";
            data.forEach(u => {
                sel.innerHTML += `<option value="${u.idubicacion}">${u.ubicacion}</option>`;
            });
        });
}

const mapa = document.getElementById("mapa");
const marcador = document.getElementById("marcador");

mapa.addEventListener("click", function(e) {
    const rect = mapa.getBoundingClientRect();

    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const xm = x / mapa.offsetWidth;
    const ym = y / mapa.offsetHeight;

    document.getElementById("xm").value = xm.toFixed(6);
    document.getElementById("ym").value = ym.toFixed(6);

    marcador.style.left = (xm * mapa.offsetWidth) + "px";
    marcador.style.top = (ym * mapa.offsetHeight) + "px";
    marcador.style.display = "block";
});

function guardarXY() {
    const idubicacion = document.getElementById("ubicacion").value;
    const xm = document.getElementById("xm").value;
    const ym = document.getElementById("ym").value;

    fetch("guardar_xy.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `idubicacion=${idubicacion}&xm=${xm}&ym=${ym}`
    })
    .then(r => r.text())
    .then(t => alert(t));
}

window.onload = () => {
    cargarMapa();
    cargarUbicaciones();
};
</script>

</body>
</html>
