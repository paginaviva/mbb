<?php
// includes/lista_posts_tag_mobile.php
// Plantilla MÓVIL para LPT

// Asegurar que tenemos acceso a las funciones del núcleo y modelos de datos
if (!function_exists('lpt_get_ordered_menu_list')) {
    include_once 'lista_posts_tag_core.php';
}
// Incluimos el proveedor de datos para tener acceso a $posts y funciones de ordenamiento
// Asumimos que estamos en directorio includes, pero 'home_data_provider.php' carga 'posts_manifest.php'
// y helper functions.
include_once __DIR__ . '/home_data_provider.php';

// Obtener lista de botones para los bloques de navegación
$menu_tags = lpt_get_ordered_menu_list();
$active_slug = $tag_data['slug'] ?? '';

// Incluir cabecera específica (ya genera el HTML head, body, menu y header image)
include 'header_lista_posts_tag.php';
?>
<!-- Inclusión de estilos compartidos -->
<link href="css/lista_posts.css" rel="stylesheet" />


<!-- Estilos específicos para LPT Móvil (Distribución) -->
<style>
    /* Contenedor de botones de etiquetas móvil */
    .lpt-tags-block {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        padding: 15px 10px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .lpt-tags-block.bottom {
        border-top: 1px solid #e9ecef;
        border-bottom: none;
        margin-top: 20px;
    }
</style>

<!-- 1. Primer bloque de botones de etiquetas (Superior) -->
<div class="lpt-tags-block">
    <?php foreach ($menu_tags as $slug => $info): ?>
        <?php
        $is_active = ($slug === $active_slug);
        $active_class = $is_active ? 'lpt-tag-button-active' : '';
        $url = $info['url']; // URL SEF absoluta
        ?>
        <a href="<?php echo $url; ?>" class="lpt-tag-button <?php echo $active_class; ?>">
            <?php echo htmlspecialchars($info['text']); ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Estructura del contenedor principal del listado -->
<div class="container px-3 my-4">
    <!-- Formulario simplificado móvil -->
    <div class="mb-4">
        <form action="<?php echo SITE_URL; ?>lpt.php" method="GET" class="d-flex">
            <input type="hidden" name="tag" value="<?php echo htmlspecialchars($active_slug); ?>">
            <div class="input-group">
                <input class="form-control" type="search" name="q" placeholder="Buscar..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
            <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                <a href="<?php echo SITE_URL; ?>lpt.php?tag=<?php echo urlencode($active_slug); ?>" class="btn btn-link text-secondary ms-1 p-0 d-flex align-items-center"><i class="fas fa-times fa-lg"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <?php
    $search_q = isset($_GET['q']) ? trim($_GET['q']) : null;
    // Obtener lote usando el núcleo con búsqueda
    $batch_data = lpt_get_tag_batch($active_slug, 0, $search_q);
    $filtered_posts = $batch_data['posts'];
    $has_more = $batch_data['has_more'];
    ?>

    <?php if (empty($filtered_posts)): ?>
        <div class="alert alert-light text-center" role="alert">
            <h4 class="alert-heading"><i class="fas fa-info-circle"></i></h4>
            <p><?php echo htmlspecialchars($tag_data['msg'] ?? 'Sin noticias de momento'); ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($filtered_posts as $post): ?>
            <article class="post-preview border-bottom mb-4 pb-4">
                <h2 class="post-title h3 mb-2 text-dark font-weight-bold">
                    <span class="lpt-inline-date"><em><?php echo $post['date']; ?></em></span>
                    <a href="<?php echo $post['url']; ?>" class="text-dark text-decoration-none"><?php echo $post['title']; ?></a>
                </h2>
                <a href="<?php echo $post['url']; ?>" class="text-decoration-none text-secondary">
                    <p class="post-subtitle h5 fw-light mb-0">
                        <?php echo $post['subtitle']; ?>
                    </p>
                </a>
            </article>
        <?php endforeach; ?>

        <!-- Elemento centinela para Infinite Scroll -->
        <div id="lpt-sentinel-mobile" class="text-center py-4" style="display: <?php echo $has_more ? 'block' : 'none'; ?>;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- Script de Scroll Infinito (Móvil) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sentinel = document.getElementById('lpt-sentinel-mobile');
        // En móvil el contenedor es .container (donde están los posts) o mejor usamos el parent del sentinel
        // Pero necesitamos apender al final del listado, antes del sentinel
        // EL sentinel está DENTRO del container, al final.
        // Así que insertAdjacentHTML('beforebegin', html) sobre el sentinel funcionaría, 
        // o append al container pero el sentinel quedaría al final.

        // Mejor estrategia: Insertar antes del sentinel.

        // Estado inicial desde PHP
        let offset = <?php echo isset($batch_data['next_offset']) ? $batch_data['next_offset'] : 6; ?>;
        let hasMore = <?php echo isset($has_more) && $has_more ? 'true' : 'false'; ?>;
        let isLoading = false;

        // Parámetros de contexto
        const tagSlug = "<?php echo isset($active_slug) ? htmlspecialchars($active_slug) : ''; ?>";
        const searchQuery = "<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>";

        if (!sentinel || !hasMore) return;

        const loadMore = () => {
            if (isLoading || !hasMore) return;
            isLoading = true;

            // Construir URL
            let url = `lpt_load.php?tag=${encodeURIComponent(tagSlug)}&offset=${offset}`;
            if (searchQuery) {
                url += `&q=${encodeURIComponent(searchQuery)}`;
            }

            fetch(url)
                .then(response => {
                    const nextHasMore = response.headers.get('X-Has-More') === '1';
                    const nextOffsetVal = parseInt(response.headers.get('X-Next-Offset'));

                    hasMore = nextHasMore;
                    if (!isNaN(nextOffsetVal)) offset = nextOffsetVal;

                    return response.text();
                })
                .then(html => {
                    if (html.trim().length > 0) {
                        // Insertar nuevas tarjetas antes del centinela
                        sentinel.insertAdjacentHTML('beforebegin', html);
                    }

                    isLoading = false;

                    if (!hasMore) {
                        sentinel.style.display = 'none';
                        observer.disconnect();
                    }
                })
                .catch(err => {
                    console.error('Error cargando posts:', err);
                    isLoading = false;
                    sentinel.style.display = 'none';
                });
        };

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && hasMore) {
                loadMore();
            }
        }, {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        });

        observer.observe(sentinel);
    });
</script>

<!-- 2. Segundo bloque de botones de etiquetas (Inferior) -->
<div class="lpt-tags-block bottom">
    <?php foreach ($menu_tags as $slug => $info): ?>
        <?php
        $is_active = ($slug === $active_slug);
        $active_class = $is_active ? 'lpt-tag-button-active' : '';
        $url = $info['url']; // URL SEF absoluta
        ?>
        <a href="<?php echo $url; ?>" class="lpt-tag-button <?php echo $active_class; ?>">
            <?php echo htmlspecialchars($info['text']); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>