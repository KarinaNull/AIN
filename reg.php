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

        <div class="registration-container">
            <h1>Регистрация</h1>
            <form action="register.php" method="POST" class="registration-form">
                <div class="form-group">
                    <label for="email">Логин</label>
                    <input name="login" type="email" autocomplete="email" id="email" placeholder="Введите e-mail" required>
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <div class="password-container">
                        <input name="password" type="password" autocomplete="new-password" id="password" placeholder="Введите пароль" required>
                        <span class="password-toggle"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                <button type="submit" class="submit-btn"><a href="auth.php#account">Зарегистрироваться</a></button>
            </form>
            <div class="login-link">
                <p>У вас уже есть аккаунт? <a href="auth.php#account">Войти</a></p>
            </div>
            <div class="footer-text">
                <img src="img/AIn.png" alt="Иконка" class="footer-text-logo">
                <p>Коммерческие услуги, предоставляемые Архобейкером, корпорацией FGC и ассоциированным лицензиатом Mastercard International Inc.</p>
            </div>
        </div>
    </div>
</body>

</html>