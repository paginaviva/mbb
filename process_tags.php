<?php
// process_tags.php
require_once 'config.php';

// Aumentar tiempo de ejecución por si son muchos archivos
set_time_limit(300);

$action = $_POST['action'] ?? '';
$raw_tags = $_POST['tags'] ?? '';
$raw_urls = $_POST['urls'] ?? '';

if (empty($action) || empty($raw_tags) || empty($raw_urls)) {
    die("Error: Faltan datos requeridos. <a href='admin_tags.php'>Volver</a>");
}

// Procesar entradas
$tags_list = array_filter(array_map('trim', explode("\n", $raw_tags)));
$urls_list = array_filter(array_map('trim', explode("\n", $raw_urls)));

$results = [];

foreach ($urls_list as $url_input) {
    // Extraer el slug/nombre del archivo de la URL
    $filename = basename($url_input);
    // Asegurar extensión .php
    if (!str_ends_with($filename, '.php')) {
        $filename .= '.php';
    }
    
    $filepath = __DIR__ . '/post/' . $filename;
    
    if (!file_exists($filepath)) {
        $results[] = ['status' => 'error', 'msg' => "Archivo no encontrado: $filename"];
        continue;
    }

    // Leer contenido del archivo
    $content = file_get_contents($filepath);
    
    // Buscar la definición del array $tags
    // Patrón flexible para encontrar $tags = array(...) o $tags = [...]
    // Asumimos formato estándar generado por el sistema: $tags = array ( ... );
    
    // Estrategia: Leer el archivo, encontrar la sección de tags, parsearla (evaluarla es peligroso, mejor regex/string manipulation)
    // Dado que los archivos son generados, el formato es predecible.
    // Buscamos: $tags = array ( ... );
    
    // 1. Extraer el bloque de tags actual
    // Soporta $tags = array(...); y $tags = [...];
    if (preg_match('/\$tags\s*=\s*(?:array\s*\(|\[)(.*?)(?:\)|\]);/s', $content, $matches)) {
        $current_tags_block = $matches[1];
        
        // Extraer valores individuales
        // Busca cadenas entre comillas simples, ignorando claves numéricas si existen
        preg_match_all("/'([^']+)'/", $current_tags_block, $tag_matches);
        $current_tags = $tag_matches[1] ?? [];
        
        // Modificar tags
        $original_count = count($current_tags);
        
        if ($action === 'assign') {
            // Agregar nuevos tags (sin duplicados)
            $current_tags = array_unique(array_merge($current_tags, $tags_list));
        } elseif ($action === 'delete') {
            // Eliminar tags seleccionados
            $current_tags = array_diff($current_tags, $tags_list);
        }
        
        // Reindexar array
        $current_tags = array_values($current_tags);
        
        // Reconstruir el bloque de código
        // Usamos formato array() con claves explícitas para mantener consistencia con el generador original
        $new_tags_block = "\$tags = \n    array (\n";
        foreach ($current_tags as $index => $tag) {
            $new_tags_block .= "      $index => '$tag',\n";
        }
        $new_tags_block .= "    );";
        
        // Reemplazar en el contenido
        $new_content = str_replace($matches[0], $new_tags_block, $content);
        
        if (file_put_contents($filepath, $new_content)) {
            $results[] = [
                'status' => 'success', 
                'msg' => "Actualizado: $filename",
                'tags_before' => $original_count,
                'tags_after' => count($current_tags)
            ];
        } else {
            $results[] = ['status' => 'error', 'msg' => "Error al escribir: $filename"];
        }
        
    } else {
        $results[] = ['status' => 'error', 'msg' => "No se pudo parsear tags en: $filename"];
    }
}

// Regenerar manifiesto
ob_start();
include 'generate_manifest.php';
$manifest_output = ob_get_clean();

// Cargar manifiesto actualizado para verificación
include 'posts_manifest.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Resultados - Admin Etiquetas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { padding: 20px; }</style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Resultados del Procesamiento</h2>
        
        <div class="alert alert-info">
            <strong>Acción:</strong> <?php echo $action === 'assign' ? 'Asignar' : 'Eliminar'; ?><br>
            <strong>Etiquetas:</strong> <?php echo implode(', ', $tags_list); ?>
        </div>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Estado</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $res): ?>
                <tr class="<?php echo $res['status'] === 'success' ? 'table-success' : 'table-danger'; ?>">
                    <td><?php echo str_replace('Actualizado: ', '', str_replace('Archivo no encontrado: ', '', $res['msg'])); ?></td>
                    <td><?php echo strtoupper($res['status']); ?></td>
                    <td>
                        <?php 
                        if ($res['status'] === 'success') {
                            echo "Tags: {$res['tags_before']} -> {$res['tags_after']}";
                        } else {
                            echo $res['msg'];
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="mt-5">Verificación en Manifiesto (Estado Actual)</h3>
        <p class="text-muted">Consultando directamente <code>posts_manifest.php</code></p>
        
        <div class="row">
            <?php 
            foreach ($urls_list as $url_input) {
                $slug = basename($url_input, '.php');
                if (isset($posts[$slug])) {
                    $p = $posts[$slug];
                    echo '<div class="col-md-6 mb-3">';
                    echo '<div class="card">';
                    echo '<div class="card-header fw-bold">' . $p['title'] . '</div>';
                    echo '<div class="card-body">';
                    echo '<h6 class="card-subtitle mb-2 text-muted">' . $slug . '</h6>';
                    echo '<p class="card-text"><strong>Etiquetas actuales:</strong></p>';
                    echo '<ul>';
                    foreach ($p['tags'] as $t) {
                        // Resaltar tags que fueron parte de la operación
                        $highlight = in_array($t, $tags_list) ? 'text-primary fw-bold' : '';
                        echo "<li class='$highlight'>$t</li>";
                    }
                    echo '</ul>';
                    echo '</div></div></div>';
                }
            }
            ?>
        </div>

        <div class="mt-4">
            <a href="admin_tags.php" class="btn btn-primary">Volver al Formulario</a>
            <a href="index.php" class="btn btn-secondary">Ir al Home</a>
        </div>
    </div>
</body>
</html>
