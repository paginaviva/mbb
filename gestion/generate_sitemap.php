<?php

// ================= CONFIG =================
$baseUrl   = "https://www.meridiano.com";  // <-- CAMBIA ESTO a tu dominio (sin / final)
$rootDir   = dirname(__DIR__); // Parent directory since we're in /gestion/
$indexFile = $rootDir . "/index.php";
// ==========================================

// Array para almacenar estadísticas
$stats = [
    'urls_added' => [],
    'priority_count' => [],
    'total_urls' => 0,
    'indexnow' => [
        'submitted' => false,
        'urls_count' => 0,
        'http_code' => null,
        'message' => '',
        'urls_list' => []
    ],
    'indexing_api' => [
        'authenticated' => false,
        'new_urls' => 0,
        'updated_urls' => 0,
        'deleted_urls' => 0,
        'success_count' => 0,
        'error_count' => 0,
        'message' => '',
        'details' => []
    ]
];

// IndexNow configuration
$indexnowKey = 'e48a97f544db4ca0a331f4c830ccf202';
$indexnowKeyLocation = 'https://www.meridiano.com/e48a97f544db4ca0a331f4c830ccf202.txt';
$indexnowHost = 'www.meridiano.com';

// Google Indexing API - Include authentication module
require_once __DIR__ . '/indexing_api_auth.php';

// Convierte "17 de noviembre de 2025" → "2025-11-17"
function spanishDateToISO($dateStr)
{
    $dateStr = trim($dateStr);

    $months = [
        'enero'      => '01',
        'febrero'    => '02',
        'marzo'      => '03',
        'abril'      => '04',
        'mayo'       => '05',
        'junio'      => '06',
        'julio'      => '07',
        'agosto'     => '08',
        'septiembre' => '09',
        'setiembre'  => '09',
        'octubre'    => '10',
        'noviembre'  => '11',
        'diciembre'  => '12',
    ];

    if (preg_match('/(\d{1,2})\s+de\s+([a-záéíóúñ]+)\s+de\s+(\d{4})/i', $dateStr, $m)) {
        $day       = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        $monthName = strtolower($m[2]);
        $year      = $m[3];

        $month = isset($months[$monthName]) ? $months[$monthName] : '01';

        return $year . '-' . $month . '-' . $day;
    }

    return null;
}

/**
 * Parse sitemap.xml and extract URLs with their lastmod dates
 */
function parseSitemap($sitemapPath)
{
    if (!file_exists($sitemapPath)) {
        return [];
    }

    $urls = [];
    $xml = @simplexml_load_file($sitemapPath);

    if ($xml === false) {
        return [];
    }

    foreach ($xml->url as $urlNode) {
        $loc = (string)$urlNode->loc;
        $lastmod = (string)$urlNode->lastmod;
        $urls[$loc] = $lastmod;
    }

    return $urls;
}

/**
 * Detect new, modified OR DELETED URLs by comparing old and new sitemaps
 */
function detectChangedUrls($oldUrls, $newUrls)
{
    $changed = [];

    // 1. Detect Modified or New
    foreach ($newUrls as $url => $lastmod) {
        // URL is new or has different lastmod date
        if (!isset($oldUrls[$url]) || $oldUrls[$url] !== $lastmod) {
            $changed[] = $url;
        }
    }

    // 2. Detect Deleted (Present in OLD but NOT in NEW)
    foreach ($oldUrls as $url => $lastmod) {
        if (!isset($newUrls[$url])) {
            $changed[] = $url;
        }
    }

    return array_unique($changed);
}

/**
 * Submit URLs to IndexNow API
 */
