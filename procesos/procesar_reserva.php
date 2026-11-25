<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../src/ServiciosGastrodalk.php';

use Gastrodalk\ServiciosGastrodalk;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../interfaces/reservas.php');
    exit;
}

try {
    $servicio = new ServiciosGastrodalk($conexion);
    $reserva = $servicio->registrarReserva(
        (string) ($_POST['nombre_contacto'] ?? ''),
        (string) ($_POST['telefono_contacto'] ?? ''),
        (string) ($_POST['fecha_reserva'] ?? ''),
        (string) ($_POST['hora_reserva'] ?? ''),
        $_POST['numero_personas'] ?? 1
    );

    $nombreUrl = urlencode($reserva['nombre_contacto']);
    $fechaUrl  = urlencode($reserva['fecha_reserva']);
    $horaUrl   = urlencode($reserva['hora_reserva']);

    header("Location: ../interfaces/reservas.php?estado=ok&nombre={$nombreUrl}&fecha={$fechaUrl}&hora={$horaUrl}");
    exit;
} catch (Throwable $e) {
    $msg = urlencode($e->getMessage());
    header("Location: ../interfaces/reservas.php?estado=error&msg={$msg}");
    exit;
}
