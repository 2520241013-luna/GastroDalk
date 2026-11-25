<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastrodalk - Plataforma Gastronómica</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .badge-colombia-btn {
            cursor: pointer !important;
            user-select: none;
            position: relative;
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s cubic-bezier(0.22, 1, 0.36, 1),
                        box-shadow 0.2s ease,
                        filter 0.2s ease;
        }

        .badge-colombia-btn:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.38);
            filter: brightness(1.06);
        }

        .badge-colombia-btn:active {
            transform: scale(0.96);
        }

        .gastro-logo-shine-target {
            position: relative !important;
            overflow: hidden !important;
        }

        .gastro-logo-shine-target::after {
            content: "";
            position: absolute;
            top: -20%;
            left: -130%;
            width: 55%;
            height: 140%;
            background: linear-gradient(
                110deg,
                transparent 0%,
                rgba(255, 255, 255, 0.08) 35%,
                rgba(255, 255, 255, 0.65) 50%,
                rgba(255, 255, 255, 0.08) 65%,
                transparent 100%
            );
            transform: skewX(-22deg);
            pointer-events: none;
            opacity: 0;
            z-index: 20;
        }

        .gastro-logo-shine-target.is-shining::after {
            opacity: 1;
            animation: gastroLogoSweep 0.95s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
        }

        @keyframes gastroLogoSweep {
            0% {
                left: -130%;
                opacity: 0.2;
            }
            20% {
                opacity: 1;
            }
            85% {
                opacity: 0.95;
            }
            100% {
                left: 160%;
                opacity: 0;
            }
        }

        #gastro-emoji-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            overflow: hidden;
            z-index: 2147483647;
        }

        .gastro-rain-emoji {
            position: fixed;
            top: 0;
            left: 0;
            display: block;
            pointer-events: none;
            user-select: none;
            line-height: 1;
            font-family: "Segoe UI Emoji", "Apple Color Emoji", "Noto Color Emoji", sans-serif;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.45);
        }

        @media (prefers-reduced-motion: reduce) {
            #gastro-emoji-layer {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar.php'; ?>

    <main class="home-hero-section" style="background-image: url('imagenes/fachada.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: 60px 20px;">
        <div class="home-hero-overlay" style="background-color: rgba(15, 15, 15, 0.68); backdrop-filter: blur(4px); border-radius: 16px; padding: 40px 32px; max-width: 820px; margin: 0 auto; color: #ffffff;">
            <span id="colombia-badge"
                  class="badge badge-colombia-btn"
                  role="button"
                  tabindex="0"
                  onclick="window.lanzarEmojisGastro && window.lanzarEmojisGastro(this)"
                  onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();window.lanzarEmojisGastro&&window.lanzarEmojisGastro(this);}"
                  aria-label="Sabores Auténticos de Colombia"
                  title="Sabores Auténticos de Colombia">
                Sabores Auténticos de Colombia
            </span>
            <h1>Bienvenido a Gastrodalk</h1>
            <p>Disfruta de nuestra gastronomía tradicional preparada al momento. Explora el menú digital, agenda tu mesa con anticipación o consulta a nuestro asistente virtual desde la barra superior.</p>
        </div>
    </main>

    <footer style="text-align: center; padding: 20px; background-color: #1a1a1a; color: #ffffff;">
        <p>© <?php echo date("Y"); ?> Gastrodalk - Coreducación. Todos los derechos reservados.</p>
    </footer>

    <div id="gastro-emoji-layer" aria-hidden="true"></div>

    <script>
    (() => {
        "use strict";

        const CONFIG = {
            emojis: ['🇨🇴', '🍲', '🥟', '🥑', '🥩', '🍗', '🐟', '🍍', '☕', '🍌', '🌽', '🥭'],
            maxEmojisActivos: 120,
            intervaloBrilloLogoMs: 5000,
            duracionAnimacionLogoMs: 950
        };

        const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

        function rand(min, max) {
            return Math.random() * (max - min) + min;
        }

        function randInt(min, max) {
            return Math.floor(rand(min, max + 1));
        }

        function pickRandom(arr) {
            return arr[Math.floor(Math.random() * arr.length)];
        }

        function obtenerCapaEmojis() {
            let layer = document.getElementById('gastro-emoji-layer');
            if (!layer) {
                layer = document.createElement('div');
                layer.id = 'gastro-emoji-layer';
                layer.setAttribute('aria-hidden', 'true');
                document.body.appendChild(layer);
            }
            return layer;
        }

        function animarUnEmoji(layer) {
            if (reducedMotionQuery.matches) return;

            while (layer.childElementCount >= CONFIG.maxEmojisActivos) {
                layer.firstElementChild.remove();
            }

            const vw = window.innerWidth || document.documentElement.clientWidth || 1280;
            const vh = window.innerHeight || document.documentElement.clientHeight || 720;

            const startX = rand(20, Math.max(40, vw - 40));
            const startY = rand(-90, -25);
            const endX = Math.max(10, Math.min(vw - 20, startX + rand(-160, 160)));
            const endY = vh + rand(40, 120);

            const fontSize = randInt(26, 40);
            const startScale = rand(0.7, 0.95);
            const endScale = rand(1.0, 1.35);
            const rotStart = randInt(-35, 35);
            const rotEnd = rotStart + randInt(-260, 260);
            const duration = randInt(1750, 2900);
            const delay = randInt(0, 500);

            const el = document.createElement('span');
            el.className = 'gastro-rain-emoji';
            el.textContent = pickRandom(CONFIG.emojis);
            el.style.fontSize = `${fontSize}px`;
            el.style.opacity = '0';

            layer.appendChild(el);

            const limpiar = () => {
                if (el.isConnected) el.remove();
            };

            if (typeof el.animate === 'function') {
                const anim = el.animate([
                    {
                        opacity: 0,
                        transform: `translate3d(${startX}px, ${startY}px, 0) scale(${startScale}) rotate(${rotStart}deg)`
                    },
                    {
                        opacity: 1,
                        offset: 0.14,
                        transform: `translate3d(${(startX + endX) * 0.25}px, ${vh * 0.18}px, 0) scale(${(startScale + endScale) * 0.55}) rotate(${(rotStart + rotEnd) * 0.25}deg)`
                    },
                    {
                        opacity: 1,
                        offset: 0.82,
                        transform: `translate3d(${(startX + endX) * 0.85}px, ${vh * 0.82}px, 0) scale(${endScale}) rotate(${(rotStart + rotEnd) * 0.82}deg)`
                    },
                    {
                        opacity: 0,
                        transform: `translate3d(${endX}px, ${endY}px, 0) scale(${endScale * 0.9}) rotate(${rotEnd}deg)`
                    }
                ], {
                    duration,
                    delay,
                    easing: 'cubic-bezier(0.2, 0.75, 0.35, 1)',
                    fill: 'forwards'
                });

                anim.onfinish = limpiar;
                anim.oncancel = limpiar;
                setTimeout(limpiar, duration + delay + 300);
            } else {
                el.style.left = `${startX}px`;
                el.style.top = `${startY}px`;
                el.style.opacity = '1';
                el.style.transition = `transform ${duration}ms linear ${delay}ms, opacity 400ms ease ${duration + delay - 400}ms`;
                requestAnimationFrame(() => {
                    el.style.transform = `translate3d(${endX - startX}px, ${endY - startY}px, 0) rotate(${rotEnd}deg)`;
                    el.style.opacity = '0';
                });
                setTimeout(limpiar, duration + delay + 350);
            }
        }

        window.lanzarEmojisGastro = function(triggerEl) {
            const target = triggerEl || document.getElementById('colombia-badge');
            if (target && typeof target.animate === 'function') {
                target.animate([
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.07)' },
                    { transform: 'scale(1)' }
                ], { duration: 280, easing: 'ease-out' });
            }

            const layer = obtenerCapaEmojis();
            const vw = window.innerWidth || 1280;
            const cantidad = vw < 640 ? 34 : 55;

            for (let i = 0; i < cantidad; i++) {
                animarUnEmoji(layer);
            }
        };

        function localizarLogoGastrodalk() {
            const candidatos = document.querySelectorAll(
                'header a, header .logo, header .brand, nav a, nav .logo, nav .brand, .navbar a, .navbar .logo'
            );

            for (const el of candidatos) {
                const rect = el.getBoundingClientRect();
                const texto = (el.textContent || '').toLowerCase();
                const estaArribaIzquierda =
                    rect.left >= 0 &&
                    rect.left < window.innerWidth * 0.45 &&
                    rect.top >= 0 &&
                    rect.top < 140;

                if (estaArribaIzquierda && (texto.includes('gastrodalk') || el.querySelector('img'))) {
                    return el;
                }
            }

            for (const el of candidatos) {
                const rect = el.getBoundingClientRect();
                if (rect.width > 20 && rect.height > 15 && rect.left < 280 && rect.top < 120) {
                    return el;
                }
            }

            return null;
        }

        function dispararBrilloLogo(logoEl) {
            if (!logoEl || reducedMotionQuery.matches) return;

            logoEl.classList.add('gastro-logo-shine-target');
            logoEl.classList.remove('is-shining');
            void logoEl.offsetWidth;
            logoEl.classList.add('is-shining');

            window.setTimeout(() => {
                logoEl.classList.remove('is-shining');
            }, CONFIG.duracionAnimacionLogoMs + 120);
        }

        function iniciarLogoShine() {
            const logoGastrodalk = localizarLogoGastrodalk();
            if (logoGastrodalk && logoGastrodalk.dataset.gastroShineBound !== '1') {
                logoGastrodalk.dataset.gastroShineBound = '1';
                logoGastrodalk.classList.add('gastro-logo-shine-target');
                window.setInterval(() => {
                    dispararBrilloLogo(logoGastrodalk);
                }, CONFIG.intervaloBrilloLogoMs);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', iniciarLogoShine);
        } else {
            iniciarLogoShine();
        }
    })();
    </script>

</body>
</html>