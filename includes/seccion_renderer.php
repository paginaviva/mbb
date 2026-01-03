<?php
// includes/seccion_renderer.php
// Renderer compartido para secciones temáticas

// Evitar doble inclusión
if (defined('SECCION_RENDERER_LOADED')) return;
define('SECCION_RENDERER_LOADED', true);

// Incluir proveedor de datos y funciones de fecha
require_once __DIR__ . '/home_data_provider.php';

/**
 * Función principal para renderizar una sección completa
 * @param array $config Configuración cargada del archivo .ini de la sección
 */
function render_seccion($config)
{
    global $posts; // Proviene de home_data_provider.php (que incluye posts_manifest.php)

    // --- 1. Preparar Datos Globales y Configuración ---

    // Títulos y SEO (para header_lista_post.php u otros headers si se parametrizan)
    // Nota: header_lista_post.php usa variables globales o constantes? 
    // Al ser un include simple, podemos setear variables antes de incluirlo si este las usa.
    // Sin embargo, lista_posts.php no setea título para el html head específicamente más allá de what header_common hace.
    // Vamos a asumir que el header estándar funciona bien, y personalizaremos el H1 y subtítulo en el body.

    // Configuración de Estilo
    $primary_color = $config['ESTILO']['color_primario'] ?? '#004c99';
    $secondary_color = $config['ESTILO']['color_secundario'] ?? '#0066cc';
    $bg_image = isset($config['CABECERA']['imagen_fondo']) ? '../' . $config['CABECERA']['imagen_fondo'] : ''; // Rutas relativas desde seccion/

    // --- 2. Filtrado de Artículos ---
    $filter_type = $config['FILTRO']['tipo'] ?? 'tag';
    $filter_value = $config['FILTRO']['valor'] ?? '';

    // Obtener todos los posts y ordenar por fecha
    $all_posts = $posts;
    $sorted_posts = sort_posts_by_date($all_posts); // Función de home_data_provider.php

    // Búsqueda local (dentro de la sección)
    $search_query = isset($_GET['q']) ? trim($_GET['q']) : null;

    $final_posts = [];

    // Normalizar valor de filtro
    $filter_value_norm = mb_strtolower(trim($filter_value), 'UTF-8');

    foreach ($sorted_posts as $slug => $post) {
        $include_post = false;

        // A. Verificar pertenencia a la sección (Filtro base)
        if ($filter_type === 'tag') {
            $post_tags_norm = array_map(function ($t) {
                return mb_strtolower(trim($t), 'UTF-8');
            }, $post['tags'] ?? []);
            if (in_array($filter_value_norm, $post_tags_norm)) {
                $include_post = true;
            }
        } elseif ($filter_type === 'category') {
            // Lógica para categorías (array o string)
            if (!empty($post['categories']) && is_array($post['categories'])) {
                foreach ($post['categories'] as $cat) {
                    if (mb_strtolower(trim($cat), 'UTF-8') === $filter_value_norm) {
                        $include_post = true;
                        break;
                    }
                }
            } elseif (!empty($post['category'])) {
                if (mb_strtolower(trim($post['category']), 'UTF-8') === $filter_value_norm) {
                    $include_post = true;
                }
            }
        }

        // B. Aplicar búsqueda si existe (sobre el subset de la sección)
        if ($include_post && $search_query) {
            $match_found = false;
            // 1. Título
            if (mb_stripos($post['title'], $search_query, 0, 'UTF-8') !== false) $match_found = true;
            // 2. Subtítulo
            if (!$match_found && !empty($post['subtitle']) && mb_stripos($post['subtitle'], $search_query, 0, 'UTF-8') !== false) $match_found = true;
            // 3. Contenido (opcional, requiere leer archivo, duplicando lógica de lista_posts.php)
            // Para mantenerlo ligero en SHS, buscamos en contenido también si es necesario, 
            // pero copiamos la función auxiliar search_in_post_content o la usamos si es global.
            // home_data_provider NO tiene search_in_post_content. Está definida en lista_posts.php localmente.
            // La definiremos aquí también como helper local.
            if (!$match_found) {
                // Ruta relativa desde seccion/ hacia post/
                // seccion/round-robin.php -> ../post/slug.php
                $local_path = __DIR__ . '/../post/' . $slug . '.php';
                if (seccion_search_in_content($local_path, $search_query)) {
                    $match_found = true;
                }
            }

            if (!$match_found) $include_post = false;
        }

        if ($include_post) {
            // Asegurar URL absoluta o correcta
            if (!isset($post['id'])) $post['id'] = substr(md5($slug), 0, 8);
            $post['slug'] = $slug;
            $final_posts[] = $post;
        }
    }

    // --- 3. Renderizado de la Página ---

    // Config variables for header_secciones.php (based on header_common.php)
    $site_h1 = $config['CABECERA']['titulo'];
    $site_subheading = $config['CABECERA']['subtitulo'] ?? '';
    $masthead_bg = isset($config['CABECERA']['imagen_fondo']) ? SITE_URL . $config['CABECERA']['imagen_fondo'] : '';

    // SEO
    $page_title = $config['SEO']['meta_title'] ?? $site_h1;
    $page_description = $config['SEO']['meta_description'] ?? '';

    // OG Tags
    $og_title = $page_title;
    $og_description = $page_description;
    if ($masthead_bg) {
        $og_image = $masthead_bg;
    }

    // Incluir Header Especifico de Secciones
    require_once __DIR__ . '/../header_secciones.php';

?>
    <!-- Inyección de Estilos de Sección -->
    <style>
        :root {
            --seccion-primary: <?php echo $primary_color; ?>;
            --seccion-secondary: <?php echo $secondary_color; ?>;
        }

        /* Override de estilos base para esta sección */

        /* Ajuste de colores en elementos */
        .text-primary-section {
            color: var(--seccion-primary) !important;
        }

        .btn-primary-section {
            background-color: var(--seccion-primary);
            border-color: var(--seccion-primary);
            color: white;
        }

        .btn-primary-section:hover {
            background-color: var(--seccion-secondary);
            border-color: var(--seccion-secondary);
        }

        .sidebar-link {
            display: block;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }

        .sidebar-link:hover {
            background-color: #f8f9fa;
            color: var(--seccion-primary);
            padding-left: 20px;
        }

        .sidebar-link.active {
            font-weight: bold;
            color: var(--seccion-primary);
            border-left: 4px solid var(--seccion-primary);
        }
    </style>

    <div class="container my-5">
        <div class="row">
            <!-- Columna Izquierda: Listado -->
            <div class="col-lg-8">

                <!-- Buscador Local -->
                <div class="mb-4 bg-light p-3 rounded">
                    <form action="" method="GET" class="d-flex">
                        <input class="form-control me-2" type="search" name="q"
                            placeholder="Buscar en <?php echo htmlspecialchars($config['IDENTIDAD']['nombre']); ?>..."
                            value="<?php echo htmlspecialchars($search_query ?? ''); ?>">
                        <button class="btn btn-primary-section" type="submit">Buscar</button>
                    </form>
                </div>

                <?php if ($search_query): ?>
                    <h3 class="mb-4 fs-4 border-bottom pb-2">
                        Resultados para: <em>"<?php echo htmlspecialchars($search_query); ?>"</em>
                        <small class="ms-2"><a href="?" class="text-decoration-none text-muted fs-6"><i class="fas fa-times"></i> Limpiar</a></small>
                    </h3>
                <?php endif; ?>

                <div class="posts-list">
                    <?php if (empty($final_posts)): ?>
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                            <h4>No se encontraron artículos</h4>
                            <p class="mb-0">No hay contenido disponible con estos criterios en esta sección.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($final_posts as $post): ?>
                            <article class="post-item mb-5 border-bottom pb-4">
                                <div class="post-header">
                                    <h3 class="post-title h4 mb-2">
                                        <a href="../<?php echo $post['url']; ?>"
                                            class="text-decoration-none text-dark hover-primary">
                                            <?php echo $post['title']; ?>
                                        </a>
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
                                            <span class="badge bg-light text-dark fw-normal border me-1"><?php echo $t; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna Derecha: Panel Lateral Centralizado -->
            <div class="col-lg-4">
                <div class="sidebar-sticky ps-lg-4">
                    <?php render_panel_lateral($config); ?>
                </div>
            </div>
        </div>
    </div>

<?php
    // Incluir Footer Global
    require_once __DIR__ . '/../footer.php';
}

