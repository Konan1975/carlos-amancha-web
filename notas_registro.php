<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

$rootDir = __DIR__;
$dataDir = $rootDir . DIRECTORY_SEPARATOR . 'data';
$metricsPath = $dataDir . DIRECTORY_SEPARATOR . 'notas_metrics.json';

if (!is_dir($dataDir) && !mkdir($dataDir, 0775, true) && !is_dir($dataDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo preparar el directorio de datos.']);
    exit;
}

if (!file_exists($metricsPath)) {
    $initialData = [
        'totalUsers' => 0,
        'totalSimulations' => 0,
        'users' => [],
        'events' => []
    ];
    file_put_contents($metricsPath, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'metrics') {
    $data = readMetrics($metricsPath);
    echo json_encode([
        'success' => true,
        'metrics' => [
            'totalUsers' => (int) ($data['totalUsers'] ?? 0),
            'totalSimulations' => (int) ($data['totalSimulations'] ?? 0)
        ]
    ]);
    exit;
}

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo no permitido.']);
    exit;
}

$input = file_get_contents('php://input');
$payload = json_decode($input ?: '{}', true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Payload invalido.']);
    exit;
}

$event = cleanText((string) ($payload['event'] ?? ''));
if ($event !== 'save_simulation') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Evento no soportado.']);
    exit;
}

$clientId = cleanText((string) ($payload['clientId'] ?? ''));
$student = is_array($payload['student'] ?? null) ? $payload['student'] : [];
$simulation = is_array($payload['simulation'] ?? null) ? $payload['simulation'] : [];

$name = cleanText((string) ($student['name'] ?? ''));
$email = cleanEmail((string) ($student['email'] ?? ''));
$title = cleanText((string) ($simulation['title'] ?? ''));
$code = cleanText((string) ($simulation['code'] ?? ''));
$status = cleanText((string) ($simulation['status'] ?? ''));
$score = isset($simulation['score']) ? (float) $simulation['score'] : 0.0;

$userKey = '';
if ($email !== '') {
    $userKey = 'email:' . hash('sha256', strtolower($email));
} elseif ($clientId !== '') {
    $userKey = 'client:' . hash('sha256', $clientId);
}

$data = readMetrics($metricsPath);
$users = is_array($data['users'] ?? null) ? $data['users'] : [];
$events = is_array($data['events'] ?? null) ? $data['events'] : [];

if ($userKey !== '' && !in_array($userKey, $users, true)) {
    $users[] = $userKey;
}

$events[] = [
    'at' => gmdate('c'),
    'event' => 'save_simulation',
    'title' => substr($title, 0, 120),
    'code' => substr($code, 0, 60),
    'status' => substr($status, 0, 40),
    'score' => round($score, 2),
    'name' => substr($name, 0, 80)
];

if (count($events) > 1000) {
    $events = array_slice($events, -1000);
}

$data['users'] = $users;
$data['events'] = $events;
$data['totalUsers'] = count($users);
$data['totalSimulations'] = count($events);

if (!writeMetrics($metricsPath, $data)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo escribir el registro.']);
    exit;
}

echo json_encode([
    'success' => true,
    'metrics' => [
        'totalUsers' => $data['totalUsers'],
        'totalSimulations' => $data['totalSimulations']
    ]
]);

function readMetrics(string $path): array
{
    $contents = @file_get_contents($path);
    if ($contents === false || trim($contents) === '') {
        return [
            'totalUsers' => 0,
            'totalSimulations' => 0,
            'users' => [],
            'events' => []
        ];
    }

    $decoded = json_decode($contents, true);
    if (!is_array($decoded)) {
        return [
            'totalUsers' => 0,
            'totalSimulations' => 0,
            'users' => [],
            'events' => []
        ];
    }

    return $decoded;
}

function writeMetrics(string $path, array $data): bool
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        return false;
    }

    $fp = @fopen($path, 'c+');
    if ($fp === false) {
        return false;
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return false;
    }

    ftruncate($fp, 0);
    rewind($fp);
    $written = fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $written !== false;
}

function cleanText(string $value): string
{
    return trim(preg_replace('/\s+/', ' ', $value) ?? '');
}

function cleanEmail(string $value): string
{
    $email = trim($value);
    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return '';
    }
    return $email;
}
