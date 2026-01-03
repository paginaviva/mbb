<?php

$file = 'posts_manifest.php';
$content = file_get_contents($file);

if ($content === false) {
    echo "Error leyendo archivo.";
    exit(1);
}

// Mapa de sustituciones (Orden importante preservado del prompt)
$replacements = [
    'Romer Cuadrado' => 'Rómer Cuadrado',
    'Luisangel Acuña' => 'Luisángel Acuña',
    'Ali Castillo' => 'Alí Castillo',
    'Angelo Castellano' => 'Ángelo Castellano',
    'Ali Sánchez' => 'Alí Sánchez',
    'Juego de La Chinita' => 'Juego de la Chinita',
    'José Pirela' => 'José Pírela',
    'Eliezer Alfonzo Jr.' => 'Eliézer Alfonzo Jr.',
    'Eliézer Alfonzo Jr' => 'Eliézer Alfonzo Jr.',
    'Eliezer Alfonzo Jr' => 'Eliézer Alfonzo Jr.',
    'Yilber Díaz' => 'Yílber Díaz',
    'DJ Johnson' => 'D.J. Johnson',
    'Resumen de la semana' => 'Resumen Semanal',
    'Resumen de jornada' => 'Resumen Diario',
    'Redacción Meridiano BB' => 'Redacción Meridiano',
    'Redaccin Meridiano' => 'Redacción Meridiano',
    'REEMPLAZAR_CON_AUTOR_VISIBLE' => 'Redacción Meridiano',
    'Salvador Pérez' => 'Salvador “Salvy” Pérez',
    'Magallanes' => 'Navegantes del Magallanes',
    'Caribes' => 'Caribes de Anzoátegui',
    'Bravos' => 'Bravos de Margarita',
    'Águilas' => 'Águilas del Zulia',
    '500 hits en la LVBP' => '500 hits en LVBP',
    'identidad del equipo sin Simmons' => 'Andrelton Simmons',
    'filosofía de Ozzie Guillén' => 'Oswaldo “Ozzie” Guillén',
];

$original_content = $content;

foreach ($replacements as $search => $replace) {
    // Encapsulamos en comillas simples para ser estrictos con exactitud de "elemento de array"
    // Esto evita reemplazar substrings en títulos o descripciones.
    $search_str = "'" . $search . "'";
    $replace_str = "'" . $replace . "'";
    
    $content = str_replace($search_str, $replace_str, $content);
}

// Verificación básica de que no quedó corrupto (conteo similar)
// (Opcional, pero confiamos en str_replace)

if (file_put_contents($file, $content) === false) {
    echo "Error escribiendo archivo.";
    exit(1);
}

echo "Sustituciones completadas.";
?>
