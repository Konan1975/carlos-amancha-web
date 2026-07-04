<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/GestorArchivos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Location: index.php?mensaje=error&texto=' . urlencode('Método no permitido.'));
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nombreServidor = isset($_GET['nombre_servidor']) ? basename((string) $_GET['nombre_servidor']) : '';
if ($id <= 0 || $nombreServidor === '') {
    header('Location: index.php?mensaje=error&texto=' . urlencode('Datos de eliminación inválidos.'));
    exit;
}

$gestor = new GestorArchivos();
$resultado = $gestor->eliminar($id, $nombreServidor);

header('Location: index.php?mensaje=' . ($resultado['success'] ? 'success' : 'error') . '&texto=' . urlencode($resultado['message']));
exit;
