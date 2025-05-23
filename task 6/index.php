<?php
// Файл главной страницы ( регистрации )
// Старт сессии и чтение подсказок из Cookies
session_start();

// Получаем сообщения об ошибках и поля для подсветки
$errors = isset($_COOKIE['errors']) ? json_decode($_COOKIE['errors'], true) : [];
$errorFields = isset($_COOKIE['error_fields'])
    ? json_decode($_COOKIE['error_fields'], true)
    : [];

// Функции получения значения ( массива ) из cookie по ключу
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
setcookie('errors', '', -1, '/');
setcookie('error_fields', '',  -1, '/');
setcookie('name', '',-1, '/');
setcookie('phone', '', -1, '/');
setcookie('email', '', -1, '/');
setcookie('dob','', -1, '/');
setcookie('gender','', -1, '/');
setcookie('languages', '', -1, '/');
setcookie('bio', '', -1, '/');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма регистрации</title>
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
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="form-container">
        <h1>Форма регистрации</h1>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <div class="error-message"><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <form action="form.php" method="post">
        <label for="name">Фамилия Имя Отчество:</label>
            <!-- 
            значение получаем из соответсвующей cookie,
            подсвечиваем красным если поле находится в списке ошибок
            -->
        <input
            type="text"
            id="name"
            name="name"
            placeholder="Фамилия Имя Отчество"
            value="<?= $get('name') ?>"
            class="<?= in_array('name', $errorFields) ? 'error' : ''?>"
        >

        <label for="phone">Телефон:</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            placeholder="+79991234567"
            value="<?= $get('phone') ?>"
            class="<?= in_array('phone', $errorFields) ? 'error' : '' ?>"
        >

        <label for="email">E-mail:</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="example@mail.com"
            value="<?= $get('email') ?>"
            class="<?= in_array('email', $errorFields) ? 'error' : '' ?>"
        >

        <label for="dob">Дата рождения:</label>
        <input
            type="date"
            id="dob"
            name="dob"
            value="<?= $get('dob') ?>"
            class="<?= in_array('dob', $errorFields) ? 'error' : '' ?>"
        >

        <label>Пол:</label>
        <div class="radio-group <?= in_array('gender', $errorFields) ? 'error' : '' ?>">
            <label>
                <input
                    type="radio"
                    name="gender"
                    value="male"
                    <?= $get('gender') === 'male' ? 'checked' : '' ?>
                >
                Мужской
            </label>
            <label>
                <input
                    type="radio"
                    name="gender"
                    value="female"
                    <?= $get('gender') === 'female' ? 'checked' : '' ?>
                >
                Женский
            </label>
        </div>

        <label for="languages">Любимый язык программирования:</label>
        <select
            id="languages"
            name="languages[]"
            multiple
            class="<?= in_array('languages', $errorFields) ? 'error' : '' ?>"
        >
    
                    <?php
                    $selectedLanguages = isset($_COOKIE['languages']) ? json_decode($_COOKIE['languages'], true) : [];
            $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
            foreach ($languages as $language) {
                $selected = in_array($language, $selectedLanguages) ? 'selected' : '';
                echo "<option value='$language' $selected>$language</option>";
            }
            ?>
        </select>

        <label for="bio">Биография:</label>
        <textarea
            id="bio"
            name="bio"
            class="<?= in_array('bio', $errorFields) ? 'error' : '' ?>"
        ><?= $get('bio') ?></textarea>

        <div style="margin-bottom:15px;" class="<?= in_array('contract', $errorFields) ? 'error' : '' ?>">
            <label>
                <input
                    type="checkbox"
                    name="contract"
                    <?= $get('contract') === 'on' ? 'checked' : '' ?>
                >
                С контрактом ознакомлен(а)
            </label>
        </div>

        <input type="submit" value="Сохранить">
    </form>
    <form style="gap: 0; justify-items: center; margin-top: 10px;" action="login.php" method="get"> 
            <label style="margin: 0; text-align:center;">Уже зарегистрированы?</label>
        <button type="submit">Войти</button>
        </form>
        <form style="gap: 0; justify-items: center; margin-top: 10px;" action="login.php" method="get"> 
            <label style="margin: 0; text-align:center;">Вы администратор?</label>
        <button type="submit">Админ-панель</button>
        </form>
            </div>
</body>
</html>
