<?php
require "auth.php";
require "db.php";

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT id, ip, user_agent, fecha_creacion, fecha_expira
    FROM user_sessions
    WHERE user_id = ?
    ORDER BY fecha_creacion DESC
");

$stmt->execute([$user_id]);
$sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Sesiones activas</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>IP</th>
        <th>Dispositivo</th>
        <th>Inicio</th>
        <th>Expira</th>
        <th>Acción</th>
    </tr>

    <?php foreach ($sesiones as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['ip']) ?></td>
            <td><?= htmlspecialchars($s['user_agent']) ?></td>
            <td><?= $s['fecha_creacion'] ?></td>
            <td><?= $s['fecha_expira'] ?></td>
            <td>
                <a href="cerrar_sesion.php?id=<?= $s['id'] ?>">Cerrar</a>
            </td>
        </tr>
    <?php endforeach; ?>

</table>