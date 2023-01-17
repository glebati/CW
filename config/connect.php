<?php
    $connect = mysqli_connect('localhost', 'root', '', 'coursework');

    if (!$connect)
    {
        die('Error to connect db');
    }
?>
