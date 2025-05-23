<?php
// файл выхода из аккаунта
//очищаем сессию, куки, отправляем на страницу логина
session_start();
session_unset();
session_destroy();
setcookie('errors',     '', -1, '/');
setcookie('error_fields','', -1, '/');
setcookie('error_form','', -1, '/');
setcookie('name',       '', -1, '/');
setcookie('phone',      '', -1, '/');
setcookie('email',      '', -1, '/');
setcookie('dob',        '', -1, '/');
setcookie('gender',     '', -1, '/');
setcookie('languages',  '', -1, '/');
setcookie('bio',        '', -1, '/');
setcookie('contract',   '', -1, '/');
header('Location: /web5/login.php');
exit;
?>
