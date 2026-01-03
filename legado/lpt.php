<?php
// lpt.php - Punto de entrada para listas por etiqueta (LPT)
include 'config.php';
include 'includes/lista_posts_tag_core.php';

// Resolver etiqueta y obtener datos
$tag_slug = lpt_resolve_slug();
$tag_data = lpt_get_active_tag_data($tag_slug);

// Detectar dispositivo mediante User Agent (Lógica compartida con index.php)
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $user_agent);
$is_tablet = preg_match('/(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/i', $user_agent);

// Enrutamiento de vista
if ($is_mobile || $is_tablet) {
    // Cargar versión móvil específica
    include 'includes/lista_posts_tag_mobile.php';
} else {
    // Versión Desktop
    include 'includes/lista_posts_tag_desktop.php';
}
