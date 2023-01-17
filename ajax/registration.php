<?php
    require_once "../config/connect.php";

    $login = $_POST['login'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    $count = mysqli_fetch_row(mysqli_query($connect, "SELECT count(*) FROM `users` WHERE `login` = '$login'"));
    if ($count[0] > 0){
        echo "0";
        return;
    }

    $salt = random_bytes(16);
    $hash = hash('sha256', $password . $salt);
    mysqli_query($connect, "INSERT INTO `users` (`id`, `login`, `type`, `hash`, `salt`) VALUES (NULL, '$login', '$type', '$hash', '$salt')");
    session_start();
    $_SESSION["_".$login] = true;
    echo "1";
?>
