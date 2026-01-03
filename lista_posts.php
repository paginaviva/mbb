<?php
// lista_posts.php - Listado general con búsqueda y filtros avanzados

include 'header_lista_post.php';
include 'includes/home_data_provider.php';

// Configuración de zona horaria
date_default_timezone_set('America/Caracas');

// 1. Obtener todos los posts del manifiesto (home_data_provider ya incluye posts_manifest.php)
// Usamos la lista cruda pero ordenada por fecha
$all_posts = $posts; 
$sorted_posts = sort_posts_by_date($all_posts);

// 2. Parámetros de entrada
$filter_tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
$filter_category = isset($_GET['category']) ? trim($_GET['category']) : null;
$filter_month = isset($_GET['month']) ? trim($_GET['month']) : null; // Formato esperado: "m-Y" (ej: "12-2025")
$search_query = isset($_GET['q']) ? trim($_GET['q']) : null;

// Normalización para comparación
$filter_tag_norm = $filter_tag ? mb_strtolower($filter_tag, 'UTF-8') : null;
$filter_category_norm = $filter_category ? mb_strtolower($filter_category, 'UTF-8') : null;

// 3. Procesamiento y Filtrado
$final_posts = [];
$months_list = []; // Coleccionar meses para el sidebar
$categories_list = []; // Coleccionar categorías para el sidebar
$all_tags_raw = []; // Coleccionar todos los tags para clasificación

// Función auxiliar para buscar en contenido del archivo
function search_in_post_content($filepath, $query) {
    if (!file_exists($filepath)) return false;
    
    $content = file_get_contents($filepath);
    
    // Extraer solo el contenido dentro de <article> si es posible, o todo el body
    // Para simplificar y ser robusto, limpiamos tags y buscamos en texto plano
    // Pero primero intentamos aislar el contenido principal para evitar falsos positivos en headers/footers
    if (preg_match('/<article[^>]*>(.*?)<\/article>/s', $content, $matches)) {
        $content = $matches[1];
    }
    
    $text = strip_tags($content);
    // Convertir entidades HTML
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Búsqueda case-insensitive
    return mb_stripos($text, $query, 0, 'UTF-8') !== false;
}

foreach ($sorted_posts as $slug => $post) {
    // Asegurar datos mínimos
    if (!isset($post['id'])) $post['id'] = substr(md5($slug), 0, 8);
    $post['slug'] = $slug;
    
    // Recolectar datos para sidebar (de TODOS los posts, independientemente del filtro actual)
    
    // 1. Fecha / Mes
    if (!empty($post['date'])) {
        $ts = parse_spanish_date($post['date']);
        if ($ts) {
            $month_key = date('m-Y', $ts);
            $month_label = get_spanish_month_name(date('n', $ts)) . ' ' . date('Y', $ts);
            if (!isset($months_list[$month_key])) {
                $months_list[$month_key] = ['label' => $month_label, 'ts' => $ts];
            }
        }
    }
    
    // 2. Categoría
    if (!empty($post['categories']) && is_array($post['categories'])) {
        foreach ($post['categories'] as $cat) {
            $cat = trim($cat);
            if ($cat) $categories_list[$cat] = $cat;
        }
    } elseif (!empty($post['category'])) {
        // Fallback
        $cat = trim($post['category']);
        if ($cat) $categories_list[$cat] = $cat; 
    }
    
    // 3. Tags
    if (!empty($post['tags']) && is_array($post['tags'])) {
        foreach ($post['tags'] as $t) {
            $t = trim($t);
            if ($t) $all_tags_raw[$t] = $t;
        }
    }

    // --- LÓGICA DE FILTRADO ---
    $include_post = true;

    // A. Filtro por Buscador
    if ($search_query) {
        $match_found = false;
        // 1. Buscar en Título
        if (mb_stripos($post['title'], $search_query, 0, 'UTF-8') !== false) $match_found = true;
        // 2. Buscar en Subtítulo
        if (!$match_found && !empty($post['subtitle']) && mb_stripos($post['subtitle'], $search_query, 0, 'UTF-8') !== false) $match_found = true;
        // 3. Buscar en Contenido (Archivo físico)
        if (!$match_found) {
            // Extraer path relativo del URL o construirlo
            // post['url'] es full URL. Necesitamos path local.
            // Asumimos estructura estándar: /post/slug.php
            $local_path = __DIR__ . '/post/' . $slug . '.php';
            if (search_in_post_content($local_path, $search_query)) {
                $match_found = true;
            }
        }
        
        if (!$match_found) $include_post = false;
    }

    // B. Filtro por Tag
    if ($include_post && $filter_tag_norm) {
        $post_tags_norm = array_map(function($t) { return mb_strtolower(trim($t), 'UTF-8'); }, $post['tags'] ?? []);
        if (!in_array($filter_tag_norm, $post_tags_norm)) {
            $include_post = false;
        }
    }

    // C. Filtro por Categoría
    if ($include_post && $filter_category_norm) {
        $found_cat = false;
        
        if (!empty($post['categories']) && is_array($post['categories'])) {
            // Buscar en array de categorías
            foreach ($post['categories'] as $cat) {
                if (mb_strtolower(trim($cat), 'UTF-8') === $filter_category_norm) {
                    $found_cat = true;
                    break;
                }
            }
        } elseif (!empty($post['category'])) {
            // Fallback antigua variable
            if (mb_strtolower(trim($post['category']), 'UTF-8') === $filter_category_norm) {
                $found_cat = true;
            }
        }
        
        if (!$found_cat) {
            $include_post = false;
        }
    }

    // D. Filtro por Mes
    if ($include_post && $filter_month) {
        $ts = parse_spanish_date($post['date'] ?? '');
        if ($ts) {
            $post_month = date('m-Y', $ts);
            if ($post_month !== $filter_month) {
                $include_post = false;
            }
        } else {
            $include_post = false; // Si no tiene fecha válida, no pasa filtro de fecha
        }
    }

    if ($include_post) {
        $final_posts[] = $post;
    }
}