function submitToIndexNow($urls, $host, $key, $keyLocation)
{
    if (empty($urls)) {
        return [
            'success' => true,
            'http_code' => null,
            'message' => 'No hay URLs nuevas o modificadas para enviar',
            'urls_count' => 0
        ];
    }

    $endpoint = 'https://api.indexnow.org/IndexNow';

    $payload = [
        'host' => $host,
        'key' => $key,
        'keyLocation' => $keyLocation,
        'urlList' => array_values($urls) // Ensure numeric array
    ];

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($jsonPayload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Interpret HTTP response codes
    $messages = [
        200 => '✅ URLs enviadas exitosamente a IndexNow',
        202 => '✅ URLs aceptadas para procesamiento (IndexNow las procesará de forma asíncrona)',
        400 => '❌ Error: Formato inválido en la solicitud',
        403 => '❌ Error: API key no válida o no encontrada',
        422 => '❌ Error: URLs no pertenecen al dominio o key no coincide',
        429 => '⚠️ Error: Demasiadas solicitudes (posible spam detectado)'
    ];

    if ($curlError) {
        return [
            'success' => false,
            'http_code' => null,
            'message' => '❌ Error de conexión: ' . $curlError,
            'urls_count' => count($urls)
        ];
    }

    $message = isset($messages[$httpCode]) ? $messages[$httpCode] : "⚠️ Código HTTP desconocido: $httpCode";

    return [
        'success' => ($httpCode == 200 || $httpCode == 202),
        'http_code' => $httpCode,
        'message' => $message,
        'urls_count' => count($urls)
    ];
}

/**
 * Submit URL to Google Indexing API
 * 
 * @param string $url URL to notify
 * @param string $type URL_UPDATED or URL_DELETED
 * @param string $accessToken OAuth 2.0 Access Token
 * @return array Result with http_code, message, success status
 */
function submitToIndexingAPI($url, $type, $accessToken)
{
    $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

    $payload = [
        'url' => $url,
        'type' => $type
    ];

    $jsonPayload = json_encode($payload);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken,
        'Content-Length: ' . strlen($jsonPayload)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return [
            'success' => false,
            'http_code' => null,
            'message' => 'Error de conexión: ' . $curlError,
            'response' => null
        ];
    }

    // Success codes: 200 is the main one.
    $success = ($httpCode == 200);
    $responseData = json_decode($response, true);

    $message = $success ? 'Notificación aceptada' : 'Error en notificación';

    // Better error message extraction from Google API response
    if (!$success && isset($responseData['error']['message'])) {
        $message = $responseData['error']['message'];
    } elseif (!$success && isset($responseData['error']['status'])) {
        $message = $responseData['error']['status'];
    }

    return [
        'success' => $success,
        'http_code' => $httpCode,
        'message' => $message,
        'response' => $responseData
    ];
}

/**
 * Log Indexing API action
 */
function logIndexingAction($logFile, $url, $operationType, $notificationType, $httpCode, $message)
{
    $timestamp = date('Y-m-d H:i:s');
    $status = ($httpCode == 200) ? 'SUCCESS' : 'FAILED';
    if ($httpCode === null) $httpCode = 'N/A';

    // Format: [YYYY-MM-DD HH:MM:SS] Operation: NEW|UPDATED|DELETED | Type: X | URL: Y | HTTP Code: Z | Status: S | Message: M
    $line = sprintf(
        "[%s] Operation: %s | Type: %s | URL: %s | HTTP Code: %s | Status: %s | Message: %s\n",
        $timestamp,
        $operationType,
        $notificationType,
        $url,
        $httpCode,
        $status,
        str_replace(["\r", "\n"], " ", $message)
    );
    file_put_contents($logFile, $line, FILE_APPEND);
}



// -----------------------------------------------------------
// 1) Leer index.php y extraer href + fecha de cada post
// -----------------------------------------------------------

$datesByPath = [];

if (!file_exists($indexFile)) {
    die("No se encontró index.php en: " . $indexFile);
}

$content = file_get_contents($indexFile);

// Patrón para cosas tipo:
// <a href="/post/xxx.php">...</a>
// ...
// on 17 de noviembre de 2025
$pattern = '/href="([^"]+)"[^>]*>.*?<\/a>.*?on\s+([^<]+)/is';

if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $hrefRaw = $match[1];  // ej: /post/semana-5-resumen.php
        $dateRaw = $match[2];  // ej: 17 de noviembre de 2025

        $href     = trim($hrefRaw);
        $dateText = trim($dateRaw);

        // Normalizamos ruta relativa: "post/xxx.php"
        $relativePath = ltrim($href, "/");

        $isoDate = spanishDateToISO($dateText);

        if ($isoDate !== null) {
            $datesByPath[$relativePath] = $isoDate;
        }
    }
}


// -----------------------------------------------------------
// 2) Obtener SOLO index.php y archivos dentro de /post/
// -----------------------------------------------------------

