<?php
    require_once "../config/connect.php";

    $login = $_POST['login'];
    $password = $_POST['password'];

    $count = mysqli_fetch_row(mysqli_query($connect, "SELECT count(*) FROM `users` WHERE `login` = '$login'"));
    if ($count[0] == 0){
        echo "0";
    }
    else {
        $user = mysqli_fetch_row(mysqli_query($connect, "SELECT * FROM `users` WHERE `login` = '$login'"));
        $type = $user[2];
        $hash = $user[3];
        $salt = $user[4];
        if ($hash == hash('sha256', $password . $salt)) {
            session_start();
            $_SESSION["_".$login] = true;
            echo "$type";
        } else {
            echo "0";
        }
    }
?>
