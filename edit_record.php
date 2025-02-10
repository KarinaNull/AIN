<?php
session_start();

// Проверка на авторизацию
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

// Подключение к базе данных
require 'db.php';

$changed_by = $_SESSION['user_id']; // ID текущего пользователя
$conn->query("SET @changed_by = {$changed_by}");

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

// Получаем имя таблицы и id записи из GET-параметров
$table = isset($_GET['table']) ? $_GET['table'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Защита от SQL инъекций
$table = mysqli_real_escape_string($conn, $table);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updates = [];
    foreach ($_POST as $key => $value) {
        $updates[] = "`$key` = '" . mysqli_real_escape_string($conn, $value) . "'";
    }
    $query = "UPDATE `$table` SET " . implode(", ", $updates) . " WHERE id = $id";
    if ($conn->query($query)) {
        // Успешное обновление
        $_SESSION['success_message'] = "Запись успешно обновлена!";
        header("Location: view_table.php?table=" . urlencode($table)); // Перенаправление после обновления
        exit();
    } else {
        // Ошибка обновления
        $_SESSION['error_message'] = "Ошибка обновления записи: " . $conn->error;
        header("Location: edit_record.php?table=" . urlencode($table) . "&id=$id");
        exit();
    }
}

// Получение записи для редактирования
$query = "SELECT * FROM `$table` WHERE id = $id";
$result = $conn->query($query);
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать запись в таблице: <?php echo htmlspecialchars($table); ?></title>
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
    <div class="dashboardEdit">
        <h2>Редактировать запись в таблице: <?php echo htmlspecialchars($table); ?></h2>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form action="edit_record.php?table=<?php echo htmlspecialchars($table); ?>&id=<?php echo $id; ?>" method="POST">
            <?php
            // Получаем структуру таблицы
            $stmt = $conn->prepare("DESCRIBE `$table`");
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($field, $type, $null, $key, $default, $extra);
            while ($stmt->fetch()) {
                if ($key !== 'PRI') { // исключаем id, если оно автоинкрементное
                    echo "<label for='{$field}'>" . ucfirst($field) . ":</label>";
                    echo "<input type='text' name='{$field}' value='" . htmlspecialchars($row[$field] ?: '') . "' required><br><br>";
                }
            }
            $stmt->close();
            ?>
            <button type="submit">Обновить запись</button>
        </form>
    </div>
</body>

</html>