function getTargetFiles($rootDir)
{
    $files = [];

    // Incluir siempre index.php si existe
    $indexPath = $rootDir . '/index.php';
    if (file_exists($indexPath)) {
        $files[] = $indexPath;
    }

    // Incluir solo archivos dentro de /post/
    $postDir = $rootDir . '/post';
    if (is_dir($postDir)) {
        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $postDir,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            )
        );

        foreach ($rii as $file) {
            /** @var SplFileInfo $file */
            if ($file->isDir()) {
                continue;
            }

            $path = $file->getPathname();

            // Si quieres solo .php, deja así:
            if (preg_match('/\.php$/i', $path)) {
                $files[] = $path;
            }

            // Si quisieras también .html:
            // if (preg_match('/\.(php|html)$/i', $path)) { ... }
        }
    }

    return $files;
}

$files = getTargetFiles($rootDir);


// -----------------------------------------------------------
// 3) Generar sitemap.xml (solo index.php + /post/)
// -----------------------------------------------------------

$urlDataList = [];

foreach ($files as $file) {
    // Ruta relativa respecto a la raíz
    $relative = str_replace($rootDir . DIRECTORY_SEPARATOR, '', $file);
    $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

    $url = rtrim($baseUrl, '/') . '/' . $relative;

    // Lógica de fechas y prioridades
    if ($relative === "index.php") {
        // REGLA 1: index.php se modifica diario -> fecha actual
        $lastmod = date("Y-m-d");
        $dateSource = "index.php (forced daily)";
        $priority = "1.0";
        $changefreq = "daily";
    } else {
        // Para otros archivos
        if (isset($datesByPath[$relative])) {
            $lastmod = $datesByPath[$relative];
            $dateSource = "index.php";
        } else {
            $lastmod = date("Y-m-d", filemtime($file));
            $dateSource = "filemtime";
        }
        $priority = "0.8";
        $changefreq = "never";
    }

    $urlDataList[] = [
        'url'        => $url,
        'relative'   => $relative,
        'lastmod'    => $lastmod,
        'changefreq' => $changefreq,
        'priority'   => $priority,
        'date_source' => $dateSource
    ];
}

// REGLA 2: Ordenar (index.php primero, luego fecha DESC)
usort($urlDataList, function ($a, $b) {
    // 1. index.php siempre de primero
    if ($a['relative'] === 'index.php') return -1;
    if ($b['relative'] === 'index.php') return 1;

    // 2. Fecha descendente (más reciente primero)
    if ($a['lastmod'] == $b['lastmod']) {
        return 0;
    }
    return ($a['lastmod'] > $b['lastmod']) ? -1 : 1;
});

// Generar XML
$sitemap  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$sitemap .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

foreach ($urlDataList as $item) {
    $sitemap .= "  <url>\n";
    $sitemap .= "    <loc>" . htmlspecialchars($item['url'], ENT_XML1) . "</loc>\n";
    $sitemap .= "    <lastmod>{$item['lastmod']}</lastmod>\n";
    $sitemap .= "    <changefreq>{$item['changefreq']}</changefreq>\n";
    $sitemap .= "    <priority>{$item['priority']}</priority>\n";
    $sitemap .= "  </url>\n";

    // Guardar estadísticas
    $stats['urls_added'][] = [
        'url' => $item['url'],
        'lastmod' => $item['lastmod'],
        'priority' => $item['priority'],
        'date_source' => $item['date_source']
    ];

    if (!isset($stats['priority_count'][$item['priority']])) {
        $stats['priority_count'][$item['priority']] = 0;
    }
    $stats['priority_count'][$item['priority']]++;
    $stats['total_urls']++;
}

$sitemap .= "</urlset>\n";

// -----------------------------------------------------------
// 3.5) IndexNow Integration: Detect and submit changed URLs
// -----------------------------------------------------------

$sitemapPath = $rootDir . "/sitemap.xml";
$oldSitemapPath = $rootDir . "/sitemap.old.xml";

// Backup old sitemap if it exists
$oldUrls = [];
if (file_exists($sitemapPath)) {
    // Parse old sitemap before overwriting
    $oldUrls = parseSitemap($sitemapPath);
    // Create backup
    @copy($sitemapPath, $oldSitemapPath);
}

// Write new sitemap
file_put_contents($sitemapPath, $sitemap);

// Parse new sitemap
$newUrls = [];
foreach ($urlDataList as $item) {
    $newUrls[$item['url']] = $item['lastmod'];
}

// Detect and classify URLs (new, updated, deleted)
$newUrlsList = [];
$updatedUrlsList = [];
$deletedUrlsList = [];

