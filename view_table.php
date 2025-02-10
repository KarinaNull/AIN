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
// Подключение к базе данных
require 'db.php';

// Получаем username из сессии и роль
$username = $_SESSION['username'];
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

// Получение имени таблицы из GET-параметра
$table = isset($_GET['table']) ? $_GET['table'] : '';

// Защита от SQL инъекций: проверяем, существует ли таблица в базе данных
$table = mysqli_real_escape_string($conn, $table); // Защита от SQL инъекций для имени таблицы

// Проверяем, существует ли таблица
$query = "SHOW TABLES LIKE '$table'";  // Прямое использование переменной для имени таблицы
$result = $conn->query($query);

if ($result->num_rows == 0) {
    echo "Таблица не существует.";
    exit();
}

// Запрос для получения всех данных из выбранной таблицы
$query = "SELECT * FROM `$table`";  // Экранируем имя таблицы
$result = $conn->query($query);

// Получение структуры таблицы для динамического создания формы
$stmt = $conn->prepare("DESCRIBE `$table`");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($field, $type, $null, $key, $default, $extra);

// Закрытие соединения после всех операций с БД
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление таблицей: <?php echo htmlspecialchars($table ?: ''); ?></title>
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

    <div class="dashboardDataView">
        <h2>Таблица: <?php echo htmlspecialchars($table ?: ''); ?></h2>

        <!-- Таблица с данными -->
        <?php if ($result->num_rows > 0): ?>
            <table border="1">
                <thead>
                    <tr>
                        <?php
                        // Выводим заголовки колонок
                        $columns = $result->fetch_fields();
                        foreach ($columns as $column) {
                            // Проверка на null перед использованием htmlspecialchars
                            echo "<th>" . htmlspecialchars($column->name ?: '') . "</th>";
                        }
                        ?>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <?php foreach ($row as $key => $value): ?>
                                <td><?php echo htmlspecialchars($value ?: ''); ?></td>
                            <?php endforeach; ?>
                            <td>
                                <a href="edit_record.php?table=<?php echo htmlspecialchars($table ?: ''); ?>&id=<?php echo $row['id']; ?>">Изменить</a>
                                <a href="delete_record.php?table=<?php echo htmlspecialchars($table ?: ''); ?>&id=<?php echo $row['id']; ?>">Удалить</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Нет данных для отображения.</p>
        <?php endif; ?>

        <h3>Добавить новую запись</h3>
        <form action="add_record.php?table=<?php echo htmlspecialchars($table ?: ''); ?>" method="POST">
            <!-- Формы для добавления записи будут зависеть от структуры таблицы -->
            <?php
            while ($stmt->fetch()) {
                if ($key !== 'PRI') { // исключаем id, если оно автоинкрементное
                    echo "<label for='{$field}'>" . ucfirst($field) . ":</label>";
                    echo "<input type='text' name='{$field}' required><br><br>";
                }
            }
            $stmt->close();
            ?>
            <button type="submit">Добавить запись</button>
        </form>
    </div>
</body>

</html>