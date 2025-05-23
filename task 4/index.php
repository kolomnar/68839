<?php
$errorFields = isset($_COOKIE['error_fields']) ? json_decode($_COOKIE['error_fields'], true) : [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web 4</title>
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
    <div class="form-container">
        <h1>Форма регистрации</h1>
        <?php
        // Отображение ошибок, если они есть
        if (isset($_COOKIE['errors'])) {
            $errors = json_decode($_COOKIE['errors'], true);
            // Удаляем Cookie с ошибками после отображения
            setcookie('errors', '', -1, '/');

            setcookie('error_fields', '', -1, '/');
            setcookie('name', '',-1, '/');
            setcookie('phone', '', -1, '/');
            setcookie('email', '', -1, '/');
            setcookie('dob','', -1, '/');
            setcookie('gender','', -1, '/');
            setcookie('languages', '', -1, '/');
            setcookie('bio', '', -1, '/');
            foreach ($errors as $error) {
                echo "<p style='color:red;'>$error</p>";
            }

        }
        ?>
            <form action="form.php" method="post">
            <!-- Группа для ФИО -->
            <div class="form-group">
                <label for="name">ФИО:</label>
                <input type="text" id="name" name="name" placeholder="Иванов Иван Иванович"  
                value="<?php echo isset($_COOKIE['name']) ? htmlspecialchars($_COOKIE['name']) : ''; ?>" 
                class="<?= in_array('name',$errorFields)?'error':'' ?>">
            </div>

            <!-- Группа для телефона -->
            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" placeholder="+79991234567" 
                value="<?php echo isset($_COOKIE['phone']) ? htmlspecialchars($_COOKIE['phone']) : ''; ?>"
                class="<?= in_array('phone',$errorFields)?'error':'' ?>">
            </div>

            <!-- Группа для email -->
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" placeholder="example@mail.com" 
                value="<?php echo isset($_COOKIE['email']) ? htmlspecialchars($_COOKIE['email']) : ''; ?>"
                class="<?= in_array('email',$errorFields)?'error':'' ?>">
            </div>

            <!-- Группа для даты рождения -->
            <div class="form-group">
                <label for="dob">Дата рождения:</label>
                <input type="date" id="dob" name="dob" 
                value="<?php echo isset($_COOKIE['dob']) ? htmlspecialchars($_COOKIE['dob']) : ''; ?>" 
                class="<?= in_array('dob',$errorFields)?'error':'' ?>" required >
            </div>

            <!-- Группа для пола (радиокнопки) -->
            <div class="form-group">
                <label>Пол:</label>
                <div class="radio-group <?= in_array('gender',$errorFields)?'error':''?>">
                    <input type="radio" id="male" name="gender" value="male" <?php echo (isset($_COOKIE['gender']) && $_COOKIE['gender'] === 'male') ? 'checked' : ''; ?>  >
                    <label for="male">Мужской</label>
                    <input type="radio" id="female" name="gender" <?php echo (isset($_COOKIE['gender']) && $_COOKIE['gender'] === 'female') ? 'checked' : ''; ?> value="female" >
                    <label for="female">Женский</label>
                </div>
            </div>

            <!-- Группа для выбора языков программирования -->
            <div class="form-group">
                <label for="languages">Любимый язык программирования:</label>
                <select id="languages" name="languages[]" multiple="multiple" class="<?= in_array('languages',$errorFields)?'error':'' ?>">
                    <?php
                    $selectedLanguages = isset($_COOKIE['languages']) ? json_decode($_COOKIE['languages'], true) : [];
            $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
            foreach ($languages as $language) {
                $selected = in_array($language, $selectedLanguages) ? 'selected' : '';
                echo "<option value='$language' $selected>$language</option>";
            }
            ?>
                </select>
            </div>

            <!-- Группа для биографии -->
            <div class="form-group">
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio" placeholder="Расскажите о себе..."
                class="<?= in_array('bio',$errorFields)?'error':'' ?>">
                    <?php echo isset($_COOKIE['bio']) ? htmlspecialchars($_COOKIE['bio']) : ''; ?>
                </textarea>
            </div>

            <!-- Группа для чекбокса -->
            <div class="form-group">
                <div class="checkbox-group <?= in_array('contract',$errorFields)?'error':'' ?>">
                    <input type="checkbox" id="contract" name="contract" >
                    <label for="contract">С контрактом ознакомлен(а)</label>
                </div>
            </div>

            <!-- Кнопка отправки формы -->
            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>