<?php
    require_once "../config/connect.php";

    $query = $_POST['query'];
    switch($query[0]) {
        case "add":
            mysqli_query($connect, "INSERT INTO points (`id`, `y`, `x`) VALUES (NULL, '$query[1]', '$query[2]')");
            break;
        case "get":
            $points = mysqli_fetch_all(mysqli_query($connect, "SELECT * FROM points"));
            echo json_encode($points);
            break;
        case "information":
            $information = mysqli_fetch_row(mysqli_query($connect, "SELECT * FROM information WHERE id = '$query[1]'"));
            echo json_encode($information);
            break;
        case "reviews":
            $reviews = mysqli_fetch_all(mysqli_query($connect, "SELECT * FROM reviews WHERE id = '$query[1]'"));
            echo json_encode($reviews);
            break;
        case "add_review":
            mysqli_query($connect, "INSERT INTO reviews (`id`, `username`, `rating`, `comment`) VALUES ('$query[1]', '$query[2]', '$query[3]', '$query[4]')");
            $information = mysqli_fetch_row(mysqli_query($connect, "SELECT * FROM information WHERE id = '$query[1]'"));
            $rating = ($information[3] * $information[4] + $query[3]) / ($information[4] + 1);
            mysqli_query($connect, "UPDATE information SET rating='$rating', reviews = reviews+1 WHERE id='$query[1]'");
            break;
        case "delete_review":
            $information = mysqli_fetch_row(mysqli_query($connect, "SELECT * FROM information WHERE id = '$query[1]'"));
            $review = mysqli_fetch_row(mysqli_query($connect, "SELECT * FROM reviews WHERE id = '$query[1]' AND `username` = '$query[2]'"));
            if ($information[4] != 1) {
                $rating = ($information[3] * ($information[4]) - $review[2]) / ($information[4] - 1);
            } else {
                $rating = 0;
            }
            mysqli_query($connect, "UPDATE information SET reviews = reviews-1, rating = '$rating' WHERE id = '$query[1]'");
            mysqli_query($connect, "DELETE FROM reviews WHERE id = '$query[1]' AND `username` = '$query[2]'");
            break;
        case "get_own":
            $points = mysqli_fetch_all(mysqli_query($connect, "SELECT * FROM points WHERE owner = '$query[1]'"));
            echo json_encode($points);
            break;
        case "save_info":
            mysqli_query($connect, "UPDATE information SET `name`='$query[2]', `description`='$query[3]' WHERE id = '$query[1]'");
            break;
        case "delete_info":
            mysqli_query($connect, "DELETE FROM information WHERE id = '$query[1]'");
            mysqli_query($connect, "DELETE FROM reviews WHERE id = '$query[1]'");
            mysqli_query($connect, "DELETE FROM points WHERE id = '$query[1]'");
            break;
        case "create_point":
            mysqli_query($connect, "INSERT INTO points (`id`, `y`, `x`, `owner`) VALUES (NULL, '$query[1]', '$query[2]', '$query[3]')");
            $point = mysqli_fetch_row(mysqli_query($connect, "SELECT * FROM points WHERE y = '$query[1]' AND x = '$query[2]'"));
            mysqli_query($connect, "INSERT INTO information (`id`, `name`, `description`, `rating`, `reviews`) VALUES ('$point[0]', '$query[4]', '$query[5]', 0, 0)");
            echo $point[0];
            break;
    }
?>
