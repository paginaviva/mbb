<?php
// Asegurar acceso a config y core si se incluye directamente (aunque lpt.php ya lo hace)
if (!defined('SITE_URL')) include '../config.php';
if (!function_exists('lpt_get_active_tag_data')) include 'lista_posts_tag_core.php';

// Si $tag_data no viene definido desde el controlador, intentamos resolverlo
if (!isset($tag_data)) {
    $tag_slug = lpt_resolve_slug();
    $tag_data = lpt_get_active_tag_data($tag_slug);
}

// Configuración de Meta Datos
$base_title = "Meridiano LVBP Blog";
$page_title = ($tag_data['header_title'] ?: "Etiqueta") . " - " . $base_title;
$page_desc  = ($tag_data['header_subtitle'] ?: "Artículos sobre béisbol");
$header_img = $tag_data['header_img']; // Ya viene resuelta con fallback
$header_bg_style = "background-image: url('" . SITE_URL . $header_img . "');";

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>" />
    <meta name="author" content="" />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo SITE_URL . 'lpt.php?tag=' . urlencode($tag_data['slug']); ?>">
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta property="og:image" content="<?php echo SITE_URL . $header_img; ?>">
    <meta property="og:url" content="<?php echo SITE_URL . 'lpt.php?tag=' . urlencode($tag_data['slug']); ?>">
    <meta property="og:site_name" content="<?php echo OG_SITE_NAME; ?>">
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL . $header_img; ?>">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico" />
    <!-- Font Awesome icons (free version)-->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
    <!-- Custom header adjustments -->
    <link href="css/header_custom.css" rel="stylesheet" />
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-Y2V5THG16Y"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-Y2V5THG16Y');
    </script>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-KNHLV46K');
    </script>
    <!-- End Google Tag Manager -->
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
    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u = "https://mato.contenedoresvr.top/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '2']);
            var d = document,
                g = d.createElement('script'),
                s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KNHLV46K"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- Navigation-->
    <?php include 'menu.php'; ?>
    <!-- Header dinámico para Lista por Etiquetas -->
    <header class="masthead" style="<?php echo $header_bg_style; ?>">
        <div class="container position-relative px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <div class="site-heading">
                        <h1><?php echo htmlspecialchars($tag_data['header_title']); ?></h1>
                        <span class="subheading"><?php echo htmlspecialchars($tag_data['header_subtitle']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>