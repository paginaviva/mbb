<?php
include __DIR__ . '/../config.php';

// [HEAD] - Metadatos para SEO y redes sociales
$page_title = "REEMPLAZAR_CON_TITULO_DOCUMENTO";  // De [HEAD] TituloDocumento
$og_type = "article";  // De [HEAD] OgType (fijo)
$og_title = "REEMPLAZAR_CON_TITULO_DOCUMENTO";  // De [HEAD] TituloDocumento
$og_description = "REEMPLAZAR_CON_META_DESCRIPTION";  // De [HEAD] MetaDescription
$og_image = rtrim(SITE_URL, '/') . "/assets/img/REEMPLAZAR_CON_OG_IMAGE";  // De [HEAD] OgImage (ajustar ruta)
$og_url = rtrim(SITE_URL, '/') . "/post/REEMPLAZAR_CON_NOMBRE_ARCHIVO_HTML";  // De UrlPublica o NombreArchivoHTML
$og_site_name = OG_SITE_NAME;  // De [HEAD] OgSiteName (fijo en config)
$twitter_card = "summary_large_image";  // De [HEAD] TwitterCard (fijo)
$twitter_title = "REEMPLAZAR_CON_TITULO_DOCUMENTO";  // De [HEAD] TituloDocumento
$twitter_description = "REEMPLAZAR_CON_META_DESCRIPTION";  // De [HEAD] MetaDescription
$twitter_image = rtrim(SITE_URL, '/') . "/assets/img/REEMPLAZAR_CON_OG_IMAGE";  // De [HEAD] TwitterImage (ajustar ruta)
$page_description = "REEMPLAZAR_CON_META_DESCRIPTION";  // De [HEAD] MetaDescription
$page_author = "REEMPLAZAR_CON_AUTOR_META";  // De [HEAD] AutorMeta

// [CABECERA_VISUAL] - Datos para el masthead y cabecera del post
$post_title = "REEMPLAZAR_CON_TITULO_VISIBLE";  // De [CABECERA_VISUAL] TituloVisible
$post_subtitle = "REEMPLAZAR_CON_SUBTITULO_VISIBLE";  // De [CABECERA_VISUAL] SubtituloVisible
$post_author = "REEMPLAZAR_CON_AUTOR_VISIBLE";  // De [CABECERA_VISUAL] AutorVisible
$post_date = "REEMPLAZAR_CON_FECHA_VISIBLE";  // De [CABECERA_VISUAL] FechaVisible
$masthead_bg = rtrim(SITE_URL, '/') . "/assets/img/REEMPLAZAR_CON_IMAGEN_FONDO";  // De [CABECERA_VISUAL] ImagenFondo (ajustar ruta)

// [CATEGORIAS] y [ETIQUETAS] - Categorización del post
$category = "REEMPLAZAR_CON_CATEGORIA_1";  // De [CATEGORIAS] (tomar la primera categoría)
$tags = ['REEMPLAZAR_CON_ETIQUETA_1', 'REEMPLAZAR_CON_ETIQUETA_2', 'REEMPLAZAR_CON_ETIQUETA_3'];  // De [ETIQUETAS] (array de etiquetas)

include __DIR__ . '/../header_common.php';
?>
<!-- Post Content-->
<article class="mb-4">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-md-10 col-lg-8 col-xl-7">
                <!-- [CONTENIDO] - Pegar aquí el HTML del artículo convertido desde Markdown -->
                <p>REEMPLAZAR_CON_CONTENIDO_HTML_DEL_ARTICULO</p>
                <!-- Fin del contenido -->
            </div>
        </div>
    </div>
</article>
<?php include __DIR__ . '/../footer.php'; ?>
