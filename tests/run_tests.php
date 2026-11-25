<?php
declare(strict_types=1);

define('GASTRODALK_TESTING', true);

require_once __DIR__ . '/../src/ReglasNegocio.php';
require_once __DIR__ . '/../src/ServiciosGastrodalk.php';

use Gastrodalk\ReglasNegocio;
use Gastrodalk\ServiciosGastrodalk;

/**
 * Suite de Pruebas Unitarias y de Integración en Memoria (SQLite) para Gastrodalk.
 * Aplica el patrón AAA (Arrange, Act, Assert) sobre casos positivos, negativos y valores frontera.
 * Ejecución: php tests/run_tests.php
 */
class TestRunnerGastrodalk
{
    private int $pasadas = 0;
    private int $fallidas = 0;

    public function assertEquals($esperado, $actual, string $mensaje = ''): void
    {
        if ($esperado !== $actual) {
            throw new AssertionError(
                ($mensaje ? "{$mensaje} - " : '') .
                'Esperado: ' . var_export($esperado, true) . ', Actual: ' . var_export($actual, true)
            );
        }
    }

    public function assertTrue(bool $condicion, string $mensaje = ''): void
    {
        if (!$condicion) {
            throw new AssertionError($mensaje ?: 'Se esperaba true, se obtuvo false.');
        }
    }

    public function assertThrows(string $claseExcepcion, callable $fn, string $mensaje = ''): void
    {
        try {
            $fn();
        } catch (Throwable $e) {
            if ($e instanceof $claseExcepcion) {
                return;
            }
            throw new AssertionError(
                ($mensaje ? "{$mensaje} - " : '') .
                "Se esperaba excepción {$claseExcepcion}, pero se lanzó " . get_class($e) . ": {$e->getMessage()}"
            );
        }
        throw new AssertionError(($mensaje ? "{$mensaje} - " : '') . "No se lanzó la excepción esperada ({$claseExcepcion}).");
    }

    public function test(string $codigo, string $nombre, callable $fn): void
    {
        try {
            $fn($this);
            $this->pasadas++;
            echo "  [PASS] {$codigo} | {$nombre}
";
        } catch (Throwable $e) {
            $this->fallidas++;
            echo "  [FAIL] {$codigo} | {$nombre} -> {$e->getMessage()}
";
        }
    }

    public function crearBaseDatosEnMemoria(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("
            CREATE TABLE platos (
                id_plato INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                descripcion TEXT,
                precio REAL NOT NULL,
                categoria TEXT,
                disponible INTEGER DEFAULT 1
            );
            CREATE TABLE pedidos (
                id_pedido INTEGER PRIMARY KEY AUTOINCREMENT,
                numero_mesa INTEGER NOT NULL,
                metodo_pago TEXT NOT NULL,
                total REAL NOT NULL,
                fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE detalle_pedidos (
                id_detalle INTEGER PRIMARY KEY AUTOINCREMENT,
                id_pedido INTEGER NOT NULL,
                id_plato INTEGER NOT NULL,
                cantidad INTEGER NOT NULL,
                precio_unitario REAL NOT NULL
            );
            CREATE TABLE reservas (
                id_reserva INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre_contacto TEXT NOT NULL,
                telefono_contacto TEXT NOT NULL,
                fecha_reserva TEXT NOT NULL,
                hora_reserva TEXT NOT NULL,
                numero_personas INTEGER NOT NULL
            );
            CREATE TABLE mensajes_contacto (
                id_mensaje INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                email TEXT NOT NULL,
                asunto TEXT NOT NULL,
                mensaje TEXT NOT NULL
            );
            CREATE TABLE usuarios (
                id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL
            );
        ");

        $pdo->exec("
            INSERT INTO platos (nombre, descripcion, precio, categoria, disponible) VALUES
            ('Ajiaco', 'Sopa tradicional bogotana', 18000, 'Sopas', 1),
            ('Bandeja paisa', 'Plato típico antioqueño', 22000, 'Platos Fuertes', 1),
            ('Plato Agotado', 'No disponible hoy', 15000, 'Otros', 0);
        ");

        return $pdo;
    }

    public function resumen(): int
    {
        $total = $this->pasadas + $this->fallidas;
        $porcentaje = $total > 0 ? round(($this->pasadas / $total) * 100, 2) : 0;
        echo "
==================================================
";
        echo "RESUMEN DE PRUEBAS UNITARIAS GASTRODALK
";
        echo "Total: {$total} | Pasadas: {$this->pasadas} | Fallidas: {$this->fallidas} | Éxito: {$porcentaje}%
";
        echo "==================================================
";
        return $this->fallidas === 0 ? 0 : 1;
    }
}

$suite = new TestRunnerGastrodalk();

echo "Ejecutando Suite de Pruebas Unitarias - Gastrodalk (PHP " . PHP_VERSION . ")...

";
echo "--- 1. Reglas de Negocio: Pedidos ---
";

$suite->test('CP-01', 'Calcula correctamente un pedido válido con múltiples platos', function (TestRunnerGastrodalk $t) {
    $res = ReglasNegocio::validarYCalcularPedido(5, 'Transferencia', [1 => 2, 2 => 1, 3 => 0], [1 => 18000, 2 => 22000, 3 => 25000]);
    $t->assertEquals(5, $res['numero_mesa']);
    $t->assertEquals('Transferencia', $res['metodo_pago']);
    $t->assertEquals(58000.0, $res['total']);
    $t->assertEquals(2, count($res['items']));
});

$suite->test('CP-02', 'Rechaza pedido sin platos seleccionados (cantidades en cero)', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarYCalcularPedido(1, 'Efectivo', [1 => 0, 2 => 0], [1 => 18000, 2 => 22000]);
    });
});

