<?php
require "db.php";

$q = $_GET['q'] ?? '';
$q = trim($q);

$sql = $pdo->prepare("
    SELECT categoria,
           subcategoria,
           desbreve,
           tema,
           descdetallada,
           id,
           solucion,
           prioridad,
           impacto,
           urgencia
    FROM IncidentesTI
    WHERE
        categoria      ILIKE :q
        OR subcategoria   ILIKE :q
        OR desbreve       ILIKE :q
        OR tema           ILIKE :q
        OR descdetallada  ILIKE :q
        OR solucion       ILIKE :q
    ORDER BY Id
");

$like = '%' . $q . '%';
$sql->execute([':q' => $like]);

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
