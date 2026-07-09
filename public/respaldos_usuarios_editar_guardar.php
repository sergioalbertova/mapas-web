<?php

require "auth.php";
require "db.php";

if (!isset($_POST['idrespaldo'])) {

    header("Location: respaldos_usuarios.php");
    exit;

}

$idrespaldo = $_POST['idrespaldo'];

$usuario = trim($_POST['usuario'] ?? '');
$iddisco = $_POST['iddisco'] ?? '';
$tamano_gb = $_POST['tamano_gb'] ?? '';
$observaciones = trim($_POST['observaciones'] ?? '');

/* ==========================
   VALIDACIONES
========================== */

if (
    $usuario === '' ||
    $iddisco === '' ||
    $tamano_gb === ''
) {

    header(
        "Location: respaldos_usuarios_editar.php?id="
        . $idrespaldo
    );

    exit;

}

/* ==========================
   OBTENER RESPALDO ACTUAL
========================== */

$stmt = $pdo->prepare("
    SELECT *
    FROM respaldos_usuarios
    WHERE idrespaldo = ?
");

$stmt->execute([$idrespaldo]);

$actual = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$actual) {

    header("Location: respaldos_usuarios.php");
    exit;

}

/* ==========================
   VALIDAR ESPACIO
========================== */

$stmt = $pdo->prepare("
    SELECT
        d.tamano_total_gb,

        COALESCE(
            SUM(r.tamano_gb),
            0
        ) AS utilizado

    FROM discos_respaldo d

    LEFT JOIN respaldos_usuarios r
        ON r.iddisco = d.iddisco

    WHERE d.iddisco = ?

    GROUP BY
        d.iddisco,
        d.tamano_total_gb
");

$stmt->execute([$iddisco]);

$disco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$disco) {

    header("Location: respaldos_usuarios.php");
    exit;

}

/*
   Si estoy editando el mismo respaldo,
   le restamos el tamaño actual para
   no contarlo doble.
*/

$utilizado = $disco['utilizado'];

if ($actual['iddisco'] == $iddisco) {

    $utilizado -= $actual['tamano_gb'];

}

$disponible =
    $disco['tamano_total_gb']
    - $utilizado;

if ($tamano_gb > $disponible) {

    die(
        "El respaldo excede el espacio disponible del disco."
    );

}

/* ==========================
   ACTUALIZAR
========================== */

$stmt = $pdo->prepare("
    UPDATE respaldos_usuarios
    SET

        usuario = ?,
        iddisco = ?,
        tamano_gb = ?,
        observaciones = ?

    WHERE idrespaldo = ?
");

$stmt->execute([
    $usuario,
    $iddisco,
    $tamano_gb,
    $observaciones,
    $idrespaldo
]);

/* ==========================
   FIN
========================== */

header("Location: respaldos_usuarios.php?edit=1");
exit;