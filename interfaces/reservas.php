<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Gastrodalk</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        #gastro-likes-layer {
            position: fixed;
            inset: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            overflow: hidden;
            z-index: 2147483647;
        }

        .gastro-like-pop {
            position: fixed;
            left: 0;
            top: 0;
            display: block;
            pointer-events: none;
            user-select: none;
            line-height: 1;
            font-family: "Segoe UI Emoji", "Apple Color Emoji", "Noto Color Emoji", sans-serif;
            filter: drop-shadow(0 6px 12px rgba(234, 88, 12, 0.28));
        }

        @media (prefers-reduced-motion: reduce) {
            #gastro-likes-layer {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div id="gastro-likes-layer" aria-hidden="true"></div>

    <?php include '../includes/navbar.php'; ?>

    <section id="reservas" class="reserva-bg-section">
        <div class="reserva-overlay">
            <div class="section-header">
                <h2 style="color: #ffedd5;">Reserva tu Mesa</h2>
                <p style="color: #fed7aa;">Asegura tu lugar con anticipación y disfruta de la mejor gastronomía tradicional</p>
            </div>

            <?php if (isset($_GET['estado'])): ?>
                <?php if ($_GET['estado'] === 'ok'): ?>
                    <div class="alerta-banner alerta-exito">
                        <span>✅ <strong>¡Reserva confirmada!</strong> Te esperamos, <strong><?php echo htmlspecialchars($_GET['nombre'] ?? ''); ?></strong>, el día <strong><?php echo htmlspecialchars($_GET['fecha'] ?? ''); ?></strong> a las <strong><?php echo htmlspecialchars($_GET['hora'] ?? ''); ?></strong>.</span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php elseif ($_GET['estado'] === 'error'): ?>
                    <div class="alerta-banner alerta-error">
                        <span>❌ Error al registrar la reserva: <?php echo htmlspecialchars($_GET['msg'] ?? ''); ?></span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="reserva-card">
                <!-- Franja informativa superior -->
                <div class="reserva-info-strip">
                    <span>🕒 Horario: Lunes a Domingo de 12:00 PM a 10:00 PM</span>
                    <span>👥 Grupos de 1 a 20 personas</span>
                </div>

                <form action="../procesos/procesar_reserva.php" method="POST">
                    <!-- Fila 1: Datos de contacto -->
                    <div class="reserva-grid-2">
                        <div class="reserva-field">
                            <label for="res_nombre">Nombre Completo *</label>
                            <input type="text" id="res_nombre" name="nombre_contacto" placeholder="Ej: Laura Martínez" required>
                        </div>
                        <div class="reserva-field">
                            <label for="res_telefono">Teléfono / WhatsApp *</label>
                            <input type="tel" id="res_telefono" name="telefono_contacto" placeholder="Ej: 310 456 7890" required>
                        </div>
                    </div>

                    <!-- Fila 2: Fecha, Hora y Personas -->
                    <div class="reserva-grid-3">
                        <div class="reserva-field">
                            <label for="res_fecha">Fecha de Reserva *</label>
                            <input type="date" id="res_fecha" name="fecha_reserva" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="reserva-field">
                            <label for="res_hora">Hora Exacta *</label>
                            <input type="time" id="res_hora" name="hora_reserva" value="13:00" required>
                        </div>

                        <div class="reserva-field reserva-personas-box">
                            <label>Número de Personas *</label>
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarPersonas(-1)" aria-label="Menos personas">−</button>
                                <input type="number" id="res_personas" name="numero_personas" class="qty-input" value="2" min="1" max="20" readonly required>
                                <button type="button" class="qty-btn" onclick="cambiarPersonas(1)" aria-label="Más personas">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Sugerencias rápidas de horario -->
                    <div class="quick-hours-box">
                        <span class="field-caption">Horarios sugeridos (clic rápido)</span>
                        <div class="quick-hours-list">
                            <button type="button" class="hour-chip" onclick="seleccionarHora(this, '12:30')">12:30 PM</button>
                            <button type="button" class="hour-chip active" onclick="seleccionarHora(this, '13:00')">01:00 PM</button>
                            <button type="button" class="hour-chip" onclick="seleccionarHora(this, '14:00')">02:00 PM</button>
                            <button type="button" class="hour-chip" onclick="seleccionarHora(this, '19:00')">07:00 PM</button>
                            <button type="button" class="hour-chip" onclick="seleccionarHora(this, '20:30')">08:30 PM</button>
                        </div>
                    </div>

                    <div class="reserva-submit-area">
                        <button type="submit" class="btn-confirm btn-reserva-full">Confirmar Reserva</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <footer style="text-align: center; padding: 20px; background-color: #1a1a1a; color: #ffffff;">
        <p>© <?php echo date("Y"); ?> Gastrodalk - Coreducación. Todos los derechos reservados.</p>
    </footer>

    <script>
        function cambiarPersonas(cambio) {
            const input = document.getElementById('res_personas');
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 20;
            let valor = (parseInt(input.value) || 2) + cambio;

            if (valor < min) valor = min;
            if (valor > max) valor = max;
            input.value = valor;
        }

        function seleccionarHora(boton, hora24) {
            document.getElementById('res_hora').value = hora24;
            document.querySelectorAll('.hour-chip').forEach(chip => chip.classList.remove('active'));
            boton.classList.add('active');
        }

        (() => {
            "use strict";

            const OKS = ['👌', '👌🏻', '👌🏼', '👌🏽', '👌🏾', '👌🏿'];
            const MAX_OKS = 70;
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

            const rand = (min, max) => Math.random() * (max - min) + min;
            const randInt = (min, max) => Math.floor(rand(min, max + 1));
            const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];

            function obtenerCapaLikes() {
                let layer = document.getElementById('gastro-likes-layer');
                if (!layer) {
                    layer = document.createElement('div');
                    layer.id = 'gastro-likes-layer';
                    layer.setAttribute('aria-hidden', 'true');
                    document.body.appendChild(layer);
                }
                return layer;
            }

            function crearOk(layer) {
                if (reducedMotion.matches) return;

                while (layer.childElementCount >= MAX_OKS) {
                    layer.firstElementChild.remove();
                }

                const vw = window.innerWidth || 1280;
                const vh = window.innerHeight || 720;

                const startX = rand(30, Math.max(50, vw - 50));
                const startY = vh - rand(25, 80);
                const endX = Math.max(16, Math.min(vw - 44, startX + rand(-130, 130)));
                const endY = Math.max(-80, startY - rand(190, 380));
                const fontSize = randInt(28, 44);
                const rotStart = randInt(-18, 18);
                const rotEnd = randInt(-28, 28);
                const duration = randInt(1150, 2000);
                const delay = randInt(0, 360);

                const el = document.createElement('span');
                el.className = 'gastro-like-pop';
                el.textContent = pick(OKS);
                el.style.fontSize = `${fontSize}px`;
                el.style.opacity = '0';
                layer.appendChild(el);

                const removeEl = () => el.isConnected && el.remove();

                if (typeof el.animate === 'function') {
                    const anim = el.animate([
                        {
                            opacity: 0,
                            transform: `translate3d(${startX}px, ${startY}px, 0) scale(0.55) rotate(${rotStart}deg)`
                        },
                        {
                            opacity: 1,
                            offset: 0.2,
                            transform: `translate3d(${(startX + endX) * 0.5}px, ${startY - (startY - endY) * 0.35}px, 0) scale(1.12) rotate(${rotStart * 0.4}deg)`
                        },
                        {
                            opacity: 1,
                            offset: 0.78,
                            transform: `translate3d(${(startX + endX) * 0.85}px, ${startY - (startY - endY) * 0.78}px, 0) scale(1) rotate(${rotEnd * 0.8}deg)`
                        },
                        {
                            opacity: 0,
                            transform: `translate3d(${endX}px, ${endY}px, 0) scale(0.85) rotate(${rotEnd}deg)`
                        }
                    ], {
                        duration,
                        delay,
                        easing: 'cubic-bezier(0.2, 0.8, 0.25, 1)',
                        fill: 'forwards'
                    });

                    anim.onfinish = removeEl;
                    anim.oncancel = removeEl;
                    setTimeout(removeEl, duration + delay + 250);
                }
            }

            window.lanzarLikesReserva = function() {
                const layer = obtenerCapaLikes();
                const count = (window.innerWidth || 1280) < 640 ? 12 : 18;
                for (let i = 0; i < count; i++) {
                    crearOk(layer);
                }
            };
        })();
    </script>

    <?php if (isset($_GET['estado']) && $_GET['estado'] === 'ok'): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                if (typeof window.lanzarLikesReserva === 'function') {
                    window.lanzarLikesReserva();
                }
            });
        </script>
    <?php endif; ?>

</body>
</html>