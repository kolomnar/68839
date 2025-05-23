<?php
// Модуль http авторизации администратора, подключается ко всем ресурсам, доступным только для администарторов
session_start();
require 'db.php';

// Проверка наличия данных авторизации в заголовках HTTP
if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Необходима авторизация для доступа.';
    exit;
}

// Извлекаем логин и пароль
$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

// Получаем запись администратора по логину
$stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE username = :username');
$stmt->execute([':username' => $username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверяем наличие пользователя и правильность пароля
if (!$admin || !password_verify($password, $admin['password_hash'])) {
    header('HTTP/1.0 403 Forbidden');
    header('WWW-Authenticate: Basic realm="Admin Area"');
    echo 'Неверные учётные данные.';
    echo '<a class="button" href="admin_logout.php">Выйти из админки</a>';
    exit;
}
