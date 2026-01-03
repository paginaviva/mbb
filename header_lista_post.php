<?php
include 'config.php';

// Lógica de Títulos y Metas para lista_posts.php
$base_title = "Meridiano LVBP Blog";
$filter_tag = isset($_GET['tag']) ? trim($_GET['tag']) : null;

if ($filter_tag) {
    // Caso con filtro (Categoría o Etiqueta)
    // Title: Todos los artículos de AAA/CCC/EEE
    $page_title = "Todos los artículos de " . $base_title . " / " . $filter_tag;

    // Description: Lista de artículos de análisis y actualidad de béisbol y de la LVBP. CCC/EEE
    $page_description = "Lista de artículos de análisis y actualidad de béisbol y de la LVBP. " . $filter_tag;
} else {
    // Caso sin filtro
    $page_title = "Todos los artículos de " . $base_title;
    $page_description = "Lista de artículos de análisis y actualidad de béisbol y de la LVBP.";
}

// Configuración OG y Twitter
$og_title = $page_title;
$og_description = $page_description;
$og_image = SITE_URL . 'assets/img/home-lista-post.webp'; // Imagen específica para lista
$og_url = SITE_URL . 'lista_posts.php' . ($filter_tag ? '?tag=' . urlencode($filter_tag) : '');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="<?php echo $page_description ?? 'Descripción del sitio'; ?>" />
    <meta name="author" content="<?php echo $page_author ?? 'Autor'; ?>" />
    <title><?php echo $page_title; ?></title>
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo $og_url ?? SITE_URL; ?>">
    <!-- Open Graph -->
    <meta property="og:type" content="<?php echo $og_type ?? 'website'; ?>">
    <meta property="og:title" content="<?php echo $og_title ?? $page_title; ?>">
    <meta property="og:description" content="<?php echo $og_description ?? $page_description; ?>">
    <meta property="og:image" content="<?php echo $og_image ?? SITE_URL . 'assets/img/default.webp'; ?>">
    <meta property="og:url" content="<?php echo $og_url ?? SITE_URL; ?>">
    <meta property="og:site_name" content="<?php echo $og_site_name ?? OG_SITE_NAME; ?>">
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $twitter_title ?? $og_title; ?>">
    <meta name="twitter:description" content="<?php echo $twitter_description ?? $og_description; ?>">
    <meta name="twitter:image" content="<?php echo $twitter_image ?? $og_image; ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>assets/favicon.ico" />
    <!-- Font Awesome icons (free version)-->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="<?php echo SITE_URL; ?>css/styles.css" rel="stylesheet" />
    <!-- Custom header adjustments -->
    <link href="<?php echo SITE_URL; ?>css/header_custom.css" rel="stylesheet" />
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js/id=G-Y2V5THG16Y"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-Y2V5THG16Y');
    </script>
    <!-- Clarity tracking code for https://www.meridiano.com/ -->
    <script>
        (function(c, l, a, r, i, t, y) {
            c[a] = c[a] || function() {
                (c[a].q = c[a].q || []).push(arguments)
            };
            t = l.createElement(r);
            t.async = 1;
            t.src = "https://www.clarity.ms/tag/" + i + "?ref=bwt";
            y = l.getElementsByTagName(r)[0];
            y.parentNode.insertBefore(t, y);
        })(window, document, "clarity", "script", "ucqujjbabg");
    </script>
</head>

<body>
    <!-- Navigation-->
    <?php include 'menu.php'; ?>
    <!-- Header base para Meridiano LVBP Blog. Personaliza imagen/título en cada página principal -->
    <header class="masthead" style="background-image: url('<?php echo $masthead_bg ?? SITE_URL . 'assets/img/home-lista-post.webp'; ?>');">
        <div class="container position-relative px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <div class="site-heading">
                        <h1>Todos los artículos de Meridiano LVBP Blog</h1>
                        <span class="subheading">Tu blog de la LVBP y del béisbol criollo</span>
                    </div>
                </div>
            </div>
        </div>
    </header>