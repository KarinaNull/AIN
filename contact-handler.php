<?php
// contact-handler.php - Обработчик формы
require_once 'db.php';

// Функция для очистки входных данных (защита от XSS)
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Очистка входных данных
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $message = sanitizeInput($_POST['message']);

    // Проверка email на корректность
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ошибка: Введите корректный E-mail.");
    }

    // Проверка телефона (простая валидация)
    if (empty($phone)) {
        die("Ошибка: Введите номер телефона.");
    }

    // Проверка сообщения
    if (empty($message)) {
        die("Ошибка: Введите сообщение.");
    }

    // Проверка, существует ли пользователь с таким email
    $userQuery = "SELECT id FROM users WHERE login = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("s", $email); // Защита от SQL-инъекций
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
    $stmt->bind_param("isss", $userId, $status, $phone, $message); // Защита от SQL-инъекций

    // Выполняем запрос
    if ($stmt->execute()) {
        if ($userId === NULL) {
            // Если пользователь не найден, перенаправляем на главную страницу с ошибкой
            header('Location: mainPage.php?status=error');
            exit();
        } else {
            // Перенаправляем на главную страницу с параметром успеха
            header('Location: mainPage.php?status=success');
            exit();
        }
    } else {
        // Обработка ошибки при выполнении запроса
        die("Ошибка при отправке заявки: " . $stmt->error);
    }

    // Закрытие соединения
    $stmt->close();
    $conn->close();
}
