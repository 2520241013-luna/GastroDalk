<?php
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
    // Mantiene el menú por defecto si falla la consulta
}

$promptSistema = "Eres Damian, el asistente gastronómico virtual oficial del restaurante Gastrodalk. "
    . "Responde de forma amable, clara, breve y en español (sin usar asteriscos ni formato Markdown). "
    . "Basa tus recomendaciones únicamente en este menú:\n\n"
    . $contextoMenu . "\n\n"
    . "Consulta del cliente: " . $pregunta;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=" . urlencode($apiKey);
$payloadJson = json_encode([
    'contents' => [['parts' => [['text' => $promptSistema]]]],
    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 450],
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
            'timeout'       => 25,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $ctx);
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $httpCode = (int) $m[1];
    }
}

if ($response === false || $response === '') {
    echo json_encode(['respuesta' => 'El servidor no obtuvo respuesta de red desde Google Gemini.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode((string) $response, true);
if ($httpCode >= 400 || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $detalle = $data['error']['message'] ?? "HTTP {$httpCode}";
    echo json_encode(['respuesta' => "Error de Google Gemini: {$detalle}"], JSON_UNESCAPED_UNICODE);
    exit;
}

$texto = str_replace(['**', '*', '#', '`'], '', (string) $data['candidates'][0]['content']['parts'][0]['text']);
echo json_encode(['respuesta' => trim($texto)], JSON_UNESCAPED_UNICODE);