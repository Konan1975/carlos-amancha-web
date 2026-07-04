<?php
// Ejemplo de configuración para InfinityFree.
// Copia este archivo como config.php y ajusta los valores.

$db_host = 'localhost';
$db_user = 'tu_usuario_mysql';
$db_pass = 'tu_password_mysql';
$db_name = 'tu_base_de_datos';

$email_to = 'tu-correo@dominio.com';
$email_from = 'no-reply@tu-dominio.com';

define('DB_HOST', $db_host);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('DB_NAME', $db_name);
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);
define('UPLOAD_MIME_TYPES', ['application/pdf', 'image/jpeg', 'image/png']);
define('UPLOAD_MAX_SIZE', 2 * 1024 * 1024);
?>
