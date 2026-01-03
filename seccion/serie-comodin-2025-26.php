<?php
// seccion/serie-comodin-2025-26.php

require_once '../config.php';
require_once '../includes/seccion_renderer.php';

$seccion_config = parse_ini_file('../config/secciones/serie-comodin-2025-26.ini', true);

if (!$seccion_config) {
    die("Error crítico: No se pudo cargar la configuración de la sección.");
}

render_seccion($seccion_config);
