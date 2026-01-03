<?php
require_once 'config.php';
require_once 'posts_manifest.php';

// Obtener la etiqueta desde la URL
$tag_name = $_GET['name'] ?? '';

// Filtrar posts por etiqueta
$filtered_posts = array_filter($posts, function($post) use ($tag_name) {
    return in_array($tag_name, $post['tags']);
});

// Título de la página
$page_title = "Etiqueta: $tag_name";
$og_type = "website";
$og_title = "Etiqueta: $tag_name";
$og_description = "Artículos con la etiqueta $tag_name en Meridiano Blog.";
$og_image = SITE_URL . "/assets/img/home-bg.webp";
$og_url = SITE_URL . "/tag.php?name=" . urlencode($tag_name);
$og_site_name = OG_SITE_NAME;
$twitter_card = "summary_large_image";
$twitter_title = "Etiqueta: $tag_name";
$twitter_description = "Artículos con la etiqueta $tag_name en Meridiano Blog.";
$twitter_image = SITE_URL . "/assets/img/home-bg.webp";
$page_description = "Artículos con la etiqueta $tag_name en Meridiano Blog.";
$page_author = SITE_AUTHOR_DEFAULT;
$site_h1 = "Etiqueta: $tag_name";
$site_subheading = "Artículos relacionados";
$masthead_bg = "assets/img/home-bg.webp";

include 'header_common.php';
?>
        <!-- Main Content-->
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <?php if (empty($filtered_posts)): ?>
                        <p>No se encontraron artículos con esta etiqueta.</p>
                    <?php else: ?>
                        <?php foreach ($filtered_posts as $slug => $post): ?>
                            <div class="post-preview">
                                <a href="<?php echo $post['url']; ?>">
                                    <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                                    <h3 class="post-subtitle"><?php echo htmlspecialchars($post['subtitle']); ?></h3>
                                </a>
                                <p class="post-meta">
                                    Posted by
                                    <a href="#!"><?php echo htmlspecialchars($post['author']); ?></a>
                                    on <?php echo htmlspecialchars($post['date']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- Pager-->
                    <div class="d-flex justify-content-end mb-4"><a class="btn btn-primary text-uppercase" href="index.php">← Volver al Inicio</a></div>
                </div>
            </div>
        </div>
        <!-- Footer parcial -->
        <?php include 'footer.php'; ?>
    </body>
</html>