// 4. Preparar Sidebar

// A. Meses (Ordenar descendente por fecha)
uasort($months_list, function($a, $b) {
    return $b['ts'] - $a['ts'];
});

// B. Categorías (Ordenar alfabético)
sort($categories_list, SORT_STRING | SORT_FLAG_CASE);

// C. Clasificación de Tags (Jugadores vs Resto)
// Listas de exclusión conocidas (Equipos y Especiales)
$teams_list = [
    'Águilas del Zulia', 'Bravos de Margarita', 'Tiburones de La Guaira', 'Caribes de Anzoátegui',
    'Navegantes del Magallanes', 'Tigres de Aragua', 'Leones del Caracas', 'Cardenales de Lara'
];
$special_tags = ['Artículos Destacados', 'Jugador de la Semana', 'Resumen Semanal', 'Resumen Diario'];

// Cargar lista de ignorados
$ignored_tags_file = __DIR__ . '/no_son_tags.php';
$ignored_tags = file_exists($ignored_tags_file) ? include $ignored_tags_file : [];

// Normalizar listas de exclusión para comparación
$exclude_from_players = array_map('mb_strtolower', array_merge($teams_list, $special_tags, $ignored_tags));

$player_tags = [];
$other_tags = [];

foreach ($all_tags_raw as $tag) {
    $tag_norm = mb_strtolower($tag, 'UTF-8');
    
    // Si está en ignorados, no lo mostramos en NINGÚN lado (según lógica previa de no_son_tags)
    // OJO: El requerimiento dice "Resto de etiquetas: Muestra todas las demás...". 
    // Asumiremos que "ignorados" son basura y no se muestran.
    if (in_array($tag_norm, array_map('mb_strtolower', $ignored_tags))) {
        continue;
    }

    // Clasificar
    if (in_array($tag_norm, array_map('mb_strtolower', $teams_list)) || in_array($tag_norm, array_map('mb_strtolower', $special_tags))) {
        // Es Equipo o Especial -> Resto
        $other_tags[] = $tag;
    } else {
        // Asumimos Jugador -> Jugadores
        $player_tags[] = $tag;
    }
}

sort($player_tags, SORT_STRING | SORT_FLAG_CASE);
sort($other_tags, SORT_STRING | SORT_FLAG_CASE);

// Helper para meses en español
function get_spanish_month_name($month_num) {
    $months = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
    return $months[$month_num] ?? '';
}

?>

