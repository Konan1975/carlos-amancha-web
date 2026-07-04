<?php

declare(strict_types=1);

// Carga las constantes de configuración del proyecto.
require_once __DIR__ . '/../config.php';

class GestorArchivos
{
    // Directorio físico donde se almacenarán los archivos.
    private string $directorio;
    // Extensiones permitidas para la subida.
    private array $extensionesPermitidas;
    // Tipos MIME permitidos tras validar el contenido real.
    private array $tiposMIMEPermitidos;
    // Tamaño máximo de archivo en bytes.
    private int $tamanoMaximo;
    // Conexión a MySQLi para guardar los metadatos.
    private ?mysqli $conexion;

    public function __construct(string $directorio = UPLOAD_DIR)
    {
        $this->directorio = rtrim($directorio, DIRECTORY_SEPARATOR);
        $this->extensionesPermitidas = defined('UPLOAD_EXTENSIONS') ? UPLOAD_EXTENSIONS : ['pdf', 'jpg', 'jpeg', 'png'];
        $this->tiposMIMEPermitidos = defined('UPLOAD_MIME_TYPES') ? UPLOAD_MIME_TYPES : ['application/pdf', 'image/jpeg', 'image/png'];
        $this->tamanoMaximo = defined('UPLOAD_MAX_SIZE') ? (int) UPLOAD_MAX_SIZE : 2 * 1024 * 1024;
        $this->conexion = null;

        // Crea el directorio de subida si no existe.
        if (!is_dir($this->directorio) && !mkdir($this->directorio, 0755, true) && !is_dir($this->directorio)) {
            throw new RuntimeException('No se pudo crear el directorio de subida.');
        }

        // Intenta abrir la conexión a la base de datos usando las constantes de configuración.
        try {
            $this->conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($this->conexion->connect_error) {
                throw new RuntimeException('No se pudo conectar a la base de datos.');
            }
            $this->conexion->set_charset('utf8mb4');
        } catch (Throwable $e) {
            $this->conexion = null;
        }
    }

