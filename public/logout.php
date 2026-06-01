<?php
session_start();
require "db.php";

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

setcookie("remember_token", "", time() - 3600, "/");

session_destroy();
header("Location: login.php");
exit;
?>
