<?php
    session_start();
    $user = $_GET['user'];
    $_SESSION["_".$user] = false;
    header("location: ../index.php");
?>