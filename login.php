<?php
session_start();

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST["login"];
    $password = $_POST["password"];

    if (empty($login) || empty($password)) {
        die("Пожалуйста, заполните все поля.");
    }


    // Проверка учетных данных
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $passwordHash);

    if ($stmt->num_rows === 1) {
        $stmt->fetch();
        if (password_verify($password, $passwordHash)) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $id;  // После успешного логина

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
}
