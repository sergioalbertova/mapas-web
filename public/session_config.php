<?php
// Tiempo de vida de la sesión (24 horas)
$session_lifetime = 86400; // 24 * 60 * 60

// Configurar parámetros de la cookie de sesión
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'secure' => false,   // cámbialo a true si usas HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Ajustar el garbage collector
ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);

// Iniciar sesión
session_start();

// Renovar la cookie en cada request (mantiene la sesión viva mientras el usuario navega)
setcookie(session_name(), session_id(), time() + $session_lifetime, "/");
?>
