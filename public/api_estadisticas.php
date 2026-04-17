<?php
require "db.php";

/* ===========================
   PARÁMETROS DE FECHA
=========================== */
$hoy = date("Y-m-d");

$inicio  = $_GET['inicio'] ?? $hoy;
$fin     = $_GET['fin']    ?? $hoy;
$tecnico = isset($_GET['tecnico']) ? intval($_GET['tecnico']) : null;

/* === LÓGICA DE RANGOS (MISMA QUE EN itil_estadisticas.php) === */
if (isset($_GET['rango'])) {

    if ($_GET['rango'] === "hoy") {
        $inicio = $hoy;
        $fin    = $hoy;
    }

    if ($_GET['rango'] === "7") {
        $inicio = date("Y-m-d", strtotime("-6 days"));
        $fin    = $hoy;
    }

    if ($_GET['rango'] === "mes") {
        $inicio = date("Y-m-01");
        $fin    = $hoy;
    }
}

$paramsBase = [
    ':inicio' => $inicio . " 00:00:00",
    ':fin'    => $fin . " 23:59:59"
];

/* ===========================
   FUNCIÓN PARA FILTRAR POR TÉCNICO
=========================== */
function filtroTecnicoSQL(&$sql, &$params, $tecnico) {
    if ($tecnico) {
        $sql .= " AND tecnico_asignado = :tecnico";
        $params[':tecnico'] = $tecnico;
    }
}

$data = [];

/* ===========================
   KPI: TOTAL
=========================== */
$sql = "SELECT COUNT(*) FROM itil_incidentes WHERE fecha_reporte BETWEEN :inicio AND :fin";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['total'] = (int)$stmt->fetchColumn();

/* ===========================
   KPI: CERRADOS
=========================== */
$sql = "SELECT COUNT(*) FROM itil_incidentes 
        WHERE estado ILIKE 'Cerrado'
        AND fecha_reporte BETWEEN :inicio AND :fin";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['resueltos'] = (int)$stmt->fetchColumn();

/* ===========================
   KPI: ACTIVOS
=========================== */
$sql = "SELECT COUNT(*) FROM itil_incidentes 
        WHERE estado ILIKE ANY (ARRAY['Activo','Abierto','Pendiente','En espera'])
        AND fecha_reporte BETWEEN :inicio AND :fin";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['activos'] = (int)$stmt->fetchColumn();

/* ===========================
   KPI: MTTR
=========================== */
$sql = "
    SELECT ROUND(AVG(EXTRACT(EPOCH FROM (fecha_resolucion - fecha_reporte)) / 3600), 2)
    FROM itil_incidentes
    WHERE fecha_resolucion IS NOT NULL
    AND fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['mttr'] = (float)($stmt->fetchColumn() ?: 0);

/* ===========================
   KPI: SLA
=========================== */
$sql = "
    SELECT 
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL 
                         AND fecha_resolucion - fecha_reporte <= INTERVAL '24 hours') AS dentro,
        COUNT(*) FILTER (WHERE fecha_resolucion IS NOT NULL) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sla = $stmt->fetch(PDO::FETCH_ASSOC);
$data['sla'] = ($sla['total'] > 0)
    ? round(($sla['dentro'] / $sla['total']) * 100, 1)
    : 0;

/* ===========================
   KPI: BACKLOG
=========================== */
$sql = "
    SELECT COUNT(*) 
    FROM itil_incidentes
    WHERE estado ILIKE 'En progreso'
    AND fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['backlog'] = (int)$stmt->fetchColumn();

/* ===========================
   POR TÉCNICO
=========================== */
$sql = "
    SELECT 
        u.id AS id,
        COALESCE(u.nombre, 'Sin técnico') AS nombre,
        COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY u.id, u.nombre ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['porTecnico'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   POR TIPO
=========================== */
$sql = "
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY titulo ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['porTipo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   POR ESTADO
=========================== */
$sql = "
    SELECT estado, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY estado ORDER BY total DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['porEstado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   TENDENCIA MENSUAL
=========================== */
$sql = "
    SELECT TO_CHAR(fecha_reporte, 'YYYY-MM') AS mes, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY mes ORDER BY mes";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['mensual'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   POR HORA
=========================== */
$sql = "
    SELECT EXTRACT(HOUR FROM fecha_reporte) AS hora, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY hora ORDER BY hora";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['porHora'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   POR DÍA DE LA SEMANA
=========================== */
$sql = "
    SELECT EXTRACT(DOW FROM fecha_reporte) AS dow, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY dow ORDER BY dow";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['porDiaSemana'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   TOP TÉCNICOS
=========================== */
$sql = "
    SELECT 
        COALESCE(u.nombre, 'Sin técnico') AS tecnico,
        COUNT(*) AS total
    FROM itil_incidentes i
    LEFT JOIN usuarios u ON u.id = i.tecnico_asignado
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY tecnico ORDER BY total DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['topTecnicos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   TOP CATEGORÍAS
=========================== */
$sql = "
    SELECT titulo, COUNT(*) AS total
    FROM itil_incidentes
    WHERE fecha_reporte BETWEEN :inicio AND :fin
";
$params = $paramsBase;
filtroTecnicoSQL($sql, $params, $tecnico);
$sql .= " GROUP BY titulo ORDER BY total DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data['topCategorias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================
   POR UBICACIÓN
=========================== */
$sql = "
    SELECT 
        TRIM(
            REGEXP_REPLACE(
                COALESCE(TRIM(SPLIT_PART(ubicacion_detalle, '/', 1)), 'Sin ubicación'),
                'piso.*$',
                '',
                'i'
            )
        ) AS ubicacion,
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

/* ===========================
   SALIDA JSON
=========================== */
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
