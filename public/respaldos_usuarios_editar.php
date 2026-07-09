<?php

require "auth.php";
require "db.php";

$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';

if (!isset($_GET['id'])) {

    header("Location: respaldos_usuarios.php");
    exit;

}

$idrespaldo = $_GET['id'];

/* ==========================
   RESPALDO ACTUAL
========================== */

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

/* ==========================
   DISCOS
========================== */

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

<form action="respaldos_usuarios_editar_guardar.php" method="POST">
   
<input type="hidden" name="idrespaldo" value="<?= $respaldo['idrespaldo'] ?>">

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

    <select>
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
                <?= $d['iddisco'] == $respaldo['iddisco'] ? 'selected' : '' ?>>

                <?= htmlspecialchars($d['nombre']) ?>

            </option>

        <?php endforeach; ?>

    </select>

    <div
        id="infoDisco"
        class="info-disco">
    </div>

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

    <br><br>

    "
        class="btn-volver">

        ← Volver

    </a>

</form>