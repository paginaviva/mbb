<?php
// Configuración común para Meridiano LVBP Blog

// Nombre del sitio para Open Graph
if (!defined('OG_SITE_NAME')) define('OG_SITE_NAME', 'Meridiano LVBP Blog');

// Directorio físico del sitio
if (!defined('SITE_DIR')) define('SITE_DIR', '/home/udcwscico/public_html/udn_meridiano_com/');

// URL base del sitio
if (!defined('SITE_URL')) define('SITE_URL', 'https://www.meridiano.com/');

// Directorio de posts
if (!defined('POST_DIR')) define('POST_DIR', '/post/');

// Otros constantes comunes si es necesario
if (!defined('SITE_AUTHOR_DEFAULT')) define('SITE_AUTHOR_DEFAULT', 'Redacción Meridiano');

// Configuración de errores PHP
// Activar/desactivar visualización de errores en navegador (1 para activar, 0 para desactivar)
ini_set('display_errors', 1);

// Registrar errores en log
ini_set('log_errors', 1);

// Archivo de log de errores en la raíz del proyecto
ini_set('error_log', __DIR__ . '/error_log.txt');

// Reportar todos los errores
error_reporting(E_ALL);
