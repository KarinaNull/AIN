<?php
session_start();
require 'db.php';

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

function getRandomAvatar()
{
    $avatarDir = 'avatars/';
    $avatars = glob($avatarDir . '*.{jpg,png,gif,jpeg}', GLOB_BRACE);
    $randomAvatar = $avatars[array_rand($avatars)];
    return basename($randomAvatar);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"]);
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
    $username = generateUsername($conn);
    // Получаем случайный аватар
    $avatar = getRandomAvatar();

    // Сохранение пользователя в БД, включая аватар
    $stmt = $conn->prepare("INSERT INTO users (username, login, password_hash, role, avatar) VALUES (?, ?, ?, 'client', ?)");
    $stmt->bind_param("ssss", $username, $login, $passwordHash, $avatar);
    if ($stmt->execute()) {
        $_SESSION['username'] = $username;
        header("Location: acount.php");
        exit();
    } else {
        echo "Ошибка при регистрации: " . $stmt->error;
    }
}
