<?php
require "session_config.php";
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

    // Validaciones básicas
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
   ACCIÓN NO RECONOCIDA
   ============================================================ */
$_SESSION['error'] = "Acción no válida.";
header("Location: itil_problemas.php");
exit;
