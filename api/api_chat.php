<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../src/ServiciosGastrodalk.php';

use Gastrodalk\ReglasNegocio;
use Gastrodalk\ServiciosGastrodalk;

try {
    $input = json_decode((string) file_get_contents('php://input'), true);
    $mensajeCliente = trim((string) ($input['mensaje'] ?? ''));

    if ($mensajeCliente === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Por favor escribe una pregunta.']);
        exit;
    }

    if (mb_strlen($mensajeCliente) > 500) {
        http_response_code(400);
        echo json_encode(['error' => 'La pregunta no puede superar los 500 caracteres.']);
        exit;
    }

    $servicio  = new ServiciosGastrodalk($conexion);
    $menuTexto = $servicio->construirContextoMenuIA();

   // Cargar variables desde el archivo .env en la raíz del proyecto
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }
        [$clave, $valor] = explode('=', $linea, 2);
        $_ENV[trim($clave)] = trim($valor, " \t\n\r\0\x0B\"'");
    }
}

$apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '');
    $url    = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent';

    $instruccionesSistema = "Eres Damian IA, el asistente virtual de recomendación gastronómica del restaurante Gastrodalk. "
        . "Responde de forma amable, breve y persuasiva en texto plano, SIN usar asteriscos (**), negritas ni formato Markdown, basándote ÚNICAMENTE en la siguiente información:

"
        . $menuTexto . "
"
        . "Información del restaurante:
"
        . "- Los pedidos se realizan en la pestaña Menú Digital indicando número de mesa (1 a 25) y método de pago (Efectivo, Tarjeta o Transferencia Digital Nequi/PSE).
"
        . "- Las reservas se realizan en la pestaña Reservas indicando nombre, teléfono, fecha, hora (12:00 a 22:00) y número de personas (1 a 20).
"
        . "- Si preguntan por un plato que no está en la lista, indica amablemente que no lo manejamos y recomienda una opción del menú.";

    $data = [
        'system_instruction' => [
            'parts' => [['text' => $instruccionesSistema]],
        ],
        'contents' => [
            [
                'role'  => 'user',
                'parts' => [['text' => $mensajeCliente]],
            ],
        ],
        'generationConfig' => [
            'temperature'     => 0.4,
            'maxOutputTokens' => 500,
            'thinkingConfig'  => [
                'thinkingBudget' => 0,
            ],
        ],
    ];

    $opciones = [
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json
"
                             . "x-goog-api-key: {$apiKey}
",
            'content'       => json_encode($data, JSON_UNESCAPED_UNICODE),
            'timeout'       => 30,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ];

    $contexto = stream_context_create($opciones);
    $response = @file_get_contents($url, false, $contexto);

    if ($response === false) {
        $err = error_get_last();
        $detalle = $err['message'] ?? 'Verifica tu conexión a internet.';
        http_response_code(502);
        echo json_encode(['error' => 'No se pudo conectar con Google API: ' . $detalle]);
        exit;
    }

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $textoLimpio = ReglasNegocio::limpiarRespuestaIA($result['candidates'][0]['content']['parts'][0]['text']);
        echo json_encode(['respuesta' => $textoLimpio], JSON_UNESCAPED_UNICODE);
    } elseif (isset($result['error']['message'])) {
        http_response_code(502);
        echo json_encode(['error' => 'Google API: ' . $result['error']['message']], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(502);
        echo json_encode(['error' => 'Respuesta inesperada: ' . substr($response, 0, 200)], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno PHP: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
