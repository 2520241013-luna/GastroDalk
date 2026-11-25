<?php
declare(strict_types=1);

/**
 * Establece y retorna una conexión PDO singleton hacia la base de datos MySQL de Gastrodalk.
 * Soporta DATABASE_URL (EthioDeploy/Nube) o variables individuales / valores locales.
 *
 * @return PDO
 * @throws PDOException Si falla la conexión con el motor de base de datos.
 */
function obtenerConexion(): PDO
{
    static $instancia = null;

    if ($instancia instanceof PDO) {
        return $instancia;
    }

    $databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? '');

    if ($databaseUrl !== '') {
        $partes   = parse_url($databaseUrl);
        $host     = $partes['host'] ?? 'localhost';
        $port     = (string) ($partes['port'] ?? '3306');
        $dbname   = ltrim($partes['path'] ?? '', '/');
        $user     = isset($partes['user']) ? urldecode($partes['user']) : 'root';
        $password = isset($partes['pass']) ? urldecode($partes['pass']) : '';
    } else {
        $host     = getenv('DB_HOST') ?: 'localhost';
        $port     = getenv('DB_PORT') ?: '3307';
        $dbname   = getenv('DB_NAME') ?: 'gastrodalk_db';
        $user     = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '12345678';
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