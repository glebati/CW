<?php
    session_start();
    if (!isset($_GET['user']) || !isset($_SESSION["_".$_GET['user']]) || $_SESSION["_".$_GET['user']] == false){
        header("location: index.php");
    }
    $user = $_GET['user'];
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://api-maps.yandex.ru/2.1/?apikey=b489ece8-579a-4a44-9068-fa6c1bfe5ad4&lang=ru_RU" type="text/javascript"></script>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="config/style.css">
    <title>Сервис оценки заведений</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row" style="margin-top: 15px">
            <div class="col">
                <form action="config/exit.php">
                    <h5 style="margin-left:10px; margin-top:5px; margin-bottom: 20px">
                        <input type="hidden" id="user" name="user" value="<?php /*echo $user*/?>">
                        <button type="submit" class="btn btn-danger" style="margin-right:5px;">Выход</button>
                        <?php /*echo $user*/?>
                    </h5>
                </form>
                <hr>
                <div class="text-center fs-3" id="name"></div><br>
                <div class="text-start fs-5" id="description"></div><br>
                <div class="text-start fs-5" id="rating"></div>
                <div class="text-start fs-5" id="feedback"></div>
                <div class="text-start fs-6" id="feedback_error"></div>
                <div class="text-start fs-5" id="reviews"></div>
            </div>
          <div class="col">
                <table class="table table-bordered table-secondary table-sm" style="position:fixed">
                    <tr>
                        <th scope="col">
                            <div id="map"></div>
                        </th>
                    </tr>
                </table>
            </div>


        </div>
    </div>
</body>
</html>

