<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIN</title>
    <link href="https://fonts.googleapis.com/css2?family=Tenor+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <!-- CSP для защиты от XSS -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;">
</head>

<body>
    <div class="container-auth">

        <div class="navbar">
            <ul class="nav-list">
                <li><a href="mainPage.php#about">О нас</a></li>
                <li><a href="mainPage.php#important">Важно</a></li>
                <li><a href="mainPage.php#contacts">Контакты</a></li>
                <li><a href="mainPage.php#reviews">Отзывы</a></li>
            </ul>
            <div class="account">
                <a href="auth.php#account">Учетная запись</a>
            </div>
        </div>

        <div class="auth-container">
            <h1>Авторизация</h1>
            <?php
            // Показать сообщение об ошибке, если оно есть
            if (isset($_SESSION['error'])) {
                echo "<div class='notification error' id='error-message'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                unset($_SESSION['error']);  // Удаляем сообщение после его отображения
            }
            ?>

            <!-- Форма отправляется на login.php -->
            <form class="auth-form" action="login.php" method="POST">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input type="text" name="login" id="login" placeholder="Введите логин" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" placeholder="Введите пароль" required>
                        <span class="password-toggle"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                <button type="submit" class="submit-btn">Авторизоваться</button>
            </form>

            <div class="auth-link">
                <p>У вас еще нет аккаунта? <a href="reg.php#account">Зарегистрироваться</a></p>
            </div>

            <div class="auth-footer-text">
                <img src="img/AIn.png" alt="Иконка" class="auth-footer-logo">
                <p>Коммерческие услуги, предоставляемые Архобейкером, корпорацией FGC и ассоциированным лицензиатом Mastercard International Inc.</p>
            </div>
        </div>

        <script>
            // Скрываем сообщение через 5 секунд
            setTimeout(function() {
                const errorMessage = document.getElementById('error-message');
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }
            }, 5000);
        </script>

</body>

</html>