/**
 * Renderiza el panel lateral usando configuración global
 */
function render_panel_lateral($section_config)
{
    // Cargar config global
    $global_sidebar_path = __DIR__ . '/../config/sidebar_global.ini';
    $sidebar_config = parse_ini_file($global_sidebar_path, true);

    if (!$sidebar_config) {
        echo '<div class="alert alert-danger">Error cargando panel lateral</div>';
        return;
    }

    $titulo = $section_config['PANEL_LATERAL']['titulo'] ?? $sidebar_config['GLOBAL']['titulo_defecto'];

    echo '<div class="sidebar-section mb-4">';
    echo '<h5 class="fw-bold border-bottom pb-2 mb-3">' . htmlspecialchars($titulo) . '</h5>';
    echo '<div class="sidebar-links">';

    if (isset($sidebar_config['ENLACES']['enlace']) && is_array($sidebar_config['ENLACES']['enlace'])) {
        foreach ($sidebar_config['ENLACES']['enlace'] as $link_def) {
            // Formato: "Texto|URL"
            $parts = explode('|', $link_def);
            if (count($parts) >= 2) {
                $text = $parts[0];
                $url = $parts[1];

                // Ajuste de rutas: Usar SITE_URL para todo enlace interno
                $final_url = $url;
                if (!preg_match('/^https?:\/\//', $url)) {
                    // Si el URL no es absoluto, prepend SITE_URL
                    // Asegurar que no haya doble slash si $url empieza con slash
                    $url_path = ltrim($url, '/');
                    $final_url = SITE_URL . $url_path;
                }

                $is_active = ($text === $section_config['IDENTIDAD']['nombre']);
                $active_class = $is_active ? 'active' : '';

                echo '<a href="' . htmlspecialchars($final_url) . '" class="sidebar-link ' . $active_class . '">';
                echo htmlspecialchars($text);
                echo '</a>';
            }
        }
    }

    echo '</div>';
    echo '</div>';
}

/**
 * Búsqueda simple en texto plano de contenido
 */
function seccion_search_in_content($filepath, $query)
{
    if (!file_exists($filepath)) return false;
    $content = file_get_contents($filepath);
    if (preg_match('/<article[^>]*>(.*?)<\/article>/s', $content, $matches)) {
        $content = $matches[1];
    }
    $text = strip_tags($content);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return mb_stripos($text, $query, 0, 'UTF-8') !== false;
}
?>