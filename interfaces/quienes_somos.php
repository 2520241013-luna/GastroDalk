<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiénes Somos - Gastrodalk</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body.quienes-somos-bg {
            background-image: url('../imagenes/quienessomos.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
        }

        #quienes-somos.light-bg {
            background: transparent !important;
            background-color: transparent !important;
        }

        #quienes-somos .section-header {
            background-color: rgba(15, 15, 15, 0.65);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border-radius: 14px;
            padding: 16px 24px;
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
        }

        #quienes-somos .section-header h2,
        #quienes-somos .section-header p {
            color: #ffffff;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.6);
        }

        #quienes-somos .info-card {
            background-color: rgba(255, 249, 240, 0.84) !important;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }
    </style>
</head>
<body class="quienes-somos-bg">

    <?php include '../includes/navbar.php'; ?>

    <section id="quienes-somos" class="section-container light-bg">
        <div class="section-header">
            <h2>¿Quiénes Somos?</h2>
            <p>Conoce la identidad, propósito y el equipo detrás de Gastrodalk</p>
        </div>

        <div class="mission-vision-wrapper">
            <div class="info-card centered-card">
                <h3>🎯 Misión</h3>
                <p>Brindar una experiencia tecnológica ágil y moderna a los establecimientos gastronómicos mediante un sistema digital intuitivo que optimice la visualización de menús, pedidos y la interacción con los clientes.</p>
            </div>

            <div class="info-card centered-card">
                <h3>👁️ Visión</h3>
                <p>Convertirnos en la plataforma líder de gestión y recomendación gastronómica a nivel regional, destacándonos por nuestra innovación, seguridad de datos y facilidad de uso.</p>
            </div>

            <div class="info-card centered-card">
                <h3>⚙️ Objetivos del Sistema</h3>
                <p>1. Facilitar la consulta digital de platos y precios.<br>
                2. Automatizar el registro y control de pedidos.<br>
                3. Proveer un canal seguro de contacto almacenado en base de datos.</p>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <div class="info-card creator-card">
                <h3>👥 Integrantes Responsables (Creadoras)</h3>
                <div class="creators-grid">
                    <div>
                        <p><strong>Líder de Desarrollo y Código:</strong><br>Luna Alejandra Niño Rivera</p>
                        <p class="creator-email">📧 2520241001@coreducacion.edu.co</p>
                    </div>
                    <div>
                        <p><strong>Diseño Visual y Arquitectura Web:</strong><br>Laura Michell Amaya Luna</p>
                        <p class="creator-email">📧 2520241013@coreducacion.edu.co</p>
                    </div>
                </div>
                <div class="phones-box">
                    <p>📞 <strong>Teléfonos de contacto:</strong> +57 312 3659788 / +57 311 2054173</p>
                </div>
            </div>
        </div>
    </section>

    <footer style="text-align: center; padding: 20px; background-color: #1a1a1a; color: #ffffff;">
        <p>© <?php echo date("Y"); ?> Gastrodalk - Coreducación. Todos los derechos reservados.</p>
    </footer>

</body>
</html>