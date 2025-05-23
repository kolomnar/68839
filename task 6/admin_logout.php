<?php


header('WWW-Authenticate: Basic realm="Admin Area"');
header('HTTP/1.0 401 Unauthorized');

echo 'Выполнен выход из админки. Для повторного входа закройте это окно и снова перейдите на <a href="admin.php">admin.php.</a>';
exit;
