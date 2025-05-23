<?php
//Файл страницы входа и обработки входа
session_start();
require 'db.php';

$msg = '';
// Обработка отправленных данных входа ( проверка логина и пароля), авторизация
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = :user");
    $stmt->execute([':user' => $_POST['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
       // успешно, сохраняем логин в сессию, отправляем на страницу пользователя
        $_SESSION['user_id'] = $user['id'];
        header('Location: /web5/edit.php'); exit;
    } else {
        $msg = 'Неверный логин или пароль.';
    }
}
?>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
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
</head>
<body>
    <div class='form-container'>
    <form action="login.php" method="post">
        <h1>Вход для редактирования</h1>
        <?php if ($msg): ?>
            <div class="error-message"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <label for="username">Логин:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Войти">
    </form>
    <form style="gap: 0; justify-items: center; margin-top: 10px;" action="/web5/index.php" method="get"> 
            <label style="margin: 0; text-align:center;">Еще не зарегистрированы?</label>
        <input style="margin:0;"type="submit" value="Зарегистрироваться"/>
        </form>
        </div>
</body>
</html>