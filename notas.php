<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Notas.php';

$errores = $_SESSION['errores_notas'] ?? [];
unset($_SESSION['errores_notas']);
$datos = $_SESSION['datos_nota'] ?? [];
unset($_SESSION['datos_nota']);

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$notas = [];
if ($conexion->connect_errno) {
    $notas = Notas::listar(null);
} else {
    $notas = Notas::listar($conexion);
    $conexion->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas | Carlos Amancha</title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Carlos Amancha</h1>
            <ul class="nav-links">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="notas.php" class="active">Notas</a></li>
                <li><a href="contacto.php">Contacto</a></li>
                <li><a href="archivos.php">Archivos</a></li>
            </ul>
        </div>
    </nav>

    <section class="contact-section">
        <div class="container">
            <h2><i class="fas fa-sticky-note"></i> Gestión de Notas</h2>
            <p class="contact-intro">Aquí puedes crear una nota, validarla, guardarla y verla reflejada en la lista.</p>

            <div class="form-container" style="margin-bottom: 1.5rem;">
                <h3><i class="fas fa-cogs"></i> Proceso de la nota</h3>
                <ol>
                    <li><strong>Captura:</strong> ingresas título, categoría y contenido.</li>
                    <li><strong>Validación:</strong> el sistema revisa que el texto cumpla con el mínimo requerido.</li>
                    <li><strong>Guardado:</strong> la nota se almacena en la web para que quede disponible.</li>
                    <li><strong>Visualización:</strong> aparece en la lista de notas de la misma página.</li>
                </ol>
            </div>

            <?php if (isset($_GET['estado']) && $_GET['estado'] === 'ok'): ?>
                <div class="form-container" style="margin-bottom: 1.5rem; background: #eaf7ea; border: 1px solid #b8e2b8;">
                    <p><strong>✅ Nota guardada correctamente.</strong> El proceso se completó y ya aparece en la lista.</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="form-container" style="margin-bottom: 1.5rem;">
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-container" style="margin-bottom: 1.5rem;">
                <form action="guardar_nota.php" method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="titulo"><i class="fas fa-heading"></i> Título</label>
                        <input type="text" id="titulo" name="titulo" required minlength="3" maxlength="100" value="<?php echo htmlspecialchars($datos['titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="categoria"><i class="fas fa-tags"></i> Categoría</label>
                        <input type="text" id="categoria" name="categoria" required minlength="2" maxlength="50" value="<?php echo htmlspecialchars($datos['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="contenido"><i class="fas fa-pen"></i> Contenido</label>
                        <textarea id="contenido" name="contenido" rows="5" required minlength="5" maxlength="1000"><?php echo htmlspecialchars($datos['contenido'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar nota</button>
                </form>
            </div>

            <div class="form-container">
                <h3><i class="fas fa-list"></i> Notas guardadas</h3>
                <?php if (empty($notas)): ?>
                    <p>No hay notas registradas todavía.</p>
                <?php else: ?>
                    <?php foreach ($notas as $nota): ?>
                        <div class="interest-item" style="margin-bottom: 1rem; text-align: left;">
                            <h4><?php echo htmlspecialchars($nota['titulo'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="text-muted"><strong>Categoría:</strong> <?php echo htmlspecialchars($nota['categoria'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($nota['contenido'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <small><?php echo htmlspecialchars($nota['fecha_creacion'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Carlos Amancha. Todos los derechos reservados.</p>
            <p><a href="index.html">Inicio</a> · <a href="contacto.php">Contáctame</a> · <a href="archivos.php">Gestión de Archivos</a></p>
        </div>
    </footer>
</body>
</html>
