<?php

require "auth.php";
require "db.php";

/* ==========================
   VALIDAR ID
========================== */

if (!isset($_GET['id'])) {

    header("Location: respaldos_usuarios.php");
    exit;

}

$idrespaldo = (int)$_GET['id'];

/* ==========================
   VERIFICAR QUE EXISTA
========================== */

$stmt = $pdo->prepare("
    SELECT idrespaldo
    FROM respaldos_usuarios
    WHERE idrespaldo = ?
");

$stmt->execute([$idrespaldo]);

if (!$stmt->fetch()) {

    header("Location: respaldos_usuarios.php");
    exit;

}

/* ==========================
   ELIMINAR
========================== */

$stmt = $pdo->prepare("
    DELETE
    FROM respaldos_usuarios
    WHERE idrespaldo = ?
");

$stmt->execute([$idrespaldo]);

/* ==========================
   REDIRECCIÓN
========================== */

header("Location: respaldos_usuarios.php?delete=1");
exit;