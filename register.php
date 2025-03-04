<?php
session_start();
require 'db.php';

// Функция для генерации уникального имени пользователя
function generateUsername($conn)
{
    $base = 'User';
    do {
        $username = $base . rand(1000, 9999);
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    return $username;
}

// Функция для получения случайного аватара
function getRandomAvatar()
{
    $avatarDir = 'avatars/';
    // glob() — это встроенная функция PHP, которая находит файлы, соответствующие заданному шаблону
    // Без  флага в конце {} будут игнорироваться, и поиск не будет работать правильно.
    $avatars = glob($avatarDir . '*.{jpg,png,gif,jpeg}', GLOB_BRACE);
    if (empty($avatars)) {
        return 'default-avatar.png'; // Заглушка, если нет аватаров
    }
    $randomAvatar = $avatars[array_rand($avatars)];
    return basename($randomAvatar);
}

// Защита от XSS: экранирование ввода Удаляет лишние пробелы (trim) и экранирует специальные символы (htmlspecialchars), чтобы предотвратить внедрение вредоносного кода.
function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из формы. SanitizeInput — получает значение поля login из формы, очищает его от потенциально опасных символов.
    $login = sanitizeInput($_POST["login"]);
    $password = $_POST["password"];

    // Проверка на заполненность и корректность
    if (empty($login) || !filter_var($login, FILTER_VALIDATE_EMAIL)) {
        die("Введите корректный email.");
    }
    if (empty($password) || strlen($password) < 8) {
        die("Пароль должен быть не менее 8 символов.");
    }

    // Хеширование пароля
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Генерация имени пользователя и аватара
    $username = generateUsername($conn);
    $avatar = getRandomAvatar();

    // Подготовленный запрос для защиты от SQL-инъекций - вставка данных
    $stmt = $conn->prepare("INSERT INTO users (username, login, password_hash, role, avatar) VALUES (?, ?, ?, 'client', ?)");
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("ssss", $username, $login, $passwordHash, $avatar);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        header("Location: acount.php");
        exit();
    } else {
        echo "Ошибка при регистрации: " . $stmt->error;
    }
    //Закрывает подготовленный запрос, освобождая ресурсы сервера.
    $stmt->close();
}