// 1. Detect new and updated URLs
foreach ($newUrls as $url => $lastmod) {
    if (!isset($oldUrls[$url])) {
        // URL is new
        $newUrlsList[] = $url;
    } elseif ($oldUrls[$url] !== $lastmod) {
        // URL exists but lastmod changed
        $updatedUrlsList[] = $url;
    }
}

// 2. Detect deleted URLs
foreach ($oldUrls as $url => $lastmod) {
    if (!isset($newUrls[$url])) {
        $deletedUrlsList[] = $url;
    }
}

// Combined list for IndexNow (all changes)
$changedUrls = array_merge($newUrlsList, $updatedUrlsList, $deletedUrlsList);

// Submit to IndexNow
if (!empty($changedUrls)) {
    $indexnowResult = submitToIndexNow(
        $changedUrls,
        $indexnowHost,
        $indexnowKey,
        $indexnowKeyLocation
    );

    $stats['indexnow'] = [
        'submitted' => true,
        'urls_count' => $indexnowResult['urls_count'],
        'http_code' => $indexnowResult['http_code'],
        'message' => $indexnowResult['message'],
        'urls_list' => $changedUrls,
        'success' => $indexnowResult['success']
    ];
} else {
    $stats['indexnow'] = [
        'submitted' => false,
        'urls_count' => 0,
        'http_code' => null,
        'message' => 'ℹ️ No hay URLs nuevas o modificadas para notificar',
        'urls_list' => [],
        'success' => true
    ];
}

// -----------------------------------------------------------
// 3.6) Google Indexing API Integration
// -----------------------------------------------------------

// Obtain OAuth 2.0 access token
$indexingApiToken = getIndexingAPIToken();

if ($indexingApiToken) {
    $stats['indexing_api']['authenticated'] = true;
    $stats['indexing_api']['message'] = '✅ Autenticación exitosa con Google Indexing API';

    // Store classified URL counts for summary
    $stats['indexing_api']['new_urls'] = count($newUrlsList);
    $stats['indexing_api']['updated_urls'] = count($updatedUrlsList);
    $stats['indexing_api']['deleted_urls'] = count($deletedUrlsList);

    $logFile = __DIR__ . '/indexing_api_sitemap.log';

    // Process NEW URLs
    foreach ($newUrlsList as $url) {
        $result = submitToIndexingAPI($url, 'URL_UPDATED', $indexingApiToken);
        logIndexingAction($logFile, $url, 'NEW', 'URL_UPDATED', $result['http_code'], $result['message']);
        if ($result['success']) $stats['indexing_api']['success_count']++;
        else $stats['indexing_api']['error_count']++;
    }

    // Process UPDATED URLs
    foreach ($updatedUrlsList as $url) {
        $result = submitToIndexingAPI($url, 'URL_UPDATED', $indexingApiToken);
        logIndexingAction($logFile, $url, 'UPDATED', 'URL_UPDATED', $result['http_code'], $result['message']);
        if ($result['success']) $stats['indexing_api']['success_count']++;
        else $stats['indexing_api']['error_count']++;
    }

    // Process DELETED URLs
    foreach ($deletedUrlsList as $url) {
        $result = submitToIndexingAPI($url, 'URL_DELETED', $indexingApiToken);
        logIndexingAction($logFile, $url, 'DELETED', 'URL_DELETED', $result['http_code'], $result['message']);
        if ($result['success']) $stats['indexing_api']['success_count']++;
        else $stats['indexing_api']['error_count']++;
    }
} else {
    $stats['indexing_api']['authenticated'] = false;
    $stats['indexing_api']['message'] = '❌ Error de autenticación con Google Indexing API';
}

