<?php
// Despliegue v2 - Damian IA activo
declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function leerVariableEntorno(string $nombre): string
{
    $valor = getenv($nombre);
    if ($valor !== false && $valor !== '') {
        return trim((string) $valor);
    }
    if (!empty($_ENV[$nombre])) {
        return trim((string) $_ENV[$nombre]);
    }
    if (!empty($_SERVER[$nombre])) {
        return trim((string) $_SERVER[$nombre]);
    }
    if (is_readable('/proc/self/environ')) {
        $raw = @file_get_contents('/proc/self/environ');
        if (is_string($raw) && $raw !== '') {
            foreach (explode("\0", $raw) as $par) {
                if (str_starts_with($par, $nombre . '=')) {
                    return trim(substr($par, strlen($nombre) + 1));
                }
            }
        }
    }
    $envPath = __DIR__ . '/../.env';
    if (is_readable($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $linea, 2);
            if (trim($k) === $nombre) {
                return trim($v, " \t\n\r\0\x0B\"'");
            }
        }
    }
    return '';
}

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
    echo json_encode(['respuesta' => 'Tu consulta es demasiado larga. Por favor resúmela en menos de 600 caracteres.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$apiKey = leerVariableEntorno('GEMINI_API_KEY');
if ($apiKey === '') {
    echo json_encode(['respuesta' => 'Error: No se encontró GEMINI_API_KEY en Env Vars de EthioDeploy.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$contextoMenu = "Platos de Gastrodalk: Ajiaco Santafereño ($18.000), Bandeja Paisa ($25.000), Arroz con Pollo ($16.000), Caldo de Costilla ($12.000), Carne Asada ($22.000), Mojarra Frita ($24.000).";
try {
    $dbUrl = leerVariableEntorno('DATABASE_URL');
    if ($dbUrl !== '') {
        $p = parse_url($dbUrl);
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $p['host'] ?? '127.0.0.1',
            $p['port'] ?? '3306',
            ltrim($p['path'] ?? '', '/')
        );
        $pdo = new PDO($dsn, urldecode($p['user'] ?? ''), urldecode($p['pass'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $rows = $pdo->query("SELECT nombre, descripcion, precio FROM platos WHERE disponible = 1")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $lineas = array_map(fn($r) => "- {$r['nombre']} (\${$r['precio']}): {$r['descripcion']}", $rows);
            $contextoMenu = implode("\n", $lineas);
        }
    }
} catch (Throwable $e) {
    // Mantiene el menú de respaldo si falla la consulta
}

$promptSistema = "Eres Damian, el asistente gastronómico virtual oficial del restaurante Gastrodalk. "
    . "Responde de forma amable, clara, breve y en español (sin usar asteriscos ni formato Markdown). "
    . "Basa tus recomendaciones únicamente en este menú:\n\n"
    . $contextoMenu . "\n\n"
    . "Consulta del cliente: " . $pregunta;

$modelos = ['gemini-3.8-flash', 'gemini-3.8-flash'];
$payloadJson = json_encode([
    'contents' => [['parts' => [['text' => $promptSistema]]]],
    'generationConfig' => [
        'temperature'     => 0.7,
        'maxOutputTokens' => 1200,
        'thinkingConfig'  => [
            'thinkingBudget' => 0,
        ],
    ],
], JSON_UNESCAPED_UNICODE);

$respuestaFinal = null;
$ultimoError = 'Servicio temporalmente saturado.';
$codigoHttpFinal = 0;

foreach ($modelos as $modelo) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelo}:generateContent?key=" . urlencode($apiKey);
    $response = false;
    $httpCode = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => $payloadJson,
            CURLOPT_TIMEOUT        => 20,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\n",
                'content'       => $payloadJson,
                'timeout'       => 20,
                'ignore_errors' => true,
            ],
        ]);
        $response = @file_get_contents($url, false, $ctx);
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $httpCode = (int) $m[1];
        }
    }

    if ($response === false || $response === '') {
        continue;
    }

    $data = json_decode((string) $response, true);
    if ($httpCode < 400 && !empty($data['candidates'][0]['content']['parts'])) {
        $partesTexto = [];
        foreach ($data['candidates'][0]['content']['parts'] as $parte) {
            if (!empty($parte['thought'])) {
                continue;
            }
            if (isset($parte['text'])) {
                $partesTexto[] = (string) $parte['text'];
            }
        }
        $textoCandidato = trim(implode("\n", $partesTexto));
        if ($textoCandidato !== '') {
            $respuestaFinal = $textoCandidato;
            break;
        }
    }

    $codigoHttpFinal = $httpCode;
    $ultimoError = $data['error']['message'] ?? "HTTP {$httpCode}";

    if (in_array($httpCode, [429, 500, 503], true)) {
        sleep(1);
        continue;
    }
    break;
}

if ($respuestaFinal === null) {
    if (in_array($codigoHttpFinal, [0, 429, 500, 503], true)) {
        echo json_encode([
            'respuesta' => 'En este momento hay alta demanda en el servicio de IA. Por favor intenta de nuevo en unos segundos.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'respuesta' => "No se pudo procesar tu consulta ({$ultimoError})."
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$textoLimpio = str_replace(['**', '*', '#', '`'], '', $respuestaFinal);
echo json_encode(['respuesta' => trim($textoLimpio)], JSON_UNESCAPED_UNICODE);