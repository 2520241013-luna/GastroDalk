<?php
// Detecta si la página actual está dentro de la carpeta "interfaces"
$base = (basename(dirname($_SERVER['PHP_SELF'])) === 'interfaces') ? '../' : '';
?>
<header class="navbar">
    <div class="brand">
        <h1>Gastrodalk</h1>
        <span>Plataforma Gastronómica</span>
    </div>
    <nav class="nav-links">
        <a href="<?php echo $base; ?>index.php">Inicio</a>
        <a href="<?php echo $base; ?>interfaces/menu.php">Menú Digital</a>
        <a href="<?php echo $base; ?>interfaces/reservas.php">Reservas</a>
        <a href="<?php echo $base; ?>interfaces/damian_ia.php">Damian IA</a>
        <a href="<?php echo $base; ?>interfaces/quienes_somos.php">Quiénes Somos</a>
        <a href="<?php echo $base; ?>interfaces/contacto.php">Contáctenos</a>
    </nav>
</header>