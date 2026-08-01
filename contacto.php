<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Carlos Amancha</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon-dragon-head.svg">
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">Carlos Amancha</h1>
            <ul class="nav-links">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="notas.php">Notas</a></li>
                <li><a href="contacto.php" class="active">Contacto</a></li>
                <li><a href="archivos.php">Archivos</a></li>
            </ul>
        </div>
    </nav>

    <section class="contact-section">
        <div class="container">
            <h2><i class="fas fa-envelope"></i> Formulario de Contacto</h2>
            <p class="contact-intro">¿Tienes alguna pregunta o comentario? ¡No dudes en escribirme!</p>

            <div class="form-container">
                <form action="enviar.php" method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="nombre">
                            <i class="fas fa-user"></i> Nombre completo *
                        </label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            required 
                            minlength="3" 
                            maxlength="100"
                            placeholder="Ingresa tu nombre completo"
                        >
                        <small>Minimum 3 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="correo">
                            <i class="fas fa-envelope"></i> Correo electrónico *
                        </label>
                        <input 
                            type="email" 
                            id="correo" 
                            name="correo" 
                            required 
                            maxlength="100"
                            placeholder="tu@email.com"
                        >
                    </div>

                    <div class="form-group">
                        <label for="mensaje">
                            <i class="fas fa-comment"></i> Mensaje *
                        </label>
                        <textarea 
                            id="mensaje" 
                            name="mensaje" 
                            rows="5" 
                            required 
                            minlength="10" 
                            maxlength="500"
                            placeholder="Escribe tu mensaje aquí..."
                        ></textarea>
                        <small>Minimum 10 caracteres</small>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Enviar Mensaje
                    </button>
                </form>
            </div>

            <div class="contact-info">
                <h3>Otras formas de contacto</h3>
                <div class="info-items">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Ubicación</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>Disponible de Lunes a Viernes</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Carlos Amancha. Todos los derechos reservados.</p>
            <p><a href="index.html">Volver al Inicio</a> · <a href="notas.html">Notas</a></p>
        </div>
    </footer>
</body>
</html>