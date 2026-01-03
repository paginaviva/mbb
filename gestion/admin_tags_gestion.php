<?php
require_once '../config.php';
require_once '../posts_manifest.php';
$lists = require 'admin_tags_listas.php';
$specialCategories = $lists['categories'] ?? [];
$specialTags = $lists['tags'] ?? [];

// Ordenar listas alfabéticamente
sort($specialCategories);
sort($specialTags);

// --- FUNCIONES HELPER ---

function saveManifest($file, $data) {
    $content = "<?php\n";
    $content .= "// Manifiesto de posts - Generado automáticamente\n";
    $content .= "// Última actualización: " . date('Y-m-d H:i:s') . "\n";
    $content .= "// No editar manualmente\n\n";
    $content .= "\$posts = " . var_export($data, true) . ";\n";
    
    // Attempt to write safely
    if (file_put_contents($file, $content) === false) {
        throw new Exception("No se pudo escribir en el archivo de manifiesto.");
    }
}

function updatePostFile($filePath, $newCategory, $newTags, $allCategories = []) {
    $content = file_get_contents($filePath);
    if (!$content) throw new Exception("No se pudo leer el archivo del post.");
    
    // 1. Preparar datos de categorías
    // $newCategory debe ser el string de la categoría principal (compatibilidad)
    // $allCategories debe ser el array completo (nueva funcionalidad)
    
    // Asegurar que allCategories incluya la principal
    if (!in_array($newCategory, $allCategories)) {
        array_unshift($allCategories, $newCategory);
    }
    // Asegurar que newCategory sea el primero de allCategories
    if ($allCategories[0] !== $newCategory) {
        $allCategories = array_diff($allCategories, [$newCategory]);
        array_unshift($allCategories, $newCategory);
    }
    $allCategories = array_values(array_unique($allCategories));

    // Construir string para $categories (array)
    $categoriesStr = "array (\n";
    foreach ($allCategories as $i => $cat) {
        $safeCat = str_replace("'", "\\'", $cat);
        $categoriesStr .= "  $i => '$safeCat',\n";
    }
    $categoriesStr .= ")";

    // Construir string para asignación de bloque
    // 1.1 Limpiar $categories existente si la hay
    $content = preg_replace('/\$categories\s*=\s*(array\s*\(.*?\)|\[.*?\])\s*;/s', '', $content);
    $content = preg_replace('/\n\n+/', "\n\n", $content);

    // 1.2 Reemplazar $category = ... con el nuevo bloque completo ($category + $categories)
    $newBlock = "\$category = '" . str_replace("'", "\\'", $newCategory) . "';\n";
    $newBlock .= "\$categories = " . $categoriesStr . ";";

    $categoryPattern = '/(\$category\s*=\s*)([\'"])(.*?)(\2)(\s*;)/';
    if (preg_match($categoryPattern, $content)) {
        $content = preg_replace($categoryPattern, $newBlock, $content, 1);
    } else {
        // Fallback
        if (strpos($content, '[CATEGORIAS]') !== false) {
             $content = str_replace('[CATEGORIAS]', "[CATEGORIAS]\n" . $newBlock, $content);
        } else {
             $content = str_replace('<?php', "<?php\n" . $newBlock, $content);
        }
    }

    // 2. Actualizar $tags
    $tagsStr = "array (\n";
    foreach ($newTags as $i => $tag) {
        $safeTag = str_replace("'", "\\'", $tag);
        $tagsStr .= "  $i => '$safeTag',\n";
    }
    $tagsStr .= ")";
    
    $tagsPattern = '/(\$tags\s*=\s*)(array\s*\(.*?\)|\[.*?\])(\s*;)/s';
    if (preg_match($tagsPattern, $content)) {
        $content = preg_replace($tagsPattern, '${1}' . $tagsStr . '${3}', $content, 1);
    } else {
        $content .= "\n\$tags = " . $tagsStr . ";\n";
    }
    
    if (file_put_contents($filePath, $content) === false) {
        throw new Exception("No se pudo escribir en el archivo del post.");
    }
}

// --- LÓGICA DE CONTROL ---

$message = '';
$messageType = '';
$activePost = null;
$activeSlug = '';

