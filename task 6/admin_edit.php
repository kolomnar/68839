<?php
// Файл редактирования данных пользователя

// авторизация администратора
require 'auth_admin.php';
// id пользователя передается в query параметрах адресса страницы (http:...?id=...)
$appId = $_GET['id'] ?? null;
if (!$appId || !ctype_digit($appId)) {
    header('Location: admin.php');
    exit;
}

// Получаем полный список языков для селекта
$all = $pdo->query("SELECT name FROM languages")->fetchAll(PDO::FETCH_COLUMN);

$getPost = fn($key) => $_POST[$key] ?? null;
$errorFields = [];
$errors = [];

// При отправке формы (POST) — валидация и сохранение
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Читаем поля
    $name      = trim($getPost('name'));
    $phone     = trim($getPost('phone'));
    $email     = trim($getPost('email'));
    $dob       = $getPost('dob');
    $gender    = $getPost('gender');
    $bio       = trim($getPost('bio'));
    $langs     = $_POST['languages'] ?? [];

    if (empty($_POST['name'])) {
        $errors[] = "Поле ФИО обязательно для заполнения.";
        $errorFields[] = 'name';
    } elseif (!preg_match("/^[\p{L}]{2,}\s[\p{L}]{2,}\s[\p{L}]{2,}$/u", $_POST['name'])) {
        $errors[] = "Поле ФИО должно содержать ровно три слова (Иванов Иван Иванович).";
        $errorFields[] = 'name';
    }
    
    if (empty($_POST['phone'])) {
        $errors[] = "Поле Телефон обязательно для заполнения.";
        $errorFields[] = 'phone';
    } elseif (!preg_match("/^\+[0-9]{1,15}$/", $_POST['phone'])) {
        $errors[] = "Телефон должен начинаться с '+' и содержать только цифры.";
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
    }
    
    if (empty($_POST['gender'])) {
        $errors[] = "Поле Пол обязательно для заполнения.";
        $errorFields[] = 'gender';
    }
    
    if (empty($_POST['languages'])) {
        $errors[] = "Выберите хотя бы один язык программирования.";
        $errorFields[] = 'languages';
    }
    

    // Если валидация прошла — обновляем
    if (empty($errors)) {
        $pdo->beginTransaction();

        // Обновляем основную запись
        $upd = $pdo->prepare(
            "UPDATE application 
               SET name = :name, phone = :phone, email = :email, dob = :dob, gender = :gender, bio = :bio
             WHERE id = :id"
        );
        $upd->execute([
            ':name'   => $name,
            ':phone'  => $phone,
            ':email'  => $email,
            ':dob'    => $getPost('dob'),
            ':gender' => $gender,
            ':bio'    => $bio,
            ':id'     => $appId,
        ]);

        // Удаляем старые языки
        $pdo->prepare("DELETE FROM application_languages WHERE application_id = :id")
            ->execute([':id' => $appId]);

        // Вставляем новые языки
        $lnk = $pdo->prepare(
            "INSERT INTO application_languages (application_id, language_id)
             VALUES (:aid, (SELECT id FROM languages WHERE name = :lang))"
        );
        foreach ($langs as $lang) {
            $lnk->execute([':aid' => $appId, ':lang' => $lang]);
        }

        $pdo->commit();

        header('Location: admin.php');
        exit;
    }

// При GET — просто подгружаем текущие данные
} else {
    // Получаем данные заявки и языки
    $stmt = $pdo->prepare(
        "SELECT a.*, u.username
         FROM application a
         JOIN users u ON u.application_id = a.id
         WHERE a.id = :id"
    );
    $stmt->execute([':id' => $appId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        header('Location: admin.php');
        exit;
    }

    // Список выбранных языков
    $sl = $pdo->prepare(
        "SELECT l.name 
         FROM application_languages al 
         JOIN languages l ON l.id = al.language_id
         WHERE al.application_id = :id"
    );
    $sl->execute([':id' => $appId]);
    $selected = array_column($sl->fetchAll(PDO::FETCH_ASSOC), 'name');

 
}

// === HTML-форма ===
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Admin: Редактировать заявку #<?= htmlspecialchars($appId) ?></title>
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #6A1B9A;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            transition: transform 0.3s ease;
        }
        h1 {
            color: lightcoral;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.2em;
            font-weight: 600;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        label {
            color: lightcoral;
            font-weight: 500;
            font-size: 0.95em;
        }
        input, select, textarea {
            padding: 12px;
            border: 1px solid #D1C4E9;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #6A1B9A;
            box-shadow: 0 0 0 2px rgba(106, 27, 154, 0.1);
        }
        input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #6A1B9A;
        }
        .radio-group {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        select[multiple] {
            height: 120px;
            padding: 8px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        button {
            background: #6A1B9A;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 0;
        }
        button:hover {
            background: lightcoral;
        }
        .error-message {
            color: red;
            font-size: 0.9em;
        }
        .error {
            border-color: #E53935 !important;
        }
        @media (max-width: 480px) {
            .form-container {
                padding: 1.5rem;
                width: 95%;
            }
            h1 {
                font-size: 1.8em;
            }
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        label {
            display: block;
            margin-bottom: 10px;
        }
        
        input[type="text"], input[type="tel"], input[type="email"], input[type="date"], select, textarea {
            width: 95%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        
        input[type="radio"] {
            
            margin-right: 10px;
        }
        
        input[type="checkbox"] {
            margin-right: 10px;
        }
        
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        
        .radio-group  {
            margin-bottom: 20px;
        }
        .radio-group label {
          display: inline;}
        .checkbox-group {
            display: flex;
            margin-left: 20px;
            align-items: center;
            flex-direction: row;
            gap: 0;
            margin-bottom: 20px;
        }
        
        .checkbox-group label {
            margin-left: 10px;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Редактировать заявку #<?= htmlspecialchars($appId) ?></h1>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post">
        <label>ФИО:
            <input type="text" name="name" value="<?= htmlspecialchars($data['name'] ?? $name) ?>">
        </label>
        <label>Телефон:
            <input type="text" name="phone" value="<?= htmlspecialchars($data['phone'] ?? $phone) ?>">
        </label>
        <label>E-mail:
            <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? $email) ?>">
        </label>
        <label>Дата рождения:
            <input type="date" name="dob" value="<?= htmlspecialchars($data['dob'] ?? $getPost('dob')) ?>">
        </label>
        <label>Пол:
            <label><input type="radio" name="gender" value="male" <?= ($data['gender'] ?? $gender) === 'male' ? 'checked' : '' ?>> Мужской</label>
            <label><input type="radio" name="gender" value="female" <?= ($data['gender'] ?? $gender) === 'female' ? 'checked' : '' ?>> Женский</label>
        </label>
        <label>Языки:
            <select name="languages[]" multiple>
                <?php foreach ($all as $lang): ?>
                    <option value="<?= htmlspecialchars($lang) ?>"
                        <?= in_array($lang, $selected ?? $langs ?? []) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Биография:
            <textarea name="bio"><?= htmlspecialchars($data['bio'] ?? $bio) ?></textarea>
        </label>
        <input type="submit" value="Сохранить">
        <p><a href="admin.php">← Назад к списку</a></p>
    </form>
</body>
</html>
