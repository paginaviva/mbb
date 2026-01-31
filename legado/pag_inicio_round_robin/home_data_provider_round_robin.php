<?php
// Extracto: lógica Round Robin extraída de includes/home_data_provider.php
// Archivo pensado para uso independiente en pag_inicio_round_robin/

/**
 * Convenciones: $posts es un array asociativo [$slug => $postArray]
 * Función principal: get_round_robin_group($posts, $tag = 'resumen diario round robin', $latest=2, $others=4)
 */

function parse_spanish_date($date_string) {
    $meses = [
        'enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04',
        'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08',
        'septiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12',
        'November' => '11'
    ];

    $parts = explode(' ', $date_string);
    $day = 0; $month = 0; $year = 0;

    foreach ($parts as $part) {
        if (is_numeric($part)) {
            if ($part > 31) $year = $part;
            else $day = $part;
        } else {
            $clean_part = trim(str_replace('de', '', $part));
            if (isset($meses[$clean_part])) $month = $meses[$clean_part];
        }
    }

    if ($day && $month && $year) return strtotime("$year-$month-$day");
    return 0;
}

function sort_posts_by_date($posts) {
    uasort($posts, function($a, $b) {
        $tA = parse_spanish_date($a['date']);
        $tB = parse_spanish_date($b['date']);
        return $tB - $tA;
    });
    return $posts;
}

function enrich_post($post, $slug) {
    if (!isset($post['id'])) $post['id'] = substr(md5($slug), 0, 8);
    $post['slug'] = $slug;
    if (!isset($post['url']) || empty($post['url'])) $post['url'] = 'post/' . $slug . '.php';
    if (!isset($post['category']) || empty($post['category'])) {
        if (isset($post['tags']) && is_array($post['tags']) && !empty($post['tags'])) {
            $post['category'] = $post['tags'][0];
        } else {
            $post['category'] = 'General';
        }
    }
    if (!isset($post['categories']) || !is_array($post['categories'])) $post['categories'] = [$post['category']];
    return $post;
}

function get_round_robin_group($posts, $tag = 'resumen diario round robin', $latest = 2, $others = 4) {
    // Ordenar
    $sorted = sort_posts_by_date($posts);

    $group = [];
    foreach ($sorted as $slug => $post) {
        $post = enrich_post($post, $slug);
        if (isset($post['tags']) && is_array($post['tags'])) {
            $tags_norm = array_map(function($t) { return mb_strtolower(trim($t), 'UTF-8'); }, $post['tags']);
            if (in_array(mb_strtolower($tag, 'UTF-8'), $tags_norm)) $group[] = $post;
        }
    }

    return [
        'latest_two' => array_slice($group, 0, $latest),
        'others'     => array_slice($group, $latest, $others)
    ];
}

// Ejemplo de uso (descomentarlo cuando se tenga acceso a $posts):
// $rr = get_round_robin_group($posts);
// print_r($rr['latest_two']);
// print_r($rr['others']);

?>