<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contacto.php');
    exit;
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

$errors = [];
if (strlen($nombre) < 3) {
    $errors[] = 'El nombre debe tener al menos 3 caracteres.';
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Correo inválido.';
}
if (strlen($mensaje) < 10) {
    $errors[] = 'El mensaje debe tener al menos 10 caracteres.';
}

$saved = false;
$sent = false;

if (empty($errors)) {
    // Guardar en la base de datos
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        $errors[] = 'Error de conexión a la base de datos.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO mensajes (nombre, correo, mensaje, fecha_envio) VALUES (?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param('sss', $nombre, $correo, $mensaje);
            if ($stmt->execute()) {
                $saved = true;
            } else {
                $errors[] = 'Error al guardar el mensaje en la base de datos.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Error en la preparación de la consulta.';
        }
        $mysqli->close();
    }

    // Enviar correo de notificación
    $subject = "Nuevo mensaje de contacto: " . $nombre;
    $body = "Has recibido un nuevo mensaje de contacto:\n\n";
    $body .= "Nombre: " . $nombre . "\n";
    $body .= "Correo: " . $correo . "\n\n";
    $body .= "Mensaje:\n" . $mensaje . "\n";

    $headers = "From: " . $email_from . "\r\n";
    $headers .= "Reply-To: " . $correo . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // En entornos compartidos/localhost, mail() puede fallar; se intenta de todos modos.
    $sent = @mail($email_to, $subject, $body, $headers);

    if ($saved && $sent) {
        $status = 'success';
    } elseif ($saved && !$sent) {
        $status = 'partial';
        $errors[] = 'Mensaje guardado pero el correo no pudo enviarse desde este servidor.';
    } elseif (!$saved && $sent) {
        $status = 'partial';
        $errors[] = 'Correo enviado pero no se pudo guardar en la base de datos.';
    } else {
        $status = 'error';
        if (empty($errors)) $errors[] = 'No se pudo procesar el mensaje.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar - Carlos Amancha</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="container">
        <?php if ($status === 'success'): ?>
            <h2>Mensaje enviado</h2>
            <p>Gracias, <?php echo htmlspecialchars($nombre); ?>. Tu mensaje se ha recibido correctamente y se ha enviado una notificación.</p>
            <p><a href="contacto.php">Volver al formulario</a></p>
        <?php elseif ($status === 'partial'): ?>
            <h2>Procesado parcialmente</h2>
            <p>Se produjeron algunos problemas:</p>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><a href="contacto.php">Volver y corregir</a></p>
        <?php else: ?>
            <h2>Error al enviar</h2>
            <p>Se encontraron los siguientes errores:</p>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><a href="contacto.php">Volver y corregir</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
