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

// Получаем имя таблицы из GET-параметра
$table = isset($_GET['table']) ? $_GET['table'] : '';

// Защита от SQL инъекций
$table = mysqli_real_escape_string($conn, $table);

// Получение структуры таблицы для создания формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Формируем запрос на добавление данных
    $columns = [];
    $values = [];

    // Получаем данные из формы
    foreach ($_POST as $key => $value) {
        $columns[] = mysqli_real_escape_string($conn, $key);
        $values[] = "'" . mysqli_real_escape_string($conn, $value) . "'";
    }

    // Формируем запрос
    $query = "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ")";

    if ($conn->query($query)) {
        echo "Запись успешно добавлена!";
        header("Location: view_table.php?table=" . urlencode($table)); // Перенаправление после добавления
        exit();
    } else {
        echo "Ошибка добавления записи: " . $conn->error;
    }
}

// Закрытие соединения после всех операций с БД
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить запись в таблицу: <?php echo htmlspecialchars($table); ?></title>
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

    <div class="dashboard">
        <h2>Добавить запись в таблицу: <?php echo htmlspecialchars($table); ?></h2>

        <form action="add_record.php?table=<?php echo htmlspecialchars($table); ?>" method="POST">
            <?php
            // Получаем структуру таблицы
            $stmt = $conn->prepare("DESCRIBE `$table`");
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($field, $type, $null, $key, $default, $extra);

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