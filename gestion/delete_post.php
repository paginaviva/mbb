<?php
require_once '../config.php';
require_once '../posts_manifest.php';

$message = '';
$messageType = '';
$postToDelete = null;

// Función para extraer el slug de la URL
function extractSlugFromUrl($url) {
    // Eliminar espacios en blanco
    $url = trim($url);
    
    // Extraer el nombre del archivo sin extensión
    $pattern = '/\/post\/([^\/]+)\.php$/';
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    return false;
}

// Procesar el formulario de búsqueda
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_url'])) {
    $url = $_POST['search_url'];
    $slug = extractSlugFromUrl($url);
    
    if ($slug && isset($posts[$slug])) {
        $postToDelete = [
            'slug' => $slug,
            'title' => $posts[$slug]['title'],
            'url' => $posts[$slug]['url']
        ];
    } else {
        $message = 'No se encontró ningún post con esa URL.';
        $messageType = 'error';
    }
}

// Procesar la eliminación confirmada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $slug = $_POST['slug'];
    $filePath = dirname(__DIR__) . '/post/' . $slug . '.php';
    
    try {
        // Verificar que el archivo existe
        if (!file_exists($filePath)) {
            throw new Exception('El archivo del post no existe.');
        }
        
        // Eliminar el archivo físico
        if (!unlink($filePath)) {
            throw new Exception('No se pudo eliminar el archivo del post.');
        }
        
        // NO ejecutamos generate_manifest automáticamente para evitar timeouts o errores shell
        
        $postTitle = htmlspecialchars($_POST['post_title']);
        $message = "El archivo físico del post \"{$postTitle}\" ha sido eliminado exitosamente.";
        $messageType = 'success';
        
        // Guardamos datos para mostrar botones de acción siguiente
        $deletedPostData = [
            'slug' => $slug,
            'title' => $_POST['post_title']
        ];
        
        $postToDelete = null; // Limpiar formulario de confirmación
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Post - Meridiano Blog</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .message {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .confirmation-box {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .confirmation-box h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .post-info {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .post-info p {
            margin-bottom: 8px;
            color: #333;
        }
        
        .post-info strong {
            color: #667eea;
        }
        
        .post-info .url {
            word-break: break-all;
            color: #666;
            font-size: 13px;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
        }
        
        .button-group .btn {
            flex: 1;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Eliminar Post</h1>
        <p class="subtitle">Ingresa la URL del post que deseas eliminar</p>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($deletedPostData)): ?>
            <!-- Resultado de Eliminación y Pasos Siguientes -->
            <div class="confirmation-box" style="border-color: #28a745;">
                <h2 style="color: #28a745;">✅ Acción Completada</h2>
                <p class="mb-3">El archivo ha sido eliminado. Para completar el proceso de limpieza, por favor ejecute los siguientes pasos en orden:</p>
                
                <div class="action-steps">
                    <!-- Paso 1: Manifiesto -->
                    <div class="step-card" style="background: white; padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid #007bff;">
                        <p style="margin-bottom: 5px;"><strong>Paso 1:</strong> Eliminar entrada del Manifiesto</p>
                        <form action="generate_manifest.php" method="POST" target="_blank">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" name="delete_slug" value="<?php echo htmlspecialchars($deletedPostData['slug']); ?>">
                            <button type="submit" class="btn btn-primary" style="font-size: 14px; padding: 8px 16px;">
                                🛠️ Actualizar Manifiesto
                            </button>
                        </form>
                    </div>

                    <!-- Paso 2: Sitemap -->
                    <div class="step-card" style="background: white; padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid #17a2b8;">
                        <p style="margin-bottom: 5px;"><strong>Paso 2:</strong> Actualizar Sitemap e IndexNow</p>
                        <a href="generate_sitemap.php" target="_blank" class="btn btn-primary" style="background: #17a2b8; font-size: 14px; padding: 8px 16px; text-decoration: none; display: inline-block;">
                            🌐 Regenerar Sitemap
                        </a>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="delete_post.php" class="btn btn-secondary">← Volver a eliminar otro post</a>
                </div>
            </div>

        <?php elseif ($postToDelete): ?>
            <!-- Formulario de confirmación -->
            <div class="confirmation-box">
                <h2>⚠️ Confirmar eliminación</h2>
                <div class="post-info">
                    <p><strong>Título:</strong> <?php echo htmlspecialchars($postToDelete['title']); ?></p>
                    <p><strong>URL:</strong></p>
                    <p class="url"><?php echo htmlspecialchars($postToDelete['url']); ?></p>
                </div>
                <div class="warning">
                    <strong>⚠️ Advertencia:</strong> Esta acción no se puede deshacer. El archivo será eliminado permanentemente.
                </div>
                <form method="POST">
                    <input type="hidden" name="slug" value="<?php echo htmlspecialchars($postToDelete['slug']); ?>">
                    <input type="hidden" name="post_title" value="<?php echo htmlspecialchars($postToDelete['title']); ?>">
                    <div class="button-group">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">
                            ✓ Confirmar eliminación
                        </button>
                        <a href="delete_post.php" class="btn btn-secondary" style="text-decoration: none; text-align: center; line-height: 1.5;">
                            ✕ Cancelar
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Formulario de búsqueda -->
            <form method="POST">
                <div class="form-group">
                    <label for="search_url">URL del post:</label>
                    <input 
                        type="text" 
                        id="search_url" 
                        name="search_url" 
                        placeholder="https://www.meridiano.com/post/nombre-del-post.php"
                        required
                        value="<?php echo isset($_POST['search_url']) ? htmlspecialchars($_POST['search_url']) : ''; ?>"
                    >
                </div>
                <button type="submit" class="btn btn-primary">
                    🔍 Buscar post
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
