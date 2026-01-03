<?php
// includes/lista_posts_tag_desktop.php
// Plantilla de escritorio para LPT

// Asegurar que tenemos acceso a las funciones del núcleo
if (!function_exists('lpt_get_ordered_menu_list')) {
    include_once 'lista_posts_tag_core.php';
}

// Obtener lista de botones para el sidebar
$menu_tags = lpt_get_ordered_menu_list();
$active_slug = $tag_data['slug'] ?? ''; // $tag_data debe venir de lpt.php

// Incluir cabecera específica (ya genera el HTML head, body, menu y header image)
include 'header_lista_posts_tag.php';
?>

<!-- Estilos específicos reutilizados de lista_posts -->
<link href="<?php echo SITE_URL; ?>css/lista_posts.css" rel="stylesheet" />

<div class="container px-4 px-lg-5 my-5">
    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <!-- Formulario de Búsqueda Acotada -->
            <div class="mb-4">
                <form action="<?php echo SITE_URL; ?>lpt.php" method="GET" class="d-flex">
                    <input type="hidden" name="tag" value="<?php echo htmlspecialchars($active_slug); ?>">
                    <input class="form-control me-2" type="search" name="q" placeholder="Buscar en <?php echo htmlspecialchars($tag_data['button_text']); ?>..." aria-label="Search" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                        <a href="<?php echo SITE_URL . 'lpt.php?tag=' . urlencode($active_slug); ?>" class="btn btn-outline-secondary ms-2" title="Limpiar búsqueda"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <?php
            $search_q = isset($_GET['q']) ? trim($_GET['q']) : null;
            // Obtener el primer lote de posts (offset 0) con búsqueda opcional
            $batch_data = lpt_get_tag_batch($active_slug, 0, $search_q);
            $posts_list = $batch_data['posts'];
            $has_more   = $batch_data['has_more'];
            ?>

            <?php if (empty($posts_list)): ?>
                <!-- Estado Vacío -->
                <div class="p-4 bg-light rounded border text-center">
                    <h2 class="h5 text-muted"><?php echo htmlspecialchars($tag_data['msg']); ?></h2>
                </div>
            <?php else: ?>
                <!-- Lista de Posts -->
                <div class="lpt-desktop-list">
                    <?php foreach ($posts_list as $post): ?>
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
                </div>

                <!-- Elemento centinela para Infinite Scroll -->
                <div id="lpt-sentinel" class="text-center py-4" style="display: <?php echo $has_more ? 'block' : 'none'; ?>;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>

            <?php endif; ?>

            <!-- Script de Scroll Infinito -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const sentinel = document.getElementById('lpt-sentinel');
                    const container = document.querySelector('.lpt-desktop-list');

                    let offset = <?php echo isset($batch_data['next_offset']) ? $batch_data['next_offset'] : 6; ?>;
                    let hasMore = <?php echo isset($has_more) && $has_more ? 'true' : 'false'; ?>;
                    let isLoading = false;

                    const tagSlug = "<?php echo isset($active_slug) ? htmlspecialchars($active_slug) : ''; ?>";
                    const searchQuery = "<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>";

                    if (!sentinel || !hasMore) return;

                    const loadMore = () => {
                        if (isLoading || !hasMore) return;
                        isLoading = true;

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
                                    container.insertAdjacentHTML('beforeend', html);
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
        </div>

        <!-- Panel Lateral Derecho -->
        <div class="col-lg-4">
            <div class="sidebar-sticky ps-lg-4">
                <div class="sidebar-section">
                    <h5 class="sidebar-title mb-3">Etiquetas</h5>
                    <div class="lpt-tag-column">
                        <?php foreach ($menu_tags as $slug => $info): ?>
                            <?php
                            $is_active = ($slug === $active_slug);
                            $btn_color = $info['color'] ?? '#0085A1';
                            $active_class = $is_active ? 'lpt-tag-button-active' : '';
                            // URL forzada a lpt.php?tag=...
                            $url = SITE_URL . 'lpt.php?tag=' . urlencode($slug);
                            ?>
                            <div class="mb-2">
                                <a href="<?php echo $url; ?>" class="lpt-tag-button <?php echo $active_class; ?>" <?php if ($is_active) echo "style='background-color: {$btn_color} !important; border-color: {$btn_color} !important; color: #fff !important;'"; ?>>
                                    <?php echo htmlspecialchars($info['text']); ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>