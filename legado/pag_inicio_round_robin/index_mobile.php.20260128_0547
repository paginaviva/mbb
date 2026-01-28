<?php
// index_mobile.php - Versión MÓVIL/TABLET
include 'header_index.php';
include 'includes/home_data_provider.php';

// Obtener los datos procesados
$home_data = get_home_data();
?>

<!-- CSS SOLO MÓVIL -->
<link href="css/home_mobile.css" rel="stylesheet" />

<!-- Estilos para Artículo Patrocinado -->
<style>
    .sponsored-article-block {
        background-color: #fff9c4;
        /* Amarillo claro */
        border: 3px solid #f57f17;
        /* Marco naranja oscuro */
        padding: 15px;
        margin: 15px 10px;
        border-radius: 8px;
    }

    .sponsored-title {
        font-weight: bold;
        margin: 10px 0;
    }

    .sponsored-title a {
        color: #212529;
        text-decoration: none;
    }

    .sponsored-title a:hover {
        color: #f57f17;
        text-decoration: underline;
    }

    .sponsored-excerpt {
        font-style: italic;
        margin: 10px 0;
        color: #495057;
        font-size: 14px;
    }

    .sponsored-date {
        font-weight: bold;
        color: #212529;
        font-size: 14px;
    }
</style>

<!-- BLOQUE ARTÍCULO PATROCINADO -->
<!-- BLOQUE BOTONES DE SECCIONES -->
<?php
$botones_secciones_path = __DIR__ . '/config/secciones_botones_menu.php';
if (file_exists($botones_secciones_path)) {
    $botones_secciones = include $botones_secciones_path;
    if (!empty($botones_secciones) && is_array($botones_secciones)) {
?>
        <div class="section-buttons-container">
            <?php foreach ($botones_secciones as $boton):
                $bg_color = $boton['color_primario'] ?? '#004c99';
            ?>
                <a href="<?php echo $boton['url']; ?>"
                    class="btn-section"
                    target="_blank"
                    style="background-color: <?php echo $bg_color; ?>;">
                    <?php echo htmlspecialchars($boton['nombre']); ?>
                </a>
            <?php endforeach; ?>
        </div>
<?php
    }
}
?>

<!-- BLOQUE ARTÍCULO PATROCINADO -->
<?php if (!empty($home_data['sponsored_post'])):
    $sponsored = $home_data['sponsored_post'];
?>
    <div class="sponsored-article-block">
        <h3 class="sponsored-title">
            <a href="<?php echo $sponsored['url']; ?>" target="_blank">
                <?php echo htmlspecialchars($sponsored['title']); ?>
            </a>
        </h3>
        <div class="sponsored-excerpt"><?php echo $sponsored['subtitle']; ?></div>
        <div class="sponsored-date"><?php echo htmlspecialchars($sponsored['date']); ?></div>
    </div>
<?php endif; ?>

<div class="home-grid-container-mobile">

    <!-- ORDEN MÓVIL: A → B+C → E → D → Botón -->

    <!-- BLOQUE A: Round Robin Ayer (2 posts) -->
    <?php if (!empty($home_data['block_a']['latest_two'])): ?>
        <section id="block-round-robin-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Round Robin Ayer</div>
            <ul class="post-list-small-mobile">
                <?php foreach ($home_data['block_a']['latest_two'] as $post): ?>
                    <li>
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('yesterday_latest', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- BLOQUE B+C: Lo más reciente -->
    <?php if (!empty($home_data['block_b']) || !empty($home_data['block_c'])): ?>
        <section id="block-latest-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Lo más reciente</div>
            <div class="latest-news-grid-mobile">
                <?php $pos = 1;
                foreach ($home_data['block_b'] as $post): ?>
                    <article class="latest-card-mobile">
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('latest', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', <?php echo $pos; ?>)">
                            <h2><?php echo $post['title']; ?></h2>
                        </a>
                    </article>
                <?php $pos++;
                endforeach; ?>

                <!-- Block C merged here -->
                <?php foreach ($home_data['block_c'] as $post): ?>
                    <article class="latest-card-mobile">
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('last_articles', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', <?php echo $pos; ?>)">
                            <h2><?php echo $post['title']; ?></h2>
                        </a>
                    </article>
                <?php $pos++;
                endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- BLOQUE E: Historias destacadas -->
    <?php if (!empty($home_data['block_e'])): ?>
        <section id="block-featured-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Historias destacadas LVBP</div>
            <?php $pos = 1;
            foreach ($home_data['block_e'] as $post): ?>
                <div class="vertical-list-item-mobile">
                    <a href="<?php echo $post['url']; ?>" target="_blank"
                        onclick="trackHomeClick('featured_stories', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', <?php echo $pos; ?>)">
                        <h3><?php echo $post['title']; ?></h3>
                    </a>
                </div>
            <?php $pos++;
            endforeach; ?>
        </section>
    <?php endif; ?>

    <!-- BLOQUE D: Resúmenes Semanales -->
    <?php if (!empty($home_data['block_d'])): ?>
        <section id="block-summaries-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Resúmenes Semanales LVBP</div>
            <?php $pos = 1;
            foreach ($home_data['block_d'] as $post): ?>
                <div class="vertical-list-item-mobile">
                    <a href="<?php echo $post['url']; ?>" target="_blank"
                        onclick="trackHomeClick('league_summaries', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', <?php echo $pos; ?>)">
                        <h4><?php echo $post['title']; ?></h4>
                    </a>
                </div>
            <?php $pos++;
            endforeach; ?>
        </section>
    <?php endif; ?>

    <!-- BLOQUE: Otros Juegos Round Robin -->
    <?php if (!empty($home_data['block_a']['others'])): ?>
        <section id="block-other-round-robin-mobile" class="home-block-mobile">
            <div class="block-title-mobile">Otros Juegos Round Robin</div>
            <ul class="post-list-small-mobile">
                <?php foreach ($home_data['block_a']['others'] as $post): ?>
                    <li>
                        <a href="<?php echo $post['url']; ?>" target="_blank"
                            onclick="trackHomeClick('yesterday_others', '<?php echo $post['id']; ?>', '<?php echo addslashes($post['title']); ?>', '<?php echo $post['category']; ?>', 0)">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Botón Más Artículos -->
    <div class="text-center my-4">
        <a href="lista_posts.php" target="_blank" class="btn btn-primary btn-lg">Más artículos</a>
    </div>

</div>

<!-- Script de Tracking -->
<script>
    function trackHomeClick(zone, id, title, category, pos) {
        if (typeof gtag === 'function') {
            gtag("event", "click_home_article", {
                zone: zone,
                article_id: id,
                article_title: title,
                position_list: pos,
                category: category
            });
        } else {
            console.log("GA4 Event:", zone, id, title);
        }
    }
</script>

<?php include 'footer.php'; ?>