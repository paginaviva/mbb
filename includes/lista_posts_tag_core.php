<?php

/**
 * LPT - Lista Posts Tag Core
 * Núcleo lógico para gestionar la configuración y resolución de etiquetas del sistema LPT.
 *
 * Funciones principales:
 * - Lectura y validación de INI
 * - Resolución de etiqueta activa (URL/GET)
 * - Provisión de datos de cabecera e imágenes con fallback
 */

// Prevenir acceso directo si no es CLI ni include seguro
if (!defined('SITE_URL') && php_sapi_name() !== 'cli') {
    exit('Acceso denegado: Core LPT');
}

/**
 * Obtiene la configuración global parseada del INI.
 * Utiliza caché estática para evitar múltiples lecturas.
 */
function lpt_get_config()
{
    static $config = null;
    if ($config === null) {
        $ini_path = __DIR__ . '/lista_posts_tag.ini';
        if (file_exists($ini_path)) {
            // parse_ini_file con scanner_mode normal
            $config = @parse_ini_file($ini_path, true);
        }
        if ($config === false || !is_array($config)) {
            $config = [];
        }
    }
    return $config;
}

/**
 * Resuelve el slug de la etiqueta activa basándose en prioridades.
 * Prioridad 1: URL path (/tag/slug)
 * Prioridad 2: Parámetro GET (?tag=slug)
 */
function lpt_resolve_slug()
{
    // 1. Intentar desde la ruta amigable
    if (isset($_SERVER['REQUEST_URI'])) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // Busca coincidencias de /tag/<slug>
        // Soporta posibles subdirectorios previos
        if (preg_match('#/tag/([^/]+)#', $path, $matches)) {
            return strtolower(trim($matches[1]));
        }
    }

    // 2. Intentar desde parámetro GET (Compatibilidad)
    if (isset($_GET['tag']) && !empty($_GET['tag'])) {
        return strtolower(trim($_GET['tag']));
    }

    return null;
}

/**
 * Devuelve el objeto de datos completo para una etiqueta o la activa.
 * 
 * @param string|null $slug Slug a resolver. Si es null, autodetecta.
 * @return array Estructura con datos de presentación y validez.
 */
function lpt_get_active_tag_data($slug = null)
{
    $config = lpt_get_config();
    $slug = $slug ?? lpt_resolve_slug();

    // Configuración global (defaults)
    $global_conf = $config['global'] ?? [];
    $default_img = $global_conf['default_header_img'] ?? 'assets/i-tags/default.webp';
    $invalid_msg = $global_conf['invalid_tag_msg'] ?? 'Sin noticias de momento';
    $empty_msg   = $global_conf['no_results_msg'] ?? 'Sin noticias de momento';

    // Estructura base de respuesta (Estado inválido por defecto)
    $data = [
        'valid'          => false,
        'slug'           => $slug,
        'button_text'    => '',
        'header_title'   => '',
        'header_subtitle' => '',
        'header_img'     => $default_img,
        'button_color'   => '#0085A1', // Default color
        'msg'            => $invalid_msg
    ];

    if ($slug && isset($config[$slug])) {
        // La etiqueta existe en configuración
        $tag_conf = $config[$slug];

        $data['valid']           = true;
        $data['button_text']     = $tag_conf['button_text'] ?? '';
        $data['header_title']    = $tag_conf['header_title'] ?? '';
        $data['header_subtitle'] = $tag_conf['header_subtitle'] ?? '';
        $data['button_color']    = $tag_conf['button_color'] ?? '#0085A1';
        $data['msg']             = $empty_msg; // Mensaje base por si no hay posts luego

        // Lógica de resolución de imagen
        $configured_img = $tag_conf['header_img'] ?? '';
        if (!empty($configured_img)) {
            // Verificar existencia física
            // Se asume que este script está en /includes/ y la raíz está un nivel arriba
            $project_root = dirname(__DIR__);
            $img_abs_path = $project_root . '/' . ltrim($configured_img, '/');

            if (file_exists($img_abs_path)) {
                $data['header_img'] = $configured_img;
            }
        }
    }

    return $data;
}

/**
 * Devuelve la lista ordenada de etiquetas habilitadas para menús.
 */
function lpt_get_ordered_menu_list()
{
    $config = lpt_get_config();
    $order = $config['order'] ?? [];
    $result = [];

    foreach ($order as $slug => $enabled) {
        if ($enabled && isset($config[$slug])) {
            $conf = $config[$slug];
            $result[$slug] = [
                'text'  => $conf['button_text'] ?? $slug,
                'color' => $conf['button_color'] ?? '#0085A1',
                'url'   => SITE_URL . 'lpt.php?tag=' . $slug // Uso de parámetro GET para evitar 404
            ];
        }
    }
    return $result;
}

/**
 * Obtiene un lote de posts filtrados por etiqueta, ordenados y paginados.
 * 
 * @param string $tag_slug Slug de la etiqueta a filtrar.
 * @param int $offset Desplazamiento de resultados (para paginación).
 * @param string|null $search_query Término de búsqueda opcional.
 * @return array Estructura con posts del lote y flag de paginación.
 */