// -----------------------------------------------------------
// 4) Mostrar resultados detallados
// -----------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap Generado - Meridiano Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px 0;
        }

        .result-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .success-header {
            color: #28a745;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 700;
        }

        .stats-box {
            background-color: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .url-table {
            font-size: 13px;
        }

        .url-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .badge-priority {
            font-size: 11px;
        }

        .date-source {
            font-size: 11px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="result-container">
        <div class="success-header">✅ Sitemap generado exitosamente</div>

        <div class="alert alert-success mb-4">
            <strong>✅ Proceso completado:</strong> El sitemap.xml ha sido generado con <?php echo $stats['total_urls']; ?> URLs.
        </div>

        <div class="stats-box">
            <h5>📊 Resumen de Ejecución</h5>
            <ul class="mb-0">
                <li><strong>Total de URLs:</strong> <?php echo $stats['total_urls']; ?></li>
                <li><strong>Archivo generado:</strong> sitemap.xml</li>
                <li><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></li>
            </ul>
        </div>

        <div class="mb-4">
            <h5>🎯 URLs por Prioridad</h5>
            <div class="row">
                <?php foreach ($stats['priority_count'] as $priority => $count): ?>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <strong>Prioridad <?php echo $priority; ?>:</strong> <?php echo $count; ?> URL(s)
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- IndexNow Results Section -->
        <div class="mb-4">
            <h5>🔔 Notificación a Motores de Búsqueda (IndexNow)</h5>

            <?php if ($stats['indexnow']['submitted']): ?>
                <div class="alert alert-<?php echo $stats['indexnow']['success'] ? 'success' : 'danger'; ?>">
                    <strong><?php echo $stats['indexnow']['message']; ?></strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>URLs notificadas:</strong> <?php echo $stats['indexnow']['urls_count']; ?></li>
                        <?php if ($stats['indexnow']['http_code']): ?>
                            <li><strong>Código HTTP:</strong> <?php echo $stats['indexnow']['http_code']; ?></li>
                        <?php endif; ?>
                        <li><strong>Endpoint:</strong> api.indexnow.org</li>
                    </ul>
                </div>

                <?php if (!empty($stats['indexnow']['urls_list'])): ?>
                    <div class="card">
                        <div class="card-header bg-light">
                            <strong>📋 URLs enviadas a IndexNow</strong>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0" style="font-size: 12px;">
                                <?php foreach ($stats['indexnow']['urls_list'] as $url): ?>
                                    <li><?php echo htmlspecialchars($url); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-info">
                    <strong><?php echo $stats['indexnow']['message']; ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <h5>🔍 Notificación a Google Indexing API</h5>

            <?php if ($stats['indexing_api']['authenticated']): ?>
                <div class="alert alert-info">
                    <strong><?php echo $stats['indexing_api']['message']; ?></strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Estado:</strong> Autenticado correctamente</li>
                        <li><strong>Token:</strong> OAuth 2.0 (Service Account)</li>
                    </ul>
                </div>

                <div class="stats-box bg-light border-start border-warning">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-success"><?php echo $stats['indexing_api']['success_count']; ?></h3>
                            <small class="text-muted">Peticiones Exitosas</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger"><?php echo $stats['indexing_api']['error_count']; ?></h3>
                            <small class="text-muted">Peticiones Fallidas</small>
                        </div>
                        <div class="col-md-6 text-start">
                            <ul class="mb-0 small">
                                <li><strong>Nuevas notificadas:</strong> <?php echo $stats['indexing_api']['new_urls']; ?></li>
                                <li><strong>Actualizadas notificadas:</strong> <?php echo $stats['indexing_api']['updated_urls']; ?></li>
                                <li><strong>Eliminadas notificadas:</strong> <?php echo $stats['indexing_api']['deleted_urls']; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php if ($stats['indexing_api']['error_count'] > 0): ?>
                    <div class="alert alert-warning">
                        <small>⚠️ Se registraron fallos. Revise el archivo de log <code>gestion/indexing_api_sitemap.log</code> para más detalles.</small>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-danger">
                    <strong><?php echo $stats['indexing_api']['message']; ?></strong>
                    <p class="mb-0 small mt-1">Verifique el archivo de credenciales y los permisos de la cuenta de servicio.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <h5>📋 Contenido Agregado al Sitemap</h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover url-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>URL</th>
                            <th>Última Modificación</th>
                            <th>Prioridad</th>
                            <th>Fuente Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['urls_added'] as $index => $urlData): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($urlData['url']); ?></small>
                                </td>
                                <td><?php echo $urlData['lastmod']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $urlData['priority'] == '1.0' ? 'success' : 'primary'; ?> badge-priority">
                                        <?php echo $urlData['priority']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="date-source">
                                        <?php echo $urlData['date_source'] == 'index.php' ? '📅 index.php' : '📁 filemtime'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="../sitemap.xml" class="btn btn-primary" target="_blank">Ver sitemap.xml</a>
        <a href="../index.php" class="btn btn-secondary">Volver al inicio</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>