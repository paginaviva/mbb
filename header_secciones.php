<?php include_once __DIR__ . '/config.php'; ?>
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
    <!-- Header base para Meridiano Blog. Personaliza imagen/título en cada página principal -->
    <header class="masthead" style="background-image: url('<?php echo $masthead_bg ?? SITE_URL . 'assets/img/default-bg.webp'; ?>');">
        <div class="container position-relative px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-12 col-lg-10 col-xl-10">
                    <div class="site-heading">
                        <?php if (isset($site_h1)): ?>
                            <h1><?php echo $site_h1; ?></h1>
                            <span class="subheading"><?php echo $site_subheading; ?></span>
                        <?php else: ?>
                            <div class="post-heading">
                                <h1><?php echo $post_title ?? 'Título del Post'; ?></h1>
                                <h2 class="subheading"><?php echo $post_subtitle ?? 'Subtítulo'; ?></h2>
                                <span class="meta">
                                    Posted by
                                    <a href="#!"><?php echo $post_author ?? 'Autor'; ?></a>
                                    on <?php echo $post_date ?? date('d/m/Y'); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>