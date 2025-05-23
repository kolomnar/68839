<?php
// Инициализация соединения с базой данных
$host   = 'localhost';
$dbname = 'u68839';
$user   = 'u68839';
$pass   = '9707951';

try {
    // Устанавливаем PDO с режимом выброса исключений
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Завершаем скрипт при ошибке подключения
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Собираем ошибки валидации
// Список сообщений об ошибках
$errors = [];
// Список полей, содержащих ошибки
$errorFields = [];

// Проверка поля ФИО
if (empty($_POST['name'])) {
    $errors[] = "Поле ФИО обязательно для заполнения.";
    $errorFields[] = 'name';
} elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ]{1,}\s[a-zA-Zа-яА-ЯёЁ]{1,}\s[a-zA-Zа-яА-ЯёЁ]{1,}$/u", $_POST['name'])) {
    $errors[] = "Поле ФИО должно содержать ровно три слова (например, Иванов Иван Иванович).";
    $errorFields[] = 'name';
}

// Проверка телефона
if (empty($_POST['phone'])) {
    $errors[] = "Поле Телефон обязательно для заполнения.";
    $errorFields[] = 'phone';
} elseif (!preg_match("/^\+[0-9]{1,15}$/", $_POST['phone'])) {
    $errors[] = "Телефон должен начинаться с '+' и содержать только цифры (максимум 15 цифр).";
    $errorFields[] = 'phone';
}

// Проверка e-mail
if (empty($_POST['email'])) {
    $errors[] = "Поле E-mail обязательно для заполнения.";
    $errorFields[] = 'email';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errorFields[] = 'email';
    $errors[] = "Некорректный формат E-mail.";
}

// Проверка даты рождения
if (empty($_POST['dob'])) {
    $errors[] = "Поле Дата рождения обязательно для заполнения.";
    $errorFields[] = 'dob';
} else {
    $dob = DateTime::createFromFormat('Y-m-d', $_POST['dob']);
    if (!$dob || $dob->format('Y-m-d') !== $_POST['dob']) {
        $errorFields[] = 'dob';
        $errors[] = "Некорректный формат даты рождения. Используйте формат ГГГГ-ММ-ДД.";
    }
}

// Проверка поля «Пол»
if (empty($_POST['gender'])) {
    $errors[] = "Поле Пол обязательно для заполнения.";
    $errorFields[] = 'gender';
}

// Проверка выбора языков программирования
if (empty($_POST['languages'])) {
    $errors[] = "Выберите хотя бы один язык программирования.";
    $errorFields[] = 'languages';
}

// Проверка согласия с контрактом
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
    setcookie('languages', json_encode($_POST['languages'] ?? []), time() + 3600, '/');
    setcookie('bio', $_POST['bio'], time() + 3600, '/');

    header('Location: index.php');
    exit();
} 

// Если ошибок нет — сохраняем данные
try {
    // Вставка основной информации в таблицу application
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

    // Получаем ID только что вставленной записи
    $applicationId = $pdo->lastInsertId();

    // Для каждого выбранного языка сохраняем связь в application_languages
    $linkStmt = $pdo->prepare(
        "SELECT id FROM languages WHERE name = :name"
    );
    $insertLink = $pdo->prepare(
        "INSERT INTO application_languages (application_id, language_id)
         VALUES (:application_id, :language_id)"
    );

    foreach ($_POST['languages'] as $languageName) {
        // Ищем ID языка по названию
        $linkStmt->execute([':name' => $languageName]);
        $language = $linkStmt->fetch(PDO::FETCH_ASSOC);

        if ($language) {
            // Вставляем связь между заявкой и найденным языком
            $insertLink->execute([
                ':application_id' => $applicationId,
                ':language_id'    => $language['id']
            ]);
        }
    }
     // Сохраняем данные в Cookies на год
     setcookie('name', $_POST['name'], time() + 3600 * 24 * 365, '/');
     setcookie('phone', $_POST['phone'], time() + 3600 * 24 * 365, '/');
     setcookie('email', $_POST['email'], time() + 3600 * 24 * 365, '/');
     setcookie('dob', $_POST['dob'], time() + 3600 * 24 * 365, '/');
     setcookie('gender', $_POST['gender'], time() + 3600 * 24 * 365, '/');
     setcookie('languages', json_encode($_POST['languages'] ?? []), time()+3600, '/');
     setcookie('bio', $_POST['bio'], time() + 3600 * 24 * 365, '/');
     setcookie('contract', 'on', time() + 3600 * 24 * 365, '/');

    // сообщаем об успехе
    echo "<p style='color:green;'>Данные успешно сохранены!</p>";
    echo "<a href='/web4/'> Перейти к форме</a>";
} catch (PDOException $e) {
    // В случае ошибки выводим сообщение и прекращаем выполнение
    die("Ошибка при сохранении данных: " . $e->getMessage());
}
?>
