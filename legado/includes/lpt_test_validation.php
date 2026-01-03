<?php
// Script temporal de validación para LPT Core
define('SITE_URL', 'http://localhost'); // Mock const
require_once __DIR__ . '/lista_posts_tag_core.php';

echo "--- Test 1: Etiqueta Válida (serie-de-comodin-2025-26) ---\n";
$_GET['tag'] = 'serie-de-comodin-2025-26';
$data = lpt_get_active_tag_data();
print_r($data);

echo "\n--- Test 2: Etiqueta Inválida (no-existe) ---\n";
$_GET['tag'] = 'no-existe';
$data2 = lpt_get_active_tag_data();
print_r($data2);

echo "\n--- Test 3: Lista Ordenada ---\n";
$list = lpt_get_ordered_menu_list();
print_r($list);
