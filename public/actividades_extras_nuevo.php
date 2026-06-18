<?php
date_default_timezone_set('America/Mexico_City');

require "auth.php";
require "db.php";

$idIngeniero = $_SESSION['user_id'];
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

// Catálogo
$stmt = $pdo->query("
    SELECT idactividad, actividad 
    FROM catalogo_actividades 
    ORDER BY actividad ASC
");
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva Actividad Extra</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

/* ✅ VARIABLES (NO QUITES ESTO) */
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

/* BASE */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
}

/* MAIN */
.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

/* FORM */
.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    max-width: 650px;
    margin: auto;
    border: 1px solid var(--border);
}

/* INPUTS */
label {
    font-weight: 600;
    margin-top: 15px;
    display: block;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    margin-top: 6px;
    border: 1px solid var(--border);
    background: var(--card-bg);
    color: var(--text);
}

textarea {
    height: 120px;
}

/* BOTÓN */
.btn {
    margin-top: 20px;
    padding: 12px;
    background: #00AEEF;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

/* BUSCADOR */
#resultados_usuarios {
    border: 1px solid var(--border);
    margin-top: 5px;
    border-radius: 6px;
}

.item {
    padding: 10px;
    cursor: pointer;
}

.item:hover {
    background: rgba(0,0,0,0.05);
}

body.dark .item:hover {
    background: rgba(255,255,255,0.08);
}

</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Nueva Actividad Extra</h2>

<div class="form-card">

<form action="actividades_extras_guardar.php" method="POST">

<input type="hidden" name="idingeniero" value="<?= $idIngeniero ?>">

<!-- ✅ FECHA INICIO AUTOMÁTICA -->
<input type="hidden" name="fecha_inicio" value="<?= date('Y-m-d H:i:s') ?>">

<!-- ACTIVIDAD -->
<label>Actividad</label>
<select name="idactividad" required>
    <option value="">Seleccione...</option>
    <?php foreach ($actividades as $a): ?>
        <option value="<?= $a['idactividad'] ?>">
            <?= htmlspecialchars($a['actividad']) ?>
        </option>
    <?php endforeach; ?>
</select>

<!-- USUARIO -->
<label>Usuario afectado</label>
<input type="text" id="buscar_usuario">
<input type="hidden" name="usuario_afectado" id="usuario_afectado">

<div id="resultados_usuarios"></div>

<!-- EQUIPO -->
<label>Equipo</label>
<input type="text" name="equipo">

<!-- COMENTARIOS -->
<label>Comentarios</label>
<textarea name="comentarios"></textarea>

<!-- EVIDENCIA -->
<label>Evidencia</label>
<input type="text" name="evidencia">

<!-- ESTATUS -->
<label>Estatus</label>
<select name="estatus">
    <option value="en proceso" selected>En proceso</option>
    <option value="completado">Completado</option>
    <option value="cancelado">Cancelado</option>
</select>

<button class="btn">Guardar actividad</button>

</form>

</div>

</div>

<script src="theme.js"></script>

<script>
// 🔍 BUSCADOR
const input = document.getElementById("buscar_usuario");
const resultados = document.getElementById("resultados_usuarios");
const hidden = document.getElementById("usuario_afectado");

input.addEventListener("keyup", () => {
    let q = input.value.trim();

    if (!q) {
        resultados.innerHTML = "";
        hidden.value = "";
        return;
    }

    fetch("buscar_activeuser.php?q=" + encodeURIComponent(q))
    .then(r => r.json())
    .then(data => {

        resultados.innerHTML = "";

        if (data.length === 0) {
            resultados.innerHTML = "<div class='item'>No encontrado</div>";
            return;
        }

        data.forEach(u => {
            const div = document.createElement("div");
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
</script>

</body>
</html>