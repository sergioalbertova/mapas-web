<?php
require "session_config.php";
date_default_timezone_set('America/Mexico_City');
require "db.php";

if (!isset($_POST['accion'])) {
    $_SESSION['error'] = "Acción no especificada.";
    header("Location: itil_problemas.php");
    exit;
}

$accion = $_POST['accion'];

/* ============================================================
   ACCIÓN: CREAR PROBLEMA
   ============================================================ */
if ($accion === "crear_problema") {

    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $causa_raiz = trim($_POST['causa_raiz'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Investigando');
    $tecnico = $_POST['tecnico_responsable'] ?? null;

    if ($titulo === '' || $descripcion === '' || !$tecnico) {
        $_SESSION['error'] = "Faltan campos obligatorios.";
        header("Location: itil_problema_nuevo.php");
        exit;
    }

    try {
        $sql = "
            INSERT INTO problemas (titulo, descripcion, causa_raiz, estado, tecnico_responsable)
            VALUES (?, ?, ?, ?, ?)
            RETURNING id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $descripcion, $causa_raiz, $estado, $tecnico]);

        $nuevoID = $stmt->fetchColumn();

        $_SESSION['mensaje'] = "Problema registrado correctamente (ID $nuevoID).";
        header("Location: itil_problemas.php");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error al guardar el problema: " . $e->getMessage();
        header("Location: itil_problema_nuevo.php");
        exit;
    }
}

/* ============================================================
   ACCIÓN: ACTUALIZAR PROBLEMA
   ============================================================ */
if ($accion === "actualizar_problema") {

    $id = (int) ($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $causa_raiz = trim($_POST['causa_raiz'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $tecnico = $_POST['tecnico_responsable'] ?? null;

    if ($id <= 0 || $titulo === '' || $descripcion === '' || !$tecnico) {
        $_SESSION['error'] = "Faltan campos obligatorios.";
        header("Location: itil_problema_ver.php?id=$id");
        exit;
    }

    try {
        // Si el estado pasa a RESUELTO, registrar fecha_resolucion
        if ($estado === "Resuelto") {
            $sql = "
                UPDATE problemas
                SET titulo = ?, descripcion = ?, causa_raiz = ?, estado = ?, tecnico_responsable = ?, 
                    fecha_resolucion = NOW()
                WHERE id = ?
            ";
            $params = [$titulo, $descripcion, $causa_raiz, $estado, $tecnico, $id];
        } else {
            $sql = "
                UPDATE problemas
                SET titulo = ?, descripcion = ?, causa_raiz = ?, estado = ?, tecnico_responsable = ?
                WHERE id = ?
            ";
            $params = [$titulo, $descripcion, $causa_raiz, $estado, $tecnico, $id];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['mensaje'] = "Cambios guardados correctamente.";
        header("Location: itil_problema_ver.php?id=$id");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error al actualizar el problema: " . $e->getMessage();
        header("Location: itil_problema_ver.php?id=$id");
        exit;
    }
}

/* ============================================================
   ACCIÓN: CAMBIAR ESTADO (si decides usar modal después)
   ============================================================ */
if ($accion === "cambiar_estado") {

    $id = (int) ($_POST['id'] ?? 0);
    $estado_nuevo = trim($_POST['estado_nuevo'] ?? '');

    if ($id <= 0 || $estado_nuevo === '') {
        $_SESSION['error'] = "Datos incompletos.";
        header("Location: itil_problema_ver.php?id=$id");
        exit;
    }

    try {
        if ($estado_nuevo === "Resuelto") {
            $sql = "
                UPDATE problemas
                SET estado = ?, fecha_resolucion = NOW()
                WHERE id = ?
            ";
        } else {
            $sql = "
                UPDATE problemas
                SET estado = ?
                WHERE id = ?
            ";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$estado_nuevo, $id]);

        $_SESSION['mensaje'] = "Estado actualizado correctamente.";
        header("Location: itil_problema_ver.php?id=$id");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error al cambiar estado: " . $e->getMessage();
        header("Location: itil_problema_ver.php?id=$id");
        exit;
    }
}

/* ============================================================
   ACCIÓN: REASIGNAR TÉCNICO (si decides agregar modal después)
   ============================================================ */
if ($accion === "reasignar_tecnico") {

    $id = (int) ($_POST['id'] ?? 0);
    $tecnico = $_POST['tecnico_nuevo'] ?? null;

    if ($id <= 0 || !$tecnico) {
        $_SESSION['error'] = "Datos incompletos.";
        header("Location: itil_problema_ver.php?id=$id");
        exit;
    }

    try {
        $sql = "UPDATE problemas SET tecnico_responsable = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tecnico, $id]);

        $_SESSION['mensaje'] = "Técnico reasignado correctamente.";
        header("Location: itil_problema_ver.php?id=$id");
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error al reasignar técnico: " . $e->getMessage();
        header("Location: itil_problema_ver.php?id=$id");
        exit;
    }
}

/* ============================================================
   ACCIÓN NO RECONOCIDA
   ============================================================ */
$_SESSION['error'] = "Acción no válida.";
header("Location: itil_problemas.php");
exit;
