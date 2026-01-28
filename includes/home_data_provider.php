<?php
// includes/home_data_provider.php

// Evitar acceso directo si es necesario, o simplemente incluir el manifiesto
// Asumimos que este archivo se incluye desde index.php o home_nueva.php, que están en la raíz.
// Si se llama directamente para pruebas, ajustar path.

if (file_exists(__DIR__ . '/../posts_manifest.php')) {
    include __DIR__ . '/../posts_manifest.php';
} elseif (file_exists('posts_manifest.php')) {
    include 'posts_manifest.php';
} else {
    // Fallback para pruebas o si la ruta falla
    $posts = []; 
}

/**
 * Parsea fechas en formato "24 de noviembre de 2025" a Timestamp
 */
function parse_spanish_date($date_string) {
    $meses = [
        'enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04',
        'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08',
        'septiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12',
        'November' => '11' // Fix para typo visto en el manifest "24 de November de 2025"
    ];

    // Normalizar string (quitar "de ", espacios extra)
    // Formato esperado: "dd de mes de yyyy"
    $parts = explode(' ', $date_string);
    
    // Buscar el mes en las partes
    $day = 0;
    $month = 0;
    $year = 0;

    foreach ($parts as $part) {
        if (is_numeric($part)) {
            if ($part > 31) $year = $part;
            else $day = $part;
        } else {
            $clean_part = trim(str_replace('de', '', $part));
            if (isset($meses[$clean_part])) {
                $month = $meses[$clean_part];
            }
        }
    }

    if ($day && $month && $year) {
        return strtotime("$year-$month-$day");
    }
    return 0; // Fallback
}

/**
 * Ordena posts por fecha descendente
 */
function sort_posts_by_date($posts) {
    uasort($posts, function($a, $b) {
        $tA = parse_spanish_date($a['date']);
        $tB = parse_spanish_date($b['date']);
        return $tB - $tA;
    });
    return $posts;
}

/**
 * Normaliza un string para comparación (sin acentos, minúsculas)
 */
function normalize_string_for_comparison($str) {
    // Convertir a minúsculas
    $str = mb_strtolower($str, 'UTF-8');
    
    // Eliminar acentos
    $unwanted_array = array(
        'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
        'Á'=>'a', 'É'=>'e', 'Í'=>'i', 'Ó'=>'o', 'Ú'=>'u',
        'ñ'=>'n', 'Ñ'=>'n'
    );
    $str = strtr($str, $unwanted_array);
    
    // Eliminar espacios extra
    $str = trim($str);
    
    return $str;
}

/**
 * Enriquece un post con campos derivados necesarios para la vista
 */
function enrich_post($post, $slug) {
    // Asegurar ID
    if (!isset($post['id'])) {
        $post['id'] = substr(md5($slug), 0, 8);
    }
    
    // Asegurar slug
    $post['slug'] = $slug;
    
    // Generar URL si no existe
    if (!isset($post['url']) || empty($post['url'])) {
        $post['url'] = 'post/' . $slug . '.php';
    }
    
    // Generar categoría si no existe
    if (!isset($post['category']) || empty($post['category'])) {
        // Extraer categoría del primer tag si existe
        if (isset($post['tags']) && is_array($post['tags']) && !empty($post['tags'])) {
            $post['category'] = $post['tags'][0];
        } else {
            $post['category'] = 'General';
        }
    }
    
    // Generar array de categorías si no existe (polyfill)
    if (!isset($post['categories']) || !is_array($post['categories'])) {
        $post['categories'] = [$post['category']];
    }
    
    return $post;
}

/**
 * Obtiene los datos estructurados para la Home
 */
