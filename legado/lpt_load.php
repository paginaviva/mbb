<?php
// lpt_load.php - Handler para carga AJAX de posts por etiqueta (Continuous Scroll)

// Incluir núcleo de LPT
include_once 'includes/lista_posts_tag_core.php';

// Definir constante para uso interno si es necesario (ej. SITE_URL)
// Asumimos que config.php o similar define SITE_URL, si no LPT core ya maneja paths relativos o absolutos para includes.
// Sin embargo, para los enlaces generados necesitamos SITE_URL.
if (!defined('SITE_URL')) {
    // Intentar cargar config si existe, o definir fallback
    if (file_exists('includes/config.php')) include 'includes/config.php';
}

// Parámetros de entrada
$tag_slug = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$offset   = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$search_q = isset($_GET['q']) ? trim($_GET['q']) : null;

// Validación básica
if (empty($tag_slug)) {
    http_response_code(400);
    echo 'Falta etiqueta';
    exit;
}

// Obtener lote
$batch_data = lpt_get_tag_batch($tag_slug, $offset, $search_q);
$posts      = $batch_data['posts'];
$has_more   = $batch_data['has_more'];
$next_offset = $batch_data['next_offset'];

// Renderizar HTML de tarjetas
// Usamos el formato unificado definido en la fase anterior
foreach ($posts as $post) {
    if (!isset($post['id'])) {
        // Enriquecer visualmente si falta algo (aunque lpt_get_tag_batch ya debería haberlo hecho)
    }
    // URL normalizada
    $post_url = $post['url'];
    // Fecha
    $post_date = $post['date'];
    // Título
    $post_title = $post['title'];
    // Subtítulo / Extracto
    $post_subtitle = $post['subtitle'];

?>
    <article class="post-preview border-bottom mb-4 pb-4">
        <h2 class="post-title h3 mb-2 text-dark font-weight-bold">
            <span class="lpt-inline-date"><em><?php echo $post_date; ?></em></span>
            <a href="<?php echo $post_url; ?>" class="text-dark text-decoration-none"><?php echo $post_title; ?></a>
        </h2>
        <a href="<?php echo $post_url; ?>" class="text-decoration-none text-secondary">
            <p class="post-subtitle h5 fw-light mb-0">
                <?php echo $post_subtitle; ?>
            </p>
        </a>
    </article>
<?php
}

// Devolver metadatos en header personalizado o simplemente confiar en que el frontend sabe contar
// Para facilitar "has_more", podemos agregar un elemento oculto o usar un header HTTP
header('X-Has-More: ' . ($has_more ? '1' : '0'));
header('X-Next-Offset: ' . $next_offset);
?>