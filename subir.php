<?php
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/GestorArchivos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!empty($_SESSION['csrf_token']) && (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']))) {
    header('Location: index.php?mensaje=error&texto=' . urlencode('Token de seguridad inválido.'));
    exit;
}

if (!isset($_FILES['archivo']) || !is_array($_FILES['archivo'])) {
    header('Location: index.php?mensaje=error&texto=' . urlencode('No se recibió ningún archivo.'));
    exit;
}

$descripcion = isset($_POST['descripcion']) ? trim((string) $_POST['descripcion']) : '';
$descripcionSanitizada = htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8');

$gestor = new GestorArchivos();
$resultado = $gestor->subir($_FILES['archivo'], $descripcionSanitizada);

if ($resultado['success']) {
    header('Location: index.php?mensaje=success&texto=' . urlencode($resultado['message']));
} else {
    header('Location: index.php?mensaje=error&texto=' . urlencode($resultado['message']));
}

exit;
