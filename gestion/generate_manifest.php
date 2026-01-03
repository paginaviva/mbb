<?php
/**
 * Herramienta de Generación de Manifiesto por Lotes
 * 
 * Transforma el procesamiento de posts en una tarea por lotes controlada
 * para evitar timeouts en el servidor.
 */

// 1. Configuración e Inicialización
require_once '../config.php';

// Aumentar tiempo de ejecución para este script
ini_set('max_execution_time', 300); 

$manifestFile = __DIR__ . '/../posts_manifest.php';
$controlFile = __DIR__ . '/../posts_manifest_control.php';
$postsDir = __DIR__ . '/../post/';
$batchSize = 25;

// Variables de estado
$message = '';
$messageType = ''; // success, warning, info
$processedInThisBatch = [];

// 2. Cargar datos existentes

// Cargar Manifiesto actual ($posts)
$posts = [];
if (file_exists($manifestFile)) {
    include $manifestFile;
    // Si el archivo no define $posts correctamente o está vacío
    if (!isset($posts) || !is_array($posts)) {
        $posts = [];
    }
}

// Cargar Archivo de Control ($processed_posts)
$processed_posts = [];
if (file_exists($controlFile)) {
    include $controlFile;
    if (!isset($processed_posts) || !is_array($processed_posts)) {
        $processed_posts = [];
    }
}

// 3. Escanear directorio de posts
$allPostFiles = glob($postsDir . '*.php');
$allPostFiles = $allPostFiles ? $allPostFiles : [];
$totalFiles = count($allPostFiles);

// Filtrar archivos pendientes
$pendingFiles = [];
foreach ($allPostFiles as $filePath) {
    $filename = basename($filePath);
    if (!isset($processed_posts[$filename])) {
        $pendingFiles[] = $filePath;
    }
}

$totalPending = count($pendingFiles);
$totalProcessed = count($processed_posts);

// 4. Procesar Lote (Acción) o Eliminación Individual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // --- ACCIÓN: PROCESAR LOTE (Batch Processing) ---
    if ($_POST['action'] === 'process_batch') {
        if ($totalPending > 0) {
            // Seleccionar lote
            $batch = array_slice($pendingFiles, 0, $batchSize);
            $count = 0;

            foreach ($batch as $file) {
                // Limpiar variables del ámbito anterior
                unset($page_title, $post_title, $post_subtitle, $category, $categories, $tags, $post_date, $post_author, $masthead_bg, $og_image);
                
                ob_start();
                try {
                    include $file;
                } catch (Exception $e) {
                    error_log("Error procesando $file: " . $e->getMessage());
                }
                ob_end_clean();

                $filename = basename($file);
                $slug = basename($file, '.php');

                // Soporte para múltiples categorías (compatibilidad hacia atrás)
                $cats = isset($categories) ? $categories : (($category ?? false) ? [$category] : []);

                $posts[$slug] = [
                    'title' => $post_title ?? 'Sin título',
                    'subtitle' => $post_subtitle ?? '',
                    'category' => $category ?? 'Sin categoría',
                    'categories' => $cats, // Nuevo campo
                    'tags' => $tags ?? [],
                    'date' => $post_date ?? '',
                    'author' => $post_author ?? 'Redacción Meridiano',
                    'image' => $masthead_bg ?? ($og_image ?? ''),
                    'url' => SITE_URL . 'post/' . $filename
                ];

                $processed_posts[$filename] = time();
                $processedInThisBatch[] = $filename;
                $count++;
            }

            // Guardar cambios
            saveManifest($manifestFile, $posts);
            saveControl($controlFile, $processed_posts);

            $totalProcessed += $count;
            $totalPending -= $count;
            
            $message = "Se han procesado $count artículos correctamente.";
            $messageType = 'success';

        } else {
            $message = "No hay posts nuevos que procesar.";
            $messageType = 'info';
        }
    }
    
    // --- ACCIÓN: ELIMINAR POST DEL MANIFEST (Single Deletion) ---
    elseif ($_POST['action'] === 'delete_post' && !empty($_POST['delete_slug'])) {
        $slugToDelete = $_POST['delete_slug'];
        
        if (isset($posts[$slugToDelete])) {
            $deletedTitle = $posts[$slugToDelete]['title'];
            unset($posts[$slugToDelete]);
            
            // Guardar cambios en el manifest
            saveManifest($manifestFile, $posts);
            
            // Opcional: Eliminar también del control de procesados si existe referencia
            $filenameToDelete = $slugToDelete . '.php';
            if (isset($processed_posts[$filenameToDelete])) {
                unset($processed_posts[$filenameToDelete]);
                saveControl($controlFile, $processed_posts);
            }
            
            $message = "El post '{$deletedTitle}' ({$slugToDelete}) ha sido eliminado del manifiesto.";
            $messageType = 'success';
        } else {
            $message = "El post '{$slugToDelete}' no se encontró en el manifiesto (ya estaba eliminado).";
            $messageType = 'warning';
        }
    }
}

