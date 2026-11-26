<?php
declare(strict_types=1);

ob_start();
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../conexion/conexion.php';

function responderJson(array $payload, int $status = 200): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

set_exception_handler(function (Throwable $e) {
    error_log('Excepción en api_chat.php: ' . $e->getMessage());
    responderJson([
        'error' => 'Error interno en el servidor de chat.'
    ], 500);
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJson(['error' => 'Usa método POST desde el chat.'], 405);
}

$rawInput = file_get_contents('php://input');
$cuerpo = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : null;
$pregunta = trim((string) ($cuerpo['mensaje'] ?? $cuerpo['pregunta'] ?? $_POST['mensaje'] ?? ''));

if ($pregunta === '') {
    responderJson(['error' => 'Escribe una pregunta para Damian IA.'], 400);
}

$menuTexto = "Menú disponible hoy en Gastrodalk:\n";
if ($conexion instanceof PDO) {
    try {
        $stmt = $conexion->query("SELECT nombre, descripcion, precio, categoria FROM platos WHERE disponible = 1");
        $platos = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if (!empty($platos)) {
            foreach ($platos as $fila) {
                $nombre = (string) ($fila['nombre'] ?? '');
                $categoria = (string) ($fila['categoria'] ?? '');
                $descripcion = (string) ($fila['descripcion'] ?? '');
                $precio = number_format((float) ($fila['precio'] ?? 0), 0, ',', '.');
                $menuTexto .= "- {$nombre} ({$categoria}): {$descripcion} - Precio: \${$precio}\n";
            }
        } else {
            $menuTexto .= "- No hay platos activos registrados en este momento.\n";
        }
    } catch (Throwable $e) {
        error_log('Error al leer platos: ' . $e->getMessage());
        $menuTexto .= "- No se pudo cargar el menú desde la base de datos en este momento.\n";
    }
} else {
    $menuTexto .= "- Sin conexión activa a base de datos (" . ($errorConexion ?? 'revise configuración') . ").\n";
}

$apiKey = leerEntornoSeguro('GROQ_API_KEY');
if ($apiKey === '') {
    responderJson(['error' => 'Falta configurar GROQ_API_KEY en el entorno o en .env.'], 500);
}

$instruccionesSistema = "Eres Damian IA, el asistente virtual de recomendación gastronómica del restaurante Gastrodalk. Responde de forma amable, breve y persuasiva basándote ÚNICAMENTE en la siguiente información:\n\n"
    . $menuTexto . "\n"
    . "Información del restaurante:\n"
    . "- Los pedidos se realizan en la pestaña 'Menú Digital' indicando número de mesa y método de pago (Efectivo, Tarjeta o Transferencia Digital Nequi/PSE).\n"
    . "- Las reservas se realizan en la pestaña 'Reservas' indicando nombre, teléfono, fecha, hora y número de personas.\n"
    . "- Si preguntan por un plato que no está en la lista, indica amablemente que no lo manejamos y recomienda una opción del menú.\n"
    . "- No uses asteriscos (*) ni bloques de código en la respuesta.";

$payload = json_encode([
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        [
            'role' => 'system',
            'content' => $instruccionesSistema
        ],
        [
            'role' => 'user',
            'content' => $pregunta
        ]
    ],
    'temperature' => 0.4,
    'max_tokens' => 300
], JSON_UNESCAPED_UNICODE);

if (!function_exists('curl_init')) {
    responderJson(['error' => 'La extensión cURL de PHP no está habilitada en el servidor.'], 500);
}

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 20
]);

$respuestaRaw = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$errorCurl = curl_error($ch);
curl_close($ch);

if ($respuestaRaw === false || $respuestaRaw === '') {
    responderJson(['error' => 'Error de conexión con Groq: ' . ($errorCurl ?: 'respuesta vacía')], 502);
}

$data = json_decode((string) $respuestaRaw, true);
if ($httpCode < 200 || $httpCode >= 300 || !isset($data['choices'][0]['message']['content'])) {
    $msg = $data['error']['message'] ?? ('HTTP ' . $httpCode);
    responderJson(['error' => 'Error de API de Groq: ' . $msg], $httpCode >= 400 ? $httpCode : 502);
}

$textoLimpio = str_replace(['**', '*', '#', '`'], '', (string) $data['choices'][0]['message']['content']);
responderJson(['respuesta' => trim($textoLimpio)], 200);