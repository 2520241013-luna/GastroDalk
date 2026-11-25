<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../src/ServiciosGastrodalk.php';

use Gastrodalk\ServiciosGastrodalk;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../interfaces/contacto.php');
    exit;
}

try {
    $servicio = new ServiciosGastrodalk($conexion);
    $contacto = $servicio->registrarContacto(
        (string) ($_POST['nombre'] ?? ''),
        (string) ($_POST['email'] ?? ''),
        (string) ($_POST['asunto'] ?? ''),
        (string) ($_POST['mensaje'] ?? '')
    );

    $nombreUrl = urlencode($contacto['nombre']);
    header("Location: ../interfaces/contacto.php?estado=ok&nombre={$nombreUrl}");
    exit;
} catch (Throwable $e) {
    $msg = urlencode($e->getMessage());
    header("Location: ../interfaces/contacto.php?estado=error&msg={$msg}");
    exit;
}
