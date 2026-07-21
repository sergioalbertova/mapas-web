<?php
require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

if (!isset($_GET['id'])) {
    header("Location: actividades_extras.php");
    exit;
}

$idextra = $_GET['id'];

// ✅ Obtener datos actuales
$stmt = $pdo->prepare("
    SELECT *,
    EXTRACT(EPOCH FROM (fecha_fin - fecha_inicio))/60 AS duracion_min
    FROM actividades_extras 
    WHERE idextra = ?
");
$stmt->execute([$idextra]);

$extra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$extra) {
    header("Location: actividades_extras.php");
    exit;
}

// ✅ Catálogo
$stmt = $pdo->query("
    SELECT idactividad, actividad 
    FROM catalogo_actividades 
    ORDER BY actividad ASC
");
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Formatos
$inicio = $extra['fecha_inicio'] ? substr($extra['fecha_inicio'],0,19) : "-";
$fin = $extra['fecha_fin'] ? substr($extra['fecha_fin'],0,19) : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Actividad Extra</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

<style>

/* VARIABLES */
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

/* MAIN */
.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}

/* TITULO */
h2 {
    text-align: center;
    margin-bottom: 10px;
}

.subtitle {
    text-align: center;
    opacity: 0.7;
    margin-bottom: 30px;
}

/* CARD */
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
    border: 1px solid var(--border);
    margin-top: 5px;
    margin-bottom: 10px;
}

textarea {
    height: 120px;
}

/* BADGES */
.badge {
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 12px;
    white-space: nowrap;
}

.en-proceso {
    background: #f59e0b;
    color: white;
}

.completo {
    background: #10b981;
    color: white;
}

/* INFO TIEMPO */
.info-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}

/* BOTON */
.btn-guardar {
    margin-top: 20px;
    padding: 12px;
    background: #00AEEF;
    color: white;
    border-radius: 8px;
    border: none;
    cursor: pointer;
}

.info-box {
    padding: 15px 18px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--card-bg);

    display: flex;
    flex-direction: column;
    gap: 12px;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-label {
    min-width: 75px;
    font-weight: 600;
}

</style>
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<h2>Editar Actividad Extra</h2>
<div class="subtitle">Modifica la actividad y su tiempo</div>

<div class="form-card">

<!-- INFO DE TIEMPO -->
<div class="info-box">

    <div class="info-row">
        <span class="info-label">Inicio:</span>
        <span><?= $inicio ?></span>
    </div>

    <div class="info-row">
        <span class="info-label">Fin:</span>

        <?php if ($fin): ?>
            <span><?= $fin ?></span>
        <?php else: ?>
            <span class="badge en-proceso">En proceso</span>
        <?php endif; ?>
    </div>

    <div class="info-row">
        <span class="info-label">Duración:</span>

        <span>
            <?php
            if ($extra['fecha_fin']) {
                echo "⏱ " . round($extra['duracion_min'],1) . " min";
            } else {
                echo "⏳ En curso";
            }
            ?>
        </span>

    </div>

</div>

<form action="actividades_extras_editar_guardar.php"
      method="POST"
      enctype="multipart/form-data">

<input type="hidden" name="idextra" value="<?= $extra['idextra'] ?>">

<!-- ACTIVIDAD -->
<label>Actividad</label>
<select name="idactividad" required>
    <?php foreach ($actividades as $a): ?>
        <option value="<?= $a['idactividad'] ?>"
            <?= ($a['idactividad'] == $extra['idactividad']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['actividad']) ?>
        </option>
    <?php endforeach; ?>
</select>

<!-- USUARIO -->
<label>Usuario afectado</label>
<input type="text" name="usuario_afectado"
       value="<?= htmlspecialchars($extra['usuario_afectado']) ?>">

<!-- EQUIPO -->
<label>Equipo</label>
<input type="text" name="equipo"
       value="<?= htmlspecialchars($extra['equipo']) ?>">

<!-- COMENTARIOS -->
<label>Comentarios</label>
<textarea name="comentarios"><?= htmlspecialchars($extra['comentarios']) ?></textarea>

<!-- EVIDENCIA ACTUAL -->
<label>Evidencia actual</label>

<?php if (!empty($extra['evidencia'])): ?>

    <div style="margin-bottom:15px;">

        <img
            src="<?= htmlspecialchars($extra['evidencia']) ?>"
            alt="Evidencia"
            style="
                max-width:100%;
                max-height:250px;
                border-radius:8px;
                border:1px solid #ccc;
            ">

    </div>

<?php else: ?>

    <div style="margin-bottom:15px;">
        Sin evidencia adjunta
    </div>

<?php endif; ?>

<input
    type="hidden"
    name="evidencia_actual"
    value="<?= htmlspecialchars($extra['evidencia']) ?>">

<label>Reemplazar evidencia</label>

<input
    type="file"
    name="evidencia"
    accept=".jpg,.jpeg,.png,.gif,.webp">

<!-- ESTATUS -->
<label>Estatus</label>
<select name="estatus">
    <option value="en proceso" <?= $extra['estatus']=='en proceso' ? 'selected' : '' ?>>En proceso</option>
    <option value="completado" <?= $extra['estatus']=='completado' ? 'selected' : '' ?>>Completado</option>
    <option value="cancelado" <?= $extra['estatus']=='cancelado' ? 'selected' : '' ?>>Cancelado</option>
</select>

<button class="btn-guardar">Guardar cambios</button>

</form>

</div>

</div>

<script src="theme.js"></script>

</body>
</html>