// Ordenar posts para lista lateral (usando control file si existe)
$controlFile = __DIR__ . '/../posts_manifest_control.php';
$recentPostsList = [];
if (file_exists($controlFile)) {
    include $controlFile;
    if (isset($processed_posts)) {
        arsort($processed_posts);
        $recentFiles = array_slice($processed_posts, 0, 20);
        foreach ($recentFiles as $fname => $time) {
            $recentPostsList[] = [
                'file' => $fname,
                'slug' => str_replace('.php', '', $fname)
            ];
        }
    }
}

// BÚSQUEDA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_query'])) {
    $query = trim($_POST['search_query']);
    $cleanQuery = basename($query);
    $cleanQuery = str_replace('.php', '', $cleanQuery);
    
    if (isset($posts[$cleanQuery])) {
        header("Location: admin_tags_gestion.php?slug=" . urlencode($cleanQuery));
        exit;
    } else {
        $matches = [];
        foreach ($posts as $slug => $data) {
            if (strpos($slug, $cleanQuery) !== false) {
                $matches[] = $slug;
            }
        }
        if (count($matches) === 1) {
             header("Location: admin_tags_gestion.php?slug=" . urlencode($matches[0]));
             exit;
        } elseif (count($matches) > 1) {
            $message = "Múltiples coincidencias para '$query'.";
            $messageType = 'warning';
        } else {
            $message = "No se encontró el artículo '$query'.";
            $messageType = 'error';
        }
    }
}

// CARGAR POST ACTIVO
if (isset($_GET['slug'])) {
    $activeSlug = $_GET['slug'];
    if (isset($posts[$activeSlug])) {
        $activePost = $posts[$activeSlug];
        $activePost['slug'] = $activeSlug;
        $activePost['filename'] = $activeSlug . '.php';
        
        // LEER CONTENIDO REAL DEL ARCHIVO para categorías múltiples
        $realContent = @file_get_contents('../post/' . $activePost['filename']);
        if ($realContent) {
            // Extraer $category
            if (preg_match('/\$category\s*=\s*[\'"](.*?)[\'"]\s*;/', $realContent, $m)) {
                $activePost['category'] = $m[1];
            }
            
            // Extraer $categories (matriz)
            if (preg_match('/\$categories\s*=\s*array\s*\((.*?)\);/s', $realContent, $m)) {
                $arrayContent = $m[1];
                if (preg_match_all('/\d+\s*=>\s*[\'"](.*?)[\'"]/', $arrayContent, $matches)) {
                    $activePost['categories'] = $matches[1];
                }
            } else {
                // Si no hay matriz, inicializamos con la categoría simple
                if (!empty($activePost['category'])) {
                    $activePost['categories'] = [$activePost['category']];
                } else {
                     $activePost['categories'] = [];
                }
            }
            
            // Extraer $tags (para asegurar consistencia)
            if (preg_match('/\$tags\s*=\s*array\s*\((.*?)\);/s', $realContent, $m)) {
                $arrayContent = $m[1];
                if (preg_match_all('/\d+\s*=>\s*[\'"](.*?)[\'"]/', $arrayContent, $matches)) {
                    $activePost['tags'] = $matches[1];
                }
            }
        }
    }
}

