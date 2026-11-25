<?php
declare(strict_types=1);

namespace Gastrodalk;

use InvalidArgumentException;

/**
 * Reglas de negocio centralizadas de la plataforma Gastrodalk.
 * Desacopladas de HTTP y persistencia para garantizar pruebas unitarias deterministas.
 */
class ReglasNegocio
{
    public const MESA_MIN = 1;
    public const MESA_MAX = 25;

    public const CANTIDAD_PLATO_MIN = 1;
    public const CANTIDAD_PLATO_MAX = 10;

    public const PERSONAS_MIN = 1;
    public const PERSONAS_MAX = 20;

    public const HORA_APERTURA = '12:00';
    public const HORA_CIERRE   = '22:00';

    public const METODOS_PAGO_VALIDOS = ['Efectivo', 'Tarjeta', 'Transferencia'];

    /**
     * Valida y calcula el resumen de un pedido de restaurante.
     *
     * @param int|string $numeroMesa
     * @param string $metodoPago
     * @param array<int|string, int|string> $cantidades
     * @param array<int|string, float|int|string> $precios
     * @return array{numero_mesa: int, metodo_pago: string, total: float, items: array<int, array{id_plato: int, cantidad: int, precio_unitario: float, subtotal: float}>}
     * @throws InvalidArgumentException
     */
    public static function validarYCalcularPedido($numeroMesa, string $metodoPago, array $cantidades, array $precios): array
    {
        $mesa = filter_var($numeroMesa, FILTER_VALIDATE_INT);
        if ($mesa === false || $mesa < self::MESA_MIN || $mesa > self::MESA_MAX) {
            throw new InvalidArgumentException(
                sprintf('El número de mesa debe estar entre %d y %d.', self::MESA_MIN, self::MESA_MAX)
            );
        }

        $metodoLimpio = trim($metodoPago);
        if (!in_array($metodoLimpio, self::METODOS_PAGO_VALIDOS, true)) {
            throw new InvalidArgumentException(
                'Método de pago no permitido. Use: ' . implode(', ', self::METODOS_PAGO_VALIDOS) . '.'
            );
        }

        $itemsValidos = [];
        $total = 0.0;

        foreach ($cantidades as $idPlatoRaw => $cantidadRaw) {
            if ($cantidadRaw === '' || $cantidadRaw === null || $cantidadRaw === 0 || $cantidadRaw === '0') {
                continue;
            }

            $idPlato = filter_var($idPlatoRaw, FILTER_VALIDATE_INT);
            $cantidad = filter_var($cantidadRaw, FILTER_VALIDATE_INT);

            if ($idPlato === false || $idPlato <= 0) {
                throw new InvalidArgumentException('Identificador de plato inválido.');
            }

            if ($cantidad === false || $cantidad < self::CANTIDAD_PLATO_MIN || $cantidad > self::CANTIDAD_PLATO_MAX) {
                throw new InvalidArgumentException(
                    sprintf(
                        'La cantidad del plato #%d debe ser un entero entre %d y %d unidades.',
                        $idPlato,
                        self::CANTIDAD_PLATO_MIN,
                        self::CANTIDAD_PLATO_MAX
                    )
                );
            }

            if (!isset($precios[$idPlatoRaw]) || !is_numeric($precios[$idPlatoRaw])) {
                throw new InvalidArgumentException(sprintf('El plato #%d no tiene un precio válido configurado.', $idPlato));
            }

            $precioUnitario = (float) $precios[$idPlatoRaw];
            if ($precioUnitario <= 0) {
                throw new InvalidArgumentException(sprintf('El precio del plato #%d debe ser mayor a cero.', $idPlato));
            }

            $subtotal = round($cantidad * $precioUnitario, 2);
            $total = round($total + $subtotal, 2);

            $itemsValidos[] = [
                'id_plato'        => $idPlato,
                'cantidad'        => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal'        => $subtotal,
            ];
        }

        if (empty($itemsValidos)) {
            throw new InvalidArgumentException('Debe seleccionar al menos un plato para realizar el pedido.');
        }

        return [
            'numero_mesa' => $mesa,
            'metodo_pago' => $metodoLimpio,
            'total'       => $total,
            'items'       => $itemsValidos,
        ];
    }

