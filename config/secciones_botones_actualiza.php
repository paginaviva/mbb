<?php
// config/secciones_botones_actualiza.php
// Script manual para regenerar el archivo de caché de botones de secciones

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Actualizador de Botones de Secciones</h1>";

// Rutas
$sidebar_config_path = __DIR__ . '/sidebar_global.ini';
$output_file = __DIR__ . '/secciones_botones_menu.php';
$secciones_dir = __DIR__ . '/secciones/';

// Verificar que existe sidebar_global.ini
if (!file_exists($sidebar_config_path)) {
    die("ERROR: No se encuentra el archivo sidebar_global.ini");
}

// Leer configuración global
$sidebar_config = parse_ini_file($sidebar_config_path, true);
if (!$sidebar_config || !isset($sidebar_config['ENLACES']['enlace'])) {
    die("ERROR: No se pudo leer la configuración de enlaces");
}

$buttons_data = [];

// Procesar cada enlace
foreach ($sidebar_config['ENLACES']['enlace'] as $link_def) {
    $parts = explode('|', $link_def);

    // Validar formato: Nombre|URL|mostrar_en_portada
    if (count($parts) < 3) {
        echo "ADVERTENCIA: Enlace sin campo mostrar_en_portada: $link_def<br>";
        continue;
    }

    $nombre = trim($parts[0]);
    $url = trim($parts[1]);
    $mostrar = trim($parts[2]);

    // Filtros de exclusión
    if ($nombre === 'Todos los Artículos' || $nombre === 'Inicio') {
        echo "EXCLUIDO (regla fija): $nombre<br>";
        continue;
    }

    // Verificar flag mostrar_en_portada
    if ($mostrar !== '1') {
        echo "EXCLUIDO (mostrar_en_portada=0): $nombre<br>";
        continue;
    }

    // Determinar el archivo .ini de la sección
    // Extraer slug del URL (ej: seccion/round-robin.php -> round-robin)
    if (preg_match('/seccion\/(.+)\.php/', $url, $matches)) {
        $slug = $matches[1];
        $ini_file = $secciones_dir . $slug . '.ini';

        if (file_exists($ini_file)) {
            $section_config = parse_ini_file($ini_file, true);

            $color_primario = $section_config['ESTILO']['color_primario'] ?? '#004c99';
            $color_secundario = $section_config['ESTILO']['color_secundario'] ?? '#0066cc';

            $buttons_data[] = [
                'nombre' => $nombre,
                'url' => $url,
                'color_primario' => $color_primario,
                'color_secundario' => $color_secundario
            ];

            echo "INCLUIDO: $nombre (colores: $color_primario / $color_secundario)<br>";
        } else {
            echo "ADVERTENCIA: No se encontró archivo INI para $nombre ($ini_file)<br>";
        }
    } else {
        echo "ADVERTENCIA: URL no reconocida para $nombre: $url<br>";
    }
}

// Generar archivo PHP de caché
$php_content = "<?php\n";
$php_content .= "// Archivo generado automáticamente por secciones_botones_actualiza.php\n";
$php_content .= "// Fecha: " . date('Y-m-d H:i:s') . "\n";
$php_content .= "// NO EDITAR MANUALMENTE - Ejecutar secciones_botones_actualiza.php para actualizar\n\n";
$php_content .= "return " . var_export($buttons_data, true) . ";\n";

if (file_put_contents($output_file, $php_content)) {
    echo "<br><strong>✓ Archivo generado exitosamente: $output_file</strong><br>";
    echo "Total de botones: " . count($buttons_data) . "<br>";
} else {
    echo "<br><strong>✗ ERROR al escribir el archivo de caché</strong><br>";
}

echo "<br><a href='../index.php'>Ver portada</a>";