// PROCESAR ACTUALIZACIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tags']) && $activePost) {
    try {
        // 1. Obtener estado actual
        $currentCats = $activePost['categories'] ?? [];
        if (empty($currentCats) && !empty($activePost['category'])) {
            $currentCats = [$activePost['category']];
        }
        
        $currentTags = isset($activePost['tags']) ? $activePost['tags'] : [];
        if (!is_array($currentTags)) $currentTags = [];

        // 2. Identificar qué eliminar (Checkboxes Col A)
        $removeCats = isset($_POST['remove_cats']) ? $_POST['remove_cats'] : [];
        $removeTags = isset($_POST['remove_tags']) ? $_POST['remove_tags'] : [];
        
        // 3. Identificar qué añadir (Checkboxes Col B)
        $addCats = isset($_POST['add_cats']) ? $_POST['add_cats'] : [];
        $addTags = isset($_POST['add_tags']) ? $_POST['add_tags'] : [];

        // 4. Procesar Operaciones Manuales
        if (isset($_POST['manual_tags_operations'])) {
            $lines = explode("\n", $_POST['manual_tags_operations']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $prefix = substr($line, 0, 1);
                $tagVal = trim(substr($line, 1));
                
                if (!empty($tagVal)) {
                    if ($prefix === '+') {
                        $addTags[] = $tagVal;
                    } elseif ($prefix === '-') {
                        $removeTags[] = $tagVal;
                    }
                }
            }
        }
        
        // 5. Calcular Listas Finales
        $catsToKeep = array_diff($currentCats, $removeCats);
        $tagsToKeep = array_diff($currentTags, $removeTags);
        
        $finalCats = array_unique(array_merge($catsToKeep, $addCats));
        $finalTags = array_unique(array_merge($tagsToKeep, $addTags));
        
        // Reindexar y ordenar
        sort($finalCats);
        sort($finalTags);
        
        // 6. Determinar Categoría Principal (Primera alfabética o mantener la existente si posible? 
        // Usamos la primera de la lista final ordenada para consistencia total)
        $primaryCat = count($finalCats) > 0 ? $finalCats[0] : '';
        
        // 7. Actualizar Archivos
        $filePath = '../post/' . $activePost['filename'];
        updatePostFile($filePath, $primaryCat, $finalTags, $finalCats);
        
        // 8. Actualizar Manifest
        $posts[$activeSlug]['category'] = $primaryCat;
        $posts[$activeSlug]['tags'] = $finalTags;
        // Opcional: Guardamos categories si se desea en el futuro, pero category simple es la compatibilidad requerida
        saveManifest('../posts_manifest.php', $posts);
        
        $message = "Cambios guardados correctamente.";
        $messageType = 'success';
        
        // Refrescar datos en memoria
        $activePost = $posts[$activeSlug];
        $activePost['slug'] = $activeSlug;
        $activePost['filename'] = $activeSlug . '.php';
        $activePost['category'] = $primaryCat;
        $activePost['categories'] = $finalCats; // Manual override
        $activePost['tags'] = $finalTags;
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Prepara arrays para vista
$activeCats = []; 
if ($activePost) {
    if (!empty($activePost['categories']) && is_array($activePost['categories'])) {
        $activeCats = $activePost['categories'];
    } else {
        $raw = $activePost['category'] ?? '';
        $activeCats = array_map('trim', explode(',', $raw));
        $activeCats = array_filter($activeCats);
    }
    sort($activeCats);
    
    $activeTags = $activePost['tags'] ?? [];
    sort($activeTags);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Avanzada de Etiquetas - Meridiano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .main-container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .col-left { width: 30%; border-right: 1px solid #dee2e6; padding-right: 20px; }
        .col-right { width: 70%; padding-left: 20px; }
        .scrollable-list { max-height: 600px; overflow-y: auto; }
        .post-link { display: block; padding: 8px; border-bottom: 1px solid #f1f1f1; text-decoration: none; color: #333; text-overflow: ellipsis; white-space: nowrap; overflow: hidden; }
        .post-link:hover { background-color: #f8f9fa; color: #0d6efd; }
        .post-link.active { background-color: #e7f1ff; color: #0d6efd; font-weight: 500; }
        .form-check-label { cursor: pointer; user-select: none; }
    </style>
</head>
<body>
    <div class="main-container d-flex">
        
        <!-- COLUMNA IZQUIERDA -->
        <div class="col-left d-flex flex-column">
            <h5 class="mb-3">Buscar Artículo</h5>
            <form method="POST" class="d-flex mb-4">
                <input type="text" name="search_query" class="form-control me-2" placeholder="URL o nombre de archivo..." required>
                <button type="submit" class="btn btn-primary">🔍</button>
            </form>

            <h5 class="mb-3">Último 20 Artículos</h5>
            <div class="scrollable-list flex-grow-1">
                <?php foreach ($recentPostsList as $p): ?>
                    <a href="?slug=<?php echo urlencode($p['slug']); ?>" class="post-link <?php echo ($activeSlug === $p['slug']) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($p['file']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-4 pt-3 border-top text-center">
                <a href="dashboard_gestion.php" class="btn btn-outline-secondary btn-sm">← Volver al Panel</a>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div class="col-right">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> py-2 mb-3">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($activePost): ?>
                <div class="mb-4 pb-3 border-bottom">
                    <h4 class="mb-1"><?php echo htmlspecialchars($activePost['title']); ?></h4>
                    <p class="text-muted small mb-0"><a href="<?php echo $activePost['url']; ?>" target="_blank"><?php echo $activePost['url']; ?> ↗</a></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="update_tags" value="1">
                    
                    <div class="row">
                        <!-- Subcolumna A: ASIGNADOS (Eliminar) -->
                        <div class="col-md-6 mb-3">
                            <h6 class="text-secondary mb-2 bg-light p-2 rounded">ASIGNADOS <small>(Marca para quitar)</small></h6>
                            
                            <!-- Categorías Actuales -->
                            <div class="card mb-3 border-danger">
                                <div class="card-header bg-danger text-white py-1"><small>Categorías Actuales</small></div>
                                <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                                    <?php if (!empty($activeCats)): ?>
                                        <?php foreach ($activeCats as $cat): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remove_cats[]" value="<?php echo htmlspecialchars($cat); ?>" id="rm_c_<?php echo md5($cat); ?>">
                                                <label class="form-check-label" for="rm_c_<?php echo md5($cat); ?>">
                                                    <?php echo htmlspecialchars($cat); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <em class="text-muted small">Sin categorías</em>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Etiquetas Actuales -->
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white py-1"><small>Etiquetas Actuales</small></div>
                                <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (!empty($activeTags)): ?>
                                        <?php foreach ($activeTags as $tag): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remove_tags[]" value="<?php echo htmlspecialchars($tag); ?>" id="rm_t_<?php echo md5($tag); ?>">
                                                <label class="form-check-label" for="rm_t_<?php echo md5($tag); ?>">
                                                    <?php echo htmlspecialchars($tag); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <em class="text-muted small">Sin etiquetas</em>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Edición Manual (Nuevo Bloque) -->
                            <div class="card mt-3 border-secondary">
                                <div class="card-header bg-secondary text-white py-1"><small>Edición Manual de Etiquetas</small></div>
                                <div class="card-body p-2">
                                    <textarea name="manual_tags_operations" class="form-control" rows="4" placeholder="+Etiqueta Nueva&#10;-Etiqueta a Eliminar"></textarea>
                                    <div class="form-text text-muted small mt-1">
                                        Una por línea. Usa <b>+</b> para añadir y <b>-</b> para quitar.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subcolumna B: DISPONIBLES (Añadir) -->
                        <div class="col-md-6 mb-3">
                            <h6 class="text-primary mb-2 bg-light p-2 rounded">DISPONIBLES <small>(Marca para añadir)</small></h6>
                            
                            <!-- Categorías Disponibles -->
                            <div class="card mb-3 border-success">
                                <div class="card-header bg-success text-white py-1"><small>Categorías Sugeridas/Especiales</small></div>
                                <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach ($specialCategories as $cat): 
                                        if (in_array($cat, $activeCats)) continue; 
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="add_cats[]" value="<?php echo htmlspecialchars($cat); ?>" id="add_c_<?php echo md5($cat); ?>">
                                            <label class="form-check-label" for="add_c_<?php echo md5($cat); ?>">
                                                <?php echo htmlspecialchars($cat); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Etiquetas Disponibles -->
                            <div class="card border-success">
                                <div class="card-header bg-success text-white py-1"><small>Etiquetas Especiales</small></div>
                                <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                                    <?php foreach ($specialTags as $st): 
                                        if (in_array($st, $activeTags)) continue; 
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="add_tags[]" value="<?php echo htmlspecialchars($st); ?>" id="add_t_<?php echo md5($st); ?>">
                                            <label class="form-check-label" for="add_t_<?php echo md5($st); ?>">
                                                <?php echo htmlspecialchars($st); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5">💾 Aplicar Cambios</button>
                        
                        <div class="d-flex gap-2">
                            <a href="generate_manifest.php" target="_blank" class="btn btn-outline-warning btn-sm">⚡ Manifiesto</a>
                            <a href="../index.php" target="_blank" class="btn btn-outline-dark btn-sm">🌐 Blog</a>
                        </div>
                    </div>
                </form>

            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100 text-muted" style="min-height: 400px;">
                    <div class="text-center">
                        <h2 class="display-6">Gestión de Etiquetas</h2>
                        <p class="lead">Selecciona un artículo de la lista o busca uno para comenzar.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
