<?php
require "session_config.php";

/* =========================================
   ✅ VALIDAR SESIÓN ACTIVA
========================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>