    // Valida el archivo recibido antes de procesarlo: tamaño, nombre, extensión y tipo MIME.
    private function validarArchivo(array $archivo): array
    {
        $resultado = [
            'valido' => false,
            'mensaje' => '',
            'errorUpload' => false,
            'tamanoValido' => false,
            'extensionValida' => false,
            'mimeValido' => false,
            'nombreOriginal' => '',
            'extension' => '',
            'mimeType' => '',
            'tamanoBytes' => 0,
        ];

        if (!isset($archivo['name'], $archivo['tmp_name'])) {
            $resultado['mensaje'] = 'Archivo no válido.';
            return $resultado;
        }

        if (!is_uploaded_file($archivo['tmp_name'])) {
            $resultado['mensaje'] = 'El archivo no fue enviado correctamente.';
            $resultado['errorUpload'] = true;
            return $resultado;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $resultado['mensaje'] = 'Hubo un problema al subir el archivo.';
            $resultado['errorUpload'] = true;
            return $resultado;
        }

        $tamanoBytes = (int) $archivo['size'];
        if ($tamanoBytes > $this->tamanoMaximo) {
            $resultado['mensaje'] = 'El archivo excede el tamaño máximo permitido de 2 MB.';
            return $resultado;
        }
        $resultado['tamanoValido'] = true;

        $nombreOriginal = basename((string) $archivo['name']);
        if ($nombreOriginal === '.' || $nombreOriginal === '..') {
            $resultado['mensaje'] = 'El nombre del archivo no es válido.';
            return $resultado;
        }

        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->extensionesPermitidas, true)) {
            $resultado['mensaje'] = 'Tipo de archivo no permitido.';
            return $resultado;
        }
        $resultado['extensionValida'] = true;
        $resultado['nombreOriginal'] = $nombreOriginal;
        $resultado['extension'] = $extension;

        if (!function_exists('finfo_open')) {
            $resultado['mensaje'] = 'La extensión finfo no está disponible en este servidor.';
            return $resultado;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $archivo['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if ($mimeType === false || !in_array($mimeType, $this->tiposMIMEPermitidos, true)) {
            $resultado['mensaje'] = 'El MIME type del archivo no es válido.';
            return $resultado;
        }

        $resultado['mimeValido'] = true;
        $resultado['mimeType'] = $mimeType;
        $resultado['tamanoBytes'] = $tamanoBytes;
        $resultado['valido'] = true;
        return $resultado;
    }

    // Genera un nombre seguro basado en un hash SHA-256 y en un nombre sanitizado.
    private function generarNombreSeguro(string $nombreOriginal, string $extension): string
    {
        $baseNombre = $this->sanearNombreArchivo(pathinfo($nombreOriginal, PATHINFO_FILENAME));
        $hash = hash('sha256', $nombreOriginal . '|' . $extension);
        $nombreSeguro = $hash . '-' . $baseNombre;

        if ($extension !== '') {
            $nombreSeguro .= '.' . strtolower($extension);
        }

        $contador = 1;
        while (file_exists($this->directorio . DIRECTORY_SEPARATOR . $nombreSeguro)) {
            $nombreSeguro = $hash . '-' . $baseNombre . '-' . $contador . '.' . strtolower($extension);
            $contador++;
        }

        return $nombreSeguro;
    }

    // Guarda un archivo validado en el directorio de subidas y registra sus metadatos en la base de datos.
    public function subir(array $archivo, string $descripcion = ''): array
    {
        try {
            $validacion = $this->validarArchivo($archivo);
            if (!$validacion['valido']) {
                return ['success' => false, 'message' => $validacion['mensaje']];
            }

            $nombreOriginal = $validacion['nombreOriginal'];
            $extension = $validacion['extension'];
            $nombreServidor = $this->generarNombreSeguro($nombreOriginal, $extension);
            $rutaDestino = $this->directorio . DIRECTORY_SEPARATOR . $nombreServidor;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                return ['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.'];
            }

            @chmod($rutaDestino, 0644);

            $rutaReal = realpath($rutaDestino);
            $directorioReal = realpath($this->directorio);
            if ($rutaReal === false || $directorioReal === false || strpos($rutaReal, $directorioReal . DIRECTORY_SEPARATOR) !== 0) {
                @unlink($rutaDestino);
                return ['success' => false, 'message' => 'La ruta del archivo no es válida.'];
            }

            if ($this->conexion === null) {
                return ['success' => false, 'message' => 'No se pudo conectar a la base de datos.'];
            }

            $hashContenido = hash_file('sha256', $rutaDestino);
            $descripcion = trim(mb_substr($descripcion, 0, 255, 'UTF-8'));
            $tamanoBytes = (int) $validacion['tamanoBytes'];
            $stmt = mysqli_prepare($this->conexion, 'INSERT INTO archivos (nombre_original, nombre_servidor, extension, tamano_bytes, mime_type, descripcion, sha256, fecha_subida) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            if ($stmt === false) {
                @unlink($rutaDestino);
                return ['success' => false, 'message' => 'No se pudo preparar la consulta de almacenamiento.'];
            }

            mysqli_stmt_bind_param($stmt, 'sssssss', $nombreOriginal, $nombreServidor, $extension, $tamanoBytes, $validacion['mimeType'], $descripcion, $hashContenido);
            if (!mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                @unlink($rutaDestino);
                return ['success' => false, 'message' => 'No se pudo guardar la información del archivo en la base de datos.'];
            }

            $idInsertado = mysqli_insert_id($this->conexion);
            mysqli_stmt_close($stmt);

            return [
                'success' => true,
                'message' => 'Archivo subido correctamente.',
                'archivo' => [
                    'id' => $idInsertado,
                    'nombre_original' => $nombreOriginal,
                    'nombre_servidor' => $nombreServidor,
                    'extension' => $extension,
                    'tamano_bytes' => $tamanoBytes,
                    'mime_type' => $validacion['mimeType'],
                    'descripcion' => $descripcion,
                ],
            ];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Ocurrió un error inesperado al subir el archivo.'];
        }
    }

    // Devuelve los archivos registrados, ordenados del más reciente al más antiguo, con formato legible para la interfaz.
    public function listar(): array
    {
        try {
            if ($this->conexion === null) {
                return [];
            }

            $stmt = mysqli_prepare($this->conexion, 'SELECT id, nombre_original, nombre_servidor, extension, tamano_bytes, fecha_subida, descripcion FROM archivos ORDER BY fecha_subida DESC');
            if ($stmt === false) {
                return [];
            }

            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $id, $nombreOriginal, $nombreServidor, $extension, $tamanoBytes, $fechaSubida, $descripcion);

            $archivos = [];
            while (mysqli_stmt_fetch($stmt)) {
                $archivos[] = [
                    'id' => (int) $id,
                    'nombre_original' => $nombreOriginal,
                    'nombre_servidor' => $nombreServidor,
                    'extension' => $extension,
                    'tamano' => $this->formatearTamano((int) $tamanoBytes),
                    'fecha_subida' => $fechaSubida,
                    'descripcion' => $descripcion,
                ];
            }

            mysqli_stmt_close($stmt);
            return $archivos;
        } catch (Throwable $e) {
            return [];
        }
    }

    // Elimina el archivo físico y su registro asociado cuando la ruta solicitada es segura.
    public function eliminar(int $id, string $nombreServidor): array
    {
        try {
            $nombreServidor = basename($nombreServidor);
            if ($nombreServidor === '.' || $nombreServidor === '..' || $nombreServidor === '') {
                return ['success' => false, 'message' => 'Nombre de archivo inválido.'];
            }

            $rutaArchivo = $this->directorio . DIRECTORY_SEPARATOR . $nombreServidor;
            $rutaReal = realpath($rutaArchivo);
            $directorioReal = realpath($this->directorio);
            if ($rutaReal === false || $directorioReal === false || strpos($rutaReal, $directorioReal . DIRECTORY_SEPARATOR) !== 0) {
                return ['success' => false, 'message' => 'La ruta del archivo no es segura.'];
            }

            $archivoEliminado = false;
            if (is_file($rutaReal)) {
                $archivoEliminado = unlink($rutaReal);
            }

            if ($this->conexion === null) {
                return ['success' => false, 'message' => 'No se pudo conectar a la base de datos.'];
            }

            $stmt = mysqli_prepare($this->conexion, 'DELETE FROM archivos WHERE id = ? AND nombre_servidor = ?');
            if ($stmt === false) {
                return ['success' => false, 'message' => 'No se pudo preparar la consulta de eliminación.'];
            }

            mysqli_stmt_bind_param($stmt, 'is', $id, $nombreServidor);
            $resultado = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($resultado && $archivoEliminado) {
                return ['success' => true, 'message' => 'Archivo eliminado correctamente.'];
            }

            return ['success' => false, 'message' => 'No se pudo eliminar el archivo.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Ocurrió un error inesperado al eliminar el archivo.'];
        }
    }

    // Envía el archivo al navegador como descarga con cabeceras adecuadas para el cliente.
    public function descargar(string $nombreServidor): bool
    {
        try {
            $nombreServidor = basename($nombreServidor);
            if ($nombreServidor === '.' || $nombreServidor === '..' || $nombreServidor === '') {
                return false;
            }

            $rutaArchivo = $this->directorio . DIRECTORY_SEPARATOR . $nombreServidor;
            $rutaReal = realpath($rutaArchivo);
            $directorioReal = realpath($this->directorio);
            if ($rutaReal === false || $directorioReal === false || strpos($rutaReal, $directorioReal . DIRECTORY_SEPARATOR) !== 0 || !is_file($rutaReal)) {
                return false;
            }

            $nombreOriginal = $nombreServidor;
            if ($this->conexion !== null) {
                $stmt = mysqli_prepare($this->conexion, 'SELECT nombre_original FROM archivos WHERE nombre_servidor = ? LIMIT 1');
                if ($stmt !== false) {
                    mysqli_stmt_bind_param($stmt, 's', $nombreServidor);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $nombreOriginal);
                    if (mysqli_stmt_fetch($stmt)) {
                        $nombreOriginal = $nombreOriginal;
                    }
                    mysqli_stmt_close($stmt);
                }
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($nombreOriginal) . '"');
            header('Content-Length: ' . filesize($rutaReal));
            readfile($rutaReal);
            exit;
        } catch (Throwable $e) {
            return false;
        }
    }

    // Cierra la conexión a MySQLi al destruir el objeto.
    public function __destruct()
    {
        if ($this->conexion instanceof mysqli) {
            $this->conexion->close();
        }
    }

    private function sanearNombreArchivo(string $nombre): string
    {
        $nombre = basename($nombre);
        if ($nombre === '.' || $nombre === '..') {
            return 'archivo';
        }

        $nombre = preg_replace('/[^A-Za-z0-9._-]/u', '_', $nombre) ?? '';
        $nombre = trim($nombre, '._-');
        return $nombre !== '' ? $nombre : 'archivo';
    }

    private function formatearTamano(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $indice = 0;
        $tamano = (float) $bytes;

        while ($tamano >= 1024 && $indice < count($unidades) - 1) {
            $tamano /= 1024;
            $indice++;
        }

        return round($tamano, 2) . ' ' . $unidades[$indice];
    }
}
