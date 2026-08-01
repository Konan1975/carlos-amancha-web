<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Notas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: notas.html');
    exit;
}

$datos = [
    'titulo' => trim((string) ($_POST['titulo'] ?? '')),
    'contenido' => trim((string) ($_POST['contenido'] ?? '')),
    'categoria' => trim((string) ($_POST['categoria'] ?? '')),
];

$errores = Notas::validar($datos);

if (empty($errores)) {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conexion->connect_errno) {
        $resultado = Notas::guardar(null, $datos);
    } else {
        $resultado = Notas::guardar($conexion, $datos);
        $conexion->close();
    }

    if (!$resultado['ok']) {
        $errores = array_merge($errores, $resultado['errores']);
    }
}

if (empty($errores)) {
    header('Location: notas.php?estado=ok');
    exit;
}

session_start();
$_SESSION['errores_notas'] = $errores;
$_SESSION['datos_nota'] = $datos;
header('Location: notas.html');
exit;
