<?php
require "db.php";

$q = $_GET['q'] ?? '';
$like = "%$q%";

$sql = $pdo->prepare("
    SELECT categoria,
           subcategoria,
           Desbreve,
           Tema,
           Descdetallada,
           Id,
           Solucion,
           prioridad,
           impacto,
           urgencia
    FROM IncidentesTI
    WHERE
        categoria      LIKE :q
        OR subcategoria   LIKE :q
        OR Desbreve       LIKE :q
        OR Tema           LIKE :q
        OR Descdetallada  LIKE :q
        OR Solucion       LIKE :q
    ORDER BY Id
");
$sql->execute([':q' => $like]);

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
