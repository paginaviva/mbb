<?php
// update_tags_task_2.php
// Tarea para limpieza de tags en posts_manifest.php (Fase 2)

// Configuración de archivo
$manifest_file = 'posts_manifest.php';

// 1. Verificación de existencia
if (!file_exists($manifest_file)) {
    die("Error: El archivo $manifest_file no existe.\n");
}

// 2. Creación de Backup
// Formato de fecha: YYYYMMDD_HHMMSS
$backup_file = 'posts_manifest_backup_' . date('Ymd_His') . '.php';

if (!copy($manifest_file, $backup_file)) {
    die("Error: No se pudo crear la copia de seguridad $backup_file.\n");
}
echo "Copia de seguridad creada exitosamente: $backup_file\n";

// 3. Carga del contenido actual
include $manifest_file;

// Verificamos que se haya cargado la variable $posts
if (!isset($posts) || !is_array($posts)) {
    die("Error: Estructura inválida en $manifest_file. No se encontró el array \$posts.\n");
}

// 4. Definición de Listas de Limpieza

// Lista EXACTA de etiquetas a eliminar
// Se usan comillas dobles para interpretar caracteres de control como \n si los hubiera
$tags_to_delete = [
    "Barquisimeto\n\n\n\n`",
    "Condiciones de Campeonato 2025-2026\n\n\n\n`",
    "Japón\n\n\n\n`",
    'REEMPLAZAR_CON_ETIQUETA_1',
    'REEMPLAZAR_CON_ETIQUETA_2',
    'REEMPLAZAR_CON_ETIQUETA_3',
    'El Emergente',
    'MVP de la Liga Mayor de Béisbol Profesional 2022',
    'Confederación de Béisbol Profesional del Caribe',
    'Líder en Deportes',
    'Líder en deportes',
    'Maracaibo',
    'BeisbolPlay',
    'Barquisimeto',
    'Caracas',
    'Guatamare',
    'La Guaira',
    'Maracay',
    'Puerto La Cruz',
    'Valencia',
    'Estadio Luis Aparicio',
    'Estadio Universitario',
    'MLB',
    'Grandes Ligas',
    'Ligas Menores',
    '20-N',
    '80 aniversario',
    'bateo',
    'bateo situacional',
    'bullpen',
    'carácter competitivo',
    'club de los 500 hits',
    'control del caos',
    'cupo para lanzador importado',
    'enfoque en prevención de carreras',
    'error bajo los reflectores',
    'escala en Asia',
    'fondo de la tabla',
    'futuro en beisbol organizado',
    'ganar juegos cerrados',
    'impacto ofensivo melenudo',
    'jonrones',
    'juventud bajo examen',
    'liderato en solitario',
    'limpieza de roster',
    'líder jonronero del Caracas',
    'margen de error mínimo',
    'medio juego de diferencia',
    'mejor WHIP de la liga',
    'mejor staff de pitcheo',
    'menos carreras permitidas',
    'movimiento planificado',
    'no es corte definitivo',
    'pelea por el cuarto puesto',
    'pieza clave del lineup',
    'pitcheo',
    'presión de calendario',
    'presión del calendario',
    'profundidad en la lomita',
    'promedio sobre .330',
    'rachas de remontada',
    'rachas negativas',
    'reacción turca',
    'reconstrucción de carrera',
    'redención en la LVBP',
    'regreso en diciembre',
    'relevo largo importado',
    'reordenar campocorto',
    'rotación de miedo',
    'round robin en riesgo',
    'salió de Boston',
    'segunda oportunidad',
    'seguro defensivo',
    'shortstop titular',
    'staff candidato a enero',
    'séptima semana',
    'triple persecución',
    'zona gris de la clasificación',
];

// Lista de sustituciones (comillas tipográficas -> comilla simple)
$tags_to_replace = [
    'comillas tipográficas abiertas' => "'",
    'comillas tipográficas cerradas' => "'",
];

$modified_posts_count = 0;

// 5. Procesamiento de Etiquetas
foreach ($posts as $slug => &$post_data) {
    if (isset($post_data['tags']) && is_array($post_data['tags'])) {
        $original_tags = $post_data['tags'];
        $new_tags = [];
        $has_changes = false;

        foreach ($original_tags as $tag) {
            // Caso 1: Eliminación
            if (in_array($tag, $tags_to_delete, true)) {
                $has_changes = true;
                continue; // Saltar este tag (eliminarlo)
            }

            // Caso 2: Sustitución
            if (array_key_exists($tag, $tags_to_replace)) {
                $new_tags[] = $tags_to_replace[$tag];
                $has_changes = true;
            } else {
                // Caso 3: Mantener
                $new_tags[] = $tag;
            }
        }

        if ($has_changes) {
            // Re-indexar array para evitar huecos en las claves numéricas
            $post_data['tags'] = array_values($new_tags);
            $modified_posts_count++;
        }
    }
}
unset($post_data); // Romper referencia del último elemento

// 6. Guardado de Cambios
// Generamos el contenido del archivo preservando la estructura PHP
$php_content = "<?php\n";
$php_content .= "// Manifiesto de posts - Generado automáticamente por generate_manifest.php\n";
$php_content .= "// Última actualización: " . date('Y-m-d H:i:s') . "\n";
$php_content .= "// No editar manualmente\n\n";
$php_content .= "\$posts = " . var_export($posts, true) . ";\n";

if (file_put_contents($manifest_file, $php_content)) {
    echo "Proceso completado con éxito.\n";
    echo "Posts modificados: $modified_posts_count\n";
    echo "Archivo actualizado: $manifest_file\n";
} else {
    die("Error: No se pudo escribir en el archivo $manifest_file.\n");
}
?>
