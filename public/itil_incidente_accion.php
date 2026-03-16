<?php
// itil_incidente_accion.php
require "session_config.php";
require "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido.");
}

$accion = $_POST['accion'] ?? null;
$incidente_id = isset($_POST['incidente_id']) ? (int)$_POST['incidente_id'] : 0;

if (!$accion || !$incidente_id) {
    die("Datos incompletos.");
}

/* OBTENER INCIDENTE ACTUAL */
$stmt = $pdo->prepare("SELECT * FROM itil_incidentes WHERE id = ?");
$stmt->execute([$incidente_id]);
$incidente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$incidente) {
    die("Incidente no encontrado.");
}

/* Usuario actual (activeuser.idu en sesión) */
$usuario_actual_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
if (!$usuario_actual_id) {
    die("Sesión no válida.");
}

/* REGISTRAR EN HISTORIAL */
function registrar_historial($pdo, $incidente_id, $usuario_id, $estado_anterior, $estado_nuevo) {
    $sql = "INSERT INTO itil_incidente_historial (incidente_id, usuario_id, estado_anterior, estado_nuevo)
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$incidente_id, $usuario_id, $estado_anterior, $estado_nuevo]);
}

/* REDIRECCIÓN */
function volver($id) {
    header("Location: itil_incidente_ver.php?id=" . $id);
    exit;
}

switch ($accion) {

    /* ============================
       CAMBIAR ESTADO
       ============================ */
    case 'cambiar_estado':
        $estado_nuevo = $_POST['estado_nuevo'] ?? null;
        if (!$estado_nuevo) die("Estado nuevo requerido.");

        $estado_anterior = $incidente['estado'] ?? 'Abierto';

        /* 🚫 BLOQUEO: No permitir Resuelto sin solución */
        $solucion_actual = isset($incidente['solucion']) ? trim((string)$incidente['solucion']) : '';
        if ($estado_nuevo === 'Resuelto' && $solucion_actual === '') {
            $_SESSION['error'] = "Debes registrar una solución antes de marcar el incidente como Resuelto.";
            volver($incidente_id);
        }

        $sql = "UPDATE itil_incidentes SET estado = :estado";
        $params = [':estado' => $estado_nuevo, ':id' => $incidente_id];

        if ($estado_nuevo === 'En progreso' && empty($incidente['fecha_asignacion'])) {
            $sql .= ", fecha_asignacion = now()";
        }
        if ($estado_nuevo === 'Resuelto' && empty($incidente['fecha_resolucion'])) {
            $sql .= ", fecha_resolucion = now()";
        }
        if ($estado_nuevo === 'Cerrado' && empty($incidente['fecha_cierre'])) {
            $sql .= ", fecha_cierre = now()";
        }

        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        registrar_historial($pdo, $incidente_id, $usuario_actual_id, $estado_anterior, $estado_nuevo);

        volver($incidente_id);
        break;

        case 'asociar_problema':

    // Bloqueo ITIL: no permitir cambios si el incidente está cerrado
    if ($incidente['estado'] === 'Cerrado') {
        $_SESSION['error'] = "No puedes asociar un problema a un incidente cerrado.";
        volver($incidente_id);
    }

    // Obtener el problema seleccionado
    $problema_id = $_POST['problema_id'] ?? null;

    // Guardar la relación en la base de datos
    $sql = "UPDATE itil_incidentes SET problema_id = :pid WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':pid' => $problema_id ?: null,
        ':id'  => $incidente_id
    ]);

    // Registrar historial
    $descripcion_hist = $problema_id
        ? "Incidente asociado al problema #$problema_id"
        : "Se eliminó la asociación con el problema";

    registrar_historial($pdo, $incidente_id, $usuario_actual_id, "Problema", $descripcion_hist);

    $_SESSION['ok'] = "Relación con problema actualizada correctamente.";
    volver($incidente_id);
    break;
    /* ============================
       REASIGNAR TÉCNICO
       ============================ */
    case 'reasignar_tecnico':
        $tecnico_nuevo = isset($_POST['tecnico_nuevo']) ? (int)$_POST['tecnico_nuevo'] : 0;
        if (!$tecnico_nuevo) die("Técnico nuevo requerido.");

        $tecnico_anterior = $incidente['tecnico_asignado'] ?? null;

        $sql = "UPDATE itil_incidentes 
                SET tecnico_asignado = :tec, fecha_asignacion = COALESCE(fecha_asignacion, now())
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tec' => $tecnico_nuevo,
            ':id'  => $incidente_id
        ]);

        registrar_historial(
            $pdo,
            $incidente_id,
            $usuario_actual_id,
            "Técnico: " . ($tecnico_anterior !== null ? $tecnico_anterior : 'N/D'),
            "Técnico: " . $tecnico_nuevo
        );

        volver($incidente_id);
        break;

    /* ============================
       AGREGAR NOTA
       ============================ */
    case 'agregar_nota':
        $nota_raw = $_POST['nota'] ?? '';
        $nota = trim((string)$nota_raw);
        if ($nota === '') die("Nota vacía.");

        $sql = "INSERT INTO itil_incidente_notas (incidente_id, usuario_id, nota)
                VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$incidente_id, $usuario_actual_id, $nota]);

        volver($incidente_id);
        break;

    /* ============================
       REGISTRAR SOLUCIÓN
       ============================ */
    case 'registrar_solucion':
        $sol_raw = $_POST['solucion'] ?? '';
        $solucion = trim((string)$sol_raw);
        if ($solucion === '') die("Solución vacía.");

        $estado_anterior = $incidente['estado'] ?? 'Abierto';
        $estado_nuevo = $estado_anterior;

        if ($estado_anterior !== 'Resuelto' && $estado_anterior !== 'Cerrado') {
            $estado_nuevo = 'Resuelto';
        }

        $sql = "UPDATE itil_incidentes 
                SET solucion = :sol, estado = :estado, fecha_resolucion = COALESCE(fecha_resolucion, now())
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sol'    => $solucion,
            ':estado' => $estado_nuevo,
            ':id'     => $incidente_id
        ]);

        if ($estado_nuevo !== $estado_anterior) {
            registrar_historial($pdo, $incidente_id, $usuario_actual_id, $estado_anterior, $estado_nuevo);
        }

        volver($incidente_id);
        break;

    default:
        die("Acción no soportada.");
}