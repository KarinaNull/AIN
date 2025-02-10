<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['new_password'];
    $username = $_SESSION['username'];

    // Получаем текущий пароль пользователя из базы данных
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($currentPasswordHash);
    $stmt->fetch();
    $stmt->close();

    // Проверка на совпадение паролей
    if (password_verify($newPassword, $currentPasswordHash)) {
        $_SESSION['message'] = "Новый пароль не может быть таким же, как старый!";
        header("Location: acount.php"); // Перенаправляем обратно в личный кабинет
        exit();
    }

    // Хеширование нового пароля
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Обновление пароля в базе данных
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashedPassword, $username);
    $stmt->execute();
    $stmt->close();

    // Сообщение об успешном обновлении пароля
    $_SESSION['message'] = "Пароль успешно изменен!";

    header("Location: acount.php");  // Перенаправляем обратно в личный кабинет
    exit();
}
