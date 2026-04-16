<?php
require "db.php";

/* ============================================================
   PARÁMETROS RECIBIDOS
   ============================================================ */
$inicio  = $_GET['inicio'] ?? date("Y-m-d");
$fin     = $_GET['fin'] ?? date("Y-m-d");
$tecnico = isset($_GET['tecnico']) ? intval($_GET['tecnico']) : null;

$paramsBase = [
    ':inicio' => $inicio . " 00:00:00",
    ':fin'    => $fin . " 23:59:59"
];

/* ============================================================
   FUNCIÓN PARA AGREGAR FILTRO POR TÉCNICO
   ============================================================ */
function filtroTecnicoSQL(&$sql, &$params, $tecnico) {
    if ($tecnico) {
        $sql .= " AND tecnico_asignado = :tecnico";
        $params[':tecnico'] = $tecnico;
    }
}

/* ============================================================
   RESPUESTA JSON
   ============================================================ */
$data = [];

/* ---------- TOTAL ---------- */
$sql = "SELECT COUNT(*) FROM itil_incidentes WHERE fecha_reporte BETWEEN :inicio AND :fin";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['total'] = $stmt->fetchColumn();

/* ---------- CERRADOS ---------- */
$sql = "SELECT COUNT(*) FROM itil_incidentes 
        WHERE estado ILIKE 'Cerrado' 
        AND fecha_reporte BETWEEN :inicio AND :fin";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['resueltos'] = $stmt->fetchColumn(); // mantenemos la clave para no romper el JS


/* ---------- ACTIVOS ---------- */
$sql = "SELECT COUNT(*) FROM itil_incidentes 
        WHERE estado ILIKE ANY (ARRAY['Activo','Abierto','Pendiente','En espera'])
        AND fecha_reporte BETWEEN :inicio AND :fin";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['activos'] = $stmt->fetchColumn();

/* ---------- POR TÉCNICO ---------- */
$sql = "
    SELECT u.id, COALESCE(u.nombre,'Sin técnico') AS nombre, COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY u.id, nombre ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['porTecnico'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------- POR UBICACIÓN ---------- */
$sql = "
    SELECT 
        COALESCE(TRIM(SPLIT_PART(ubicacion_detalle,'/',1)),'Sin ubicación') AS ubicacion,
        COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY ubicacion ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['ubicacion'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================================================
   SALIDA JSON
   ============================================================ */
header('Content-Type: application/json');
echo json_encode($data);