$suite->test('CP-03', 'Rechaza número de mesa fuera de rango (0 o 26)', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarYCalcularPedido(0, 'Efectivo', [1 => 1], [1 => 18000]);
    });
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarYCalcularPedido(26, 'Efectivo', [1 => 1], [1 => 18000]);
    });
});

$suite->test('CP-04', 'Rechaza cantidad por plato mayor a 10 o negativa', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarYCalcularPedido(2, 'Tarjeta', [1 => 11], [1 => 18000]);
    });
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarYCalcularPedido(2, 'Tarjeta', [1 => -1], [1 => 18000]);
    });
});

$suite->test('CP-05', 'Rechaza método de pago no permitido (ej. Bitcoin)', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarYCalcularPedido(3, 'Bitcoin', [1 => 1], [1 => 18000]);
    });
});

$suite->test('CP-06', 'Acepta valores frontera válidos en pedido (Mesa 1 y 25, Cantidad 1 y 10)', function (TestRunnerGastrodalk $t) {
    $resMin = ReglasNegocio::validarYCalcularPedido(1, 'Efectivo', [1 => 1], [1 => 18000]);
    $resMax = ReglasNegocio::validarYCalcularPedido(25, 'Tarjeta', [1 => 10], [1 => 18000]);
    $t->assertEquals(18000.0, $resMin['total']);
    $t->assertEquals(180000.0, $resMax['total']);
});

$suite->test('CP-07', 'Rechaza cantidad no numérica en plato (ej. "abc")', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarYCalcularPedido(4, 'Efectivo', [1 => 'abc'], [1 => 18000]);
    });
});

echo "
--- 2. Reglas de Negocio: Reservas ---
";

$suite->test('CP-08', 'Valida una reserva correcta dentro de horario y cupo', function (TestRunnerGastrodalk $t) {
    $res = ReglasNegocio::validarReserva('Laura Martínez', '310 456 7890', '2026-11-25', '13:30', 4, '2026-11-19');
    $t->assertEquals('Laura Martínez', $res['nombre_contacto']);
    $t->assertEquals(4, $res['numero_personas']);
});

$suite->test('CP-09', 'Rechaza reserva en fecha pasada', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarReserva('Carlos Pérez', '3001234567', '2026-11-18', '13:00', 2, '2026-11-19');
    });
});

$suite->test('CP-10', 'Rechaza reserva fuera del horario de atención (12:00 - 22:00)', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarReserva('Carlos Pérez', '3001234567', '2026-11-20', '09:30', 2, '2026-11-19');
    });
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarReserva('Carlos Pérez', '3001234567', '2026-11-20', '23:15', 2, '2026-11-19');
    });
});

$suite->test('CP-11', 'Rechaza número de personas mayor a 20 o menor a 1', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarReserva('Carlos Pérez', '3001234567', '2026-11-20', '14:00', 25, '2026-11-19');
    });
});

$suite->test('CP-12', 'Acepta valores frontera de personas (1 y 20) y horario límite (12:00 y 22:00)', function (TestRunnerGastrodalk $t) {
    $r1 = ReglasNegocio::validarReserva('Ana Torres', '3112223344', '2026-11-20', '12:00', 1, '2026-11-19');
    $r2 = ReglasNegocio::validarReserva('Grupo Empresarial', '3112223344', '2026-11-20', '22:00', 20, '2026-11-19');
    $t->assertEquals(1, $r1['numero_personas']);
    $t->assertEquals(20, $r2['numero_personas']);
});

echo "
--- 3. Reglas de Negocio: Contacto, Usuarios e IA ---
";

$suite->test('CP-13', 'Valida formulario de contacto correcto y rechaza correo inválido o mensaje corto', function (TestRunnerGastrodalk $t) {
    $ok = ReglasNegocio::validarContacto('Andrés López', 'andres@correo.com', 'Sugerencia', 'Excelente atención y comida típica.');
    $t->assertEquals('andres@correo.com', $ok['email']);

    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarContacto('Andrés López', 'correo-invalido', 'Sugerencia', 'Mensaje suficientemente largo.');
    });
});

