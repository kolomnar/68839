<?php
//файл соединения с базой данных mysql
$host   = 'localhost';
$dbname = 'u68839';
$user   = 'u68839';
$pass   = '9707951';
//Подключаемся к базе
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
?>