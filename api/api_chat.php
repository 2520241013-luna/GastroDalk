<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../conexion/conexion.php';
    require_once __DIR__ . '/../src/ReglasNegocio.php';
    require_once __DIR__ . '/../src/ServiciosGastrodalk.php';
} catch (Throwable $e) {
    echo json_encode(['respuesta' => 'Error interno al cargar archivos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

use Gastrodalk\ReglasNegocio;
use Gastrodalk\ServiciosGastrodalk;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['respuesta' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawBody = file_get_contents('php://input');
$input = is_string($rawBody) ? json_decode($rawBody, true) : [];
if (!is_array($input)) {
    $input = [];
}

$pregunta = trim((string) (
    $input['pregunta']
    ?? $input['mensaje']
    ?? $input['message']
    ?? $input['prompt']
    ?? $_POST['pregunta']
    ?? $_POST['mensaje']
    ?? $_POST['message']
    ?? ''
));

if ($pregunta === '') {
    echo json_encode(['respuesta' => 'Por favor escribe tu consulta sobre nuestro menú o servicios.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($pregunta) > 600) {
    echo json_encode(['respuesta' => 'Tu consulta es demasiado larga (máximo 600 caracteres).'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Cargar .env local si existe
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }
        [$clave, $valor] = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");
        if (!isset($_ENV[$clave])) {
            $_ENV[$clave] = $valor;
        }
    }
}

$apiKeyRaw = getenv('GEMINI_API_KEY');
if (!$apiKeyRaw && isset($_ENV['GEMINI_API_KEY'])) {
    $apiKeyRaw = $_ENV['GEMINI_API_KEY'];
}
if (!$apiKeyRaw && isset($_SERVER['GEMINI_API_KEY'])) {
    $apiKeyRaw = $_SERVER['GEMINI_API_KEY'];
}
$apiKey = trim((string) $apiKeyRaw);

if ($apiKey === '') {
    echo json_encode([
        'respuesta' => 'Falta configurar GEMINI_API_KEY en Env Vars de EthioDeploy.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $servicio = new ServiciosGastrodalk($conexion ?? null);
    $contextoMenu = $servicio->construirContextoMenuIA();
} catch (Throwable $e) {
    $contextoMenu = "Menú disponible hoy en Gastrodalk: platos típicos colombianos preparados al momento.";
}

$promptSistema = "Eres Damian, el asistente gastronómico virtual oficial del restaurante Gastrodalk. "
    . "Responde de forma amable, clara, breve y en español (sin usar asteriscos ni formato Markdown). "
    . "Basa tus recomendaciones únicamente en la siguiente información del menú:\n\n"
    . $contextoMenu . "\n\n"
    . "Consulta del cliente: " . $pregunta;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . urlencode($apiKey);

$payloadJson = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $promptSistema]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature'     => 0.7,
        'maxOutputTokens' => 450,
    ]
], JSON_UNESCAPED_UNICODE);

$response = false;
$httpCode = 0;

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payloadJson,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    $context = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\n",
            'content'       => $payloadJson,
            'timeout'       => 25,
            'ignore_errors' => true,
        ]
    ]);
    $response = @file_get_contents($url, false, $context);
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $httpCode = (int) $m[1];
    }
}

if ($response === false || $response === '') {
    echo json_encode([
        'respuesta' => 'El servidor no pudo conectarse con Google Gemini (error de red saliente).'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode((string) $response, true);

if ($httpCode >= 400 || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $detalle = $data['error']['message'] ?? "HTTP {$httpCode}";
    echo json_encode([
        'respuesta' => "Error de Google Gemini ({$detalle}). Revisa tu GEMINI_API_KEY en EthioDeploy."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$textoCrudo  = (string) $data['candidates'][0]['content']['parts'][0]['text'];
$textoLimpio = ReglasNegocio::limpiarRespuestaIA($textoCrudo);

echo json_encode(['respuesta' => $textoLimpio], JSON_UNESCAPED_UNICODE);