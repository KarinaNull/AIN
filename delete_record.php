<?php
session_start();

// Проверка на авторизацию
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

// Подключение к базе данных
require 'db.php';

if (isset($_SESSION['user_id'])) {
    $changed_by = $_SESSION['user_id']; // ID текущего пользователя
    $conn->query("SET @changed_by = {$changed_by}");
} else {
    $_SESSION['error_message'] = "Не удалось определить пользователя, выполняющего изменения.";
    header("Location: mainPage.php");
    exit();
}

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

try {
    // Проверяем таблицу и удаляем зависимые записи
    if ($table === 'users') {
        // Удаление связанных записей в requests
        $deleteRequestsQuery = "DELETE FROM `requests` WHERE user_id = $id";
        if (!$conn->query($deleteRequestsQuery)) {
            throw new Exception("Ошибка удаления связанных заявок: " . $conn->error);
        }
    }

    // Удаляем запись из таблицы
    $query = "DELETE FROM `$table` WHERE id = $id";
    if (!$conn->query($query)) {
        throw new Exception("Ошибка удаления записи: " . $conn->error);
    }

    // Запись успешна
    $_SESSION['success_message'] = "Запись успешно удалена!";
} catch (Exception $e) {
    // Ошибка при удалении
    $_SESSION['error_message'] = $e->getMessage();
}

// Перенаправление после операции
header("Location: view_table.php?table=" . urlencode($table));
exit();

// Закрытие соединения после всех операций с БД
$conn->close();