    /**
     * Valida los datos y reglas operativas de una reserva de mesa.
     */
    public static function validarReserva(
        string $nombre,
        string $telefono,
        string $fecha,
        string $hora,
        $personas,
        ?string $fechaReferencia = null
    ): array {
        $nombreLimpio = trim($nombre);
        if (mb_strlen($nombreLimpio) < 3) {
            throw new InvalidArgumentException('El nombre de contacto debe tener al menos 3 caracteres.');
        }

        $telefonoLimpio = trim($telefono);
        $soloDigitos = preg_replace('/\D+/', '', $telefonoLimpio);
        if (strlen($soloDigitos) < 7 || strlen($soloDigitos) > 15) {
            throw new InvalidArgumentException('El teléfono debe contener entre 7 y 15 dígitos válidos.');
        }

        $fechaLimpia = trim($fecha);
        $dtFecha = \DateTimeImmutable::createFromFormat('Y-m-d', $fechaLimpia);
        if (!$dtFecha || $dtFecha->format('Y-m-d') !== $fechaLimpia) {
            throw new InvalidArgumentException('La fecha de reserva debe tener formato válido (AAAA-MM-DD).');
        }

        $hoyStr = $fechaReferencia ?? date('Y-m-d');
        if ($fechaLimpia < $hoyStr) {
            throw new InvalidArgumentException('No se permiten reservas en fechas anteriores al día actual.');
        }

        $horaLimpia = substr(trim($hora), 0, 5);
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $horaLimpia)) {
            throw new InvalidArgumentException('La hora de reserva debe tener formato válido (HH:MM).');
        }

        if ($horaLimpia < self::HORA_APERTURA || $horaLimpia > self::HORA_CIERRE) {
            throw new InvalidArgumentException(
                sprintf('El horario de atención para reservas es de %s a %s.', self::HORA_APERTURA, self::HORA_CIERRE)
            );
        }

        $numPersonas = filter_var($personas, FILTER_VALIDATE_INT);
        if ($numPersonas === false || $numPersonas < self::PERSONAS_MIN || $numPersonas > self::PERSONAS_MAX) {
            throw new InvalidArgumentException(
                sprintf('El número de personas debe estar entre %d y %d.', self::PERSONAS_MIN, self::PERSONAS_MAX)
            );
        }

        return [
            'nombre_contacto'   => $nombreLimpio,
            'telefono_contacto' => $telefonoLimpio,
            'fecha_reserva'     => $fechaLimpia,
            'hora_reserva'      => $horaLimpia,
            'numero_personas'   => $numPersonas,
        ];
    }

    /**
     * Valida y sanea un mensaje enviado desde el formulario de Contáctenos.
     */
    public static function validarContacto(string $nombre, string $email, string $asunto, string $mensaje): array
    {
        $nombreLimpio = trim($nombre);
        if (mb_strlen($nombreLimpio) < 3) {
            throw new InvalidArgumentException('El nombre debe tener al menos 3 caracteres.');
        }

        $emailLimpio = trim($email);
        if (!filter_var($emailLimpio, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo electrónico ingresado no tiene un formato válido.');
        }

        $asuntoLimpio = trim($asunto);
        if (mb_strlen($asuntoLimpio) < 3 || mb_strlen($asuntoLimpio) > 120) {
            throw new InvalidArgumentException('El asunto debe tener entre 3 y 120 caracteres.');
        }

        $mensajeLimpio = trim($mensaje);
        if (mb_strlen($mensajeLimpio) < 10) {
            throw new InvalidArgumentException('El mensaje debe contener al menos 10 caracteres.');
        }

        return [
            'nombre'  => $nombreLimpio,
            'email'   => $emailLimpio,
            'asunto'  => $asuntoLimpio,
            'mensaje' => $mensajeLimpio,
        ];
    }

    /**
     * Valida el registro de un nuevo usuario cumpliendo políticas de seguridad.
     */
    public static function validarRegistroUsuario(string $nombre, string $email, string $password): array
    {
        $nombreLimpio = trim($nombre);
        if (mb_strlen($nombreLimpio) < 3) {
            throw new InvalidArgumentException('El nombre del usuario debe tener al menos 3 caracteres.');
        }

        $emailLimpio = strtolower(trim($email));
        if (!filter_var($emailLimpio, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El formato de correo electrónico no es válido.');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            throw new InvalidArgumentException('La contraseña debe incluir al menos una letra y un número.');
        }

        return [
            'nombre'        => $nombreLimpio,
            'email'         => $emailLimpio,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ];
    }

    /**
     * Limpia formato Markdown residual (** o *) y espacios redundantes en respuestas de IA.
     */
    public static function limpiarRespuestaIA(string $texto): string
    {
        $sinAsteriscos = str_replace(['**', '*'], '', $texto);
        $normalizado = preg_replace("/
{3,}/", "

", trim($sinAsteriscos));
        return $normalizado ?? '';
    }
}
