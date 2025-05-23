<?php
// Подключаем проверку авторизации администратора
require 'auth_admin.php';


// Получаем ID заявки из параметра запроса
if (empty($_GET['id']) || !ctype_digit($_GET['id'])) {
    // Неверный или отсутствующий ID — возвращаемся обратно
    header('Location: /2_sem_6/admin.php');
    exit;
}

$appId = (int)$_GET['id'];

try {
    // Удаляем связи с языками
    $pdo->prepare("DELETE FROM application_languages WHERE application_id = :id")
        ->execute([':id' => $appId]);

    // Удаляем запись пользователя (логин/пароль)
    $pdo->prepare("DELETE FROM users WHERE application_id = :id")
        ->execute([':id' => $appId]);

    // Удаляем саму заявку
    $pdo->prepare("DELETE FROM application WHERE id = :id")
        ->execute([':id' => $appId]);

    // После удаления перенаправляем обратно в админ‑панель
    header('Location: /2_sem_6/admin.php');
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo "Ошибка при удалении: " . htmlspecialchars($e->getMessage());
    exit;
}
