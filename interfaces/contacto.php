<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contáctenos - Gastrodalk</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body.contacto-page {
            min-height: 100vh;
            margin: 0;
            position: relative;
            background-image: url('../imagenes/contactenos.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        body.contacto-page::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(15, 15, 15, 0.22);
            z-index: 0;
            pointer-events: none;
        }

        .contact-bg-section,
        .contact-overlay {
            position: relative;
            z-index: 1;
            background: transparent !important;
            background-color: transparent !important;
        }

        .contact-bg-section {
            padding: 40px 20px;
        }

        .contact-overlay {
            max-width: 860px;
            margin: 0 auto;
        }

        .contact-form-card {
            background: rgba(255, 252, 247, 0.94);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
        }

        @media (max-width: 768px) {
            body.contacto-page {
                background-attachment: scroll;
            }
        }
    </style>
</head>
<body class="contacto-page">

    <?php include '../includes/navbar.php'; ?>

    <section id="contacto" class="contact-bg-section">
        <div class="contact-overlay">
            <div class="section-header">
                <h2 style="color: #ffffff; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.75);">Contáctenos</h2>
                <p style="color: #fff7ed; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.75);">Envíanos tus dudas, sugerencias o solicitudes de eventos</p>
            </div>

            <?php if (isset($_GET['estado'])): ?>
                <?php if ($_GET['estado'] === 'ok'): ?>
                    <div class="alerta-banner alerta-exito">
                        <span>✅ <strong>¡Gracias por contactarnos, <?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?>!</strong> Tu mensaje ha sido enviado con éxito.</span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php elseif ($_GET['estado'] === 'error'): ?>
                    <div class="alerta-banner alerta-error">
                        <span>❌ Error al enviar el mensaje: <?php echo htmlspecialchars($_GET['msg'] ?? ''); ?></span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="contact-form-card">
                <form action="../procesos/procesar_contacto.php" method="POST">
                    <div class="contact-grid-2">
                        <div class="contact-group">
                            <label for="nombre">Nombre Completo *</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Ej: Carlos Gómez" required>
                        </div>
                        <div class="contact-group">
                            <label for="email">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" required>
                        </div>
                    </div>

                    <div class="quick-hours-box" style="margin-bottom: 18px;">
                        <span class="field-caption">Motivo rápido (opcional)</span>
                        <div class="quick-hours-list">
                            <button type="button" class="hour-chip active" onclick="seleccionarAsunto(this, 'Sugerencia Gastronómica')">Sugerencia</button>
                            <button type="button" class="hour-chip" onclick="seleccionarAsunto(this, 'Evento o Reserva Grupal')">Evento / Grupo</button>
                            <button type="button" class="hour-chip" onclick="seleccionarAsunto(this, 'Felicitación al Equipo')">Felicitación</button>
                            <button type="button" class="hour-chip" onclick="seleccionarAsunto(this, 'Consulta General')">Consulta</button>
                        </div>
                    </div>

                    <div class="contact-group">
                        <label for="asunto">Asunto *</label>
                        <input type="text" id="asunto" name="asunto" value="Sugerencia Gastronómica" placeholder="Motivo de contacto" required>
                    </div>

                    <div class="contact-group">
                        <label for="mensaje">Mensaje *</label>
                        <textarea id="mensaje" name="mensaje" rows="4" placeholder="Cuéntanos en qué podemos ayudarte..." required></textarea>
                    </div>

                    <div style="margin-top: 22px;">
                        <button type="submit" class="btn-confirm btn-reserva-full">Enviar Mensaje</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <footer style="text-align: center; padding: 20px; background-color: #1a1a1a; color: #ffffff; position: relative; z-index: 1;">
        <p>© <?php echo date("Y"); ?> Gastrodalk - Coreducación. Todos los derechos reservados.</p>
    </footer>

    <script>
        function seleccionarAsunto(boton, textoAsunto) {
            document.getElementById('asunto').value = textoAsunto;
            boton.parentElement.querySelectorAll('.hour-chip').forEach(chip => chip.classList.remove('active'));
            boton.classList.add('active');
        }
    </script>

</body>
</html>