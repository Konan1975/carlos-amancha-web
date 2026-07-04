<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/GestorArchivos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Location: index.php?mensaje=error&texto=' . urlencode('Método no permitido.'));
    exit;
}

$nombreServidor = isset($_GET['nombre_servidor']) ? basename((string) $_GET['nombre_servidor']) : '';
if ($nombreServidor === '') {
    header('Location: index.php?mensaje=error&texto=' . urlencode('No se recibió el nombre del archivo.'));
    exit;
}

$gestor = new GestorArchivos();
$gestor->descargar($nombreServidor);
