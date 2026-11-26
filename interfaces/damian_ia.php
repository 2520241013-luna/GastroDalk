<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Damian IA - Gastrodalk</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body.damian-page {
            background-image: url('../imagenes/damian.jpg');
            background-size: cover;
            background-position: right center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
        }

        #damian-section.section-container {
            background: transparent !important;
            background-color: transparent !important;
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            min-height: 85vh;
            padding: 36px 24px 48px;
            display: flex;
            align-items: center;
        }

        .damian-layout-grid {
            width: 100%;
            max-width: 1120px;
            margin-left: 3%;
            margin-right: auto;
            display: flex;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }

        .damian-main-col {
            flex: 1 1 540px;
            max-width: 620px;
        }

        .damian-header-box {
            margin-bottom: 14px;
        }

        .damian-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255, 255, 255, 0.92);
            color: #c2410c;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 999px;
            margin-bottom: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.14);
        }

        .damian-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.25);
        }

        .damian-header-box h2 {
            color: #ffffff;
            margin: 0;
            font-size: 32px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.45);
        }

        .damian-header-box p {
            color: #fff7ed;
            margin: 4px 0 0;
            font-size: 14px;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.45);
        }

        .damian-card.sophisticated-chat {
            background: rgba(255, 252, 247, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 20px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
            overflow: hidden;
            max-width: 100%;
            width: 100%;
            margin: 0;
        }

        .damian-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            background: linear-gradient(90deg, #ea580c, #f97316);
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
        }

        .damian-clear-btn {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 999px;
            padding: 4px 11px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
        }

        .damian-clear-btn:hover {
            background: rgba(255, 255, 255, 0.32);
            transform: translateY(-1px);
        }

        .damian-messages.sophisticated-messages {
            height: 290px;
            overflow-y: auto;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: linear-gradient(180deg, #fffdf9 0%, #fff7ed 100%);
        }

        .chat-bubble {
            max-width: 86%;
            padding: 11px 15px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.45;
            animation: fadeInUp 0.25s ease;
            word-wrap: break-word;
        }

        .chat-bubble.bot {
            align-self: flex-start;
            background: #ffffff;
            color: #1f2937;
            border: 1px solid #fed7aa;
            border-bottom-left-radius: 4px;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.08);
        }

        .chat-bubble.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #ea580c, #c2410c);
            color: #ffffff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 6px 14px rgba(194, 65, 12, 0.22);
        }

        .chat-time {
            display: block;
            font-size: 10px;
            opacity: 0.65;
            margin-top: 5px;
            text-align: right;
        }

        .damian-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            padding: 10px 18px;
            background: #fffaf3;
            border-top: 1px solid #ffedd5;
        }

        .suggestion-chip {
            background: #ffffff;
            color: #c2410c;
            border: 1px solid #fdba74;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .suggestion-chip:hover {
            background: #ea580c;
            color: #ffffff;
            border-color: #ea580c;
            transform: translateY(-1px);
        }

        .damian-input-area.sophisticated-input {
            display: flex;
            gap: 10px;
            padding: 14px 18px;
            background: #ffffff;
            border-top: 1px solid #ffedd5;
        }

        .damian-input-area.sophisticated-input input {
            flex: 1;
            border: 1.5px solid #fed7aa;
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .damian-input-area.sophisticated-input input:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.16);
        }

        .damian-side-loader {
            width: 190px;
            margin-top: 96px;
            background: rgba(255, 252, 247, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(251, 146, 60, 0.55);
            border-radius: 18px;
            padding: 16px 14px;
            text-align: center;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.18);
            opacity: 0;
            transform: translateX(18px) scale(0.96);
            pointer-events: none;
            transition: opacity 0.28s ease, transform 0.28s ease;
        }

        .damian-side-loader.active {
            opacity: 1;
            transform: translateX(0) scale(1);
        }

        .chef-pulse-ring {
            width: 52px;
            height: 52px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fff7ed, #fed7aa);
            border: 2px solid #ea580c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            animation: pulseChef 1.4s infinite ease-in-out;
        }

        .side-loader-title {
            font-size: 13px;
            font-weight: 700;
            color: #9a3412;
            margin-bottom: 4px;
        }

        .side-loader-sub {
            font-size: 11px;
            color: #57534e;
            margin-bottom: 10px;
        }

        .typing-dots {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .typing-dots span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #ea580c;
            animation: bounceDot 1.1s infinite ease-in-out;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.18s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.36s;
        }

        @keyframes pulseChef {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.35);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(234, 88, 12, 0);
                transform: scale(1.05);
            }
        }

        @keyframes bounceDot {
            0%, 80%, 100% {
                transform: translateY(0);
                opacity: 0.45;
            }
            40% {
                transform: translateY(-6px);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .damian-layout-grid {
                margin-left: auto;
                margin-right: auto;
            }

            .damian-main-col {
                max-width: 100%;
            }

            .damian-side-loader {
                width: 100%;
                margin-top: 0;
                display: none;
                align-items: center;
                justify-content: center;
                gap: 12px;
                text-align: left;
            }

            .damian-side-loader.active {
                display: flex;
            }

            .chef-pulse-ring {
                margin: 0;
                flex-shrink: 0;
            }
        }
    </style>
</head>
<body class="damian-page">

    <?php include '../includes/navbar.php'; ?>

    <section id="damian-section" class="section-container">
        <div class="damian-layout-grid">
            <div class="damian-main-col">
                <div class="damian-header-box">
                    <div class="damian-status-pill">
                        <span class="damian-status-dot"></span>
                        En línea • Chef Virtual Gastrodalk
                    </div>
                    <h2>Damian IA</h2>
                    <p>Asistente virtual de recomendación gastronómica</p>
                </div>

                <div class="damian-card sophisticated-chat">
                    <div class="damian-topbar">
                        <span>🍽️ Chat con Damian</span>
                        <button type="button" class="damian-clear-btn" onclick="limpiarChat()">Limpiar chat</button>
                    </div>

                    <div id="chat-messages" class="damian-messages sophisticated-messages">
                        <div class="chat-bubble bot">
                            🤖 <strong>Damian IA:</strong> ¡Hola! Soy Damian, tu asistente gastronómico en Gastrodalk. ¿En qué te puedo ayudar hoy con el menú o tu reserva?
                            <span class="chat-time" id="initial-time"></span>
                        </div>
                    </div>

                    <div class="damian-suggestions">
                        <button type="button" class="suggestion-chip" onclick="usarSugerencia('Recomiéndame un plato típico de hoy')">✨ Recomiéndame un plato</button>
                        <button type="button" class="suggestion-chip" onclick="usarSugerencia('¿Qué platos tienen en el menú y cuáles son sus precios?')">📋 Menú y precios</button>
                        <button type="button" class="suggestion-chip" onclick="usarSugerencia('¿Cómo puedo hacer una reserva de mesa?')">🪑 Reservar mesa</button>
                    </div>

                    <div class="damian-input-area sophisticated-input">
                        <input type="text" id="chat-input" placeholder="Pregúntame sobre platos, ingredientes, precios o recomendaciones..." onkeypress="if(event.key === 'Enter') enviarMensaje()">
                        <button type="button" id="btn-enviar" onclick="enviarMensaje()" class="btn-primary">Enviar</button>
                    </div>
                </div>
            </div>

            <aside id="damian-side-loader" class="damian-side-loader" aria-live="polite">
                <div class="chef-pulse-ring">👨‍🍳</div>
                <div>
                    <div class="side-loader-title">Damián pensando...</div>
                    <div class="side-loader-sub">Revisando sabores y carta</div>
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <footer style="text-align: center; padding: 20px; background-color: #1a1a1a; color: #ffffff;">
        <p>© <?php echo date("Y"); ?> Gastrodalk - Coreducación. Todos los derechos reservados.</p>
    </footer>

    <script>
    function horaActual() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    document.getElementById('initial-time').textContent = horaActual();

    function usarSugerencia(texto) {
        const input = document.getElementById('chat-input');
        input.value = texto;
        input.focus();
        enviarMensaje();
    }

    function limpiarChat() {
        const messages = document.getElementById('chat-messages');
        messages.innerHTML = `
            <div class="chat-bubble bot">
                🤖 <strong>Damian IA:</strong> ¡Chat limpio! ¿Qué te gustaría probar o consultar ahora?
                <span class="chat-time">${horaActual()}</span>
            </div>
        `;
    }

    function setCargando(estaCargando) {
        const sideLoader = document.getElementById('damian-side-loader');
        const btnEnviar = document.getElementById('btn-enviar');
        sideLoader.classList.toggle('active', estaCargando);
        btnEnviar.disabled = estaCargando;
    }

    async function enviarMensaje() {
        const input = document.getElementById('chat-input');
        const messages = document.getElementById('chat-messages');
        const texto = input.value.trim();
        if (!texto) return;

        const hora = horaActual();
        messages.innerHTML += `
            <div class="chat-bubble user">
                <strong>Tú:</strong> ${texto}
                <span class="chat-time">${hora}</span>
            </div>
        `;
        input.value = '';
        messages.scrollTop = messages.scrollHeight;

        const loadingId = 'loading-' + Date.now();
        messages.innerHTML += `
            <div id="${loadingId}" class="chat-bubble bot">
                🤖 <strong>Damian IA:</strong> Preparando tu recomendación...
            </div>
        `;
        messages.scrollTop = messages.scrollHeight;
        setCargando(true);

        try {
            const res = await fetch('../api/api_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensaje: texto })
            });

            const rawText = await res.text();
            let data = null;
            try {
                data = JSON.parse(rawText);
            } catch (_) {
                throw new Error(`Respuesta no JSON (HTTP ${res.status}): ${rawText.slice(0, 140)}`);
            }

            document.getElementById(loadingId)?.remove();

            if (!res.ok) {
                messages.innerHTML += `
                    <div class="chat-bubble bot">
                        ⚠️ <strong>Error HTTP ${res.status}:</strong> ${data.error || data.respuesta || 'Fallo en el servidor.'}
                        <span class="chat-time">${horaActual()}</span>
                    </div>
                `;
            } else if (data.respuesta) {
                messages.innerHTML += `
                    <div class="chat-bubble bot">
                        🤖 <strong>Damian IA:</strong> ${data.respuesta}
                        <span class="chat-time">${horaActual()}</span>
                    </div>
                `;
            } else {
                messages.innerHTML += `
                    <div class="chat-bubble bot">
                        ⚠️ <strong>Error:</strong> ${data.error || 'No se pudo obtener respuesta.'}
                        <span class="chat-time">${horaActual()}</span>
                    </div>
                `;
            }
        } catch (err) {
            document.getElementById(loadingId)?.remove();
            messages.innerHTML += `
                <div class="chat-bubble bot">
                    ⚠️ <strong>Detalle:</strong> ${err && err.message ? err.message : 'Error de conexión con el servidor.'}
                    <span class="chat-time">${horaActual()}</span>
                </div>
            `;
        } finally {
            setCargando(false);
            messages.scrollTop = messages.scrollHeight;
        }
    }
    </script>

</body>
</html>