<?php
require "db.php";

$q = $_GET['q'] ?? '';
$like = "%$q%";

$sql = $pdo->prepare("
    SELECT categoria,
           subcategoria,
           desbreve,
           Tema,
           descdetallada,
           id,
           solucion,
           prioridad,
           impacto,
           urgencia
    FROM IncidentesTI
    WHERE
        categoria      LIKE :q
        OR subcategoria   LIKE :q
        OR desbreve       LIKE :q
        OR tema           LIKE :q
        OR descdetallada  LIKE :q
        OR solucion       LIKE :q
    ORDER BY Id
");
$sql->execute([':q' => $like]);

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