// Helper functions para guardar datos
function saveManifest($file, $data) {
    $content = "<?php\n";
    $content .= "// Manifiesto de posts - Generado automáticamente\n";
    $content .= "// Última actualización: " . date('Y-m-d H:i:s') . "\n";
    $content .= "// No editar manualmente\n\n";
    $content .= "\$posts = " . var_export($data, true) . ";\n";
    file_put_contents($file, $content);
}

function saveControl($file, $data) {
    $content = "<?php\n";
    $content .= "// Control de procesamiento de posts\n";
    $content .= "\$processed_posts = " . var_export($data, true) . ";\n";
    file_put_contents($file, $content);
}

// 5. Preparar datos para la vista (Lista últimos procesados)
// Ordenar processed_posts por timestamp descendente
arsort($processed_posts);
$lastProcessed = array_slice($processed_posts, 0, 10, true);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Manifiesto de Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; padding-top: 40px; }
        .card { margin-bottom: 20px; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); border: none; }
        .card-header { background-color: #fff; border-bottom: 1px solid #eee; font-weight: 600; }
        .stat-box { text-align: center; padding: 15px; background: #fff; border-radius: 8px; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #333; }
        .stat-label { color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .list-group-item { font-size: 0.9rem; border-left: none; border-right: none; }
        .badge-time { font-size: 0.75rem; font-weight: normal; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <h1 class="text-center mb-4">Generador de Manifiesto de Posts</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'info'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Resumen de Estado -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-value text-primary"><?php echo $totalFiles; ?></div>
                            <div class="stat-label">Total Archivos</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-value text-success"><?php echo $totalProcessed; ?></div>
                            <div class="stat-label">Procesados</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="stat-value <?php echo $totalPending > 0 ? 'text-danger' : 'text-muted'; ?>"><?php echo $totalPending; ?></div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Acción -->
                <div class="card text-center p-4 mb-4">
                    <div class="card-body">
                        <?php if ($totalPending > 0): ?>
                            <h5 class="card-title mb-3">Hay <?php echo $totalPending; ?> artículos pendientes</h5>
                            <p class="card-text text-muted mb-4">El procesamiento se realiza en lotes de <?php echo $batchSize; ?> archivos para asegurar la estabilidad del servidor.</p>
                            <form method="POST">
                                <input type="hidden" name="action" value="process_batch">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    Procesar siguiente lote (<?php echo min($batchSize, $totalPending); ?>)
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="py-3">
                                <h3 class="text-success"><i class="fas fa-check-circle"></i> Todo actualizado</h3>
                                <p class="text-muted mt-2">No hay posts nuevos que procesar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($processedInThisBatch)): ?>
                        <div class="card-footer bg-light text-start">
                            <small class="fw-bold">Procesados en esta ejecución:</small>
                            <ul class="list-inline mb-0 mt-1">
                                <?php foreach ($processedInThisBatch as $f): ?>
                                    <li class="list-inline-item badge bg-secondary fw-normal"><?php echo htmlspecialchars($f); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Últimos Procesados -->
                <div class="card">
                    <div class="card-header">
                        Últimos 10 artículos procesados
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if (empty($lastProcessed)): ?>
                            <li class="list-group-item text-muted text-center py-3">No hay registros de procesamiento aún.</li>
                        <?php else: ?>
                            <?php foreach ($lastProcessed as $fname => $timestamp): 
                                $slugRef = basename($fname, '.php');
                                $titleRef = isset($posts[$slugRef]['title']) ? $posts[$slugRef]['title'] : $fname;
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="text-truncate me-3">
                                        <div class="fw-bold text-truncate"><?php echo htmlspecialchars($titleRef); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($fname); ?></small>
                                    </div>
                                    <span class="badge-time text-nowrap">
                                        <?php echo date('d/m/Y H:i:s', $timestamp); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="text-center mt-4 mb-5">
                    <a href="../index.php" class="text-decoration-none text-muted">← Volver a la portada</a>
                </div>

            </div>
        </div>
    </div>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</body>
</html>