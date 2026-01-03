<?php
include '../config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gestión - Meridiano Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .tool-card {
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .tool-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        .section-title {
            margin-top: 30px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0d6efd;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1>Panel de Gestión</h1>
            <div>
                <a href="../index.php" class="btn btn-outline-primary me-2">← Volver al blog</a>
                <a href="../index.php" target="_blank" class="btn btn-primary">Abrir blog ↗</a>
            </div>
        </div>
        <p class="text-muted mb-4">Meridiano Béisbol Blog - Herramientas Administrativas</p>
        
        <h3 class="section-title">Gestión de Contenido</h3>
        <div class="row">
            <!-- 1. Crear Nuevo Post -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">✍️ Crear Nuevo Post</h5>
                        <p class="card-text">Formulario para crear artículos mediante formato estructurado [DATOS_DOCUMENTO].</p>
                        <a href="crear-post-admin.php" target="_blank" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <!-- 2. Manifest Generator -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">⚡ Manifest Generator</h5>
                        <p class="card-text">Herramienta para actualizar el manifiesto de posts por lotes (evita timeouts).</p>
                        <a href="generate_manifest.php" target="_blank" class="btn btn-success">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- 3. Generar Sitemap -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">🗺️ Generar Sitemap</h5>
                        <p class="card-text">Generar sitemap.xml y notificar cambios a motores de búsqueda vía IndexNow.</p>
                        <a href="generate_sitemap.php" target="_blank" class="btn btn-warning">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- 4. Kanban Destacados -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">📋 Kanban Destacados</h5>
                        <p class="card-text">Gestor visual tipo kanban para artículos destacados en portada con drag & drop.</p>
                        <a href="kanban_destacados.php" target="_blank" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- 5. Eliminar Post -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">🗑️ Eliminar Post</h5>
                        <p class="card-text">Eliminar artículos existentes del blog de forma segura.</p>
                        <a href="delete_post.php" target="_blank" class="btn btn-danger">Acceder</a>
                    </div>
                </div>
            </div>
        </div>
        
        <h3 class="section-title">Gestión de Etiquetas</h3>
        <div class="row">
            <!-- 1. Gestión Avanzada de Etiquetas (Nueva) -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">🏷️ Gestión Avanzada de Etiquetas</h5>
                        <p class="card-text">Añadir o eliminar categorías y etiquetas de artículos individuales.</p>
                        <a href="admin_tags_gestion.php" target="_blank" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>

            <!-- 2. Administrar Etiquetas (Legacy) -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">🏷️ Administrar Etiquetas (Masivo)</h5>
                        <p class="card-text">Gestión de etiquetas y categorías del blog (modo texto).</p>
                        <a href="admin_tags.php" target="_blank" class="btn btn-info">Acceder</a>
                    </div>
                </div>
            </div>
            
            <!-- 3. Listar Etiquetas -->
            <div class="col-md-6">
                <div class="card tool-card">
                    <div class="card-body">
                        <h5 class="card-title">📋 Listar Etiquetas</h5>
                        <p class="card-text">Ver todas las etiquetas utilizadas en los artículos del blog.</p>
                        <a href="list_tags.php" target="_blank" class="btn btn-secondary">Acceder</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
