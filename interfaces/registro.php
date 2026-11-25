<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion/conexion.php';
require_once __DIR__ . '/../src/ServiciosGastrodalk.php';

use Gastrodalk\ServiciosGastrodalk;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $servicio = new ServiciosGastrodalk($conexion);
        $usuario = $servicio->registrarUsuario(
            (string) ($_POST['nombre'] ?? ''),
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? '')
        );

        $nombreUrl = urlencode($usuario['nombre']);
        header("Location: registro.php?estado=ok&nombre={$nombreUrl}");
        exit;
    } catch (Throwable $e) {
        $msg = urlencode($e->getMessage());
        header("Location: registro.php?estado=error&msg={$msg}");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Gastrodalk</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <section class="reserva-bg-section">
        <div class="reserva-overlay" style="max-width: 620px;">
            <div class="section-header">
                <h2 style="color: #ffedd5;">Crear Cuenta</h2>
                <p style="color: #fed7aa;">Regístrate en Gastrodalk para gestionar tus pedidos y reservas</p>
            </div>

            <?php if (isset($_GET['estado'])): ?>
                <?php if ($_GET['estado'] === 'ok'): ?>
                    <div class="alerta-banner alerta-exito">
                        <span>✅ <strong>¡Bienvenido, <?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>!</strong> Tu cuenta fue creada correctamente.</span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php elseif ($_GET['estado'] === 'error'): ?>
                    <div class="alerta-banner alerta-error">
                        <span>❌ <?php echo htmlspecialchars($_GET['msg'] ?? ''); ?></span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="reserva-card">
                <form action="registro.php" method="POST">
                    <div class="contact-group">
                        <label for="reg_nombre">Nombre Completo *</label>
                        <input type="text" id="reg_nombre" name="nombre" placeholder="Ej: Ana María Torres" required>
                    </div>
                    <div class="contact-group">
                        <label for="reg_email">Correo Electrónico *</label>
                        <input type="email" id="reg_email" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="contact-group">
                        <label for="reg_pass">Contraseña * (mín. 8 caracteres, letras y números)</label>
                        <input type="password" id="reg_pass" name="password" placeholder="••••••••" minlength="8" required>
                    </div>
                    <div style="margin-top: 22px;">
                        <button type="submit" class="btn-confirm btn-reserva-full">Registrarme</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
