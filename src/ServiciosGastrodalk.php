<?php
declare(strict_types=1);

namespace Gastrodalk;

use PDO;
use RuntimeException;
use Throwable;

require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/ReglasNegocio.php';

/**
 * Capa de servicios transaccionales sobre PDO con Inyección de Dependencias (?PDO $db = null).
 * En producción utiliza MySQL vía obtenerConexion(); en pruebas unitarias recibe sqlite::memory:.
 */
class ServiciosGastrodalk
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? obtenerConexion();
    }

    public function obtenerColumnasTabla(string $tabla): array
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla)) {
            throw new RuntimeException('Nombre de tabla inválido.');
        }

        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $stmt = $this->db->query("PRAGMA table_info({$tabla})");
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            return array_column($rows, 'name');
        }

        $stmt = $this->db->query("DESCRIBE {$tabla}");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    public function registrarPedido($numeroMesa, string $metodoPago, array $cantidades, array $precios): array
    {
        $pedidoValidado = ReglasNegocio::validarYCalcularPedido($numeroMesa, $metodoPago, $cantidades, $precios);

        $colsPedidos = $this->obtenerColumnasTabla('pedidos');
        $colsDetalle = $this->obtenerColumnasTabla('detalle_pedidos');

        $this->db->beginTransaction();
        try {
            $datosPedido = [];
            if (in_array('numero_mesa', $colsPedidos, true)) $datosPedido['numero_mesa'] = $pedidoValidado['numero_mesa'];
            if (in_array('mesa', $colsPedidos, true))        $datosPedido['mesa']        = $pedidoValidado['numero_mesa'];
            if (in_array('metodo_pago', $colsPedidos, true)) $datosPedido['metodo_pago'] = $pedidoValidado['metodo_pago'];
            if (in_array('total', $colsPedidos, true))       $datosPedido['total']       = $pedidoValidado['total'];

            $camposP = implode(', ', array_keys($datosPedido));
            $paramsP = ':' . implode(', :', array_keys($datosPedido));
            $stmtPedido = $this->db->prepare("INSERT INTO pedidos ({$camposP}) VALUES ({$paramsP})");
            $stmtPedido->execute($datosPedido);
            $idPedido = (int) $this->db->lastInsertId();

            foreach ($pedidoValidado['items'] as $item) {
                $datosDet = [];
                if (in_array('id_pedido', $colsDetalle, true))       $datosDet['id_pedido']       = $idPedido;
                if (in_array('id_plato', $colsDetalle, true))        $datosDet['id_plato']        = $item['id_plato'];
                if (in_array('cantidad', $colsDetalle, true))        $datosDet['cantidad']        = $item['cantidad'];
                if (in_array('precio_unitario', $colsDetalle, true)) $datosDet['precio_unitario'] = $item['precio_unitario'];
                if (in_array('precio', $colsDetalle, true))          $datosDet['precio']          = $item['precio_unitario'];
                if (in_array('subtotal', $colsDetalle, true))        $datosDet['subtotal']        = $item['subtotal'];

                $camposD = implode(', ', array_keys($datosDet));
                $paramsD = ':' . implode(', :', array_keys($datosDet));
                $stmtDetalle = $this->db->prepare("INSERT INTO detalle_pedidos ({$camposD}) VALUES ({$paramsD})");
                $stmtDetalle->execute($datosDet);
            }

            $this->db->commit();

            return array_merge(['id_pedido' => $idPedido], $pedidoValidado);
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerPedidoPorId(int $idPedido): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pedidos WHERE id_pedido = :id");
        $stmt->execute([':id' => $idPedido]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ?: null;
    }

    public function registrarReserva(
        string $nombre,
        string $telefono,
        string $fecha,
        string $hora,
        $personas,
        ?string $fechaReferencia = null
    ): array {
        $reservaValidada = ReglasNegocio::validarReserva($nombre, $telefono, $fecha, $hora, $personas, $fechaReferencia);

        $stmtCheck = $this->db->prepare(
            "SELECT COUNT(*) FROM reservas WHERE fecha_reserva = :fecha AND hora_reserva = :hora"
        );
        $stmtCheck->execute([
            ':fecha' => $reservaValidada['fecha_reserva'],
            ':hora'  => $reservaValidada['hora_reserva'],
        ]);
        if ((int) $stmtCheck->fetchColumn() >= 10) {
            throw new RuntimeException('No hay mesas disponibles para la fecha y hora seleccionadas.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO reservas (nombre_contacto, telefono_contacto, fecha_reserva, hora_reserva, numero_personas)
             VALUES (:nombre, :telefono, :fecha, :hora, :personas)"
        );
        $stmt->execute([
            ':nombre'   => $reservaValidada['nombre_contacto'],
            ':telefono' => $reservaValidada['telefono_contacto'],
            ':fecha'    => $reservaValidada['fecha_reserva'],
            ':hora'     => $reservaValidada['hora_reserva'],
            ':personas' => $reservaValidada['numero_personas'],
        ]);

        return array_merge(['id_reserva' => (int) $this->db->lastInsertId()], $reservaValidada);
    }

    public function registrarContacto(string $nombre, string $email, string $asunto, string $mensaje): array
    {
        $contactoValidado = ReglasNegocio::validarContacto($nombre, $email, $asunto, $mensaje);
        $colsContacto = $this->obtenerColumnasTabla('mensajes_contacto');

        $datos = [];
        if (in_array('nombre', $colsContacto, true))  $datos['nombre']  = $contactoValidado['nombre'];
        if (in_array('email', $colsContacto, true))   $datos['email']   = $contactoValidado['email'];
        if (in_array('asunto', $colsContacto, true))  $datos['asunto']  = $contactoValidado['asunto'];
        if (in_array('mensaje', $colsContacto, true)) $datos['mensaje'] = $contactoValidado['mensaje'];

        $campos = implode(', ', array_keys($datos));
        $params = ':' . implode(', :', array_keys($datos));

        $stmt = $this->db->prepare("INSERT INTO mensajes_contacto ({$campos}) VALUES ({$params})");
        $stmt->execute($datos);

        return array_merge(['id_mensaje' => (int) $this->db->lastInsertId()], $contactoValidado);
    }

    public function registrarUsuario(string $nombre, string $email, string $password): array
    {
        $usuarioValidado = ReglasNegocio::validarRegistroUsuario($nombre, $email, $password);

        $stmtCheck = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
        $stmtCheck->execute([':email' => $usuarioValidado['email']]);
        if ($stmtCheck->fetch()) {
            throw new RuntimeException('Este correo electrónico ya se encuentra registrado.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (nombre, email, password_hash) VALUES (:nombre, :email, :pass)"
        );
        $stmt->execute([
            ':nombre' => $usuarioValidado['nombre'],
            ':email'  => $usuarioValidado['email'],
            ':pass'   => $usuarioValidado['password_hash'],
        ]);

        return [
            'id_usuario' => (int) $this->db->lastInsertId(),
            'nombre'     => $usuarioValidado['nombre'],
            'email'      => $usuarioValidado['email'],
        ];
    }

    public function construirContextoMenuIA(): string
    {
        $stmt = $this->db->query("SELECT nombre, descripcion, precio, categoria FROM platos WHERE disponible = 1");
        $platos = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        if (empty($platos)) {
            return "Menú disponible hoy en Gastrodalk:
- No hay platos activos registrados en este momento.
";
        }

        $menuTexto = "Menú disponible hoy en Gastrodalk:
";
        foreach ($platos as $fila) {
            $precioFormateado = number_format((float) $fila['precio'], 0, ',', '.');
            $menuTexto .= "- {$fila['nombre']} ({$fila['categoria']}): {$fila['descripcion']} - Precio: \${$precioFormateado}
";
        }
        return $menuTexto;
    }
}
