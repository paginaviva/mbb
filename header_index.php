<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Meridiano LVBP Blog: Artículos de opinión y análisis sobre béisbol, con especial atención a la Liga Venezolana de Béisbol Profesional." />
    <meta name="author" content="" />
    <title>Meridiano LVBP Blog · Análisis de béisbol de Venezuela y el Caribe</title>
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo SITE_URL; ?>">
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Meridiano LVBP Blog · Análisis de béisbol de Venezuela y el Caribe">
    <meta property="og:description" content="Artículos de opinión y análisis sobre béisbol, con especial atención a la Liga Venezolana de Béisbol Profesional.">
    <meta property="og:image" content="<?php echo SITE_URL; ?>assets/img/home-bg.webp">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:site_name" content="<?php echo OG_SITE_NAME; ?>">
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Meridiano LVBP Blog · Análisis de béisbol del Caribe">
    <meta name="twitter:description" content="Artículos y análisis sobre béisbol del Caribe y Grandes Ligas, centrados en la Liga Venezolana de Béisbol Profesional y otras ligas invernales.">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>assets/img/home-bg.webp">
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
    <!-- Header base para Meridiano LVBP Blog. Personaliza imagen/título en cada página principal -->
    <header class="masthead" style="background-image: url('assets/img/home-bg.webp');">
        <div class="container position-relative px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-md-10 col-lg-8 col-xl-7">
                    <div class="site-heading">
                        <h1>Meridiano LVBP Blog</h1>
                        <span class="subheading">Tu blog de la LVBP y del béisbol criollo</span>
                    </div>
                </div>
            </div>
        </div>
    </header>