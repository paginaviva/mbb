<?php
// seccion/round-robin.php

// Cargar configuración general si es necesaria (por ahora para constantes de ruta si las hay)
require_once '../config.php';

// Cargar el renderer compartido
require_once '../includes/seccion_renderer.php';

// Cargar configuración específica de esta sección
$seccion_config = parse_ini_file('../config/secciones/round-robin.ini', true);

// Validar que la configuración se cargó correctamente
if (!$seccion_config) {
    die("Error crítico: No se pudo cargar la configuración de la sección.");
}

// Renderizar la página
render_seccion($seccion_config);
