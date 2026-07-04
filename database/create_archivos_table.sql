

USE if0_42064976_sub_seg;

CREATE TABLE IF NOT EXISTS archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_servidor VARCHAR(255) NOT NULL,
    extension VARCHAR(20) NOT NULL,
    tamano_bytes INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    sha256 VARCHAR(64) NOT NULL,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_archivo_nombre_servidor (nombre_servidor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;