function get_home_data() {
    global $posts;
    
    // 1. Ordenar todos los posts por fecha
    $sorted_posts = sort_posts_by_date($posts);
    
    // Estructura de retorno
    $data = [
        'block_a' => ['latest' => null, 'others' => []], // La jornada de ayer
        'block_b' => [], // Lo más reciente (2)
        'block_c' => [], // Últimos artículos (4)
        'block_d' => [], // Resúmenes liga
        'block_e' => [], // Historias destacadas
    ];

    // --- Lógica Bloque B y C (Breaking News) ---
    // Requerimiento v3: Total 10 artículos en "Lo más reciente". 
    // TODOS los artículos vienen de la selección manual del Kanban (10 items).

    $final_10_list = [];
    $manual_selection_file = __DIR__ . '/home_featured.json';

    if (file_exists($manual_selection_file)) {
        $selection_data = json_decode(file_get_contents($manual_selection_file), true);
        if (isset($selection_data['ids']) && is_array($selection_data['ids'])) {
            // Indexar posts
            $posts_by_id = [];
            foreach ($sorted_posts as $slug => $post) {
                $post = enrich_post($post, $slug);
                $posts_by_id[$post['id']] = $post;
            }

            // Recuperar hasta 10 artículos
            foreach ($selection_data['ids'] as $id) {
                if (isset($posts_by_id[$id])) {
                    $final_10_list[] = $posts_by_id[$id];
                }
                if (count($final_10_list) >= 10) break;
            }
        }
    }

    // Rellenar con posts cronológicos si faltan (hasta llegar a 10)
    if (count($final_10_list) < 10) {
        foreach ($sorted_posts as $slug => $post) {
            if (count($final_10_list) >= 10) break;

            $post = enrich_post($post, $slug);
            
            // Chequear duplicados
            $already_in = false;
            foreach ($final_10_list as $existing) {
                if ($existing['id'] === $post['id']) {
                    $already_in = true;
                    break;
                }
            }
            if ($already_in) continue;

            $final_10_list[] = $post;
        }
    }

    // 5. Asignar a Bloques
    // Block B: Items 0 y 1 (2 items)
    // Block C: Items 2 al 9 (8 items)
    
    $data['block_b'] = array_slice($final_10_list, 0, 2);
    $data['block_c'] = array_slice($final_10_list, 2, 8); 

    // --- Lógica Bloque D (Resúmenes) y E (Destacados) ---
    // Filtramos por tags. No importa si ya salieron en B/C (o sí? Por ahora permitimos duplicados si son destacados)
    foreach ($sorted_posts as $slug => $post) {
        // Enriquecer post
        $post = enrich_post($post, $slug);
        
        // Check tags
        if (isset($post['tags']) && is_array($post['tags'])) {
            // Normalizar tags a minúsculas para búsqueda (UTF-8 safe)
            $tags_norm = array_map(function($t) { return mb_strtolower(trim($t), 'UTF-8'); }, $post['tags']);
            
            if (in_array('resumen semanal', $tags_norm)) {
                if (count($data['block_d']) < 7) {
                    $data['block_d'][] = $post;
                }
            }
            
            // Match para destacados: "destacados", "destacado", "historia", "artículos destacados"
            if (in_array('destacados', $tags_norm) || 
                in_array('destacado', $tags_norm) || 
                in_array('historia', $tags_norm) || 
                in_array('artículos destacados', $tags_norm) || 
                in_array('articulos destacados', $tags_norm)) { 
                if (count($data['block_e']) < 7) {
                    $data['block_e'][] = $post;
                }
            }
        }
    }

    // --- Lógica Bloque A (La Final) ---
    // Obtener posts con etiqueta "resumen diario la final"
    // - latest_two: Los 2 más recientes para "La Final Ayer"
    // - others: Los siguientes 4 para "Otros Juegos La Final"
    $la_final_summaries = [];
    foreach ($sorted_posts as $slug => $post) {
        $post = enrich_post($post, $slug);
        if (isset($post['tags']) && is_array($post['tags'])) {
            $tags_norm = array_map(function($t) { 
                return mb_strtolower(trim($t), 'UTF-8'); 
            }, $post['tags']);
            
            if (in_array('resumen diario la final', $tags_norm) || in_array('resumen diario round robin', $tags_norm)) {
                $la_final_summaries[] = $post;
            }
        }
    }
    
    // Separar en 2 grupos
    $data['block_a']['latest_two'] = array_slice($la_final_summaries, 0, 2);
    $data['block_a']['others'] = array_slice($la_final_summaries, 2, 4);

    // --- Lógica Artículo Patrocinado ---
    // Buscar posts con etiqueta "Artículo Patrocinado" (comparación normalizada)
    $sponsored_candidates = [];
    $target_tag_normalized = normalize_string_for_comparison('Artículo Patrocinado');
    
    foreach ($sorted_posts as $slug => $post) {
        if (isset($post['tags']) && is_array($post['tags'])) {
            foreach ($post['tags'] as $tag) {
                $tag_normalized = normalize_string_for_comparison($tag);
                if ($tag_normalized === $target_tag_normalized) {
                    // Enriquecer el post antes de agregarlo
                    $sponsored_candidates[] = enrich_post($post, $slug);
                    break; // Ya encontramos la etiqueta en este post
                }
            }
        }
    }
    
    // Seleccionar el más reciente si hay candidatos
    if (!empty($sponsored_candidates)) {
        // Ordenar candidatos por fecha descendente
        usort($sponsored_candidates, function($a, $b) {
            $tA = parse_spanish_date($a['date']);
            $tB = parse_spanish_date($b['date']);
            return $tB - $tA;
        });
        
        // El primero es el más reciente
        $data['sponsored_post'] = $sponsored_candidates[0];
    } else {
        $data['sponsored_post'] = null;
    }
    
    // Etiqueta literal para mostrar en la vista
    $data['sponsored_label'] = 'Artículo Patrocinado';

    return $data;
}

function date_to_human_es($date_ymd) {
    $ts = strtotime($date_ymd);
    $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $d = date('j', $ts);
    $m = (int)date('n', $ts);
    return "$d " . $meses[$m];
}
