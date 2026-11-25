<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Digital - Gastrodalk</title>
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

    <section id="menu" class="menu-bg-section">
        <div class="menu-overlay">
            <div class="section-header">
                <h2 style="color: #ffedd5;">Menú Digital</h2>
                <p style="color: #fed7aa;">Selecciona tu mesa, el método de pago y tus platos favoritos (máx. 10 por plato)</p>
            </div>

            <?php if (isset($_GET['estado'])): ?>
                <?php if ($_GET['estado'] === 'ok'): ?>
                    <div class="alerta-banner alerta-exito">
                        <span>✅ <strong>¡Pedido #<?php echo htmlspecialchars($_GET['id'] ?? ''); ?> registrado con éxito!</strong> Mesa <?php echo htmlspecialchars($_GET['mesa'] ?? ''); ?> — Total: <strong>$<?php echo number_format(floatval($_GET['total'] ?? 0), 0, ',', '.'); ?></strong></span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php elseif ($_GET['estado'] === 'vacio'): ?>
                    <div class="alerta-banner alerta-aviso">
                        <span>⚠️ Por favor selecciona al menos un plato antes de confirmar tu pedido.</span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php elseif ($_GET['estado'] === 'error'): ?>
                    <div class="alerta-banner alerta-error">
                        <span>❌ No se pudo registrar el pedido: <?php echo htmlspecialchars($_GET['msg'] ?? ''); ?></span>
                        <button type="button" onclick="this.parentElement.remove()">✕</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form action="../procesos/procesar_pedido.php" method="POST">

                <!-- Barra de pedido modernizada -->
                <div class="order-bar">
                    <div class="order-controls">
                        <!-- Selector de Mesa -->
                        <div class="order-field-block">
                            <span class="field-caption">Número de Mesa</span>
                            <div class="qty-stepper mesa-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarMesa(-1)" aria-label="Mesa anterior">−</button>
                                <input type="number" id="numero_mesa" name="numero_mesa" class="qty-input" value="1" min="1" max="25" readonly required>
                                <button type="button" class="qty-btn" onclick="cambiarMesa(1)" aria-label="Mesa siguiente">+</button>
                            </div>
                        </div>

                        <!-- Selector visual de Método de Pago -->
                        <div class="order-field-block">
                            <span class="field-caption">Método de Pago</span>
                            <div class="payment-options">
                                <label class="payment-chip">
                                    <input type="radio" name="metodo_pago" value="Efectivo" checked>
                                    <span>Efectivo</span>
                                </label>
                                <label class="payment-chip">
                                    <input type="radio" name="metodo_pago" value="Tarjeta">
                                    <span>Tarjeta</span>
                                </label>
                                <label class="payment-chip">
                                    <input type="radio" name="metodo_pago" value="Transferencia">
                                    <span>Nequi / PSE</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Total en vivo y botón confirmar -->
                    <div class="order-summary-action">
                        <div class="live-total-box">
                            <span>Total estimado</span>
                            <strong id="total-pedido">$0</strong>
                        </div>
                        <button class="btn-confirm" type="submit">Confirmar pedido</button>
                    </div>
                </div>

                <div class="dishes-list">

                    <!-- Plato 1: Ajiaco -->
                    <article class="dish-item">
                        <div class="dish-image">
                            <img src="../imagenes/ajiaco.jpg" alt="Ajiaco Santafereño">
                        </div>
                        <div class="dish-details">
                            <span class="dish-badge">Sopa Tradicional</span>
                            <h3>Ajiaco</h3>
                            <p>Deliciosa sopa tradicional bogotana con pollo, papa criolla, mazorca y alcaparras.</p>
                            <span class="price">$18.000</span>
                        </div>
                        <div class="dish-action">
                            <span class="qty-label">Cantidad</span>
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, -1)" aria-label="Disminuir">−</button>
                                <input type="number" name="cantidades[1]" class="qty-input" value="0" min="0" max="10" data-precio="18000" readonly>
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, 1)" aria-label="Aumentar">+</button>
                            </div>
                            <input type="hidden" name="precios[1]" value="18000">
                        </div>
                    </article>

                    <!-- Plato 2: Arroz con Pollo -->
                    <article class="dish-item">
                        <div class="dish-image">
                            <img src="../imagenes/arroz-pollo.jpg" alt="Arroz con Pollo">
                        </div>
                        <div class="dish-details">
                            <span class="dish-badge">Clásico de la Casa</span>
                            <h3>Arroz con pollo</h3>
                            <p>Plato clásico colombiano con arroz, pollo desmenuzado y verduras frescas.</p>
                            <span class="price">$16.000</span>
                        </div>
                        <div class="dish-action">
                            <span class="qty-label">Cantidad</span>
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, -1)" aria-label="Disminuir">−</button>
                                <input type="number" name="cantidades[2]" class="qty-input" value="0" min="0" max="10" data-precio="16000" readonly>
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, 1)" aria-label="Aumentar">+</button>
                            </div>
                            <input type="hidden" name="precios[2]" value="16000">
                        </div>
                    </article>

                    <!-- Plato 3: Bandeja Paisa -->
                    <article class="dish-item">
                        <div class="dish-image">
                            <img src="../imagenes/bandeja-paisa.jpg" alt="Bandeja Paisa">
                        </div>
                        <div class="dish-details">
                            <span class="dish-badge">Especialidad</span>
                            <h3>Bandeja paisa</h3>
                            <p>Plato típico antioqueño con fríjoles, arroz, carne molida, huevo y chicharrón.</p>
                            <span class="price">$22.000</span>
                        </div>
                        <div class="dish-action">
                            <span class="qty-label">Cantidad</span>
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, -1)" aria-label="Disminuir">−</button>
                                <input type="number" name="cantidades[3]" class="qty-input" value="0" min="0" max="10" data-precio="22000" readonly>
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, 1)" aria-label="Aumentar">+</button>
                            </div>
                            <input type="hidden" name="precios[3]" value="22000">
                        </div>
                    </article>

                    <!-- Plato 4: Mojarra Frita -->
                    <article class="dish-item">
                        <div class="dish-image">
                            <img src="../imagenes/mojarra.jpg" alt="Mojarra Frita">
                        </div>
                        <div class="dish-details">
                            <span class="dish-badge">Del Río</span>
                            <h3>Mojarra Frita</h3>
                            <p>Pescado entero frito crujiente acompañado de patacones, ensalada fresca y arroz.</p>
                            <span class="price">$25.000</span>
                        </div>
                        <div class="dish-action">
                            <span class="qty-label">Cantidad</span>
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, -1)" aria-label="Disminuir">−</button>
                                <input type="number" name="cantidades[4]" class="qty-input" value="0" min="0" max="10" data-precio="25000" readonly>
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, 1)" aria-label="Aumentar">+</button>
                            </div>
                            <input type="hidden" name="precios[4]" value="25000">
                        </div>
                    </article>

                    <!-- Plato 5: Caldo de Costilla -->
                    <article class="dish-item">
                        <div class="dish-image">
                            <img src="../imagenes/caldo-costilla.jpg" alt="Caldo de Costilla">
                        </div>
                        <div class="dish-details">
                            <span class="dish-badge">Reconfortante</span>
                            <h3>Caldo de Costilla</h3>
                            <p>Sustancioso caldo claro de costilla de res con papa sabanera, cilantro picado y cebolla.</p>
                            <span class="price">$14.000</span>
                        </div>
                        <div class="dish-action">
                            <span class="qty-label">Cantidad</span>
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, -1)" aria-label="Disminuir">−</button>
                                <input type="number" name="cantidades[5]" class="qty-input" value="0" min="0" max="10" data-precio="14000" readonly>
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, 1)" aria-label="Aumentar">+</button>
                            </div>
                            <input type="hidden" name="precios[5]" value="14000">
                        </div>
                    </article>

                    <!-- Plato 6: Carne Asada -->
                    <article class="dish-item">
                        <div class="dish-image">
                            <img src="../imagenes/carne-asada.jpg" alt="Carne Asada">
                        </div>
                        <div class="dish-details">
                            <span class="dish-badge">A la Parrilla</span>
                            <h3>Carne Asada</h3>
                            <p>Jugoso corte de carne asado a la parrilla, servido con papa salada, yuca y guacamole.</p>
                            <span class="price">$20.000</span>
                        </div>
                        <div class="dish-action">
                            <span class="qty-label">Cantidad</span>
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, -1)" aria-label="Disminuir">−</button>
                                <input type="number" name="cantidades[6]" class="qty-input" value="0" min="0" max="10" data-precio="20000" readonly>
                                <button type="button" class="qty-btn" onclick="cambiarCantidad(this, 1)" aria-label="Aumentar">+</button>
                            </div>
                            <input type="hidden" name="precios[6]" value="20000">
                        </div>
                    </article>

                </div>
            </form>
        </div>
    </section>

    <footer style="text-align: center; padding: 20px; background-color: #1a1a1a; color: #ffffff;">
        <p>© <?php echo date("Y"); ?> Gastrodalk - Coreducación. Todos los derechos reservados.</p>
    </footer>

    <script>
        function cambiarMesa(cambio) {
            const inputMesa = document.getElementById('numero_mesa');
            const min = parseInt(inputMesa.min) || 1;
            const max = parseInt(inputMesa.max) || 25;
            let valor = (parseInt(inputMesa.value) || 1) + cambio;

            if (valor < min) valor = min;
            if (valor > max) valor = max;
            inputMesa.value = valor;
        }

        function cambiarCantidad(boton, cambio) {
            const contenedor = boton.parentElement;
            const input = contenedor.querySelector('.qty-input');
            const tarjeta = boton.closest('.dish-item');
            const min = parseInt(input.min) || 0;
            const max = parseInt(input.max) || 10;
            let valorActual = parseInt(input.value) || 0;

            let nuevoValor = valorActual + cambio;
            if (nuevoValor < min) nuevoValor = min;
            if (nuevoValor > max) nuevoValor = max;

            input.value = nuevoValor;

            if (nuevoValor > 0) {
                tarjeta.classList.add('dish-selected');
            } else {
                tarjeta.classList.remove('dish-selected');
            }

            actualizarTotal();
        }

        function actualizarTotal() {
            const inputs = document.querySelectorAll('.qty-input');
            let total = 0;

            inputs.forEach(input => {
                const cantidad = parseInt(input.value) || 0;
                const precio = parseFloat(input.dataset.precio) || 0;
                total += cantidad * precio;
            });

            document.getElementById('total-pedido').textContent = '$' + total.toLocaleString('es-CO');
        }

        (() => {
            "use strict";

            const LIKES = ['👍', '👍🏻', '👍🏼', '👍🏽', '👍🏾', '👍🏿'];
            const MAX_LIKES = 70;
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

            function crearLike(layer) {
                if (reducedMotion.matches) return;

                while (layer.childElementCount >= MAX_LIKES) {
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
                el.textContent = pick(LIKES);
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

            window.lanzarLikesPedido = function() {
                const layer = obtenerCapaLikes();
                const count = (window.innerWidth || 1280) < 640 ? 12 : 18;
                for (let i = 0; i < count; i++) {
                    crearLike(layer);
                }
            };
        })();
    </script>

    <?php if (isset($_GET['estado']) && $_GET['estado'] === 'ok'): ?>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                if (typeof window.lanzarLikesPedido === 'function') {
                    window.lanzarLikesPedido();
                }
            });
        </script>
    <?php endif; ?>

</body>
</html>