<?php
session_start();

require 'db.php';

// Функция для очистки входных данных (защита от XSS)
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Очистка входных данных
    $login = sanitizeInput($_POST["login"]);
    $password = $_POST["password"];

    if (empty($login) || empty($password)) {
        die("Пожалуйста, заполните все поля.");
    }

    // Проверка учетных данных с использованием подготовленного запроса (защита от SQL-инъекций)
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE login = ?");
    //Привязка переменных к параметрам подготавливаемого запроса "s" указывает, что $login является строкой.
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->store_result();
    //После выполнения $stmt->execute(), запрос будет выглядеть примерно так SELECT id, username, password_hash FROM users WHERE login = 'example@example.com';
    $stmt->bind_result($id, $username, $passwordHash);



    if ($stmt->num_rows === 1) {
        $stmt->fetch();
        if (password_verify($password, $passwordHash)) {
            // Регенерация ID сессии для защиты от фиксации сессий
            session_regenerate_id(true);
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $id;

            // Перенаправление на личный кабинет
            header("Location: acount.php");
            exit();
        } else {
            // Неверный пароль
            $_SESSION['error'] = "Неверный логин или пароль";
            header("Location: auth.php");
            exit();
        }
    } else {
        // Пользователь не найден
        $_SESSION['error'] = "Такой пользователь не найден.";
        header("Location: auth.php");
        exit();
    }

    // Закрытие соединения
    $stmt->close();
}