<script>
    let id = null;
    let user = "<?php echo $user?>";
    ymaps.ready(init);

    function init()
    {
        let myMap = new ymaps.Map("map", {
            center: [59.933420, 30.305673],
            zoom: 15,
            controls: []
        }, {searchControlProvider: 'yandex#search',
            autoFitToViewport: 'always'
        });
        $("#map").width("50%");
        $("#map").height($(window).height() + "px");

        let points;
        $.ajax({
            url: "ajax/mapdb.php",
            type: "POST",
            cache: false,
            async: false,
            data: {"query": ["get"]},
            dataType: "json",
            success: function(data){
                points = data;
            }
        });

        let collection = new ymaps.GeoObjectCollection(null, {
            hasBalloon: false,
            iconColor: '#db3545'
        });
        for (let i = 0; i < points.length; i++){
            let point = new ymaps.Placemark([points[i][1], points[i][2]], {
                balloonContent: 1
            });
            point.events.add('click', function(){
                myMap.setCenter([points[i][1], points[i][2]]);
                id = points[i][0];
                output_information();
            });
            collection.add(point);
        }
        myMap.geoObjects.add(collection);
    }



    function output_information()
    {
        let information;
        $.ajax({
            url: "ajax/mapdb.php",
            type: "POST",
            cache: false,
            async: false,
            data: {"query": ["information", id]},
            dataType: "json",
            success: function(data){
                information = data;
            }
        });
        $("#name").text(information[1]);


        $("#description").empty();
        $("#description").append("<div class='text fw-bold'>Описание:</div>"+information[2]);


        let rating = information[3];
        let round_rating = Math.round(rating);
        let rating_arr = [];
        for (let i = 0; i < 5; i++){
            if (round_rating > i){
                rating_arr[i] = "<span class='active'></span>";
            }
            else{
                rating_arr[i] = "<span></span>";
            }
        }
        rating = Math.round(rating*10)/10;
        $("#rating").empty();
        $("#rating").append("<div class='text fw-bold'>Рейтинг:</div>"+
        "<div class='align-bottom rating-result'>"+rating_arr.join('')+"</div>"+
        "<div style='margin-left:5px'>" + rating+"/5 | отзывы: "+information[4] + "</div><hr>");


        $("#feedback").empty();
        let reviews;
        $.ajax({
            url: "ajax/mapdb.php",
            type: "POST",
            cache: false,
            async: false,
            data: {"query": ["reviews", id]},
            dataType: "json",
            success: function(data){
                reviews = data;
            }
        });
        let review_exists = false,
            review_num = -1;
        for (let i = 0; i < reviews.length; i++){
            if (reviews[i][1] == user){
                review_exists = true;
                review_num = i;
                break;
            }
        }
        if (review_exists)
        {
            for (let i = 0; i < 5; i++){
                if (i <= reviews[review_num][2]-1){
                    rating_arr[i] = "<span class='active'></span>";
                }
                else{
                    rating_arr[i] = "<span></span>";
                }
            }
            $("#feedback").append("<div class='text fw-bold' style='margin-bottom:5px'>Ваш отзыв:</div><div class='align-bottom rating-result'>"+rating_arr.join('')+"</div>");
            $("#feedback").append("<div style='margin-top:5px; margin-bottom:10px' class='text-start fs-5'>"+reviews[review_num][3]+"</div>");
            $("#feedback").append('<button type="button" class="btn btn-danger" onclick="delete_review(); output_information();">Удалить</button><hr>');
        }
        else {
            $("#feedback").append('<div class="text fw-bold" style="margin-bottom:5px">Оставить отзыв:</div><div class="rating-area"><br><input type="radio" id="star-5" name="rating" value="5"><label for="star-5" title="Оценка «5»"></label><input type="radio" id="star-4" name="rating" value="4"><label for="star-4" title="Оценка «4»"></label><input type="radio" id="star-3" name="rating" value="3"><label for="star-3" title="Оценка «3»"></label><input type="radio" id="star-2" name="rating" value="2"><label for="star-2" title="Оценка «2»"></label><input type="radio" id="star-1" name="rating" value="1"><label for="star-1" title="Оценка «1»"></label></div>');
            $("#feedback").append('<textarea class="form-control" style="margin-top:10px; margin-bottom:10px" id="comment" rows="3" placeholder="Комментарий (необязательно)"></textarea>');
            $("#feedback").append('<button type="button" class="btn btn-success" onclick="send_feedback(); output_information();">Отправить</button><hr>');
        }

        $("#reviews").empty();
        if (reviews.length > 0 && !(reviews.length == 1 && review_exists)){
            $("#reviews").append("<div class='text fw-bold' style='margin-bottom:5px'>Отзывы:</div><hr>");
            for (let i = 0; i < reviews.length; i++) if (i != review_num) {
                $("#reviews").append("<div class='text-start fs-5 fw-bold'>" + reviews[i][1] + "</div>");
                for (let j = 0; j < 5; j++) {
                    if (j <= reviews[i][2] - 1) {
                        rating_arr[j] = "<span class='active'></span>";
                    } else {
                        rating_arr[j] = "<span></span>";
                    }
                }
                $("#reviews").append("<div class='align-bottom rating-result'>" + rating_arr.join('') + "</div>");
                $("#reviews").append("<div style='margin-top:5px; margin-bottom:10px' class='text-start fs-5'>" + reviews[i][3] + "</div><hr>");
            }
        }
    }



    function send_feedback()
    {
        let evaluation = 0;
        for (let i = 1; i <= 5; i++){
            if (document.getElementById("star-"+i).checked){
                evaluation = i;
                break;
            }
        }
        if (evaluation == 0){
            $("#feedback_error").append("Вы не поставили оценку<hr>");
        }
        else{
            $("#feedback_error").empty();
            let comment = document.getElementById("comment").value;
            $.ajax({
                url: "ajax/mapdb.php",
                type: "POST",
                cache: false,
                async: false,
                data: {"query": ["add_review", id, user, evaluation, comment]},
                dataType: "json",
                success: function(data){
                }
            });
        }
    }

    function delete_review()
    {
        $.ajax({
            url: "ajax/mapdb.php",
            type: "POST",
            cache: false,
            async: false,
            data: {"query": ["delete_review", id, user]},
            dataType: "json",
            success: function(data){
            }
        });
    }
</script>
