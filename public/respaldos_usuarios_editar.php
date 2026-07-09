<?php

require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

if (!isset($_GET['id'])) {

    header("Location: respaldos_usuarios.php");
    exit;

}

$idrespaldo = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM respaldos_usuarios
    WHERE idrespaldo = ?
");

$stmt->execute([$idrespaldo]);

$respaldo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$respaldo) {

    header("Location: respaldos_usuarios.php");
    exit;

}

$stmt = $pdo->query("
    SELECT
        d.iddisco,
        d.nombre,
        d.tamano_total_gb,

        COALESCE(
            SUM(r.tamano_gb),
            0
        ) AS utilizado

    FROM discos_respaldo d

    LEFT JOIN respaldos_usuarios r
        ON r.iddisco = d.iddisco

    GROUP BY
        d.iddisco,
        d.nombre,
        d.tamano_total_gb

    ORDER BY d.nombre
");

$discos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<title>Editar Respaldo</title>

<link rel="stylesheet" href="sidebar.css">
">

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
    --border: rgba(255,255,255,0.15);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    max-width: 700px;
    margin: auto;
    border: 1px solid var(--border);
}

h2 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    opacity: .7;
    margin-bottom: 30px;
}

label {
    font-weight: 600;
    display: block;
    margin-top: 15px;
}

input,
select,
textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
    margin-top: 5px;
    box-sizing: border-box;
    background: var(--card-bg);
    color: var(--text);
}

textarea {
    min-height: 120px;
}

.btn {
    margin-top: 20px;
    width: 100%;
    padding: 12px;
    background: #00AEEF;
    border: none;
    color: white;
    border-radius: 8px;
    cursor: pointer;
}

.btn-volver {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 18px;
    background: #64748b;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}

#resultados_usuarios {
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-top: 5px;
}

.item {
    padding: 10px;
    cursor: pointer;
}

.item:hover {
    background: rgba(0,174,239,.10);
}

.info-disco {
    margin-top: 10px;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border);
}

.disponible-ok {
    color: #10b981;
    font-weight: 600;
}

.disponible-alerta {
    color: #f59e0b;
    font-weight: 600;
}

.disponible-critico {
    color: #ef4444;
    font-weight: 600;
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Editar Respaldo</h2>

<div class="subtitle">
Modificar respaldo de usuario
</div>

<div class="form-card">

<form action="rios_editar_guardar.php

<input
    type="hidden"
    name="idrespaldo"
    value="<?= $respaldo['idrespaldo'] ?>">

<label>Usuario</label>

<input
    type="text"
    id="buscar_usuario"
    value="<?= htmlspecialchars($respaldo['usuario']) ?>">

<input
    type="hidden"
    name="usuario"
    id="usuario"
    value="<?= htmlspecialchars($respaldo['usuario']) ?>">

<div id="resultados_usuarios"></div>

<label>Disco</label>

<select
    name="iddisco"
    id="iddisco"
    required>

    <?php foreach($discos as $d): ?>

        <?php
        $disponible =
            $d['tamano_total_gb']
            - $d['utilizado'];
        ?>

        <option
            value="<?= $d['iddisco'] ?>"
            data-total="<?= $d['tamano_total_gb'] ?>"
            data-utilizado="<?= $d['utilizado'] ?>"
            data-disponible="<?= $disponible ?>"
            <?= ($d['iddisco'] == $respaldo['iddisco']) ? 'selected' : '' ?>>

            <?= htmlspecialchars($d['nombre']) ?>

        </option>

    <?php endforeach; ?>

</select>

<div id="infoDisco" class="info-disco"></div>

<label>Tamaño del respaldo (GB)</label>

<input
    type="number"
    step="0.01"
    min="0"
    name="tamano_gb"
    value="<?= $respaldo['tamano_gb'] ?>"
    required>

<label>Observaciones</label>

<textarea
    name="observaciones"><?= htmlspecialchars($respaldo['observaciones']) ?></textarea>

<button class="btn">
    Guardar cambios
</button>

<br>

<a href="respaldos_usuarios.php"
    class="btn-volver">

    ← Volver

</a>

</form>

</div>

</div>

<script src="theme.js"></script>

<script>

const input = document.getElementById("buscar_usuario");
const resultados = document.getElementById("resultados_usuarios");
const hidden = document.getElementById("usuario");

input.addEventListener("keyup", () => {

    let q = input.value.trim();

    if (!q) {

        resultados.innerHTML = "";

        return;

    }

    fetch(
        "buscar_activeuser.php?q="
        + encodeURIComponent(q)
    )
    .then(r => r.json())
    .then(data => {

        resultados.innerHTML = "";

        data.forEach(u => {

            const div =
                document.createElement("div");

            div.className = "item";
            div.textContent = u.nomuser;

            div.onclick = () => {

                input.value = u.nomuser;
                hidden.value = u.nomuser;

                resultados.innerHTML = "";

            };

            resultados.appendChild(div);

        });

    });

});

const comboDisco =
    document.getElementById("iddisco");

const infoDisco =
    document.getElementById("infoDisco");

function actualizarDisco() {

    const option =
        comboDisco.selectedOptions[0];

    if (!option) return;

    const total =
        parseFloat(option.dataset.total);

    const utilizado =
        parseFloat(option.dataset.utilizado);

    const disponible =
        parseFloat(option.dataset.disponible);

    let clase = "disponible-ok";

    if (disponible < 50) {

        clase = "disponible-critico";

    } else if (disponible < 100) {

        clase = "disponible-alerta";

    }

    infoDisco.innerHTML = `

        <strong>Capacidad:</strong>
        ${total.toFixed(2)} GB
        <br>

        <strong>Utilizado:</strong>
        ${utilizado.toFixed(2)} GB
        <br>

        <strong>Disponible:</strong>

        <span class="${clase}">
            ${disponible.toFixed(2)} GB
        </span>

    `;

}

comboDisco.addEventListener(
    "change",
    actualizarDisco
);

actualizarDisco();

</script>

</body>
</html>