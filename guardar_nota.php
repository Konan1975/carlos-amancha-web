<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /notas.php');
    exit;
}

$datos = [
    'titulo' => trim((string) ($_POST['titulo'] ?? '')),
    'contenido' => trim((string) ($_POST['contenido'] ?? '')),
    'categoria' => trim((string) ($_POST['categoria'] ?? '')),
];

$errores = [];
if (mb_strlen($datos['titulo']) < 3) {
    $errores[] = 'El título debe tener al menos 3 caracteres.';
}
if (mb_strlen($datos['contenido']) < 5) {
    $errores[] = 'El contenido debe tener al menos 5 caracteres.';
}
if (mb_strlen($datos['categoria']) < 2) {
    $errores[] = 'La categoría es obligatoria.';
}

if (empty($errores)) {
    $rutaDirectorio = __DIR__ . '/data';
    if (!is_dir($rutaDirectorio)) {
        mkdir($rutaDirectorio, 0777, true);
    }

    $rutaArchivo = $rutaDirectorio . '/notas.json';
    $notas = [];
    if (file_exists($rutaArchivo)) {
        $contenido = file_get_contents($rutaArchivo);
        if ($contenido !== false && trim($contenido) !== '') {
            $datosArchivo = json_decode($contenido, true);
            if (is_array($datosArchivo)) {
                $notas = $datosArchivo;
            }
        }
    }

    $notas[] = [
        'id' => time() . '-' . mt_rand(1000, 9999),
        'titulo' => $datos['titulo'],
        'contenido' => $datos['contenido'],
        'categoria' => $datos['categoria'],
        'fecha_creacion' => date('Y-m-d H:i:s')
    ];

    $guardado = file_put_contents($rutaArchivo, json_encode($notas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($guardado === false) {
        $errores[] = 'No se pudo guardar la nota.';
    }
}

session_start();
if (empty($errores)) {
    header('Location: /notas.php?estado=ok');
    exit;
}

$_SESSION['errores_notas'] = $errores;
$_SESSION['datos_nota'] = $datos;
header('Location: /notas.php');
exit;