function lpt_get_tag_batch($tag_slug, $offset = 0, $search_query = null)
{
    // Asegurar dependencias (Provider incluye manifest y helpers de fecha/string)
    if (!function_exists('sort_posts_by_date')) {
        $provider_path = __DIR__ . '/home_data_provider.php';
        if (file_exists($provider_path)) {
            include_once $provider_path;
        }
    }

    // Acceder a la variable global $posts definida en el manifest
    global $posts;
    $all_posts = $posts ?? [];

    $config = lpt_get_config();
    $batch_size = (int)($config['global']['batch_size'] ?? 6);
    if ($batch_size <= 0) $batch_size = 6;

    // Normalizar slug de entrada
    // Usamos la función del provider si existe, o fallback simple
    if (function_exists('normalize_string_for_comparison')) {
        $target_tag = normalize_string_for_comparison($tag_slug);
    } else {
        $target_tag = mb_strtolower(trim($tag_slug), 'UTF-8');
    }
    // Asegurar que los espacios sean guiones para comparar con slugs
    $target_tag = str_replace(' ', '-', $target_tag);

    $filtered = [];

    // Fase 1: Filtrado por Etiqueta
    foreach ($all_posts as $slug => $post) {
        if (!empty($post['tags']) && is_array($post['tags'])) {
            $match = false;
            foreach ($post['tags'] as $t) {
                // Normalizar cada tag del post
                if (function_exists('normalize_string_for_comparison')) {
                    $t_norm = normalize_string_for_comparison($t);
                } else {
                    $t_norm = mb_strtolower(trim($t), 'UTF-8');
                }
                // Normalizar espacios a guiones para comparación con el slug
                $t_norm = str_replace(' ', '-', $t_norm);

                if ($t_norm === $target_tag) {
                    $match = true;
                    break;
                }
            }

            if ($match) {
                // Enriquecer post para asegurar campos de vista (ID, URL, Categoría)
                if (function_exists('enrich_post')) {
                    $post = enrich_post($post, $slug);
                } else {
                    // Fallback de enriquecimiento mínimo
                    $post['slug'] = $slug;
                    if (!isset($post['url'])) $post['url'] = 'post/' . $slug . '.php';
                    if (!isset($post['date'])) $post['date'] = '';
                }
                $filtered[$slug] = $post; // Usar slug como key para evitar duplicados en pasos posteriores
            }
        }
    }

    // Fase 2: Filtrado por Búsqueda (si aplica)
    if (!empty($search_query)) {
        $search_str = trim($search_query);
        $search_results = [];

        // Helper interno para buscar en archivo (copiado lógica de lista_posts.php)
        $search_in_content = function ($slug, $query) {
            // Asumimos estructura plana donde posts están en raiz/post o similar
            // Intentamos localizar el archivo físico
            // El slug suele ser el nombre del archivo sin .php
            $candidates = [
                __DIR__ . '/../post/' . $slug . '.php',
                __DIR__ . '/../' . $slug . '.php'
            ];

            foreach ($candidates as $filepath) {
                if (file_exists($filepath)) {
                    $content = file_get_contents($filepath);
                    // Aislar contenido artícula
                    if (preg_match('/<article[^>]*>(.*?)<\/article>/s', $content, $matches)) {
                        $content = $matches[1];
                    }
                    $text = strip_tags($content);
                    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    return mb_stripos($text, $query, 0, 'UTF-8') !== false;
                }
            }
            return false;
        };

        foreach ($filtered as $slug => $post) {
            $found = false;
            // 1. Título
            if (mb_stripos($post['title'], $search_str, 0, 'UTF-8') !== false) {
                $found = true;
            }
            // 2. Subtítulo
            elseif (!empty($post['subtitle']) && mb_stripos($post['subtitle'], $search_str, 0, 'UTF-8') !== false) {
                $found = true;
            }
            // 3. Contenido (Lectura de archivo)
            else {
                if ($search_in_content($slug, $search_str)) {
                    $found = true;
                }
            }

            if ($found) {
                $search_results[] = $post;
            }
        }
        $filtered = $search_results;
    } else {
        // Si no hay búsqueda, aplanar el array asociativo a numérico
        $filtered = array_values($filtered);
    }

    // Ordenamiento
    if (function_exists('sort_posts_by_date')) {
        $filtered = sort_posts_by_date($filtered);
    }

    // Paginación
    $total = count($filtered);
    // Asegurar offset válido
    if ($offset < 0) $offset = 0;

    $slice = array_slice($filtered, $offset, $batch_size);
    $next_offset = $offset + $batch_size;
    $has_more = $next_offset < $total;

    return [
        'posts' => $slice,
        'has_more' => $has_more,
        'next_offset' => $next_offset, // Útil para el frontend
        'total' => $total
    ];
}
