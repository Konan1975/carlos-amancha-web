#  Sistema de Gestión de Archivos Seguro con PHP OOP

Este proyecto consiste en un módulo web desarrollado con **PHP Orientado a Objetos (POO)** que permite a los usuarios subir, visualizar y eliminar archivos de forma segura. El sistema está diseñado siguiendo buenas prácticas de desarrollo, priorizando la seguridad en la manipulación de archivos y una interfaz de usuario limpia y responsiva.

##  Características Principales

-   **Subida de Archivos:** Interfaz amigable para cargar archivos con validaciones de tipo y tamaño.
-   **Listado Dinámico:** Visualización de archivos subidos con detalles (nombre, tamaño, fecha).
-   **Gestión de Archivos:** Capacidad para descargar o eliminar archivos individualmente.
-   **Programación Orientada a Objetos:** Lógica encapsulada en la clase `GestorArchivos` para reutilización y mantenimiento.
-   **Seguridad Reforzada:**
    -   Validación estricta de **tipos MIME** y extensiones.
    -   **Renombrado de archivos** para evitar conflictos y ataques.
    -   Prevención de ejecución de scripts maliciosos.
    -   Validación de **Path Traversal**.
-   **Diseño Responsivo:** Interfaz adaptable a dispositivos móviles (usando CSS personalizado o Bootstrap).

## 🛠️ Tecnologías Utilizadas

-   **Lenguaje:** PHP 7.4+ (Compatible con PHP 8.x)
-   **Frontend:** HTML5 Semántico, CSS3 / Bootstrap 5, JavaScript
-   **Servidor:** Apache (XAMPP / LAMP / WAMP)
-   **Paradigma:** Orientación a Objetos (POO)

## 📁 Estructura del Proyecto

El proyecto sigue una estructura modular y limpia:

```text
gestor-archivos/
├── css/                  # Estilos CSS personalizados
│   └── estilos.css
├── uploads/              # Carpeta protegida donde se almacenan los archivos
├── GestorArchivos.php    # Clase principal con la lógica de negocio
├── index.php             # Página principal (Formulario + Listado)
├── subir.php             # Script de procesamiento de subida
├── eliminar.php          # Script de procesamiento de eliminación
── README.md             # Documentación del proyecto
