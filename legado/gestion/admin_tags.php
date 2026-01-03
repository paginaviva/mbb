<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Etiquetas - Meridiano Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .container { max-width: 800px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        textarea { font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">Administrar Etiquetas de Posts</h2>
        
        <form action="../process_tags.php" method="POST">
            <div class="mb-3">
                <label for="tags" class="form-label">Etiquetas (una por línea):</label>
                <textarea class="form-control" id="tags" name="tags" rows="5" required placeholder="Ej: LVBP&#10;Leones del Caracas"></textarea>
            </div>

            <div class="mb-3">
                <label for="urls" class="form-label">URLs de Posts (una por línea):</label>
                <textarea class="form-control" id="urls" name="urls" rows="5" required placeholder="Ej: https://www.meridiano.com/post/ejemplo-1.php&#10;https://www.meridiano.com/post/ejemplo-2.php"></textarea>
                <div class="form-text">Puedes pegar la URL completa o solo el nombre del archivo.</div>
            </div>

            <div class="mb-4">
                <label class="form-label d-block">Acción:</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="action" id="assign" value="assign" checked>
                    <label class="form-check-label" for="assign">Asignar (Agregar)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="action" id="delete" value="delete">
                    <label class="form-check-label" for="delete">Eliminar (Quitar)</label>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Procesar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>
