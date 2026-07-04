# Módulo de Gestión de Archivos - Carlos Amancha

## Descripción
Sistema para subir, listar, descargar y eliminar archivos con Programación Orientada a Objetos en PHP y medidas de seguridad.

## Requisitos
- PHP 7.4+ con extensiones: mysqli, fileinfo
- MySQL 5.7+
- Apache con mod_rewrite habilitado

## Instalación
1. Importar el esquema de la base de datos desde [database/create_archivos_table.sql](database/create_archivos_table.sql).
2. Configurar las credenciales de MySQL en [config.php](config.php).
3. Asegurar permisos 755 en la carpeta [uploads](uploads).
4. Subir los archivos del proyecto al directorio htdocs del hosting.

## Uso
1. Acceder a [archivos.php](archivos.php).
2. Subir un archivo válido (PDF/JPG/PNG menor a 2 MB).
3. Listar, descargar o eliminar desde la tabla.

## Medidas de Seguridad Aplicadas
- Validación de extensión y MIME type real.
- Renombrado con hash SHA-256.
- Consultas preparadas contra SQL injection.
- Escape de salida contra XSS.
- Archivo .htaccess para bloquear ejecución en [uploads](uploads).
