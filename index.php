<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>

<html lang="ru">
<head>
    <meta charset="UTF-8">
<!--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
-->    <title>Сервис оценки заведений</title>
</head>
<body>
    <div class="container">
        <br><h4>Регистрация</h4>
        <form>
            <input type="text" id="reg_nick" placeholder="Никнейм" class="form-control"><br>
            <input type="password" id="reg_pass" placeholder="Пароль" class="form-control"><br>
            <input type="password" id="reg_pass_rep" placeholder="Повтор пароля" class="form-control"><br>
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" id="visitor">Я посетитель</button>
                <button type="button" class="btn btn-secondary" id="owner">Я владелец</button>
            </div>
            <button type="button" class="btn btn-success" id="register">Зарегистрироваться</button>
        </form>
        <div id="reg_error"></div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <div class="container">
        <br><hr><br><h4>Вход</h4>
        <form>
            <input type="text" id="auth_nick" placeholder="Никнейм" class="form-control"><br>
            <input type="password" id="auth_pass" placeholder="Пароль" class="form-control"><br>
            <button type="button" class="btn btn-success" id="authorisation">Войти</button>
        </form>
        <div id="auth_error"></div>
    </div>

    <script>
        let user_type = "visitor";
        $("#visitor").prop("disabled", true);
        $("#owner").on("click", function(){
            user_type = "owner";
            $("#visitor").prop("disabled", false);
            $("#owner").prop("disabled", true);
        });
        $("#visitor").on("click", function(){
            user_type = "visitor";
            $("#owner").prop("disabled", false);
            $("#visitor").prop("disabled", true);
        });

        $("#register").on("click", function(){
            let reg_nick = $("#reg_nick").val().trim(),
                reg_pass = $("#reg_pass").val().trim(),
                reg_pass_rep = $("#reg_pass_rep").val().trim();

            if(reg_nick == ""){
                $("#reg_error").text("Введите никнейм");
                return false;
            }
            if(reg_nick.length > 32){
                $("#reg_error").text("Никнейм не должен превышать 32 символов");
                return false;
            }
            if (reg_pass == ""){
                $("#reg_error").text("Введите пароль");
                return false;
            }
            if (reg_pass != reg_pass_rep){
                $("#reg_error").text("Пароли не совпадают");
                return false;
            }
            $("#reg_error").text("");

            $.ajax({
                url: "ajax/registration.php",
                type: "POST",
                cache: false,
                data: {"login": String(reg_nick), "password": String(reg_pass), "type": user_type},
                dataType: "text",
                beforeSend: function(){
                    $("#register").prop("disabled", true);
                    $("#authorisation").prop("disabled", true);
                },
                success: function (data) {
                    $("#register").prop("disabled", false);
                    $("#authorisation").prop("disabled", false);
                    if (data == "0"){
                        $("#reg_error").text("Пользователь с таким ником уже существует");
                    }
                    else{
                        if (user_type == "visitor") {
                            window.location.href = 'visitor.php?user=' + reg_nick;
                        }
                        else{
                            window.location.href = 'owner.php?user=' + reg_nick;
                        }
                    }
                }
            });
        });

        $("#authorisation").on("click", function() {
            let auth_nick = $("#auth_nick").val().trim(),
                auth_pass = $("#auth_pass").val().trim();

            if(auth_nick == ""){
                $("#auth_error").text("Введите никнейм");
                return false;
            }
            if (auth_pass == ""){
                $("#auth_error").text("Введите пароль");
                return false;
            }
            $("#auth_error").text("");

            $.ajax({
                url: "ajax/authorisation.php",
                type: "POST",
                cache: false,
                data: {"login": auth_nick, "password": auth_pass},
                dataType: "text",
                beforeSend: function(){
                    $("#register").prop("disabled", true);
                    $("#authorisation").prop("disabled", true);
                },
                success: function (data) {
                    $("#register").prop("disabled", false);
                    $("#authorisation").prop("disabled", false);
                    if (data == "0") {
                        $("#auth_error").text("Неверное имя пользователя или пароль");
                    }
                    else if (data == "visitor"){
                        window.location.href = 'visitor.php?user=' + auth_nick;
                    }
                    else if (data == "owner"){
                        window.location.href = 'owner.php?user=' + auth_nick;
                    }
                    else{
                        $("#auth_error").text("Ошибка входа");
                    }
                }
            });
        });
    </script>

</body>
</html>
