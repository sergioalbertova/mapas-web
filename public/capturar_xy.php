<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Capturar coordenadas XM / YM</title>

<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    padding: 20px;
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

.panel {
    margin: 20px auto;
    max-width: 600px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

input {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
}
</style>
</head>

<body>

<h2>Capturar XM / YM desde el mapa</h2>

<div class="panel">
    <label>Selecciona el piso:</label>
    <select id="piso" onchange="cargarMapa()">
        <option value="1">Piso 1</option>
        <option value="2">Piso 2</option>
        <option value="3">Piso 3</option>
        <option value="4">Piso 4</option>
        <option value="5">Piso 5</option>
    </select>

    <label>XM</label>
    <input type="text" id="xm" readonly>

    <label>YM</label>
    <input type="text" id="ym" readonly>
</div>

<div class="mapa-container">
    <img id="mapa" src="piso1.png" class="mapa">
    <div id="marcador" class="marcador" style="display:none;"></div>
</div>

<script>
function cargarMapa() {
    const piso = document.getElementById("piso").value;
    document.getElementById("mapa").src = "piso" + piso + ".jpg";
    document.getElementById("marcador").style.display = "none";
}

const mapa = document.getElementById("mapa");
const marcador = document.getElementById("marcador");

mapa.addEventListener("click", function(e) {
    const rect = mapa.getBoundingClientRect();

    // Coordenadas relativas dentro de la imagen
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Normalizar entre 0 y 1
    const xm = x / mapa.offsetWidth;
    const ym = y / mapa.offsetHeight;

    // Mostrar valores
    document.getElementById("xm").value = xm.toFixed(6);
    document.getElementById("ym").value = ym.toFixed(6);

    // Mover marcador
    marcador.style.left = (xm * mapa.offsetWidth) + "px";
    marcador.style.top = (ym * mapa.offsetHeight) + "px";
    marcador.style.display = "block";
});
</script>

</body>
</html>
