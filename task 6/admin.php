<?php
// admin.php

// HTTP Basic авторизация администратора
require  'auth_admin.php';

// 1) Получаем все заявки с данными пользователя и списком языков
$sql = "
SELECT 
    a.id,
    u.username,
    a.name,
    a.phone,
    a.email,
    a.dob,
    a.gender,
    a.bio,
    GROUP_CONCAT(l.name SEPARATOR ', ') AS languages
FROM application a
JOIN users u ON u.application_id = a.id
LEFT JOIN application_languages al ON al.application_id = a.id
LEFT JOIN languages l ON l.id = al.language_id
GROUP BY 
    a.id,
    u.username,
    a.name,
    a.phone,
    a.email,
    a.dob,
    a.gender,
    a.bio
ORDER BY a.id
";

$stmt = $pdo->query($sql);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Статистика: сколько пользователей любит каждый язык
$sql2 = "
SELECT 
    l.name,
    COUNT(al.application_id) AS cnt
FROM languages l
LEFT JOIN application_languages al ON al.language_id = l.id
GROUP BY l.id
ORDER BY cnt DESC, l.name
";
$stmt2 = $pdo->query($sql2);
$stats = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
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
            color: #4A148C;
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
            background: #4A148C;
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
        table { border-collapse: collapse; width:100%; margin-bottom:30px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:left; }
        th { background:#eee; }
        a.button { display:inline-block; padding:4px 8px; margin-right:4px; background:#007bff; color:#fff; text-decoration:none; border-radius:4px; }
        a.button.delete { background:#d9534f; }
        .stats { max-width:400px; }
    </style>
</head>
<body>
    <h1>Админ-панель</h1>

    <h2>Список заявок</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>E-mail</th>
                <th>Дата рожд.</th>
                <th>Пол</th>
                <th>Языки</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?= htmlspecialchars($app['id']) ?></td>
                    <td><?= htmlspecialchars($app['username']) ?></td>
                    <td><?= htmlspecialchars($app['name']) ?></td>
                    <td><?= htmlspecialchars($app['phone']) ?></td>
                    <td><?= htmlspecialchars($app['email']) ?></td>
                    <td><?= htmlspecialchars($app['dob']) ?></td>
                    <td><?= htmlspecialchars($app['gender']=='male' ? 'М' : 'Ж') ?></td>
                    <td><?= htmlspecialchars($app['languages']) ?></td>
                    <td>
                        <a class="button" href="admin_edit.php?id=<?= $app['id'] ?>">Ред.</a>
                        <a class="button delete" href="admin_delete.php?id=<?= $app['id'] ?>"
                           onclick="return confirm('Удалить заявку #<?= $app['id'] ?>?');"
                        >Удал.</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Статистика по языкам</h2>
    <div class="stats">
        <table>
            <thead>
                <tr><th>Язык</th><th>Число любителей</th></tr>
            </thead>
            <tbody>
                <?php foreach ($stats as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['cnt']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a class="button" href="admin_logout.php">Выйти из админки</a>
</body>
</html>
