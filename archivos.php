<?php
// Inicia la sesión para conservar el token de seguridad entre peticiones y proteger la gestión de archivos.
session_start();

require_once __DIR__ . '/classes/GestorArchivos.php';
require_once __DIR__ . '/config.php';

$manager = new GestorArchivos();
$messages = [];

// Genera un token CSRF si aún no existe para proteger formularios sensibles.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Procesa operaciones POST para subir o eliminar archivos.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, (string) $_POST['csrf_token'])) {
        $messages[] = 'Token de seguridad inválido.';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'], $_POST['file'])) {
        $result = $manager->eliminar((int) $_POST['id'], (string) $_POST['file']);
        $messages[] = $result['message'];
    } elseif (isset($_FILES['archivo'])) {
        $descripcion = isset($_POST['descripcion']) ? trim((string) $_POST['descripcion']) : '';
        $result = $manager->subir($_FILES['archivo'], $descripcion);
        $messages[] = $result['message'];
    }
}

// Procesa solicitudes de descarga con validación de token para evitar accesos no autorizados.
if (isset($_GET['action'], $_GET['file'], $_GET['token']) && $_GET['action'] === 'download') {
    if (!hash_equals($csrfToken, (string) $_GET['token'])) {
        $messages[] = 'Token de seguridad inválido.';
    } elseif (!$manager->descargar((string) $_GET['file'])) {
        $messages[] = 'No se pudo descargar el archivo solicitado.';
    }
}

$files = $manager->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de archivos - Carlos Amancha</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Carlos Amancha</h1>
            <ul class="nav-links">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="contacto.php">Contacto</a></li>
                <li><a href="archivos.php" class="active">Archivos</a></li>
            </ul>
        </div>
    </nav>

    <section class="file-manager-section">
        <div class="container">
            <h2><i class="fas fa-folder-open"></i> Gestión de Archivos</h2>
            <p class="contact-intro">Sube archivos de forma segura, descárgalos o elimínalos cuando ya no los necesites.</p>

            <?php if (!empty($messages)): ?>
                <div class="message-box">
                    <ul>
                        <?php foreach ($messages as $message): ?>
                            <li><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="file-manager-grid">
                <form action="archivos.php" method="post" enctype="multipart/form-data" class="file-upload-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                    <label for="archivo"><i class="fas fa-upload"></i> Selecciona un archivo</label>
                    <input type="file" id="archivo" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required>
                    <label for="descripcion">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" maxlength="255" placeholder="Describe brevemente el archivo">
                    <small>Tipos permitidos: PDF, JPG, JPEG y PNG. Tamaño máximo: 2 MB.</small>
                    <button type="submit" class="btn-submit">Subir archivo</button>
                </form>

                <div class="file-list-card">
                    <h3><i class="fas fa-list"></i> Archivos disponibles</h3>
                    <?php if (empty($files)): ?>
                        <p>No hay archivos subidos todavía.</p>
                    <?php else: ?>
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tamaño</th>
                                    <th>Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($file['nombre_original'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                            <small><?php echo htmlspecialchars($file['descripcion'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($file['tamano'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($file['fecha_subida'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a class="action-link" href="archivos.php?action=download&file=<?php echo urlencode($file['nombre_servidor']); ?>&token=<?php echo urlencode($csrfToken); ?>">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo (int) $file['id']; ?>">
                                                <input type="hidden" name="file" value="<?php echo htmlspecialchars($file['nombre_servidor'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="delete-btn" onclick="return confirm('¿Deseas eliminar este archivo?');">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Carlos Amancha. Todos los derechos reservados.</p>
            <p><a href="index.html">Volver al Inicio</a></p>
        </div>
    </footer>
</body>
</html>
