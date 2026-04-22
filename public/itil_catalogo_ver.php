<?php
require "session_config.php";
require "db.php";

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM catapoyo WHERE idapoyo = ?");
$stmt->execute([$id]);
$apoyo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apoyo) {
    die("Apoyo no encontrado");
}

/* Obtener categorías reales */
$categorias = $pdo->query("
    SELECT 
        idcategoria AS id,
        nombre
    FROM categorias
    WHERE activo = true
    ORDER BY orden, nombre
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar apoyo</title>

<link rel="stylesheet" href="itil_estadisticas.css">
</head>
<body>

<?php include "sidebar.php"; ?>

<!-- === TOPBAR REAL === -->
<div class="itil-topbar">

    <a href="itil_incidentes.php">
        <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10z"/></svg>
        Incidentes
    </a>

    <a href="itil_incidente_nuevo.php">
        <svg><path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Nuevo
    </a>

    <a href="itil_problemas.php">
        <svg><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Problemas
    </a>

    <a href="itil_catalogo.php">
        <svg><path d="M4 4h16v4H4zm0 6h16v10H4z"/></svg>
        Catálogo Incidentes
    </a>

    <a href="itil_solicitudes.php">
        <svg><rect x="3" y="6" width="18" height="12" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Solicitudes
    </a>

    <a href="itil_sla.php">
        <svg><path d="M12 2v20m10-10H2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        SLA
    </a>

    <a href="itil_estadisticas.php">
        <svg><path d="M4 20V10m6 10V4m6 16v-6m6 6V8" stroke="currentColor" stroke-width="2" fill="none"/></svg>
        Estadísticas
    </a>

</div>

<div class="main">

    <h2 class="dashboard-title">Editar apoyo</h2>
    <div class="dashboard-subtitle">Modifica la información del apoyo seleccionado</div>

    <form action="itil_catalogo_accion.php" method="POST" class="form-card">

        <!-- ID REAL -->
        <input type="hidden" name="idapoyo" value="<?= $apoyo['idapoyo'] ?>">

        <label>Título del incidente</label>
        <input type="text" name="tituloincidente" value="<?= htmlspecialchars($apoyo['tituloincidente'] ?? '') ?>" required>

        <label>Descripción</label>
        <textarea name="descripcion" rows="4"><?= htmlspecialchars($apoyo['descripcion'] ?? '') ?></textarea>

        <label>Categoría</label>
        <select name="categoria">
            <option value="">— Sin categoría —</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" 
                    <?= $apoyo['categoria'] == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Subcategoría</label>
        <input type="text" name="subcategoria" value="<?= htmlspecialchars($apoyo['subcategoria'] ?? '') ?>">

        <label>Prioridad</label>
        <select name="prioridad">
            <option value="Alta"  <?= $apoyo['prioridad'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
            <option value="Media" <?= $apoyo['prioridad'] === 'Media' ? 'selected' : '' ?>>Media</option>
            <option value="Baja"  <?= $apoyo['prioridad'] === 'Baja' ? 'selected' : '' ?>>Baja</option>
        </select>

        <label>Impacto</label>
        <select name="impacto">
            <option value="Alto"   <?= $apoyo['impacto'] === 'Alto' ? 'selected' : '' ?>>Alto</option>
            <option value="Medio"  <?= $apoyo['impacto'] === 'Medio' ? 'selected' : '' ?>>Medio</option>
            <option value="Bajo"   <?= $apoyo['impacto'] === 'Bajo' ? 'selected' : '' ?>>Bajo</option>
        </select>

        <label>Urgencia</label>
        <select name="urgencia">
            <option value="Alta"  <?= $apoyo['urgencia'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
            <option value="Media" <?= $apoyo['urgencia'] === 'Media' ? 'selected' : '' ?>>Media</option>
            <option value="Baja"  <?= $apoyo['urgencia'] === 'Baja' ? 'selected' : '' ?>>Baja</option>
        </select>

        <label>Tiempo estimado (minutos)</label>
        <input type="number" name="tiempo_estimado" value="<?= htmlspecialchars($apoyo['tiempo_estimado'] ?? '') ?>">

        <label>Requiere aprobación</label>
        <select name="requiere_aprobacion">
            <option value="0" <?= $apoyo['requiere_aprobacion'] == 0 ? 'selected' : '' ?>>No</option>
            <option value="1" <?= $apoyo['requiere_aprobacion'] == 1 ? 'selected' : '' ?>>Sí</option>
        </select>

        <label>Notas internas</label>
        <textarea name="notas_internas" rows="3"><?= htmlspecialchars($apoyo['notas_internas'] ?? '') ?></textarea>

        <label>Solución propuesta</label>
        <textarea name="solucion_propuesta" rows="3"><?= htmlspecialchars($apoyo['solucion_propuesta'] ?? '') ?></textarea>

        <label>Activo</label>
        <select name="activo">
            <option value="1" <?= $apoyo['activo'] == 1 ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= $apoyo['activo'] == 0 ? 'selected' : '' ?>>No</option>
        </select>

        <button type="submit" class="btn-guardar">Guardar cambios</button>
        <a href="itil_catalogo.php" class="btn-cancelar">Cancelar</a>

    </form>

</div>

</body>
</html>
