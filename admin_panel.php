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

// Получаем параметры из GET-запроса
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc'; // По умолчанию сортировка A → Z

// Получение всех таблиц из базы данных
$tablesQuery = "SHOW TABLES";
$tablesResult = $conn->query($tablesQuery);

// Инициализируем пустой массив $filteredTables для хранения имён таблиц, которые прошли фильтрацию
$filteredTables = [];

// Проверяем, есть ли в базе данных хотя бы одна таблица
if ($tablesResult->num_rows > 0) {
    // Цикл перебора всех таблиц из результата запроса "SHOW TABLES"
    while ($table = $tablesResult->fetch_array()) {
        // Проверяем два условия:
        // 1. Таблица не должна называться 'user_change_log' (исключаем её из списка)
        // 2. Имя таблицы должно содержать поисковый запрос ($searchTerm), регистр символов не учитывается
        if ($table[0] !== 'user_change_log' && stripos($table[0], $searchTerm) !== false) {
            $filteredTables[] = $table[0];
        }
    }
}

// Сортируем таблицы в зависимости от параметра $order
if ($order === 'asc') {
    sort($filteredTables); // Сортировка A → Z
} elseif ($order === 'desc') {
    rsort($filteredTables); // Сортировка Z → A
}

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
    </div>

    <!-- Поисковая форма -->
    <div class="search-bar">
        <form method="GET" action="admin_panel.php">
            <input type="text" name="search" placeholder="Поиск по таблицам..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit">Поиск</button>
        </form>
    </div>

    <div class="sort-dropdown">
        <form method="GET" action="admin_panel.php">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <label for="order">Сортировка:</label>
            <select id="order" name="order" onchange="this.form.submit()">
                <option value="asc" <?php echo $order === 'asc' ? 'selected' : ''; ?>>A → Z</option>
                <option value="desc" <?php echo $order === 'desc' ? 'selected' : ''; ?>>Z → A</option>
            </select>
        </form>
    </div>

    <div class="dashboardData">
        <h2>Управление данными</h2>
        <?php if (!empty($filteredTables)): ?>
            <h3>Список таблиц базы данных:</h3>
            <ul>
                <?php foreach ($filteredTables as $table): ?>
                    <li>
                        <a href="view_table.php?table=<?php echo htmlspecialchars($table); ?>">
                            <?php echo htmlspecialchars($table); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Таблицы не найдены в базе данных.</p>
        <?php endif; ?>
    </div>
</body>

</html>