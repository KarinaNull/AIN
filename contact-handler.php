<?php
// contact-handler.php - Обработчик формы
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    // Проверка, существует ли пользователь с таким email
    $userQuery = "SELECT id FROM users WHERE login = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if ($userResult->num_rows > 0) {
        // Пользователь найден, получаем его id
        $user = $userResult->fetch_assoc();
        $userId = $user['id'];
    } else {
        // Пользователь не найден, устанавливаем userId как NULL
        $userId = NULL;
    }

    // Добавляем заявку в таблицу requests
    $requestQuery = "INSERT INTO requests (user_id, status, phone, message, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($requestQuery);
    $status = 'В обработке';

    // Если пользователь не найден, передаем NULL для user_id, иначе передаем ID пользователя
    $stmt->bind_param("isss", $userId, $status, $phone, $message);

    // Выполняем запрос
    $stmt->execute();

    // Если пользователь не найден, перенаправляем на главную страницу с ошибкой
    if ($userId === NULL) {
        header('Location: mainPage.php?status=error');
        exit();
    }

    // Перенаправляем на главную страницу с параметром успеха
    header('Location: mainPage.php?status=success');
    exit();

    $stmt->close();
    $conn->close();
}
