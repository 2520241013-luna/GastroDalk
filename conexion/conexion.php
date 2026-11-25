<?php
declare(strict_types=1);

/**
 * Establece y retorna una conexión PDO singleton hacia la base de datos MySQL de Gastrodalk.
 * Soporta DATABASE_URL (EthioDeploy/Nube) o variables individuales / valores locales.
 */
function obtenerConexion(): PDO
{
    static $instancia = null;

    if ($instancia instanceof PDO) {
        return $instancia;
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

    // Leer DATABASE_URL desde cualquier fuente de entorno del servidor
    $databaseUrl = getenv('DATABASE_URL');
    if (!$databaseUrl && isset($_ENV['DATABASE_URL'])) {
        $databaseUrl = $_ENV['DATABASE_URL'];
    }
    if (!$databaseUrl && isset($_SERVER['DATABASE_URL'])) {
        $databaseUrl = $_SERVER['DATABASE_URL'];
    }

    if (!empty($databaseUrl)) {
        $partes   = parse_url((string) $databaseUrl);
        $host     = $partes['host'] ?? 'localhost';
        $port     = (string) ($partes['port'] ?? '3306');
        $dbname   = ltrim($partes['path'] ?? '', '/');
        $user     = isset($partes['user']) ? urldecode($partes['user']) : 'root';
        $password = isset($partes['pass']) ? urldecode($partes['pass']) : '';
    } else {
        $host     = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? 'localhost'));
        $port     = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? ($_SERVER['DB_PORT'] ?? '3307'));
        $dbname   = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? ($_SERVER['DB_NAME'] ?? 'gastrodalk_db'));
        $user     = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? ($_SERVER['DB_USER'] ?? 'root'));
        $passEnv  = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($_ENV['DB_PASS'] ?? ($_SERVER['DB_PASS'] ?? null));
        $password = $passEnv !== null ? (string) $passEnv : '12345678';
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $instancia = new PDO($dsn, $user, $password, $opciones);
    return $instancia;
}

if (!defined('GASTRODALK_TESTING')) {
    try {
        $conexion = obtenerConexion();
    } catch (PDOException $e) {
        die("Error al conectar con la base de datos: " . $e->getMessage());
    }
}