<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Post - Meridiano Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container h1 {
            color: #212529;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 700;
        }
        .form-container p {
            color: #6c757d;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .form-group label {
            font-weight: 600;
            color: #212529;
            margin-bottom: 10px;
            display: block;
        }
        textarea {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            min-height: 510px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            width: 100%;
            box-sizing: border-box;
        }
        textarea:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .btn-submit {
            background-color: #0d6efd;
            color: white;
            padding: 10px 30px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            color: #004085;
            font-size: 14px;
        }
        .format-example {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
            font-size: 12px;
            color: #333;
            max-height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Crear Nuevo Post</h1>
        <div class="info-box">
            <p><strong>Instrucciones:</strong> Pega el bloque completo [DATOS_DOCUMENTO] en el campo de abajo y haz clic en "Crear Post". <br><span class="text-danger">IMPORTANTE:</span> Tras crear el post, debes ir al panel principal y ejecutar "Manifest Generator" para que aparezca en el listado.</p>
        </div>
        
        <form action="procesar-post.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="datos_documento">Bloque [DATOS_DOCUMENTO]:</label>
                <textarea 
                    name="datos_documento" 
                    id="datos_documento" 
                    required
                    placeholder="Pega aquí el bloque completo [DATOS_DOCUMENTO] incluyendo todas las secciones: [HEAD], [CABECERA_VISUAL], [CONTENIDO], [CATEGORIAS], [ETIQUETAS]">
</textarea>
            </div>
            
            <button type="submit" class="btn-submit">Crear Post</button>
        </form>

        <div class="format-example">
            <strong>Formato esperado (ejemplo):</strong>
            <pre>[DATOS_DOCUMENTO]
NombreArchivoHTML: ejemplo-post.php
UrlPublica: https://www.meridiano.com/ejemplo-post

[HEAD]
TituloDocumento: Título del post aquí
MetaDescription: Descripción meta aquí
OgType: article
OgImage: https://www.meridiano.com/assets/img/imagen.webp
OgUrl: https://www.meridiano.com/ejemplo-post
OgSiteName: Meridiano Blog de Béisbol Caribeño
TwitterCard: summary_large_image
TwitterTitle: Título Twitter aquí
TwitterDescription: Descripción Twitter aquí
TwitterImage: https://www.meridiano.com/assets/img/imagen.webp
AutorMeta: Autor aquí

[CABECERA_VISUAL]
ImagenFondo: assets/img/post-bg.webp
TituloVisible: Título visible aquí
SubtituloVisible: Subtítulo visible aquí
AutorVisible: Autor visible aquí
FechaVisible: (No se usa - se aplica fecha actual)

[CONTENIDO]
&lt;p&gt;Contenido HTML aquí&lt;/p&gt;

[CATEGORIAS]
Categoría 1, Categoría 2

[ETIQUETAS]
Etiqueta 1, Etiqueta 2, Etiqueta 3
]

NOTA: El sistema utilizará automáticamente:
- Fecha actual para el post
- Autor: "Redacción Meridiano BB"
- El index.php lee automáticamente del manifiesto (posts_manifest.php)</pre>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
