<?php
//require 'db.php';
//require 'auth.php';

require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

// Obtener lista de problemas
$sql = "
    SELECT p.id, p.titulo, p.estado, p.fecha_creacion,
           u.nombre AS tecnico
    FROM problemas p
    LEFT JOIN usuarios u ON u.id = p.tecnico_responsable
    ORDER BY p.fecha_creacion DESC
";
$stmt = $pdo->query($sql);
$problemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Problemas</title>
    <link rel="stylesheet" href="tu_css.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Problemas</h2>
        <a href="problema_nuevo.php" class="btn btn-primary">+ Nuevo problema</a>
    </div>

    <div class="card">
        <div class="card-body">

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Técnico responsable</th>
                        <th>Fecha creación</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($problemas as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['titulo']) ?></td>
                        <td><?= $p['estado'] ?></td>
                        <td><?= $p['tecnico'] ?: 'Sin asignar' ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                        <td>
                            <a href="problema_ver.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

</main>

</body>
</html>
