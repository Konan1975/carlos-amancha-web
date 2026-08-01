<?php
class Notas
{
    private static function rutaArchivo(): string
    {
        $rutaDirectorio = __DIR__ . '/../data';
        if (!is_dir($rutaDirectorio)) {
            mkdir($rutaDirectorio, 0777, true);
        }

        return $rutaDirectorio . '/notas.json';
    }

    public static function validar(array $datos): array
    {
        $errores = [];
        $titulo = trim((string) ($datos['titulo'] ?? ''));
        $contenido = trim((string) ($datos['contenido'] ?? ''));
        $categoria = trim((string) ($datos['categoria'] ?? ''));

        if (mb_strlen($titulo) < 3) {
            $errores[] = 'El título debe tener al menos 3 caracteres.';
        }

        if (mb_strlen($contenido) < 5) {
            $errores[] = 'El contenido debe tener al menos 5 caracteres.';
        }

        if (mb_strlen($categoria) < 2) {
            $errores[] = 'La categoría es obligatoria.';
        }

        return $errores;
    }

    public static function guardar($conexion, array $datos): array
    {
        $errores = [];
        $titulo = trim((string) ($datos['titulo'] ?? ''));
        $contenido = trim((string) ($datos['contenido'] ?? ''));
        $categoria = trim((string) ($datos['categoria'] ?? ''));

        if ($conexion instanceof mysqli && !$conexion->connect_errno) {
            $stmt = $conexion->prepare('INSERT INTO notas (titulo, contenido, categoria, fecha_creacion) VALUES (?, ?, ?, NOW())');
            if (!$stmt) {
                $errores[] = 'No se pudo preparar la consulta en la base de datos.';
                return ['ok' => false, 'errores' => $errores];
            }

            $stmt->bind_param('sss', $titulo, $contenido, $categoria);
            $resultado = $stmt->execute();
            $stmt->close();

            if (!$resultado) {
                $errores[] = 'No se pudo guardar la nota en la base de datos.';
            }

            return ['ok' => $resultado, 'errores' => $errores];
        }

        $rutaArchivo = self::rutaArchivo();
        $notas = [];
        if (file_exists($rutaArchivo)) {
            $contenidoArchivo = file_get_contents($rutaArchivo);
            $notas = $contenidoArchivo !== '' ? json_decode($contenidoArchivo, true) : [];
            if (!is_array($notas)) {
                $notas = [];
            }
        }

        $notas[] = [
            'id' => time() . '-' . mt_rand(1000, 9999),
            'titulo' => $titulo,
            'contenido' => $contenido,
            'categoria' => $categoria,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];

        $guardado = file_put_contents($rutaArchivo, json_encode($notas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($guardado === false) {
            $errores[] = 'No se pudo escribir el archivo de notas.';
            return ['ok' => false, 'errores' => $errores];
        }

        return ['ok' => true, 'errores' => []];
    }

    public static function listar($conexion): array
    {
        if ($conexion instanceof mysqli && !$conexion->connect_errno) {
            $resultado = $conexion->query('SELECT id, titulo, contenido, categoria, fecha_creacion FROM notas ORDER BY fecha_creacion DESC');
            if (!$resultado) {
                return [];
            }

            $notas = [];
            while ($fila = $resultado->fetch_assoc()) {
                $notas[] = $fila;
            }

            return $notas;
        }

        $rutaArchivo = self::rutaArchivo();
        if (!file_exists($rutaArchivo)) {
            return [];
        }

        $contenidoArchivo = file_get_contents($rutaArchivo);
        $notas = $contenidoArchivo !== '' ? json_decode($contenidoArchivo, true) : [];
        return is_array($notas) ? $notas : [];
    }
}
