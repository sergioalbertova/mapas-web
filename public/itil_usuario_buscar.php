<?php
require __DIR__ . "/session_config.php";
require __DIR__ . "/db.php";

$texto = "%" . $_GET['q'] . "%";

$stmt = $pdo->prepare("
    SELECT idu, nomuser 
    FROM activeuser
    WHERE nomuser ILIKE ?
    ORDER BY nomuser
    LIMIT 20
");
$stmt->execute([$texto]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
