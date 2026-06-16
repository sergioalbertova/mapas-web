<?php
require "session_config.php";
require "db.php";

if (!isset($_GET['id'])) {
    header("Location: actividades_extras.php");
    exit;
}

$idextra = $_GET['id'];

// Obtener datos actuales
$stmt = $pdo->prepare("
    SELECT * FROM actividades_extras 
    WHERE idextra = ?
");
$stmt->execute([$idextra]);
$extra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$extra) {
    header("Location: actividades_extras.php");
    exit;
}

// Obtener catálogo de actividades
$stmt = $pdo->query("SELECT idactividad, actividad FROM catalogo_actividades ORDER BY actividad ASC");
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Actividad Extra</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

:root {
    --bg: #F4F7FA;
    --text: #1F2933;
    --card-bg: #FFFFFF;
    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;
    --card-bg: #1f2937;
    --shadow: rgba(0,0,0,0.45);
}

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

.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 8px;
    font-weight: 600;
}

.subtitle {
    text-align: center;
    opacity: 0.7;
    margin-bottom: 40px;
    font-size: 15px;
}

/* FORMULARIO */
.form-card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px var(--shadow);
    max-width: 650px;
    margin: auto;
    box-sizing: border-box;
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
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
    margin-top: 5px;
    margin-bottom: 12px;
    box-sizing: border-box;
}

textarea {
    height: 120px;
    resize: vertical;
}

/* BUSCADOR LIVE */
#buscar_usuario {
    width: 100%;
}

#resultados_usuarios {
    margin-top: 10px;
    background: var(--card-bg);
    border-radius: 10px;
    border: 1px solid #ccc;
}

.item {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
}

.item:hover {
    background: rgba(0,0,0,0.05);
}

.btn-guardar {
    margin-top: 25px;
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    border: none;
    cursor: pointer;
}

</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Editar Actividad Extra</h2>
<div class="subtitle">Modificar información de la actividad registrada</div>

<div class="form-card">

<form action="actividades_extras_editar_guardar.php" method="POST">

    <input type="hidden" name="idextra" value="<?= $extra['idextra'] ?>">

    <!-- ACTIVIDAD -->
    <label>Actividad realizada</label>
    <select name="idactividad" required>
        <?php foreach ($actividades as $a): ?>
            <option value="<?= $a['idactividad'] ?>"
                <?= ($a['idactividad'] == $extra['idactividad']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['actividad']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- USUARIO AFECTADO (BUSCADOR LIVE) -->
    <label>Usuario afectado (opcional)</label>
    <input type="text" id="buscar_usuario" placeholder="Escribe un nombre…" 
           value="<?= htmlspecialchars($extra['usuario_afectado']) ?>">

    <input type="hidden" name="usuario_afectado" id="usuario_afectado"
           value="<?= htmlspecialchars($extra['usuario_afectado']) ?>">

    <div id="resultados_usuarios"></div>

    <!-- EQUIPO -->
    <label>Equipo</label>
    <input type="text" name="equipo" value="<?= htmlspecialchars($extra['equipo']) ?>">

    <!-- COMENTARIOS -->
    <label>Comentarios</label>
    <textarea name="comentarios"><?= htmlspecialchars($extra['comentarios']) ?></textarea>

    <!-- EVIDENCIA -->
    <label>Evidencia</label>
    <input type="text" name="evidencia" value="<?= htmlspecialchars($extra['evidencia']) ?>">

    <!-- ESTATUS -->
    <label>Estatus</label>
    <select name="estatus" required>
        <option value="pendiente"     <?= $extra['estatus']=='pendiente' ? 'selected' : '' ?>>Pendiente</option>
        <option value="en proceso"    <?= $extra['estatus']=='en proceso' ? 'selected' : '' ?>>En proceso</option>
        <option value="completado"    <?= $extra['estatus']=='completado' ? 'selected' : '' ?>>Completado</option>
        <option value="cancelado"     <?= $extra['estatus']=='cancelado' ? 'selected' : '' ?>>Cancelado</option>
    </select>

    <button class="btn-guardar">Guardar cambios</button>

</form>

</div>

</div>

<script src="theme.js"></script>

<script>
// BUSCADOR LIVE DE USUARIOS (tipo ActiveUser)
const input = document.getElementById("buscar_usuario");
const resultados = document.getElementById("resultados_usuarios");
const hidden = document.getElementById("usuario_afectado");

input.addEventListener("keyup", () => {
    let q = input.value.trim();

    if (q.length === 0) {
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
                hidden.value = "";
                return;
            }

            data.forEach(u => {
                let div = document.createElement("div");
                div.className = "item";
                div.textContent = u.nomuser + " — " + (u.ubicacion ?? "");
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
