<?php

/**
 * Herramienta de Consulta de Historial - Google Indexing API
 * 
 * Permite consultar los metadatos de notificaciones (updates/deletes)
 * que Google API tiene registrados para una URL específica.
 */

// Incluir módulo de autenticación (independiente de sitemap/dashboard)
require_once __DIR__ . '/indexing_api_auth.php';

$result = null;
$error = null;
$urlToCheck = '';

// Procesar petición (GET o POST)
if (isset($_REQUEST['url'])) {
    $urlToCheck = trim($_REQUEST['url']);

    if (!empty($urlToCheck)) {
        // 1. Obtener Token
        $authError = null;
        $token = getIndexingAPIToken(null, $authError);

        if ($token) {
            // 2. Consultar API
            // GET https://indexing.googleapis.com/v3/urlNotifications/metadata?url={url_encoded}
            $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications/metadata?url=' . urlencode($urlToCheck);

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                $error = [
                    'title' => 'Error de Conexión',
                    'message' => $curlError,
                    'code' => null
                ];
            } else {
                $data = json_decode($response, true);

                if ($httpCode == 200) {
                    $result = $data;
                } elseif ($httpCode == 404) {
                    // 404 en getMetadata significa que la URL no tiene notificaciones registradas
                    // o no es conocida por la Indexing API.
                    $error = [
                        'title' => 'Sin Registros',
                        'message' => 'Google indica que no tiene notificaciones registradas para esta URL en la Indexing API.',
                        'code' => 404
                    ];
                } elseif ($httpCode == 403) {
                    // Error de Permisos (Ownership)
                    $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Acceso Denegado';
                    $suggestion = '';

                    if (strpos($msg, 'ownership') !== false) {
                        $suggestion = ' <br><br><strong>Solución:</strong> Debe añadir el email de la cuenta de servicio (ver archivo JSON) como <strong>Propietario</strong> en Google Search Console para esta propiedad.';
                    }

                    $error = [
                        'title' => 'Error de Permisos (403)',
                        'message' => 'Google rechazó la solicitud: ' . $msg . $suggestion,
                        'code' => 403
                    ];
                } else {
                    // Otros errores (400, 429, 500...)
                    $msg = isset($data['error']['message']) ? $data['error']['message'] : 'Error desconocido de la API';
                    $error = [
                        'title' => 'Error de la API',
                        'message' => $msg,
                        'code' => $httpCode
                    ];
                }
            }
        } else {
            // Manejo preciso de errores de autenticación
            $title = 'Error de Autenticación';
            $message = 'No se pudo obtener el token de acceso.';

            if ($authError) {
                if (strpos($authError, 'file not found') !== false) {
                    $title = 'Archivo de Credenciales No Encontrado';
                    $message = "El archivo de credenciales de la cuenta de servicio (<code>meridiano-mbb-4ba1b54b57a9.json</code>) no se encuentra en el directorio <code>gestion</code>, o no es legible.";
                } elseif (strpos($authError, 'Failed to parse') !== false) {
                    $title = 'Credenciales Inválidas';
                    $message = "El archivo de credenciales no tiene un formato JSON válido.";
                } else {
                    // Error técnico general
                    $message .= " Detalle técnico: " . htmlspecialchars($authError);
                }
            } else {
                $message .= " Verifique el archivo de credenciales.";
            }

            $error = [
                'title' => $title,
                'message' => $message,
                'code' => null
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta Indexing API | Meridiano</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 40px;
        }

        .main-card {
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .result-box {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #dee2e6;
            margin-top: 20px;
        }

        .json-dump {
            background: #f1f3f5;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.85em;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card main-card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">🔍 Consulta Historial Indexing API</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Verifica si Google tiene registradas notificaciones (<code>URL_UPDATED</code> o <code>URL_DELETED</code>)
                    para una dirección específica.
                </p>

                <form method="GET" action="" class="row g-3">
                    <div class="col-md-10">
                        <input type="url" name="url" class="form-control" placeholder="https://www.meridiano.com/post/..."
                            required value="<?php echo htmlspecialchars($urlToCheck); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Consultar</button>
                    </div>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-<?php echo ($error['code'] == 404 ? 'warning' : 'danger'); ?> mt-4">
                        <h5><?php echo htmlspecialchars($error['title']); ?></h5>
                        <p class="mb-0">
                            <?php echo htmlspecialchars($error['message']); ?>
                            <?php if ($error['code']): ?>
                                <span class="badge bg-secondary ms-2">HTTP <?php echo $error['code']; ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <?php if ($result): ?>
                    <div class="result-box border-success border-2">
                        <h5 class="text-success mb-3">✅ Datos Encontrados</h5>
                        <p><strong>URL Consultada:</strong> <code><?php echo htmlspecialchars($urlToCheck); ?></code></p>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3 h-100">
                                    <div class="card-header">Última Actualización (URL_UPDATED)</div>
                                    <div class="card-body">
                                        <?php if (isset($result['latestUpdate']['notifyTime'])): ?>
                                            <h5 class="text-primary"><?php echo date('d/m/Y H:i:s', strtotime($result['latestUpdate']['notifyTime'])); ?></h5>
                                            <small class="text-muted">Fecha UTC reportada por Google</small>
                                        <?php else: ?>
                                            <p class="text-muted mb-0">No registrada</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card mb-3 h-100">
                                    <div class="card-header">Última Eliminación (URL_DELETED)</div>
                                    <div class="card-body">
                                        <?php if (isset($result['latestRemove']['notifyTime'])): ?>
                                            <h5 class="text-danger"><?php echo date('d/m/Y H:i:s', strtotime($result['latestRemove']['notifyTime'])); ?></h5>
                                            <small class="text-muted">Fecha UTC reportada por Google</small>
                                        <?php else: ?>
                                            <p class="text-muted mb-0">No registrada</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawJson">
                                Ver respuesta JSON completa
                            </button>
                            <div class="collapse mt-2" id="rawJson">
                                <pre class="json-dump mb-0"><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <div class="card-footer text-muted text-center small">
                Herramienta interna de diagnóstico - Meridiano Béisbol Blog
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>