# Despliegue en InfinityFree (carpeta `htdocs`)

Sigue estos pasos para subir el sitio a `carlos-amancha.infinityfreeapp.com` y alojarlo en el directorio `htdocs`.

1) Preparar archivos localmente
- Opcional: crea una copia de seguridad del proyecto.
- Asegúrate de que `contacto.php`, `enviar.php`, `index.html`, `css/`, `img/` y `database/create_mensajes_table.sql` estén actualizados.
- No subas `config.php` con credenciales sensibles si el repositorio será público. Usa `config.example.php` como plantilla.

2) Comprimir los archivos (Windows PowerShell)
```powershell
# Desde la raíz del proyecto
Compress-Archive -Path * -DestinationPath deploy.zip
```

3) Acceder al panel de InfinityFree
- Ve a https://app.infinityfree.net/ e inicia sesión.
- Selecciona tu cuenta y el dominio `carlos-amancha.infinityfreeapp.com`.

4) Obtener credenciales FTP o usar File Manager
- En el Panel de Control (Control Panel) busca **FTP Accounts** o **File Manager**.
- Si usas FTP: anota `Host`, `FTP username` y `FTP password`.
- Recomiendo FileZilla para FTP: crea una nueva conexión con esos datos.

5) Subir archivos a `htdocs`
- Con File Manager (más sencillo): abre el administrador de archivos y sube `deploy.zip`, luego descomprímelo dentro de `/htdocs`.
- Con FTP (FileZilla): sube todos los archivos y carpetas al directorio remoto `/htdocs`.
- Asegúrate de que `index.html` esté en la raíz de `/htdocs` (no dentro de otra carpeta).

6) Configurar `config.php` en el servidor
- En el panel de InfinityFree, ve a **MySQL Databases** y crea una base de datos (anota DB host, DB name, user y password).
- En el servidor, copia `config.example.php` a `config.php` y edita las variables con las credenciales proporcionadas.
- Ajusta `$email_to` y `$email_from` con la dirección que recibirá las notificaciones.

7) Importar la tabla `mensajes`
- Abre **phpMyAdmin** desde el panel de control.
- Selecciona la base de datos que creaste y ejecuta el SQL en `database/create_mensajes_table.sql` (Import → subir el archivo SQL).

8) Permisos y comprobaciones
- Archivos: permisos 644, carpetas: 755 (normalmente los gestores FTP y File Manager aplican permisos adecuados).
- Revisa que `enviar.php`, `contacto.php` y `config.php` estén en `/htdocs`.

9) Probar el sitio
- Abre en el navegador: `http://carlos-amancha.infinityfreeapp.com/contacto.php` y envía un mensaje de prueba.
- Verifica en phpMyAdmin que la fila se haya insertado en `mensajes`.

10) Correo en InfinityFree
- Muchos servicios de hosting gratuitos no permiten `mail()` o limitan el envío. Si no recibes correo:
  - Considera usar PHPMailer con SMTP (SendGrid, Mailgun, Gmail SMTP — con credenciales SMTP).
  - Puedo integrar PHPMailer en `enviar.php` si quieres; necesitarás las credenciales SMTP del servicio que elijas.

11) Seguridad y buenas prácticas
- No subas `config.php` con credenciales a repositorios públicos.
- Cambia contraseñas por defecto y usa contraseñas fuertes.
- Considera usar HTTPS (InfinityFree ofrece certificados SSL gratuitos en el panel o usar Cloudflare).

Si quieres, hago lo siguiente ahora:
- A) Integrar PHPMailer y ejemplo de configuración SMTP en `enviar.php`.
- B) Crear `deploy.zip` desde la workspace para que lo subas.

Indica si prefieres que: "A" integre PHPMailer, o que prepare el `deploy.zip` (opción B).
