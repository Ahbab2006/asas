<?php
// backend_php/api/health.php
declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$response = [
    'status' => 'ok',
    'php_version' => PHP_VERSION,
    'environment' => getenv('VERCEL') ? 'Vercel Serverless' : 'Local / Custom Server',
    'extensions' => [
        'mongodb' => extension_loaded('mongodb'),
        'curl' => extension_loaded('curl'),
        'json' => extension_loaded('json'),
        'mbstring' => extension_loaded('mbstring')
    ],
    'mongodb' => [
        'connected' => false,
        'database' => Database::getDbName(),
        'ping' => null,
        'error' => null
    ],
    'timestamp' => date('Y-m-d H:i:s T')
];

try {
    $manager = Database::getManager();
    $cmd = new MongoDB\Driver\Command(['ping' => 1]);
    $cursor = $manager->executeCommand('admin', $cmd);
    $pingResult = $cursor->toArray()[0];
    $response['mongodb']['connected'] = true;
    $response['mongodb']['ping'] = $pingResult->ok ?? 1;
} catch (Exception $e) {
    $response['status'] = 'degraded';
    $response['mongodb']['error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);

