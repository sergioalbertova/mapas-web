<?php

require "auth.php";
require "db.php";

$usuario = trim($_GET['usuario'] ?? '');

if ($usuario === '') {

    exit;

}

$stmt = $pdo->prepare("
    SELECT

        r.idrespaldo,
        r.usuario,
        r.tamano_gb,
        r.fecha_respaldo,

        d.nombre AS disco,

        u.nombre AS ingeniero

    FROM respaldos_usuarios r

    INNER JOIN discos_respaldo d
        ON d.iddisco = r.iddisco

    INNER JOIN usuarios u
        ON u.id = r.creado_por

    WHERE r.usuario = ?

    ORDER BY r.fecha_respaldo DESC
");

$stmt->execute([$usuario]);

$respaldos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$respaldos) {

    echo '
        <div class="sin-resultados">
            No se encontraron respaldos.
        </div>
    ';

    exit;
}

?>

<h3>
    Respaldos encontrados:
    <?= count($respaldos) ?>
</h3>

<table>

    <thead>

        <tr>

            <th>Usuario</th>
            <th>Disco</th>
            <th>Tamaño (GB)</th>
            <th>Fecha</th>
            <th>Ingeniero</th>
            <th>Acciones</th>

        </tr>

    </thead>

    <tbody>

    <?php foreach ($respaldos as $r): ?>

        <tr>

            <td>
                <?= htmlspecialchars($r['usuario']) ?>
            </td>

            <td>
                <?= htmlspecialchars($r['disco']) ?>
            </td>

            <td>
                <?= number_format($r['tamano_gb'], 2) ?>
            </td>

            <td>
                <?= date(
                    'Y-m-d H:i',
                    strtotime($r['fecha_respaldo'])
                ) ?>
            </td>

            <td>
                <?= htmlspecialchars($r['ingeniero']) ?>
            </td>

            <td>

                <a href="respaldos_usuarios_ver.php?id=<?= $r['idrespaldo'] ?>"
                    class="btn-ver">

                    Ver

                </a>

            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>