$suite->test('CP-14', 'Valida registro de usuario con hash BCRYPT y política de contraseña segura', function (TestRunnerGastrodalk $t) {
    $usr = ReglasNegocio::validarRegistroUsuario('Luna Rivera', 'LUNA@COREDUCACION.EDU.CO', 'Gastro2026');
    $t->assertEquals('luna@coreducacion.edu.co', $usr['email']);
    $t->assertTrue(password_verify('Gastro2026', $usr['password_hash']));

    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarRegistroUsuario('Luna Rivera', 'luna@test.com', '12345678');
    });
});

$suite->test('CP-15', 'Rechaza registro de usuario con nombre compuesto solo por espacios en blanco', function (TestRunnerGastrodalk $t) {
    $t->assertThrows(InvalidArgumentException::class, function () {
        ReglasNegocio::validarRegistroUsuario('   ', 'usuario@test.com', 'Clave1234');
    });
});

$suite->test('CP-16', 'Limpia asteriscos Markdown de las respuestas de Damian IA', function (TestRunnerGastrodalk $t) {
    $sucio = "Te recomiendo nuestro **Ajiaco Santafereño** por \$18.000 o la *Bandeja Paisa*.";
    $limpio = ReglasNegocio::limpiarRespuestaIA($sucio);
    $t->assertEquals("Te recomiendo nuestro Ajiaco Santafereño por \$18.000 o la Bandeja Paisa.", $limpio);
});

echo "
--- 4. Pruebas de Integración Transaccional con Base de Datos (PDO SQLite en Memoria) ---
";

$suite->test('CP-17', 'Persiste pedido y sus detalles atómicamente en base de datos', function (TestRunnerGastrodalk $t) {
    $pdo = $t->crearBaseDatosEnMemoria();
    $servicio = new ServiciosGastrodalk($pdo);

    $res = $servicio->registrarPedido(4, 'Efectivo', [1 => 2, 2 => 1], [1 => 18000, 2 => 22000]);
    $t->assertEquals(1, $res['id_pedido']);
    $t->assertEquals(58000.0, $res['total']);

    $t->assertEquals(1, (int) $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn());
    $t->assertEquals(2, (int) $pdo->query("SELECT COUNT(*) FROM detalle_pedidos")->fetchColumn());
});

$suite->test('CP-18', 'Retorna null de forma segura al consultar un ID de pedido inexistente', function (TestRunnerGastrodalk $t) {
    $pdo = $t->crearBaseDatosEnMemoria();
    $servicio = new ServiciosGastrodalk($pdo);

    $t->assertEquals(null, $servicio->obtenerPedidoPorId(9999));
});

$suite->test('CP-19', 'Persiste reserva y bloquea sobrecupo en misma fecha y hora (máx. 10 mesas)', function (TestRunnerGastrodalk $t) {
    $pdo = $t->crearBaseDatosEnMemoria();
    $servicio = new ServiciosGastrodalk($pdo);

    for ($i = 1; $i <= 10; $i++) {
        $servicio->registrarReserva("Cliente {$i}", "300000000{$i}", '2026-12-01', '19:00', 2, '2026-11-19');
    }

    $t->assertThrows(RuntimeException::class, function () use ($servicio) {
        $servicio->registrarReserva('Cliente 11', '3000000011', '2026-12-01', '19:00', 2, '2026-11-19');
    });
});

$suite->test('CP-20', 'Persiste mensaje de contacto y evita correos duplicados en usuarios', function (TestRunnerGastrodalk $t) {
    $pdo = $t->crearBaseDatosEnMemoria();
    $servicio = new ServiciosGastrodalk($pdo);

    $msg = $servicio->registrarContacto('Laura Amaya', 'laura@coreducacion.edu.co', 'Evento', 'Quisiera cotizar un almuerzo empresarial.');
    $t->assertEquals(1, $msg['id_mensaje']);

    $servicio->registrarUsuario('Laura Amaya', 'laura@coreducacion.edu.co', 'ClaveSegura1');
    $t->assertThrows(RuntimeException::class, function () use ($servicio) {
        $servicio->registrarUsuario('Otra Laura', 'laura@coreducacion.edu.co', 'ClaveSegura2');
    });
});

$suite->test('CP-21', 'Construye contexto de menú IA excluyendo platos no disponibles', function (TestRunnerGastrodalk $t) {
    $pdo = $t->crearBaseDatosEnMemoria();
    $servicio = new ServiciosGastrodalk($pdo);

    $contexto = $servicio->construirContextoMenuIA();
    $t->assertTrue(str_contains($contexto, 'Ajiaco'));
    $t->assertTrue(str_contains($contexto, 'Bandeja paisa'));
    $t->assertTrue(!str_contains($contexto, 'Plato Agotado'));
});

exit($suite->resumen());
