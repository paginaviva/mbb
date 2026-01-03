<?php
// index.php - Detector de dispositivo y router
include 'config.php';

// Detectar dispositivo mediante User Agent
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $user_agent);
$is_tablet = preg_match('/(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/i', $user_agent);

// Redirigir según dispositivo
if ($is_mobile || $is_tablet) {
    // Cargar versión móvil/tablet
    include 'index_mobile.php';
} else {
    // Cargar versión desktop
    include 'index_desktop.php';
}
?>
