<?php
require "auth.php";
require "db.php";

/* =========================
   PARÁMETROS
=========================*/
$modulo  = $_GET['modulo'] ?? 'itil';
$inicio  = $_GET['inicio'] ?? date("Y-m-d");
$fin     = $_GET['fin'] ?? date("Y-m-d");
$tecnico = $_GET['tecnico'] ?? null;

$params = [
    ':inicio' => $inicio . " 00:00:00",
    ':fin'    => $fin . " 23:59:59"
];

/* =========================
   FILTRO TÉCNICO DINÁMICO
=========================*/
$filtroTecITIL = "";
$filtroTecACT  = "";

if ($tecnico) {
    $filtroTecITIL = " AND i.tecnico_asignado = :tec";
    $filtroTecACT  = " AND ae.idingeniero = :tec";
    $params[':tec'] = $tecnico;
}

/* ======================================================
   ACTIVIDADES EXTRA
======================================================*/
if ($modulo === 'actividades') {

    // TOTAL
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM actividades_extras ae
        WHERE fecha_inicio BETWEEN :inicio AND :fin $filtroTecACT
    ");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // COMPLETADAS
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM actividades_extras ae
        WHERE estatus = 'completado'
        AND fecha_inicio BETWEEN :inicio AND :fin $filtroTecACT
    ");
    $stmt->execute($params);
    $completadas = $stmt->fetchColumn();

    // EN PROCESO
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM actividades_extras ae
        WHERE estatus = 'en proceso'
        AND fecha_inicio BETWEEN :inicio AND :fin $filtroTecACT
    ");
    $stmt->execute($params);
    $proceso = $stmt->fetchColumn();

    // TÉCNICOS (CORREGIDO ✅)
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, COUNT(*) total
        FROM actividades_extras ae
        JOIN usuarios u ON u.id = ae.idingeniero
        WHERE fecha_inicio BETWEEN :inicio AND :fin
        $filtroTecACT
        GROUP BY u.id, u.nombre
        ORDER BY total DESC
    ");
    $stmt->execute($params);
    $tec = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ESTATUS
    $stmt = $pdo->prepare("
        SELECT estatus, COUNT(*) total
        FROM actividades_extras ae
        WHERE fecha_inicio BETWEEN :inicio AND :fin $filtroTecACT
        GROUP BY estatus
    ");
    $stmt->execute($params);
    $estado = $stmt->fetchAll(PDO::FETCH_ASSOC);

}

/* ======================================================
   ITIL
======================================================*/
else {

    // TOTAL
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM itil_incidentes i
        WHERE fecha_reporte BETWEEN :inicio AND :fin $filtroTecITIL
    ");
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // COMPLETADAS
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM itil_incidentes i
        WHERE estado = 'Cerrado'
        AND fecha_reporte BETWEEN :inicio AND :fin $filtroTecITIL
    ");
    $stmt->execute($params);
    $completadas = $stmt->fetchColumn();

    // EN PROCESO
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM itil_incidentes i
        WHERE estado != 'Cerrado'
        AND fecha_reporte BETWEEN :inicio AND :fin $filtroTecITIL
    ");
    $stmt->execute($params);
    $proceso = $stmt->fetchColumn();

    // TÉCNICOS (CORREGIDO ✅)
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, COUNT(*) total
        FROM itil_incidentes i
        JOIN usuarios u ON u.id = i.tecnico_asignado
        WHERE fecha_reporte BETWEEN :inicio AND :fin
        $filtroTecITIL
        GROUP BY u.id, u.nombre
        ORDER BY total DESC
    ");
    $stmt->execute($params);
    $tec = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ESTADO
    $stmt = $pdo->prepare("
        SELECT estado, COUNT(*) total
        FROM itil_incidentes i
        WHERE fecha_reporte BETWEEN :inicio AND :fin $filtroTecITIL
        GROUP BY estado
    ");
    $stmt->execute($params);
    $estado = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ======================================================
   RESPUESTA JSON
======================================================*/
echo json_encode([
    "total"        => (int)$total,
    "completadas"  => (int)$completadas,
    "proceso"      => (int)$proceso,

    "tecIDs"       => array_column($tec, 'id'),
    "tecLabels"    => array_column($tec, 'nombre'),
    "tecData"      => array_map('intval', array_column($tec, 'total')),

    "estadoLabels" => array_map(fn($x)=>$x['estado'] ?? $x['estatus'], $estado),
    "estadoData"   => array_map('intval', array_column($estado, 'total'))
]);
