# Instrucciones para agentes de codificación

## Propósito del proyecto
Este repositorio contiene un sitio web personal en HTML/CSS/JavaScript con un formulario de contacto funcional en PHP y almacenamiento en MySQL. El objetivo principal es mantener el sitio simple, estático y fácil de desplegar en XAMPP o InfinityFree.

## Stack y estructura
- Frontend: HTML, CSS y JavaScript simples.
- Backend: PHP (sin framework).
- Base de datos: MySQL.
- Despliegue: XAMPP local y hosting InfinityFree.

Archivos clave:
- [index.html](index.html): página principal del sitio.
- [contacto.php](contacto.php): formulario de contacto.
- [enviar.php](enviar.php): validación, guardado en base de datos y envío de correo.
- [config.php](config.php): credenciales de base de datos y correo; no exponer secretos en commits públicos.
- [css/estilos.css](css/estilos.css): estilos del sitio.
- [database/create_mensajes_table.sql](database/create_mensajes_table.sql): esquema de la tabla de mensajes.
- [README.md](README.md): descripción general del proyecto.
- [DEPLOY.md](DEPLOY.md): instrucciones de despliegue.

## Convenciones importantes
- Mantén cambios compatibles con PHP puro y archivos HTML/CSS estáticos.
- Prefiere soluciones simples y legibles sobre introducir frameworks o dependencias nuevas.
- Conserva el estilo y textos en español del sitio.
- Si cambias el formulario de contacto, revisa tanto [contacto.php](contacto.php) como [enviar.php](enviar.php).
- Usa rutas relativas y evita depender de rutas absolutas.
- No cambies credenciales sensibles en [config.php](config.php) salvo que sea para desarrollo local explícito.

## Flujo de trabajo local
- Para desarrollo local, el proyecto se espera en la carpeta htdocs de XAMPP.
- Prueba los cambios abriendo el sitio en el navegador y verificando el flujo del formulario.
- Si modificas PHP, valida sintaxis con:
  - `php -l contacto.php`
  - `php -l enviar.php`

## Notas de despliegue
- El despliegue se documenta en [DEPLOY.md](DEPLOY.md).
- Para producción, revisa que la base de datos y las credenciales estén configuradas correctamente.
- El envío de correo puede fallar en entornos compartidos; el código ya maneja ese caso de forma parcial.

## Recomendaciones para agentes
- Prioriza cambios pequeños y enfocados.
- Cuando agregues contenido nuevo, mantén la experiencia del sitio consistente con el diseño existente.
- Si un cambio afecta formularios, validación, base de datos o correo, documenta el impacto de forma breve.
