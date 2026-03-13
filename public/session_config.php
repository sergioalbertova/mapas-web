<?php
session_start();

// Tiempo máximo de inactividad (ej. 8 horas)
$max_inactividad = 8 * 60 * 60;

if (isset($_SESSION['ultimo_movimiento'])) {
    if (time() - $_SESSION['ultimo_movimiento'] > $max_inactividad) {
        session_unset();
        session_destroy();
        header("Location: login.php?msg=session_expired");
        exit;
    }
}

$_SESSION['ultimo_movimiento'] = time();

// Validar sesión activa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=no_session");
    exit;
}
?>
