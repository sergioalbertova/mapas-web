<?php
require "auth.php";
require "db.php";

$id = $_GET['id'] ?? null;

if ($id) {

    $stmt = $pdo->prepare("
        DELETE FROM user_sessions 
        WHERE id = ? AND user_id = ?
    ");

    $stmt->execute([$id, $_SESSION['user_id']]);
}

header("Location: mis_sesiones.php");
exit;