<!-- Estilos específicos -->
<link href="css/lista_posts.css" rel="stylesheet" />
<style>
    /* Estilos inline para ajustes rápidos de nuevos elementos */
    .sidebar-section { margin-bottom: 2rem; }
    .sidebar-section h5 { 
        font-size: 1.1rem; 
        border-bottom: 2px solid #eee; 
        padding-bottom: 0.5rem; 
        margin-bottom: 1rem; 
        font-weight: 700;
        color: #333;
    }
    .tag-cloud a {
        display: inline-block;
        font-size: 0.85rem;
        padding: 2px 8px;
        margin: 0 4px 6px 0;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        color: #555;
        text-decoration: none;
        transition: all 0.2s;
    }
    .tag-cloud a:hover, .tag-cloud a.active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .search-box {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
</style>

<div class="container my-5">
    <div class="row">
        <!-- Columna Izquierda: Listado -->
        <div class="col-lg-8">
            
            <!-- Buscador -->
            <div class="search-box">
                <form action="lista_posts.php" method="GET" class="d-flex">
                    <input class="form-control me-2" type="search" name="q" placeholder="Buscar en artículos..." aria-label="Buscar" value="<?php echo htmlspecialchars($search_query ?? ''); ?>">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                    <!-- Mantener otros filtros si se desea, por ahora búsqueda limpia -->
                </form>
            </div>

            <!-- Título del Listado -->
            <h1 class="mb-4 fs-2 border-bottom pb-3">
                <?php if ($search_query): ?>
                    Resultados para: <em class="text-primary">"<?php echo htmlspecialchars($search_query); ?>"</em>
                <?php elseif ($filter_tag): ?>
                    Etiqueta: <span class="text-primary"><?php echo htmlspecialchars($filter_tag); ?></span>
                <?php elseif ($filter_category): ?>
                    Categoría: <span class="text-primary"><?php echo htmlspecialchars($filter_category); ?></span>
                <?php elseif ($filter_month): ?>
                    Archivo: <span class="text-primary"><?php echo htmlspecialchars($months_list[$filter_month]['label'] ?? $filter_month); ?></span>
                <?php else: ?>
                    Todos los Artículos
                <?php endif; ?>
            </h1>

            <div class="posts-list">
                <?php if (empty($final_posts)): ?>
                    <div class="alert alert-warning" role="alert">
                        No se encontraron artículos que coincidan con tu búsqueda.
                    </div>
                    <?php if ($search_query): ?>
                        <div class="mt-3">
                            <a href="lista_posts.php" class="btn btn-outline-secondary">Ver todos los artículos</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-4"><?php echo count($final_posts); ?> artículos encontrados</p>
                    
                    <?php foreach ($final_posts as $post): ?>
                        <article class="post-item mb-5">
                            <div class="post-header">
                                <h3 class="post-title h4 mb-2">
                                    <a href="<?php echo $post['url']; ?>" target="_blank" class="text-decoration-none text-dark hover-primary"><?php echo $post['title']; ?></a>
                                </h3>
                                <div class="post-meta text-muted small mb-2">
                                    <span class="me-3"><i class="far fa-calendar-alt me-1"></i> <?php echo $post['date']; ?></span>
                                    <?php if (!empty($post['category'])): ?>
                                        <span class="me-3"><i class="far fa-folder me-1"></i> <?php echo $post['category']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="post-excerpt">
                                <p class="mb-0 text-secondary"><?php echo $post['subtitle']; ?></p>
                            </div>
                            <?php if (!empty($post['tags'])): ?>
                                <div class="post-tags mt-2">
                                    <?php foreach (array_slice($post['tags'], 0, 3) as $t): ?>
                                        <span class="badge bg-light text-dark fw-normal border"><?php echo $t; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna Derecha: Sidebar Reorganizado -->
        <div class="col-lg-4">
            <div class="sidebar-sticky ps-lg-4">
                
                <?php if ($filter_tag || $filter_category || $filter_month || $search_query): ?>
                    <div class="mb-4">
                        <a href="lista_posts.php" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-times me-1"></i> Limpiar Filtros</a>
                    </div>
                <?php endif; ?>

                <!-- 1. Archivo por Meses -->
                <div class="sidebar-section">
                    <h5>Archivo</h5>
                    <div class="list-group list-group-flush">
                        <?php foreach ($months_list as $m_key => $data): ?>
                            <a href="lista_posts.php?month=<?php echo $m_key; ?>" 
                               class="list-group-item list-group-item-action px-0 py-2 border-0 <?php echo ($filter_month === $m_key) ? 'fw-bold text-primary' : ''; ?>">
                                <?php echo $data['label']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 2. Categorías -->
                <div class="sidebar-section">
                    <h5>Categorías</h5>
                    <div class="list-group list-group-flush">
                        <?php foreach ($categories_list as $cat): ?>
                            <a href="lista_posts.php?category=<?php echo urlencode($cat); ?>" 
                               class="list-group-item list-group-item-action px-0 py-2 border-0 <?php echo ($filter_category === $cat) ? 'fw-bold text-primary' : ''; ?>">
                                <?php echo $cat; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Etiquetas de Jugadores -->
                <?php if (!empty($player_tags)): ?>
                    <div class="sidebar-section">
                        <h5>Jugadores</h5>
                        <div class="tag-cloud">
                            <?php foreach ($player_tags as $tag): ?>
                                <a href="lista_posts.php?tag=<?php echo urlencode($tag); ?>" 
                                   class="<?php echo ($filter_tag === $tag) ? 'active' : ''; ?>">
                                    <?php echo $tag; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 4. Resto de Etiquetas -->
                <?php if (!empty($other_tags)): ?>
                    <div class="sidebar-section">
                        <h5>Otras Etiquetas</h5>
                        <div class="tag-cloud">
                            <?php foreach ($other_tags as $tag): ?>
                                <a href="lista_posts.php?tag=<?php echo urlencode($tag); ?>" 
                                   class="<?php echo ($filter_tag === $tag) ? 'active' : ''; ?>">
                                    <?php echo $tag; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
