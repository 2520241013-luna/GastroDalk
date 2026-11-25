<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../src/ReglasNegocio.php';
require_once __DIR__ . '/../src/ServiciosGastrodalk.php';

use Gastrodalk\ReglasNegocio;
use Gastrodalk\ServiciosGastrodalk;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['respuesta' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Leer el cuerpo JSON o formulario POST aceptando múltiples nombres de campo
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
    echo json_encode(['respuesta' => 'Tu consulta es demasiado larga. Por favor resúmela en menos de 600 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Cargar variables desde .env local si existe
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

// Leer GEMINI_API_KEY desde cualquier fuente de entorno del servidor
$apiKeyRaw = getenv('GEMINI_API_KEY');
if (!$apiKeyRaw && isset($_ENV['GEMINI_API_KEY'])) {
    $apiKeyRaw = $_ENV['GEMINI_API_KEY'];
}
if (!$apiKeyRaw && isset($_SERVER['GEMINI_API_KEY'])) {
    $apiKeyRaw = $_SERVER['GEMINI_API_KEY'];
}
$apiKey = trim((string) $apiKeyRaw);

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode([
        'respuesta' => 'El asistente Damian IA no tiene configurada la clave GEMINI_API_KEY en el servidor.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener contexto actualizado del menú desde la base de datos
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

$payload = [
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
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode([
        'respuesta' => 'No fue posible conectar con el servicio de IA en este momento. Intenta de nuevo en unos segundos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($response, true);

if ($httpCode >= 400 || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $detalleError = $data['error']['message'] ?? "HTTP {$httpCode}";
    http_response_code(502);
    echo json_encode([
        'respuesta' => "Damian IA no pudo procesar la respuesta ({$detalleError}). Verifica que tu GEMINI_API_KEY sea válida."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$textoCrudo  = (string) $data['candidates'][0]['content']['parts'][0]['text'];
$textoLimpio = ReglasNegocio::limpiarRespuestaIA($textoCrudo);

echo json_encode(['respuesta' => $textoLimpio], JSON_UNESCAPED_UNICODE);