<?php

session_start();

// Регенерация ID сессии для защиты от фиксации сессии
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}

// Генерация nonce для CSP - CSP требует, чтобы каждый внешний скрипт или стиль имел уникальный nonce чтобы разрешить выполнение только тех скриптов, которые имеют этот nonce
$nonce = bin2hex(random_bytes(16));

// Проверка на авторизацию
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

// Подключение к базе данных
require 'db.php';

// Получаем username из сессии
$username = $_SESSION['username'];

// Запрос к базе данных для получения роли пользователя
$stmt = $conn->prepare("SELECT login, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($login, $role);
$stmt->fetch();
$stmt->close();

// Получаем user_id пользователя
$stmt = $conn->prepare("SELECT id FROM users WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Запрос для получения заявок
$stmt = $conn->prepare("SELECT id, user_id, status, created_at FROM requests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requestsResult = $stmt->get_result();
$stmt->close();

// Обработка выхода из аккаунта
if (isset($_GET['logout'])) {
    session_unset(); // Удаляем все переменные сессии
    session_destroy(); // Уничтожаем сессию
    header("Location: auth.php"); // Перенаправляем на страницу входа
    exit();
}

// Запрос к базе данных для получения пути к аватару
$stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

// Проверяем, найден ли пользователь
if ($stmt->num_rows > 0) {
    $stmt->bind_result($avatar);
    $stmt->fetch();
    $avatarPath = 'avatars/' . htmlspecialchars($avatar); // Экранируем путь к аватару
} else {
    // Если пользователя нет, используем аватар по умолчанию
    $avatarPath = 'img/ava.png';
}

// Генерация CSRF-токена. CSRF-токен используется для проверки того, что запрос действительно отправлен с этого сайта, а не с поддельной страницы. Токен должен быть уникальным для каждого пользователя и храниться в сессии
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link href="https://fonts.googleapis.com/css2?family=Tenor+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!-- Content Security Policy (CSP)  это стандарт безопасности, который позволяет ограничить ресурсы, которые могут быть загружены или выполнены на странице. Злоумышленник не сможет внедрить вредоносные скрипты, так как они будут заблокированы CSP-->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'nonce-<?php echo $nonce; ?>'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;">
</head>

<body>
    <div class="navbar">
        <ul class="nav-list">
            <li><a href="mainPage.php#about">О нас</a></li>
            <li><a href="mainPage.php#important">Важно</a></li>
            <li><a href="mainPage.php#contacts">Контакты</a></li>
            <li><a href="mainPage.php#reviews">Отзывы</a></li>
        </ul>

        <div class="account">
            <a href="#account">Учетная запись</a>
        </div>
    </div>

    <div class="dashboard" id="account">
        <div class="header">
            <div class="profile">
                <div class="profile">
                    <img id="avatarPreview" src="<?php echo $avatarPath; ?>" alt="Avatar" class="profile-avatar">
                </div>

                <div class="profile-info">
                    <div class="edit-btn-container">
                        <button id="openLoginModal" class="edit-btn">Изменить логин</button>
                        <button id="openPasswordModal" class="edit-btn">Изменить пароль</button>
                    </div>
                    <h2>Здравствуйте, <?php echo htmlspecialchars($username); ?></h2>
                    <p>Роль: <?php echo htmlspecialchars($role); ?></p>
                    <p>Почта: <?php echo htmlspecialchars($login); ?></p>
                    <!-- Кнопка для выхода из аккаунта -->
                    <a href="?logout=true" class="logout-btn">Выйти</a>

                    <!-- Уведомление об успешном обновлении -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="notificationAcount" id="notificationAcount">
                            <?php echo htmlspecialchars($_SESSION['message']); ?>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tabs">
            <a href="#" class="tab active">Заявки</a>
        </div>

        <div id="personal" class="tab-content hidden">
            <h2>Личные данные</h2>
            <form action="update_personal.php" method="POST">
                <!-- Для каждой формы на странице необходимо включать CSRF-токен, чтобы защитить все запросы.
    Хотя токен одинаковый для всех форм на странице, он остается уникальным для каждого пользователя. -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>

                <label for="email">Электронная почта</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($login); ?>" readonly>

                <label for="role">Роль</label>
                <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($role); ?>" readonly>

                <button type="button" id="openLoginModalBtn">Изменить логин</button>
                <button type="button" id="openPasswordModalBtn">Изменить пароль</button>
            </form>
        </div>

        <?php if ($role === 'moderator'): ?>
            <div class="admin-panel">
                <a href="admin_panel.php">Панель администратора</a>
            </div>
        <?php endif; ?>

        <?php
        $stmt = $conn->prepare("SELECT id, user_id, status, created_at FROM requests WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $requestsResult = $stmt->get_result();
        ?>
        <div class="content">
            <div class="requests">
                <?php while ($request = $requestsResult->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="request-body"></div>
                        <div class="request-footer">
                            <p>Статус: <?php echo htmlspecialchars($request['status']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>

                <div class="create-request-card">
                    <div class="create-icon">+</div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для изменения логина -->
        <div id="loginModal" class="modal-main hidden">
            <div class="modal-content-main">
                <span class="close" data-modal-id="loginModal">&times;</span>
                <h3>Изменение логина</h3>
                <form action="update_login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="email" name="new_login" required placeholder="Введите новый email">
                    <button type="submit">Обновить</button>
                </form>
            </div>
        </div>

        <!-- Модальное окно для изменения пароля -->
        <div id="passwordModal" class="modal-main hidden">
            <div class="modal-content-main">
                <span class="close" data-modal-id="passwordModal">&times;</span>
                <h3>Изменение пароля</h3>
                <form action="update_password.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="password" name="new_password" required placeholder="Введите новый пароль">
                    <button type="submit">Обновить</button>
                </form>
            </div>
        </div>
    </div>
    <script nonce="<?php echo $nonce; ?>" src="script.js" defer></script>
</body>

</html>