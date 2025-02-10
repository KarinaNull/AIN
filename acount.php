<?php
session_start();
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
    $avatarPath = 'avatars/' . $avatar; // Строим полный путь к аватару
} else {
    // Если пользователя нет, можно использовать аватар по умолчанию
    $avatarPath = 'img/ava.png';
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
    <style>
        .modal-main {
            display: none;
            /* Скрыть модальные окна по умолчанию */
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            overflow: auto;
            justify-content: center;
            align-items: center;
        }

        .modal-content-main {
            background-color: #fff;
            color: black;
            padding: 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 20px;
            position: absolute;
            top: 30%;
            left: 35%;
            /* Расстояние между элементами */
        }

        /* Заголовок */
        .modal-content-main h3 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        /* Поля ввода */
        .modal-content-main input[type="email"],
        .modal-content-main input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            /* Расстояние между полями */
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .modal-content-main input[type="email"]:focus,
        .modal-content-main input[type="password"]:focus {
            border-color: #6FDBD4;
            outline: none;
        }

        /* Кнопка отправки */
        .modal-content-main button {
            background-color: #6FDBD4;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .modal-content-main button:hover {
            background-color: #57C2B5;
        }

        /* Кнопка закрытия */
        .close {
            color: #aaa;
            font-size: 30px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover,
        .close:focus {
            color: #000;
        }

        .edit-btn-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            position: absolute;
            right: 10%;
        }

        .edit-btn {
            background-color: rgba(169, 169, 169, 0.8);
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 5px;
        }

        .edit-btn:hover {
            background-color: #6FDBD4;
        }

        .logout-btn {
            color: black;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
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
                        <button onclick="openModal('loginModal')" class="edit-btn">Изменить логин</button>
                        <button onclick="openModal('passwordModal')" class="edit-btn">Изменить пароль</button>
                    </div>
                    <h2>Здравствуйте, <?php echo htmlspecialchars($username); ?></h2>
                    <p>Роль: <?php echo htmlspecialchars($role); ?></p>
                    <p>Почта: <?php echo htmlspecialchars($login); ?></p>
                    <!-- Кнопка для выхода из аккаунта -->
                    <a href="?logout=true" class="logout-btn">Выйти</a>

                    <!-- Уведомление об успешном обновлении -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="notificationAcount" id="notificationAcount">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>

                    <script>
                        window.onload = function() {
                            var notificationAcount = document.getElementById("notificationAcount");

                            if (notificationAcount) {
                                notificationAcount.style.display = "block";
                                setTimeout(function() {
                                    notificationAcount.style.opacity = 0;
                                    setTimeout(function() {
                                        notificationAcount.style.display = "none";
                                    }, 1000);
                                }, 5000);
                            }
                        };
                    </script>

                </div>
            </div>
        </div>

        <div class="tabs">
            <a href="#" class="tab active">Заявки</a>
            <!-- <a href="#" class="tab">Личные данные</a> -->
        </div>



        <div id="personal" class="tab-content" style="display: none;">
            <h2>Личные данные</h2>
            <form action="update_personal.php" method="POST">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>

                <label for="email">Электронная почта</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($login); ?>" readonly>

                <label for="role">Роль</label>
                <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($role); ?>" readonly>

                <button type="button" onclick="openModal('loginModal')">Изменить логин</button>
                <button type="button" onclick="openModal('passwordModal')">Изменить пароль</button>
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
                        <div class="request-body">
                        </div>
                        <div class="request-footer">
                            <p>Статус: <?php echo htmlspecialchars($request['status']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>

                <div class="create-request-card">
                    <div class="create-icon">+</div>
                </div>

                <script>
                    const createRequestCard = document.querySelector('.create-request-card');
                    createRequestCard.addEventListener('click', function() {
                        window.location.href = "mainPage.php#contact-form";
                    });
                </script>
            </div>
        </div>

        <!-- Модальное окно для изменения логина -->
        <div id="loginModal" class="modal-main">
            <div class="modal-content-main">
                <span class="close" onclick="closeModal('loginModal')">&times;</span>
                <h3>Изменение логина</h3>
                <form action="update_login.php" method="POST">
                    <input type="email" name="new_login" required placeholder="Введите новый email">
                    <button type="submit">Обновить</button>
                </form>
            </div>
        </div>

        <!-- Модальное окно для изменения пароля -->
        <div id="passwordModal" class="modal-main">
            <div class="modal-content-main">
                <span class="close" onclick="closeModal('passwordModal')">&times;</span>
                <h3>Изменение пароля</h3>
                <form action="update_password.php" method="POST">
                    <input type="password" name="new_password" required placeholder="Введите новый пароль">
                    <button type="submit">Обновить</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "flex";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        window.onclick = function(event) {
            var loginModal = document.getElementById("loginModal");
            var passwordModal = document.getElementById("passwordModal");

            if (event.target === loginModal) {
                closeModal('loginModal');
            }

            if (event.target === passwordModal) {
                closeModal('passwordModal');
            }
        };
    </script>



</body>

</html>