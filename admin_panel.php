<?php
session_start();


// Проверка на авторизацию
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

// Подключение к базе данных
require 'db.php';

// Получение роли пользователя из базы данных
$username = $_SESSION['username'];
$query = "SELECT role FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

// Проверка роли
if ($role !== 'moderator') {
    header("Location: mainPage.php"); // Перенаправление на главную для не модераторов
    exit();
}

// Получаем username из сессии
$username = $_SESSION['username'];

// Запрос к базе данных для получения роли пользователя
$stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

// Проверка, что роль пользователя - moderator
if ($role !== 'moderator') {
    header("Location: mainPage.php");
    exit();
}

// Получение всех таблиц из базы данных для админ-панели
$tablesQuery = "SHOW TABLES";
$tablesResult = $conn->query($tablesQuery);

// Закрываем соединение
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель</title>
    <link href="https://fonts.googleapis.com/css2?family=Tenor+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="navbar">
        <ul class="nav-list">
            <li><a href="admin_panel.php">Админ-панель</a></li>
            <li><a href="mainPage.php">Главная</a></li>
        </ul>

        <!-- <div class="account">
            <button onclick="history.back() " class="back-button">Назад</button>
        </div> -->
    </div>

    <div class="dashboardData">
        <h2>Управление данными</h2>

        <?php if ($tablesResult->num_rows > 0): ?>
            <h3>Список таблиц базы данных:</h3>
            <ul>
                <?php while ($table = $tablesResult->fetch_array()): ?>
                    <?php if ($table[0] !== 'user_change_log'): // Исключаем таблицу user_change_log 
                    ?>
                        <li>
                            <a href="view_table.php?table=<?php echo htmlspecialchars($table[0]); ?>">
                                <?php echo htmlspecialchars($table[0]); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Таблицы не найдены в базе данных.</p>
        <?php endif; ?>
    </div>

</body>

</html>