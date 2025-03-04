<?php
session_start();

// Проверка на авторизацию
if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверяем, был ли отправлен новый логин
    if (!isset($_POST['new_login']) || empty($_POST['new_login'])) {
        $_SESSION['message'] = "Новый логин не может быть пустым!";
        header("Location: account.php");
        exit();
    }

    $newLogin = trim($_POST['new_login']); // Удаляем лишние пробелы
    $username = $_SESSION['username'];

    // Получаем текущий логин пользователя из базы данных
    $stmt = $conn->prepare("SELECT login FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($currentLogin);
    $stmt->fetch();
    $stmt->close();

    // Если текущий логин не найден, обрабатываем ошибку
    if (!$currentLogin) {
        $_SESSION['message'] = "Не удалось получить текущий логин пользователя.";
        header("Location: account.php");
        exit();
    }

    // Если новый логин тот же, что и текущий, то возвращаем сообщение
    if ($newLogin === $currentLogin) {
        $_SESSION['message'] = "Вы уже используете этот логин!";
        header("Location: account.php");
        exit();
    }

    // Проверка на уникальность нового логина
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
    $stmt->bind_param("s", $newLogin);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $_SESSION['message'] = "Этот логин уже занят!";
        header("Location: account.php");
        exit();
    } else {
        // Обновление логина в базе данных
        $stmt = $conn->prepare("UPDATE users SET login = ? WHERE username = ?");
        $stmt->bind_param("ss", $newLogin, $username);

        if ($stmt->execute()) {
            // Обновляем логин в сессии
            $_SESSION['login'] = $newLogin;
            $_SESSION['message'] = "Логин успешно обновлен!";
        } else {
            // Обработка ошибки при выполнении запроса
            $_SESSION['message'] = "Ошибка при обновлении логина: " . $stmt->error;
        }

        $stmt->close();
        header("Location: acount.php"); // Переадресация на страницу учетной записи
        exit();
    }
}
