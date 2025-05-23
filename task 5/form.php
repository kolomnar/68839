<?php
//Файл обработки формы регистрации ( главной страницы )
//начинаем php сессию
session_start();
//подключаемся к базе
require 'db.php';
$errors = [];
$errorFields =[];
if (empty($_POST['name'])) {
    $errors[] = "Поле ФИО обязательно для заполнения.";
    $errorFields[] = 'name';
} elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ]{2,}\s[a-zA-Zа-яА-ЯёЁ]{2,}\s[a-zA-Zа-яА-ЯёЁ]{2,}$/u", $_POST['name'])) {
    $errors[] = "Поле ФИО должно содержать ровно три слова (например, Иванов Иван Иванович).";
    $errorFields[] = 'name';
}

if (empty($_POST['phone'])) {
    $errors[] = "Поле Телефон обязательно для заполнения.";
    $errorFields[] = 'phone';
} elseif (!preg_match("/^\+[0-9]{1,15}$/", $_POST['phone'])) {
    $errors[] = "Телефон должен начинаться с '+' и содержать только цифры (максимум 15 цифр).";
    $errorFields[] = 'phone';
}

if (empty($_POST['email'])) {
    $errors[] = "Поле E-mail обязательно для заполнения.";
    $errorFields[] = 'email';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Некорректный формат E-mail.";
    $errorFields[] = 'email';
}

if (empty($_POST['dob'])) {
    $errors[] = "Поле Дата рождения обязательно для заполнения.";
    $errorFields[] = 'dob';
} else {
    $dob = DateTime::createFromFormat('Y-m-d', $_POST['dob']);
    if (!$dob || $dob->format('Y-m-d') !== $_POST['dob']) {
        $errors[] = "Некорректный формат даты рождения. Используйте формат ГГГГ-ММ-ДД.";
    }
}

if (empty($_POST['gender'])) {
    $errors[] = "Поле Пол обязательно для заполнения.";
    $errorFields[] = 'gender';
}

if (empty($_POST['languages'])) {
    $errors[] = "Выберите хотя бы один язык программирования.";
    $errorFields[] = 'languages';
}

if (!isset($_POST['contract'])) {
    $errors[] = "Необходимо ознакомиться с контрактом.";
    $errorFields[] = 'contract';
}
// Если есть ошибки, сохраняем их в Cookies и перенаправляем на форму
if (!empty($errors)) {
    setcookie('errors', json_encode($errors), time() + 3600, '/');
    setcookie('error_fields', json_encode($errorFields), time() + 3600, '/');
    setcookie('name', $_POST['name'], time() + 3600, '/');
    setcookie('phone', $_POST['phone'], time() + 3600, '/');
    setcookie('email', $_POST['email'], time() + 3600, '/');
    setcookie('dob', $_POST['dob'], time() + 3600, '/');
    setcookie('gender', $_POST['gender'], time() + 3600, '/');
    setcookie('languages', json_encode($_POST['languages']??[]), time() + 3600, '/');
    setcookie('bio', $_POST['bio'], time() + 3600, '/');

    header('Location: index.php');
    exit();
} else { try {
    $pdo->beginTransaction();
    // Сохранение заявки
    // Подготовка запроса
    $stmt = $pdo->prepare(
        "INSERT INTO application (name, phone, email, dob, gender, bio)
         VALUES (:name, :phone, :email, :dob, :gender, :bio)"
    );
    $stmt->execute([
        ':name'   => $_POST['name'],
        ':phone'  => $_POST['phone'],
        ':email'  => $_POST['email'],
        ':dob'    => $_POST['dob'],
        ':gender' => $_POST['gender'],
        ':bio'    => $_POST['bio']
    ]);
    $appId = $pdo->lastInsertId();
    // Сохранение языков: вызываем подготовленный запрос для каждого выбраного языка
    $langStmt = $pdo->prepare(
        "INSERT INTO application_languages (application_id, language_id)
         VALUES (:app_id, (
             SELECT id FROM languages WHERE name = :lang_name
         ))"
    );
    foreach ($_POST['languages'] as $lang) {
        $langStmt->execute([
            ':app_id'   => $appId,
            ':lang_name'=> $lang
        ]);
    }
    // Генерация логина и пароля
    $username = 'user'.bin2hex(random_bytes(4));
    $password = bin2hex(random_bytes(4));
    // Создаем из пароля хеш для сохранения в базу, клиенту же отдаем обычный пароль
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    // Сохранение пользователя
    $userStmt = $pdo->prepare(
        "INSERT INTO users (username, password_hash, application_id)
         VALUES (:username, :hash, :app_id)"
    );
    $userStmt->execute([
        ':username' => $username,
        ':hash'     => $passwordHash,
        ':app_id'   => $appId
    ]);
    $pdo->commit();

    // Вывод логина и пароля пользователю
    echo '<h2 style="color:green;">Ваша заявка успешно сохранена!</h2>';
    echo '<p>Ваш логин: <strong>' . htmlspecialchars($username) . '</strong></p>';
    echo '<p>Ваш пароль: <strong>' . htmlspecialchars($password) . '</strong></p>';
    echo '<p>Сохраните эти данные для редактирования вашего профиля. <a href="login.php">Перейти к входу</a>.</p>';
} catch (Exception $e) {
    $pdo->rollBack();
    die('Ошибка при сохранении: ' . $e->getMessage());
}
}
?>