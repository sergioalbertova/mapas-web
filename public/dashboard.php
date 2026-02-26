<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}
?>
<h1>Bienvenido, <?php echo $_SESSION["user_name"]; ?></h1>
