<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../src/ServiciosGastrodalk.php';

use Gastrodalk\ServiciosGastrodalk;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../interfaces/menu.php');
    exit;
}

try {
    $servicio = new ServiciosGastrodalk($conexion);
    $resultado = $servicio->registrarPedido(
        $_POST['numero_mesa'] ?? 1,
        (string) ($_POST['metodo_pago'] ?? 'Efectivo'),
        is_array($_POST['cantidades'] ?? null) ? $_POST['cantidades'] : [],
        is_array($_POST['precios'] ?? null) ? $_POST['precios'] : []
    );

    $id    = $resultado['id_pedido'];
    $mesa  = $resultado['numero_mesa'];
    $total = $resultado['total'];

    header("Location: ../interfaces/menu.php?estado=ok&id={$id}&mesa={$mesa}&total={$total}");
    exit;
} catch (InvalidArgumentException $e) {
    if (str_contains($e->getMessage(), 'al menos un plato')) {
        header('Location: ../interfaces/menu.php?estado=vacio');
        exit;
    }
    $msg = urlencode($e->getMessage());
    header("Location: ../interfaces/menu.php?estado=error&msg={$msg}");
    exit;
} catch (Throwable $e) {
    $msg = urlencode('Error al procesar el pedido: ' . $e->getMessage());
    header("Location: ../interfaces/menu.php?estado=error&msg={$msg}");
    exit;
}
