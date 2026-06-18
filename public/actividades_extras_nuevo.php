<?php
date_default_timezone_set('America/Mexico_City');

require "auth.php"; // 🔥 importante
require "db.php";

$idIngeniero = $_SESSION['user_id'];
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

// ✅ Obtener catálogo de actividades
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
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
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
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    max-width: 650px;
    margin: auto;
}

label {
    font-weight: 600;
    display: block;
    margin-top: 15px;
}

input, select, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    margin-top: 5px;
}

textarea {
    height: 120px;
}

.btn-guardar {
    margin-top: 20px;
    padding: 12px;
    background: #00AEEF;
    color: #fff;
    border-radius: 10px;
    border: none;
    cursor: pointer;
}

/* BUSCADOR */
#resultados_usuarios {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.item {
    padding: 10px;
    cursor: pointer;
}

.item:hover {
    background: #eee;
}
</style>

</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Nueva Actividad Extra</h2>
<p style="text-align:center;">Registrar actividad y comenzar seguimiento de tiempo</p>

<div class="form-card">

<form action="actividades_extras_guardar.php" method="POST">

    <!-- 🔥 DATOS OCULTOS -->
    <input type="hidden" name="idingeniero" value="<?= $idIngeniero ?>">
    
    <!-- 🔥 AQUÍ ARRANCA EL TIEMPO -->
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

    <!-- USUARIO AFECTADO -->
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

    <button class="btn-guardar">Guardar actividad</button>

</form>

</div>

</div>

<script src="theme.js"></script>

<script>
// BUSCADOR LIVE
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

        data.forEach(u => {
            let div = document.createElement("div");
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