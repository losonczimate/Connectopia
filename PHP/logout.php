<?php
session_start();
session_unset();
unset($_SESSION["felhasznalo"]);
session_destroy();
header('Location: login.php');
?>
