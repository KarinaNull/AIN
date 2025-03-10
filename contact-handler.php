<?php
// Подключение к базе данных
require_once 'db.php';

// Функция для проверки reCAPTCHA
function verifyRecaptcha($response)
{
    $secretKey = "6LfTBPAqAAAAAIWlOtEvSwcTdfynXgnos37x3G6U"; // Ваш Secret Key
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = [
        'secret' => $secretKey,
        'response' => $response,
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка капчи
    $captchaResponse = $_POST['g-recaptcha-response'];
    $recaptchaResult = verifyRecaptcha($captchaResponse);

    if (!$recaptchaResult['success']) {
        die("Ошибка: Капча не пройдена.");
    }

    // Очистка входных данных
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

    // Проверка email на корректность
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Ошибка: Введите корректный E-mail.");
    }

    // Проверка телефона
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
    $stmt->bind_param("isss", $userId, $status, $phone, $message);

    // Выполняем запрос
    if ($stmt->execute()) {
        if ($userId === NULL) {
            header('Location: mainPage.php?status=error');
            exit();
        } else {
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
