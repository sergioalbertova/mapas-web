<?php
require "auth.php";
require "db.php";

$modulo = $_GET['modulo'];
$inicio = $_GET['inicio']." 00:00:00";
$fin    = $_GET['fin']." 23:59:59";

$params = [':inicio'=>$inicio, ':fin'=>$fin];

if($modulo=='actividades'){

$total = $pdo->query("SELECT COUNT(*) FROM actividades_extras")->fetchColumn();
$comp  = $pdo->query("SELECT COUNT(*) FROM actividades_extras WHERE estatus='completado'")->fetchColumn();
$proc  = $pdo->query("SELECT COUNT(*) FROM actividades_extras WHERE estatus='en proceso'")->fetchColumn();

$mttr = $pdo->query("SELECT AVG(EXTRACT(EPOCH FROM (fecha_fin-fecha_inicio))/3600) FROM actividades_extras WHERE fecha_fin IS NOT NULL")->fetchColumn();

$tec = $pdo->query("
SELECT u.nombre, COUNT(*) total
FROM actividades_extras ae
JOIN usuarios u ON u.id=ae.idingeniero
GROUP BY u.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$tipo = $pdo->query("
SELECT ca.actividad, COUNT(*) total
FROM actividades_extras ae
JOIN catalogo_actividades ca ON ca.idactividad=ae.idactividad
GROUP BY ca.actividad
")->fetchAll(PDO::FETCH_ASSOC);

$estado = $pdo->query("
SELECT estatus, COUNT(*) total
FROM actividades_extras
GROUP BY estatus
")->fetchAll(PDO::FETCH_ASSOC);

}else{

$total = $pdo->query("SELECT COUNT(*) FROM itil_incidentes")->fetchColumn();
$comp  = $pdo->query("SELECT COUNT(*) FROM itil_incidentes WHERE estado='Cerrado'")->fetchColumn();
$proc  = $pdo->query("SELECT COUNT(*) FROM itil_incidentes WHERE estado!='Cerrado'")->fetchColumn();

$mttr = 0;

$tec = $pdo->query("
SELECT u.nombre, COUNT(*) total
FROM itil_incidentes i
JOIN usuarios u ON u.id=i.tecnico_asignado
GROUP BY u.nombre
")->fetchAll(PDO::FETCH_ASSOC);

$tipo = $pdo->query("
SELECT titulo, COUNT(*) total
FROM itil_incidentes
GROUP BY titulo
")->fetchAll(PDO::FETCH_ASSOC);

$estado = $pdo->query("
SELECT estado, COUNT(*) total
FROM itil_incidentes
GROUP BY estado
")->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode([

"total"=>$total,
"completadas"=>$comp,
"proceso"=>$proc,
"mttr"=>round($mttr,2),

"tecLabels"=>array_column($tec,'nombre'),
"tecData"=>array_column($tec,'total'),

"tipoLabels"=>array_column($tipo,$modulo=='itil'?'titulo':'actividad'),
"tipoData"=>array_column($tipo,'total'),

"estadoLabels"=>array_map(fn($x)=>$x['estado'] ?? $x['estatus'],$estado),
"estadoData"=>array_column($estado,'total')

]);