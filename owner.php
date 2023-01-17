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
<!--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">

-->
    <link rel="stylesheet" href="design4.css">
    <title>Сервис оценки заведений</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row" style="margin-top: 15px">

            <div class="col">
                <table class="table table-bordered table-secondary table-sm" style="position:fixed">
                    <tr>
                        <th scope="col">
                            <div id="map"></div>
                        </th>
                    </tr>
                </table>
            </div>
            <div class="col">
                <form action="config/exit.php">
                    <h5 style="margin-left:10px; margin-top:5px; margin-bottom: 20px">
                        <input type="hidden" id="user" name="user" value="<?php echo $user?>">
                        <button type="submit" class="btn btn-danger" style="margin-right:5px;">Выход</button>
                        <?php echo $user?>
                    </h5>
                </form>
                <hr>
                <div class="text-center fs-3" id="title"></div>
                <div class="text-start fs-5" id="information"></div>
            </div>
        </div>
    </div>
</body>
</html>

<script>
    let id = null,
        user = "<?php echo $user?>",
        x = null,
        y = null;
        point_exists = false;
        point = null;
    ymaps.ready(init);

    function init() {
        let myMap = new ymaps.Map("map", {
            center: [59.933420, 30.305673],
            zoom: 15,
            controls: []
        }, {searchControlProvider: 'yandex#search',
            autoFitToViewport: 'always'
        });
        $("#map").width("48.8%");
        $("#map").height($(window).height()*0.94 + "px");

        load_points(myMap);

        myMap.events.add('click', function(event) {
            let coords = event.get('coords');
            id = null;
            y = coords[0];
            x = coords[1];
            if (!point_exists) {
                point = new ymaps.Placemark(coords, {}, {iconColor: "#198654"});
                myMap.geoObjects.add(point);
                point_exists = true;
                edit_information();
            } else {
                myMap.geoObjects.remove(point);
                point.geometry.setCoordinates(coords);
                myMap.geoObjects.add(point);
            }

            $("#save").on("click", function(){
                if (y == coords[0] && x == coords[1]) {
                    $("#save").prop('disable', true);

                    let name = document.getElementById("name").value,
                        description = document.getElementById("description").value;
                    $.ajax({
                        url: "ajax/mapdb.php",
                        type: "POST",
                        cache: false,
                        async: false,
                        data: {"query": ["create_point", coords[0], coords[1], user, name, description]},
                        dataType: "json",
                        success: function (data) {
                            id = data[0];
                            edit_information();
                        }
                    });
                    point_exists = false;
                    myMap.geoObjects.removeAll();
                    load_points(myMap);
                    $("#information").empty();
                    $("#title").empty();
                }
            });
        });
    }



    function edit_information(){
        $("#information").empty();
        let information;
        if (!(id == null)) {
            $("#title").text('Редактировать информацию о заведении');
            $.ajax({
                url: "ajax/mapdb.php",
                type: "POST",
                cache: false,
                async: false,
                data: {"query": ["information", id]},
                dataType: "json",
                success: function (data) {
                    information = data;
                }
            });
        }
        else{
            $("#title").text('Создать новую точку');
            information = ["", "", "", "", ""];
        }
        $("#information").append("<hr><div class='text fw-bold' style='margin-bottom:5px'>Название:</div><input type='text' id='name' placeholder='Название' class='form-control'>");
        document.getElementById("name").value = information[1];
        $("#information").append("<div class='text fw-bold' style='margin-bottom:5px; margin-top:20px'>Описание:</div><textarea class='form-control' style='margin-top:10px; margin-bottom:10px' id='description' rows='7' placeholder='Описание'>");
        document.getElementById("description").value = information[2];
        if (id != null) {
            $("#information").append('<div class="btn-toolbar" style="margin-top:20px" role="toolbar"><button type="button" class="btn btn-success" onclick="save_info(); $(`#information`).empty(); $(`#title`).text(``)">Сохранить</button><button type="button" class="btn btn-danger" style="margin-left:5px" id="delete_info">Удалить</button> <button type="button" class="btn btn-secondary" style="margin-left:5px" onclick="$(`#information`).empty(); $(`#title`).text(``)">Отмена</button></div>');
        }
        else{
            $("#information").append('<div class="btn-toolbar" style="margin-top:20px" role="toolbar"><button type="button" class="btn btn-success" id="save">Сохранить</button></div>');
        }
    }

    function save_info(){
        let name = document.getElementById("name").value,
            description = document.getElementById("description").value;
        $.ajax({
            url: "ajax/mapdb.php",
            type: "POST",
            cache: false,
            async: false,
            data: {"query": ["save_info", id, name, description]},
            dataType: "json",
            success: function(data){
            }
        });
    }



    function load_points(myMap){
        let points;
        $.ajax({
            url: "ajax/mapdb.php",
            type: "POST",
            cache: false,
            async: false,
            data: {"query": ["get_own", user]},
            dataType: "json",
            success: function(data){
                points = data;
            }
        });
        for (let i = 0; i < points.length; i++){
            let i_point = new ymaps.Placemark([points[i][1], points[i][2]], {},{iconColor:"#db3545"});

            i_point.events.add('click', function(){
                myMap.geoObjects.remove(point);
                point_exists = false;
                myMap.setCenter([points[i][1], points[i][2]]);
                id = points[i][0];
                edit_information();
                $("#delete_info").on("click", function(){
                    let sure = confirm("Вы уверены?");
                    if (sure) {
                        $.ajax({
                            url: "ajax/mapdb.php",
                            type: "POST",
                            cache: false,
                            async: false,
                            data: {"query": ["delete_info", id]},
                            dataType: "json",
                            success: function (data) {
                            }
                        });
                        myMap.geoObjects.remove(i_point);
                        $(`#information`).empty();
                        $(`#title`).text(``);
                    }
                });
            });
            myMap.geoObjects.add(i_point);
        }
    }
</script>
