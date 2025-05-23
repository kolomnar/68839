<?php
//файл вывода странички формы уже зарегистрированного пользователя
//и обработки изменения формы
session_start();
require 'db.php';
if (empty($_SESSION['user_id'])) {
    header('Location: /web5/login.php'); exit;
}
// Получаем данные пользователя / заявки
$stmt = $pdo->prepare(
    "SELECT u.id AS user_id, a.* FROM users u
     JOIN application a ON u.application_id = a.id
     WHERE u.id = :uid"
);
$stmt->execute([':uid' => $_SESSION['user_id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$errorForm = $data;
$errors = [];
$errorFields = [];
// Если запрос редактирования формы валидируем данные, в случае успеха сохраняем
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errorForm['name'] = $_POST['name'];
    if (empty($_POST['name'])) {
        $errors[] = "Поле ФИО обязательно для заполнения.";
        $errorFields[] = 'name';
    } elseif (!preg_match("/^[a-zA-Zа-яА-ЯёЁ]{2,}\s[a-zA-Zа-яА-ЯёЁ]{2,}\s[a-zA-Zа-яА-ЯёЁ]{2,}$/u", $_POST['name'])) {
        $errors[] = "Поле ФИО должно содержать ровно три слова (например, Иванов Иван Иванович).";
        $errorFields[] = 'name';
    }
    $errorForm['phone'] = $_POST['phone'];
    if (empty($_POST['phone'])) {
        $errors[] = "Поле Телефон обязательно для заполнения.";
        $errorFields[] = 'phone';
    } elseif (!preg_match("/^\+[0-9]{1,15}$/", $_POST['phone'])) {
        $errors[] = "Телефон должен начинаться с '+' и содержать только цифры (максимум 15 цифр).";
        $errorFields[] = 'phone';
    }
    $errorForm['email'] = $_POST['email'];
    if (empty($_POST['email'])) {
        $errors[] = "Поле E-mail обязательно для заполнения.";
        $errorFields[] = 'email';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный формат E-mail.";
        $errorFields[] = 'email';
    }
    $errorForm['dob'] = $_POST['dob'];
    if (empty($_POST['dob'])) {
        $errors[] = "Поле Дата рождения обязательно для заполнения.";
        $errorFields[] = 'dob';
    } else {
        $dob = DateTime::createFromFormat('Y-m-d', $_POST['dob']);
        if (!$dob || $dob->format('Y-m-d') !== $_POST['dob']) {
            $errors[] = "Некорректный формат даты рождения. Используйте формат ГГГГ-ММ-ДД.";
            $errorFields[] = 'dob';
        }
    }
    $errorForm['gender'] = $_POST['gender'];
    if (empty($_POST['gender'])) {
        $errors[] = "Поле Пол обязательно для заполнения.";
        $errorFields[] = 'gender';
    }
    $errorForm['languages'] = $_POST['languages'];
    if (empty($_POST['languages'])) {
        $errors[] = "Выберите хотя бы один язык программирования.";
        $errorFields[] = 'languages';
    }
    
    // Если есть ошибки, сохраняем их в Cookies и перенаправляем на форму
    if (!empty($errors)) {
        setcookie('errors', json_encode($errors), time() + 3600, '/');
        setcookie('error_form', json_encode($errorForm), time() + 3600, '/');
        setcookie('error_fields', json_encode($errorFields), time() + 3600, '/');
        setcookie('name', $_POST['name'], time() + 3600, '/');
        setcookie('phone', $_POST['phone'], time() + 3600, '/');
        setcookie('email', $_POST['email'], time() + 3600, '/');
        setcookie('dob', $_POST['dob'], time() + 3600, '/');
        setcookie('gender', $_POST['gender'], time() + 3600, '/');
        setcookie('languages', json_encode($_POST['languages'] ?? []), time() + 3600, '/');
        setcookie('bio', $_POST['bio'], time() + 3600, '/');
    
        header('Location: edit.php');
        exit();
    } else {
        //Начинаем транзакцию, обновляем данные
        $pdo->beginTransaction();
        $upd = $pdo->prepare(
            "UPDATE application SET name = :name,
             phone = :phone,
             email = :email,
             dob = :dob,
             gender = :gender,
             bio = :bio
             WHERE id = :aid"
        );
        $upd->execute([
            ':name'   => $_POST['name'],
            ':phone'  => $_POST['phone'],
            ':email'  => $_POST['email'],
            ':dob'    => $_POST['dob'],
            ':gender' => $_POST['gender'],
            ':bio'    => $_POST['bio'],
            ':aid'    => $data['id']
        ]);
        // Обновление языков: удалить старые, вставить новые
        $pdo->prepare("DELETE FROM application_languages WHERE application_id = :aid")
            ->execute([':aid' => $data['id']]);
        $lnk = $pdo->prepare(
            "INSERT INTO application_languages (application_id, language_id)
             VALUES (:aid, (
               SELECT id FROM languages WHERE name = :lang
             ))"
        );
        foreach ($_POST['languages'] as $lang) {
            $lnk->execute([':aid' => $data['id'], ':lang' => $lang]);
        }
        $pdo->commit();
        header('Location: edit.php?success=1');
        exit;
    }
} else {
    // get запрос на страницу редактирования формы -- подгружаем данные с базы, отдаем страницу
    $errors = isset($_COOKIE['errors']) ? json_decode($_COOKIE['errors'], true) : [];
    $errorFields = isset($_COOKIE['error_fields'])
        ? json_decode($_COOKIE['error_fields'], true)
        : [];
    // если была попытка отправить с ошибками берем данные с ошибками, иначе из базы ($data)
    $errorForm = isset($_COOKIE['error_form']) ? json_decode($_COOKIE['error_form'], true) : $data;
    $get = function($key) {
        return isset($_COOKIE[$key])
            ? htmlspecialchars($_COOKIE[$key])
            : '';
    };
    $getArray = function($key) {
        return isset($_COOKIE[$key])
            ? json_decode($_COOKIE[$key], true)
            : [];
    };

    // После чтения — очистим ошибки и error_fields
    foreach (['errors','error_form','error_fields','name','phone','email','dob','gender','languages','bio','contract'] as $c) {
        setcookie($c, '', -1, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Редактирование заявки</title></head>
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    
        body {
            background: lightcoral;
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
            border-color: lightcoral;
            box-shadow: 0 0 0 2px rgba(106, 27, 154, 0.1);
        }
    
        input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: lightcoral;
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
    
        button {
            background: lightcoral;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }
    
        button:hover {
            background: lightcoral;
        }
    
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .error{
            color: black;
            background: rgba(255, 0, 0, 0.5);
        }
        .error *{
            color: black;
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
    </style>
<body>
    <div class='form-container'>
<h1>Редактировать вашу заявку</h1>
<form method="post">
    <!-- Вывод ошибок --->
<?php foreach ($errors as $e) echo "<p style='color:red;'>$e</p>"; ?>
<div class="form-group">
<label for="name">ФИО:</label>
        <input type="text" name="name" value="<?=htmlspecialchars($errorForm['name'])?>" class="<?= in_array('name', $errorFields) ? 'error' : '' ?>">
    </div>

    <div class="form-group">
                <label for="phone">Телефон:</label>
        <input type="text" name="phone" value="<?=htmlspecialchars($errorForm['phone']) ?>" class="<?= in_array('phone', $errorFields) ? 'error' : '' ?>">
    </div>

    <div class="form-group">
    <label for="email">E-mail:</label>
        <input type="email" name="email" value="<?=htmlspecialchars($errorForm['email']) ?> " class="<?= in_array('email', $errorFields) ? 'error' : '' ?>">
    </div>

    <div class="form-group">
                <label for="dob">Дата рождения:</label>
        <input type="date" name="dob" value="<?=htmlspecialchars($errorForm['dob']) ?>" class="<?= in_array('dob', $errorFields) ? 'error' : '' ?>">
    <div>

    <div class="form-group">
        <label>Пол:</label>
        <div class="radio-group <?= in_array('gender',$errorFields)?'error':''?>">
        <input type="radio" name="gender" value="male" <?= $data['gender'] === 'male' ? 'checked' : '' ?>> 
            <label for="male">Мужской</label>
            <input type="radio" name="gender" value="female" <?= $data['gender'] === 'female' ? 'checked' : '' ?>>
            <label for="female">Женский</label>
        </div>
    </div>


    <div class="form-group">
    <label for="languages">Любимый язык программирования:</label>
<?php
    //получаем список всех возможных языков
    $stmt = $pdo->query("SELECT name FROM languages");
    //получаем список выбранных языков
    if(isset($_COOKIE['languages'])){
        $selectedLangs = $getArray('languages');
    } else {
        $userLangs = $pdo->prepare("SELECT l.name FROM application_languages al JOIN languages l ON al.language_id = l.id WHERE al.application_id = :aid");
        $userLangs->execute([':aid' => $data['id']]);
        $selectedLangs = array_column($userLangs->fetchAll(PDO::FETCH_ASSOC), 'name');
    }
    $langErr = in_array('languages', $errorFields) ? 'error' : '';
    //Выводим языки, отмечая уже выбранные ранее
    echo "<select id='languages'
            name='languages[]'
            class='{$langErr}'
            multiple >";
    foreach ($stmt as $row) {
        $checked = in_array($row['name'], $selectedLangs) ? 'selected' : '';
        echo "<option
                    value='{$row['name']}' {$checked}> {$row['name']}
                </option>";
    }
    echo '</select>';
?>
    </div>
    <div class="form-group">
                <label for="bio">Биография:</label>
        <textarea name="bio" class="<?= in_array('bio', $errorFields) ? 'error' : '' ?>"><?=htmlspecialchars($data['bio']) ?></textarea>
</div>
<div class="form-group">
    <button type="submit">Сохранить изменения</button>
</div>
</form>
<br><br>
<form action="logout.php"><button style="background: red"type="submit">Выйти</button>
</div>
</body>
</html>
