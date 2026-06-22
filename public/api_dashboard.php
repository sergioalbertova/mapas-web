<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'] ?? 'itil';

$inicio = $_GET['inicio'] ?? date("Y-m-d");
$fin    = $_GET['fin'] ?? date("Y-m-d");

$tecnico = $_GET['tecnico'] ?? null;

$params = [
    ':inicio' => $inicio . " 00:00:00",
    ':fin'    => $fin . " 23:59:59"
];

$filtroTec = "";
if ($tecnico) {
    $filtroTec = " AND idingeniero = :tec";
    $params[':tec'] = $tecnico;
}

/* ================= ACTIVIDADES ================= */
if ($modulo == 'actividades') {

    $total = $pdo->prepare("
        SELECT COUNT(*) FROM actividades_extras
        WHERE fecha_inicio BETWEEN :inicio AND :fin $filtroTec
    ");
    $total->execute($params);
    $total = $total->fetchColumn();

    $comp = $pdo->prepare("
        SELECT COUNT(*) FROM actividades_extras
        WHERE estatus='completado'
        AND fecha_inicio BETWEEN :inicio AND :fin $filtroTec
    ");
    $comp->execute($params);
    $comp = $comp->fetchColumn();

    $proc = $pdo->prepare("
        SELECT COUNT(*) FROM actividades_extras
        WHERE estatus='en proceso'
        AND fecha_inicio BETWEEN :inicio AND :fin $filtroTec
    ");
    $proc->execute($params);
    $proc = $proc->fetchColumn();

    $tec = $pdo->prepare("
        SELECT u.id, u.nombre, COUNT(*) total
        FROM actividades_extras ae
        JOIN usuarios u ON u.id = ae.idingeniero
        WHERE fecha_inicio BETWEEN :inicio AND :fin
        GROUP BY u.id, u.nombre
    ");
    $tec->execute($params);
    $tec = $tec->fetchAll(PDO::FETCH_ASSOC);

    $estado = $pdo->prepare("
        SELECT estatus, COUNT(*) total
        FROM actividades_extras
        WHERE fecha_inicio BETWEEN :inicio AND :fin $filtroTec
        GROUP BY estatus
    ");
    $estado->execute($params);
    $estado = $estado->fetchAll(PDO::FETCH_ASSOC);

} else {

    /* ================= ITIL ================= */

    $total = $pdo->prepare("
        SELECT COUNT(*) FROM itil_incidentes
        WHERE fecha_reporte BETWEEN :inicio AND :fin
    ");
    $total->execute($params);
    $total = $total->fetchColumn();

    $comp = $pdo->prepare("
        SELECT COUNT(*) FROM itil_incidentes
        WHERE estado='Cerrado'
        AND fecha_reporte BETWEEN :inicio AND :fin
    ");
    $comp->execute($params);
    $comp = $comp->fetchColumn();

    $proc = $pdo->prepare("
        SELECT COUNT(*) FROM itil_incidentes
        WHERE estado!='Cerrado'
        AND fecha_reporte BETWEEN :inicio AND :fin
    ");
    $proc->execute($params);
    $proc = $proc->fetchColumn();

    $tec = $pdo->query("
        SELECT u.id, u.nombre, COUNT(*) total
        FROM itil_incidentes i
        JOIN usuarios u ON u.id = i.tecnico_asignado
        GROUP BY u.id, u.nombre
    ")->fetchAll(PDO::FETCH_ASSOC);

    $estado = $pdo->prepare("
        SELECT estado, COUNT(*) total
        FROM itil_incidentes
        WHERE fecha_reporte BETWEEN :inicio AND :fin
        GROUP BY estado
    ");
    $estado->execute($params);
    $estado = $estado->fetchAll(PDO::FETCH_ASSOC);
}

/* RESPONSE */

echo json_encode([
    "total"=>$total,
    "completadas"=>$comp,
    "proceso"=>$proc,

    "tecIDs"=>array_column($tec,'id'),
    "tecLabels"=>array_column($tec,'nombre'),
    "tecData"=>array_column($tec,'total'),

    "estadoLabels"=>array_map(fn($x)=>$x['estado'] ?? $x['estatus'],$estado),
    "estadoData"=>array_column($estado,'total')
]);