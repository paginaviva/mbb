<?php
// list_tags.php
// Script de utilidad para listar todos los tags únicos del manifiesto

header('Content-Type: text/html; charset=utf-8');

// Incluir el manifiesto
if (file_exists('../posts_manifest.php')) {
    include '../posts_manifest.php';
} else {
    die("Error: No se encuentra posts_manifest.php");
}

$all_tags = [];
$tag_counts = [];

foreach ($posts as $slug => $post) {
    if (isset($post['tags']) && is_array($post['tags'])) {
        foreach ($post['tags'] as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $all_tags[] = $tag;
                
                // Contar ocurrencias
                if (!isset($tag_counts[$tag])) {
                    $tag_counts[$tag] = 0;
                }
                $tag_counts[$tag]++;
            }
        }
    }
}

// Eliminar duplicados y ordenar
$unique_tags = array_unique($all_tags);
sort($unique_tags, SORT_STRING | SORT_FLAG_CASE);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Tags Únicos</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; max-width: 800px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        tr:hover { background-color: #f5f5f5; }
        .count { color: #888; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>Tags Encontrados (<?php echo count($unique_tags); ?>)</h1>
    <p>Copia y pega estos tags para normalizar tu contenido.</p>
    
    <table>
        <thead>
            <tr>
                <th>Tag</th>
                <th>Uso (Cant. Posts)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($unique_tags as $tag): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tag); ?></td>
                    <td><span class="count"><?php echo $tag_counts[$tag]; ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
