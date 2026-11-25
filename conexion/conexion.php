<?php
declare(strict_types=1);

/**
 * Lee una variable de entorno desde todas las fuentes posibles en contenedores Linux/PHP.
 */
function leerEntornoSeguro(string $nombre): string
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

    // Respaldo para contenedores Linux donde PHP-FPM oculta variables a getenv()
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

    // Respaldo .env local
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

function obtenerConexion(): PDO
{
    static $instancia = null;

    if ($instancia instanceof PDO) {
        return $instancia;
    }

    $databaseUrl = leerEntornoSeguro('DATABASE_URL');

    if ($databaseUrl !== '') {
        $partes   = parse_url($databaseUrl);
        $host     = $partes['host'] ?? '127.0.0.1';
        $port     = (string) ($partes['port'] ?? '3306');
        $dbname   = ltrim($partes['path'] ?? '', '/');
        $user     = isset($partes['user']) ? urldecode($partes['user']) : 'root';
        $password = isset($partes['pass']) ? urldecode($partes['pass']) : '';
    } else {
        $host     = leerEntornoSeguro('DB_HOST') ?: '127.0.0.1';
        $port     = leerEntornoSeguro('DB_PORT') ?: '3307';
        $dbname   = leerEntornoSeguro('DB_NAME') ?: 'gastrodalk_db';
        $user     = leerEntornoSeguro('DB_USER') ?: 'root';
        $passEnv  = leerEntornoSeguro('DB_PASS');
        $password = $passEnv !== '' ? $passEnv : '12345678';
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,
    ];

    $instancia = new PDO($dsn, $user, $password, $opciones);
    return $instancia;
}

$conexion = null;
$errorConexion = null;

if (!defined('GASTRODALK_TESTING')) {
    try {
        $conexion = obtenerConexion();
    } catch (PDOException $e) {
        $errorConexion = $e->getMessage();
    }
}